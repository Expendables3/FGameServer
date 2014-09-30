delimiter $$

CREATE TABLE `Occupy_OccupyingBigBoard` (
  `Rank` smallint(5) unsigned NOT NULL,
  `Uid` int(11) NOT NULL,
  `OccupiedTime` int(11) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`Rank`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$
