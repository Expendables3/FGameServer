delimiter $$

CREATE TABLE `BossServer_Attack` (
  `Date` int(11) NOT NULL,
  `Time` mediumint(9) NOT NULL,
  `UserId` int(11) NOT NULL,
  `BossId` mediumint(9) NOT NULL,
  `DamageTotal` bigint(20) NOT NULL,
  PRIMARY KEY (`Date`,`Time`,`UserId`,`BossId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
/*!50100 PARTITION BY HASH (Date)
PARTITIONS 360 */$$