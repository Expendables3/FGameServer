-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE `CreateBoss`(_day varchar(45) ,_time int, _bossid int,_vitalitytotal Bigint, out _error int)
BEGIN

         -- insert  
        set @error      = 0 ;
        set @boss_id    = 0 ;
        set @matchnum   = '';
        set @matchnum   = concat(to_days(_day),'_', _time,'_',_bossid) ;
        
        if _day != '' and _time > 0 and _time < 240000 and _bossid > 0 and _bossid < 4 and _vitalitytotal > 0 then
            -- select BossId into @boss_id from BossServer_Boss where MatchNum = @matchnum ;
            select BossId into @boss_id from BossServer_Boss where to_days(_day) = Date and _time = Time and _bossid = BossId ;
            if @boss_id =0 then
                insert into BossServer_Boss(Date,Time,BossId,VitalityTotal,LastHitUser) values(to_days(_day),_time,_bossid,_vitalitytotal,null);
            else
                set @error = 137 ; -- boss da ton tai
            end if ;
        else
            set @error = 6 ; -- sai du lieu dau vao
        end if;
        
        set _error := @error ;
END
