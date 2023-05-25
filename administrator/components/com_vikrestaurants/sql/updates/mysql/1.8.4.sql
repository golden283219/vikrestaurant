CREATE TABLE IF NOT EXISTS `#__vikrestaurants_api_login_event_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_login` int(10) unsigned NOT NULL,
  `id_event` varchar(128) NOT NULL,
  `options` text DEFAULT NULL COMMENT 'JSON configuration for event/user relation',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry`
ADD COLUMN `stock_notified` tinyint(1) DEFAULT 0 AFTER `notify_below`;

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry_option`
ADD COLUMN `published` tinyint(1) NOT NULL DEFAULT 1 AFTER `inc_price`,
ADD COLUMN `stock_notified` tinyint(1) DEFAULT 0 AFTER `notify_below`;

UPDATE `#__vikrestaurants_config` SET `setting` = '1.8.4' WHERE `param` = 'version' LIMIT 1;