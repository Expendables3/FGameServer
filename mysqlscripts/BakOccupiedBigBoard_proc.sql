DELIMITER $$

CREATE PROCEDURE `BakOccupiedBigBoard`(rankedTime int)
BEGIN
START TRANSACTION;
 
insert into Occupy_OccupiedBigBoardBak (Rank, Uid, RankTime)
  select Rank, Uid, rankedTime  from Occupy_OccupyingBigBoard order by Rank ASC limit 1000  ;

insert into Occupy_TempBigBoard(Rank, Uid, RankTime)
    select Rank, Uid, rankedTime  from Occupy_OccupyingBigBoard order by Rank ASC limit 1000  ;

delete from Occupy_OccupyingBigBoard;

COMMIT;
END