-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE `AttackBoss`(_uid int, _day varchar(45) ,_time int, _bossid int, _damage int , OUT _vitalitytotal bigint, OUT _lasthit boolean,out _error int, out _DamTotal bigint)
BEGIN

-- start transuction 
start transaction ;

    set @vboss      = 0;
    set @boss_id    = 0;
    set @error      = 0 ;
    set @lasthit    = false ;
    set @DamTotal   = 0 ;
    
    
    
    -- tim dung boss can tru mau
    set @MatchNumBoss = '' ;
    set @MatchNumBoss = concat(to_days(_day),'_', _time,'_',_bossid) ;
    
    select VitalityTotal, BossId into @vboss,@boss_id from BossServer_Boss where to_days(_day) = Date and _time = Time and _bossid = BossId for update;
    -- select VitalityTotal, BossId into @vboss,@boss_id from BossServer_Boss where @MatchNumBoss = MatchNum for update;
    

    if @boss_id <=0 then
        set @error = 138 ; -- boss chua ton tai
    else
        if @vboss > 0 then
            set @vboss  = @vboss - _damage ;
            if @vboss < 0 then
               set @lasthit = true ;
               -- update last hit user
               update BossServer_Boss set LastHitUser = _uid  where to_days(_day) = Date and _time = Time and _bossid = BossId ;
               -- update ignore BossServer_Boss set LastHitUser = _uid  where @MatchNumBoss = MatchNum ;
               
            end if;
            
            -- update lai mau cho boss;
            update BossServer_Boss set VitalityTotal = VitalityTotal - _damage where to_days(_day) = Date and _time = Time and _bossid = BossId ;
            -- update ignore BossServer_Boss set VitalityTotal = @vboss  where @MatchNumBoss = MatchNum ; 
            
             -- cap nhan dam danh boss vao bang attach
             
            set @matchnum = '';
            set @matchnum = concat(to_days(_day),'_', _time,'_', _uid,'_', _bossid) ;
             
             
            select DamageTotal into @DamTotal from BossServer_Attack where UserId = _uid and to_days(_day) = Date and _time = Time and _bossid = BossId ; 
            -- select DamageTotal into @DamTotal from BossServer_Attack where @matchnum = MatchNum ; 
            if @DamTotal <=0 then
                -- insert 
                set @DamTotal = _damage ;
                
                -- set @error = 500 ; 
                -- insert attack boss 
                insert into BossServer_Attack(Date,Time,UserId,BossId,DamageTotal) values(to_days(_day),_time,_uid,_bossid,_damage);
            else 
                -- update attack boss 
                set @DamTotal = @DamTotal + _damage ;
                update BossServer_Attack set DamageTotal = @DamTotal  where UserId = _uid and to_days(_day) = Date and _time = Time and _bossid = BossId ; 
                -- update ignore BossServer_Attack set DamageTotal = @DamTotal  where  @matchnum = MatchNum ;
                -- set @error = 501 ;
            end if ;    
            
        else
            set @error = 139 ; -- boss da chet 
        end if;
    
    end if ;
    
    
    -- return ra ngoai 
    set _vitalitytotal  = @vboss ;
    set _error          = @error ;
    set _lasthit        = @lasthit ;
    set _DamTotal       = @DamTotal ;
    
    -- end transaction 
    commit ;
    
END
