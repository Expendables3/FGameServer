delimiter $$

CREATE TABLE `Occupy_OccupiedBigBoardBak` (
  `Rank` smallint(5) unsigned NOT NULL DEFAULT '1',
  `Uid` int(11) NOT NULL,
  `RankTime` int(11) NOT NULL,
  `OccupiedTime` int(11) DEFAULT NULL,
  PRIMARY KEY (`Rank`,`RankTime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$