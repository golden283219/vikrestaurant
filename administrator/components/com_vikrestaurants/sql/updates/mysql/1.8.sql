CREATE TABLE IF NOT EXISTS `#__vikrestaurants_table_cluster` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `id_table_1` int(6) NOT NULL,
  `id_table_2` int(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_stats_widget` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `widget` varchar(64) NOT NULL,
  `position` varchar(64) NOT NULL,
  `group` varchar(16) NOT NULL COMMENT 'restaurant or takeaway',
  `location` varchar(16) NOT NULL COMMENT 'dashboard or statistics',
  `size` varchar(32) DEFAULT NULL,
  `ordering` int(4) unsigned DEFAULT 1,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(48) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `color` varchar(8) DEFAULT NULL,
  `group` varchar(32) DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` text DEFAULT NULL,
  `id_room` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting` text DEFAULT NULL,
  `param` varchar(32) NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `#__vikrestaurants_reservation`
ADD COLUMN `arrived` tinyint(1) DEFAULT NULL AFTER `rescode`,
ADD COLUMN `modified_on` int(11) DEFAULT 0 AFTER `created_by`,
ADD COLUMN `id_operator` int(10) DEFAULT 0,
ADD COLUMN `closure` tinyint(1) DEFAULT 0,
ADD COLUMN `id_parent` int(10) unsigned DEFAULT 0 COMMENT 'the reservation ID to which this record belongs (@see clusters)',
ADD COLUMN `payment_log` text DEFAULT NULL;

ALTER TABLE `#__vikrestaurants_takeaway_reservation`
ADD COLUMN `preparation_ts` int(11) DEFAULT NULL AFTER `checkin_ts`,
ADD COLUMN `current` tinyint(1) DEFAULT NULL COMMENT 'flag used to stick the order within the current widget' AFTER `need_notif`,
ADD COLUMN `modified_on` int(11) DEFAULT 0 AFTER `created_by`,
ADD COLUMN `payment_log` text DEFAULT NULL;

ALTER TABLE `#__vikrestaurants_menus`
CHANGE `name` `name` varchar(64) NOT NULL,
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`,
ADD COLUMN `cost` decimal(10,2) DEFAULT 0.0 AFTER `description`;

ALTER TABLE `#__vikrestaurants_lang_menus`
CHANGE `name` `name` varchar(64) NOT NULL,
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`;

ALTER TABLE `#__vikrestaurants_menus_section`
ADD COLUMN `orderdishes` tinyint(1) DEFAULT 1 AFTER `highlight`;

ALTER TABLE `#__vikrestaurants_section_product`
ADD COLUMN `tags` varchar(256) DEFAULT NULL AFTER `published`;

ALTER TABLE `#__vikrestaurants_res_prod_assoc`
ADD COLUMN `rescode` int(4) DEFAULT 0,
ADD COLUMN `status` tinyint(1) DEFAULT NULL;

ALTER TABLE `#__vikrestaurants_custfields`
ADD COLUMN `multiple` tinyint(1) DEFAULT 0 AFTER `poplink`;

ALTER TABLE `#__vikrestaurants_specialdays`
ADD COLUMN `askdeposit` int(4) DEFAULT 1 AFTER `days_filter`,
ADD COLUMN `freechoose` tinyint(1) NOT NULL DEFAULT 1 AFTER `choosemenu`;

ALTER TABLE `#__vikrestaurants_coupons`
ADD COLUMN `usages` int(6) DEFAULT 0 AFTER `minvalue`,
ADD COLUMN `maxusages` int(6) DEFAULT 0 AFTER `usages`,
ADD COLUMN `maxperuser` int(6) DEFAULT 0 AFTER `maxusages`;

ALTER TABLE `#__vikrestaurants_gpayments`
ADD COLUMN `trust` int(4) unsigned DEFAULT 0 AFTER `enablecost`;

ALTER TABLE `#__vikrestaurants_res_code`
ADD COLUMN `rule` varchar(32) DEFAULT NULL AFTER `type`;

ALTER TABLE `#__vikrestaurants_user_delivery`
ADD COLUMN `type` tinyint(1) DEFAULT 1 AFTER `id_user`,
ADD COLUMN `note` varchar(1024) DEFAULT NULL AFTER `zip`,
ADD COLUMN `longitude` decimal(9,6) DEFAULT NULL AFTER `note`,
ADD COLUMN `latitude` decimal(9,6) DEFAULT NULL AFTER `longitude`;

ALTER TABLE `#__vikrestaurants_takeaway_menus`
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `title`;

ALTER TABLE `#__vikrestaurants_lang_takeaway_menus`
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`;

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry`
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`;

ALTER TABLE `#__vikrestaurants_lang_takeaway_menus_entry`
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`;

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry_option`
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`,
ADD COLUMN `stock_enabled` tinyint(1) DEFAULT 1 COMMENT 'use parent stock params if disabled' AFTER `items_in_stock`;

ALTER TABLE `#__vikrestaurants_lang_takeaway_menus_entry_option`
ADD COLUMN `alias` varchar(64) DEFAULT NULL AFTER `name`;

ALTER TABLE `#__vikrestaurants_takeaway_stock_override`
CHANGE `id_takeaway_option` `id_takeaway_option` int(10) DEFAULT NULL;

ALTER TABLE `#__vikrestaurants_takeaway_deal`
ADD COLUMN `shifts` varchar(128) DEFAULT '' COMMENT 'JSON list with shifts available' AFTER `end_ts`,
ADD COLUMN `service` tinyint(1) DEFAULT 2 COMMENT '0: pickup, 1: delivery, 2: both' AFTER `shifts`;

ALTER TABLE `#__vikrestaurants_operator`
ADD COLUMN `allres` tinyint(1) DEFAULT 0 AFTER `mail_notifications`,
ADD COLUMN `assign` tinyint(1) DEFAULT 1 AFTER `allres`,
ADD COLUMN `rooms` varchar(128) DEFAULT NULL COMMENT 'supported room IDs, comma separated' AFTER `assign`,
ADD COLUMN `products` varchar(512) DEFAULT NULL COMMENT 'supported product tags, comma separated' AFTER `rooms`;

ALTER TABLE `#__vikrestaurants_operator_log`
ADD COLUMN `content` varchar(2048) DEFAULT '' COMMENT 'a JSON containing the log data' AFTER `log`;

ALTER TABLE `#__vikrestaurants_takeaway_topping`
ADD COLUMN `description` varchar(256) DEFAULT '' AFTER `name`;

ALTER TABLE `#__vikrestaurants_lang_takeaway_topping`
ADD COLUMN `description` varchar(256) DEFAULT '' AFTER `name`;

INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES
(3, 1, 'Scheduled' , ''        , 'scheduled.png' ),
(3, 2, 'Cooking'   , 'cooking' , 'cooking.png'   ),
(3, 3, 'Prepared'  , 'prepared', 'prepared.png'  ),
(3, 4, 'Delivering', 'waiter'  , 'delivering.png');

INSERT INTO `#__vikrestaurants_stats_widget` (`name`, `widget`, `position`, `group`, `location`, `size`, `ordering`) VALUES
('', 'weekres'    , 'top'   , 'restaurant', 'statistics', ''     , 1),
('', 'weekrevenue', 'top'   , 'restaurant', 'statistics', ''     , 2),
('', 'occupancy'  , 'top'   , 'restaurant', 'statistics', ''     , 3),
('', 'trend'      , 'center', 'restaurant', 'statistics', 'large', 4),
('', 'overall'    , 'center', 'restaurant', 'statistics', ''     , 5),
('', 'statusres'  , 'bottom', 'restaurant', 'statistics', ''     , 6),
('', 'avgdaily'   , 'bottom', 'restaurant', 'statistics', ''     , 7),
('', 'customers'  , 'bottom', 'restaurant', 'statistics', ''     , 8);

INSERT INTO `#__vikrestaurants_stats_widget` (`name`, `widget`, `position`, `group`, `location`, `size`, `ordering`) VALUES
('', 'weekres'     , 'top'   , 'takeaway', 'statistics', ''     , 1),
('', 'weekrevenue' , 'top'   , 'takeaway', 'statistics', ''     , 2),
('', 'rog'         , 'top'   , 'takeaway', 'statistics', 'small', 3),
('', 'trend'       , 'center', 'takeaway', 'statistics', 'large', 4),
('', 'bestproducts', 'center', 'takeaway', 'statistics', ''     , 5),
('', 'service'     , 'bottom', 'takeaway', 'statistics', ''     , 6),
('', 'avgdaily'    , 'bottom', 'takeaway', 'statistics', ''     , 7),
('', 'customers'   , 'bottom', 'takeaway', 'statistics', ''     , 8);

INSERT INTO `#__vikrestaurants_stats_widget` (`name`, `widget`, `position`, `group`, `location`, `size`, `ordering`) VALUES
('', 'reservations', 'center', 'restaurant', 'dashboard', '', 1),
('', 'overview'    , 'footer', 'restaurant', 'dashboard', '', 2);

INSERT INTO `#__vikrestaurants_stats_widget` (`name`, `widget`, `position`, `group`, `location`, `size`, `ordering`) VALUES
('', 'weekres'     , 'top'   , 'takeaway', 'dashboard', ''     , 1),
('', 'weekrevenue' , 'top'   , 'takeaway', 'dashboard', ''     , 2),
('', 'rog'         , 'top'   , 'takeaway', 'dashboard', 'small', 3),
('', 'orders'      , 'center', 'takeaway', 'dashboard', ''     , 4);

INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES
('exportresparams', '{}'),
('askdeposit', 1),
('selfconfirm', 0),
('safedistance', 0),
('safefactor', 2),
('mindate', 0),
('maxdate', 0),
('cancmins', 0),
('orderfood', 0),
('tkshowtimes', 0),
('tkselfconfirm', 0),
('tkmealsbackslots', 2),
('tkpreorder', 0),
('tkmindate', 0),
('tkmaxdate', 0),
('tkcancmins', 0),
('googleapiplaces', 1),
('googleapidirections', 1),
('googleapistaticmap', 1);

/* add rules to default reservation codes */

UPDATE `#__vikrestaurants_res_code`
SET `rule` = 'arrived'
WHERE `id` = 1;

UPDATE `#__vikrestaurants_res_code`
SET `rule` = 'closebill'
WHERE `id` = 6;

UPDATE `#__vikrestaurants_res_code`
SET `rule` = 'leave'
WHERE `id` = 7;

UPDATE `#__vikrestaurants_res_code`
SET `rule` = 'preparing'
WHERE `id` = 8;

UPDATE `#__vikrestaurants_res_code`
SET `rule` = 'completed'
WHERE `id` = 10 OR `id` = 11;

UPDATE `#__vikrestaurants_config` SET `setting` = 'ios' WHERE `param` = 'uiradio';

UPDATE `#__vikrestaurants_config` SET `setting` = '1.8' WHERE `param` = 'version';
