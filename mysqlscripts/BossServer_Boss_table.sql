delimiter $$

CREATE TABLE `BossServer_Boss` (
  `BossId` mediumint(9) NOT NULL,
  `Date` int(11) NOT NULL,
  `Time` mediumint(9) NOT NULL,
  `VitalityTotal` bigint(20) NOT NULL,
  `LastHitUser` int(11) DEFAULT NULL,
  PRIMARY KEY (`Date`,`BossId`,`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1$$