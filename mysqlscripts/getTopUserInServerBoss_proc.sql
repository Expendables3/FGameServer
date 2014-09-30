-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE `getTopUserInServerBoss`(_day varchar(45),_time int ,_bossid int)
BEGIN
   
    if _day != '' and _time > 0 and _time < 24 and _bossid > 0 and _bossid < 4 then
        select UserId,DamageTotal from BossServer_Attack where to_days(_day) = Date and _time = Time and _bossid = BossId ORDER BY DamageTotal Desc limit 10; -- Asc
        
    end if;
    
end
