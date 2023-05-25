CREATE TABLE IF NOT EXISTS `#__vikrestaurants_origin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `address` varchar(512) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `latitude` decimal(12,9) DEFAULT NULL,
  `longitude` decimal(12,9) DEFAULT NULL,
  `image` varchar(256) DEFAULT NULL,
  `published` tinyint(1) DEFAULT 1,
  `ordering` int(10) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

UPDATE `#__vikrestaurants_config` SET `setting` = '1.8.5' WHERE `param` = 'version' LIMIT 1;