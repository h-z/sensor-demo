
DROP TABLE IF EXISTS `upraised`;
CREATE TABLE `upraised` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) DEFAULT NULL,
  `direction` tinyint(4) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_timestamp` (`timestamp`),
  KEY `idx_sensor_id` (`sensor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
