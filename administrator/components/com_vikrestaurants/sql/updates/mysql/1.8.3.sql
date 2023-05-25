CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_avail_override` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `units` int(6) DEFAULT 0,
  `ts` int(12) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `#__vikrestaurants_reservation`
CHANGE `id_payment` `id_payment` int(10) DEFAULT 0;

ALTER TABLE `#__vikrestaurants_takeaway_reservation`
CHANGE `id_payment` `id_payment` int(10) DEFAULT 0;

ALTER TABLE `#__vikrestaurants_takeaway_menus`
ADD COLUMN `publish_up` int(11) DEFAULT NULL AFTER `published`,
ADD COLUMN `publish_down` int(11) DEFAULT NULL AFTER `publish_up`;

ALTER TABLE `#__vikrestaurants_shifts`
ADD COLUMN `days` varchar(16) DEFAULT NULL AFTER `to`; 

ALTER TABLE `#__vikrestaurants_specialdays`
ADD COLUMN `minorder` decimal(10,2) DEFAULT 0.0 AFTER `priority`;

ALTER TABLE `#__vikrestaurants_stats_widget`
ADD COLUMN `id_user` int(10) unsigned DEFAULT 0 NOT NULL AFTER `id`;

INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES
('wizardstate', 1),
('editfood', 1);

-- assign DELIVERY NOTES rule to default custom field
UPDATE `#__vikrestaurants_custfields` SET `rule` = 11 WHERE `name` = 'CUSTOMF_TKNOTE';

UPDATE `#__vikrestaurants_config` SET `setting` = '1.8.3' WHERE `param` = 'version' LIMIT 1;