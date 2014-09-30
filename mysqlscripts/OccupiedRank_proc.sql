DELIMITER $$

CREATE PROCEDURE `OccupiedRank`(uidEnd int, uRank smallint ,foughtRank smallint)
BEGIN
    
    START TRANSACTION;
    SET autocommit = 0;
    
    SET @end_board = 1000;
        
    SELECT * FROM Occupy_OccupyingBigBoard WHERE Rank <= uRank AND Rank >= foughtRank FOR UPDATE;
    
    if uRank >= @end_board then 
            set @uId = uidEnd;
            set uRank = @end_board;
    else 
        select Uid from Occupy_OccupyingBigBoard where Rank = uRank into @uId;
    end if; 
        
    insert into Occupy_OccupyingBigBoard(Rank, Uid, OccupiedTime)
        select Rank + 1, Uid, occupiedTime from Occupy_OccupyingBigBoard as oocbb where Rank < uRank and Rank >= foughtRank
        on duplicate key update Uid = oocbb.Uid;     
        
    update Occupy_OccupyingBigBoard set Uid = @uId where Rank = foughtRank;       
    
    COMMIT;
END
