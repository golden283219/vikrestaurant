CREATE TABLE IF NOT EXISTS `#__vikrestaurants_res_prod_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_reservation` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_option` int(10) NOT NULL DEFAULT -1,
  `name` varchar(64) DEFAULT '',
  `quantity` int(4) DEFAULT 1,
  `price` decimal(6,2) DEFAULT 0,
  `notes` varchar(128) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_stock_override` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `items_available` int(6) unsigned NOT NULL,
  `ts` int(12) unsigned NOT NULL,
  `id_takeaway_entry` int(10) unsigned NOT NULL,
  `id_takeaway_option` int(10) DEFAULT -1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_user_delivery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `country` varchar(2) DEFAULT '',
  `state` varchar(64) DEFAULT '',
  `city` varchar(64) DEFAULT '',
  `address` varchar(128) NOT NULL,
  `address_2` varchar(64) DEFAULT '',
  `zip` varchar(12) NOT NULL,
  `ordering` int(2) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_invoice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned NOT NULL,
  `inv_number` varchar(32) NOT NULL,
  `inv_date` int(11) NOT NULL,
  `file` varchar(32) NOT NULL,
  `createdon` int(11) NOT NULL,
  `group` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL,
  `ipaddr` varchar(24) DEFAULT '',
  `timestamp` int(12) NOT NULL,
  `name` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `title` varchar(64) DEFAULT '',
  `comment` text DEFAULT '',
  `rating` int(1) unsigned DEFAULT 0, 
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `langtag` varchar(8) DEFAULT '',
  `id_takeaway_product` int(10) DEFAULT -1,
  `conf_key` varchar(12) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_order_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned NOT NULL,
  `id_rescode` int(10) NOT NULL,
  `notes` varchar(1024) DEFAULT '',
  `createdby` int(10) NOT NULL,
  `createdon` int(11) NOT NULL,
  `group` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `note` text DEFAULT '',
  `prenote` text DEFAULT '',
  `id_payment` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_customf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `choose` text DEFAULT '',
  `poplink` varchar(256) DEFAULT '',
  `id_customf` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_api_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `application` varchar(64) DEFAULT '',
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `ips` varchar(256) DEFAULT '',
  `active` tinyint(1) DEFAULT 0,
  `last_login` int(11) DEFAULT -1,
  `denied` varchar(256) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_api_login_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_login` int(10) DEFAULT -1,
  `status` tinyint(1) DEFAULT 1,
  `content` varchar(512) NOT NULL,
  `ip` varchar(24) DEFAULT '',
  `createdon` int(11) DEFAULT -1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_api_ban` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(24) DEFAULT '',
  `fail_count` int(4) DEFAULt 0,
  `last_update` int(11) DEFAULT -1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_delivery_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `content` text NOT NULL,
  `attributes` varchar(512) DEFAULT '',
  `charge` decimal(6, 2) DEFAULT 0.0,
  `min_cost` decimal(8, 2) DEFAULT 0.0,
  `published` tinyint(1) DEFAULT 1,
  `ordering` int(10) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry` 
ADD COLUMN `items_in_stock` int(6) unsigned DEFAULT 9999, 
ADD COLUMN `notify_below` int(6) unsigned DEFAULT 5,
ADD COLUMN `published` tinyint(1) DEFAULT 1 AFTER `price`,
CHANGE `description` `description` text DEFAULT '', 
CHANGE `img_path` `img_path` varchar(128) DEFAULT '';

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry_option`
ADD COLUMN `items_in_stock` int(6) unsigned DEFAULT 9999, 
ADD COLUMN `notify_below` int(6) unsigned DEFAULT 5;

ALTER TABLE `#__vikrestaurants_section_product` 
ADD COLUMN `hidden` tinyint(1) DEFAULT 0 AFTER `published`,
CHANGE `description` `description` text DEFAULT '';

ALTER TABLE `#__vikrestaurants_operator` 
ADD COLUMN `group` tinyint(2) unsigned DEFAULT 0, 
ADD COLUMN `manage_coupon` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `#__vikrestaurants_table` 
ADD COLUMN `published` tinyint(1) NOT NULL DEFAULT 1 AFTER `multi_res`;

ALTER TABLE `#__vikrestaurants_takeaway_deal` 
ADD COLUMN `cart_tcost` decimal(6,2) unsigned DEFAULT 0.0 AFTER `percentot`;

ALTER TABLE `#__vikrestaurants_takeaway_entry_group_assoc` 
ADD COLUMN `id_variation` int(10) DEFAULT -1 AFTER `id_entry`,
CHANGE `title` `title` varchar(64) DEFAULT '';

ALTER TABLE `#__vikrestaurants_takeaway_menus` 
ADD COLUMN `taxes_type` tinyint(1) NOT NULL DEFAULT 0 AFTER `published`, 
ADD COLUMN `taxes_amount` decimal(6,2) DEFAULT 0.0 AFTER `taxes_type`;

ALTER TABLE `#__vikrestaurants_gpayments` 
DROP COLUMN `val_pcent`, 
DROP COLUMN `ch_disc`, 
DROP COLUMN `shownotealw`, 
ADD COLUMN `percentot` tinyint(1) DEFAULT 2 AFTER `charge`, 
CHANGE `charge` `charge` decimal(8,4) DEFAULT NULL, 
ADD COLUMN `group` tinyint(1) DEFAULT 0 AFTER `params`, 
ADD COLUMN `enablecost` decimal(6, 2) DEFAULT 0 AFTER `published`, 
ADD COLUMN `icontype` tinyint(1) DEFAULT 0 AFTER `setconfirmed`, 
ADD COLUMN `icon` varchar(128) DEFAULT '' AFTER `icontype`, 
ADD COLUMN `prenote` text DEFAULT NULL AFTER `note`, 
ADD COLUMN `position` varchar(64) DEFAULT '' AFTER `group`;

ALTER TABLE `#__vikrestaurants_takeaway_reservation` 
ADD COLUMN `pay_charge` decimal(10,2) DEFAULT 0.0 AFTER `taxes`, 
ADD COLUMN `purchaser_address` varchar(256) DEFAULT '' AFTER `purchaser_country`, 
ADD COLUMN `delivery_charge` decimal(10,2) AFTER `pay_charge`, 
ADD COLUMN `discount_val` decimal(10,2) DEFAULT 0.0 AFTER `delivery_charge`, 
ADD COLUMN `route` varchar(512) DEFAULT '', 
ADD COLUMN `cc_details` text DEFAULT NULL,
CHANGE `coupon_str` `coupon_str` varchar(64) DEFAULT '';

ALTER TABLE `#__vikrestaurants_reservation` 
ADD COLUMN `cc_details` text DEFAULT NULL, 
ADD COLUMN `discount_val` decimal(10,2) DEFAULT 0.0 AFTER `tot_paid`, 
ADD COLUMN `stay_time` int(6) DEFAULT 0 AFTER `checkin_ts`,
CHANGE `coupon_str` `coupon_str` varchar(64) DEFAULT '';

ALTER TABLE `#__vikrestaurants_custfields` 
DROP COLUMN `isnominative`, 
DROP COLUMN `isemail`, 
DROP COLUMN `isphone`, 
ADD COLUMN `required_delivery` tinyint(1) DEFAULT 0 AFTER `required`, 
ADD COLUMN `rule` tinyint(2) DEFAULT 0 AFTER `required_delivery`;

ALTER TABLE `#__vikrestaurants_takeaway_res_prod_assoc` 
ADD COLUMN `taxes` decimal(10,2) DEFAULT 0.0 AFTER `price`;

ALTER TABLE `#__vikrestaurants_coupons`
CHANGE `minpeople` `minvalue` decimal(10,2) DEFAULT 1;
ALTER TABLE `#__vikrestaurants_coupons`
ADD COLUMN `group` tinyint(1) DEFAULT 0;

ALTER TABLE `#__vikrestaurants_menus` 
CHANGE `description` `description` text DEFAULT '', 
CHANGE `working_shifts` `working_shifts` varchar(128) DEFAULT '', 
CHANGE `days_filter` `days_filter` varchar(64) DEFAULT '';

ALTER TABLE `#__vikrestaurants_menus_section` 
CHANGE `description` `description` text DEFAULT '';

ALTER TABLE `#__vikrestaurants_section_product_option` 
CHANGE `name` `name` varchar(128) DEFAULT '';

ALTER TABLE `#__vikrestaurants_specialdays` 
CHANGE `working_shifts` `working_shifts` varchar(128) DEFAULT '';
ALTER TABLE `#__vikrestaurants_specialdays`
CHANGE `days_filter` `days_filter` varchar(64) DEFAULT '';
ALTER TABLE `#__vikrestaurants_specialdays`
ADD COLUMN `delivery_service` tinyint(1) DEFAULT -1;

ALTER TABLE `#__vikrestaurants_room` 
CHANGE `name` `name` varchar(64) NOT NULL, 
CHANGE `image` `image` varchar(128) DEFAULT '';

ALTER TABLE `#__vikrestaurants_takeaway_topping` 
CHANGE `name` `name` varchar(64) DEFAULT '';

ALTER TABLE `#__vikrestaurants_lang_menus` 
CHANGE `description` `description` text DEFAULT '';

ALTER TABLE `#__vikrestaurants_lang_menus_section` 
CHANGE `description` `description` text DEFAULT '';

ALTER TABLE `#__vikrestaurants_lang_section_product` 
CHANGE `description` `description` text DEFAULT '';

ALTER TABLE `#__vikrestaurants_lang_section_product_option` 
CHANGE `name` `name` varchar(128) DEFAULT '';

ALTER TABLE `#__vikrestaurants_lang_takeaway_menus_entry` 
CHANGE `description` `description` text DEFAULT '';

ALTER TABLE `#__vikrestaurants_res_code` 
ADD COLUMN `notes` varchar(1024) DEFAULT '' AFTER `type`, 
ADD COLUMN `ordering` int(10) unsigned DEFAULT 1;

UPDATE `#__vikrestaurants_res_code` SET `notes`='Your order is in preparation.' WHERE `id`='8' AND `code`='Preparing' LIMIT 1;
UPDATE `#__vikrestaurants_res_code` SET `notes`='Your order is ready.' WHERE `id`='9' AND `code`='Ready' LIMIT 1;
UPDATE `#__vikrestaurants_res_code` SET `notes`='Your order has been delivered.' WHERE `id`='10' AND `code`='Delivered' LIMIT 1;
UPDATE `#__vikrestaurants_res_code` SET `notes`='The order has been picked up.' WHERE `id`='11' AND `code`='Picked' LIMIT 1;

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'taxesratio', '0.0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'usetaxes', '0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enablereviews', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revtakeaway', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revleavemode', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revcommentreq', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revminlength', 48 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revmaxlength', 512 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revlimlist', 5 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revlangfilter', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revautopublished', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkenablestock', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmaxitems', 100 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkallowdate', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkwhenopen', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkuseoverlay', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkstockmailtmpl', 'takeaway_stock_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'invoiceobj', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mainmenustatus', 1);
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mediaprop', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currdecimalsep', '.' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currthousandssep', ',' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currdecimaldig', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enablerestaurant', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'pickupprice', '0.0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'pickuppercentot', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkaddrorigins', '[]' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'googleapikey', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkconfitemid', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkusetaxes', '0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'adminmailtmpl', 'admin_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'cancmailtmpl', 'cancellation_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkadminmailtmpl', 'takeaway_admin_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkcancmailtmpl', 'takeaway_cancellation_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkreviewmailtmpl', 'takeaway_review_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'printorderstext', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkproddesclength', 128 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'cancreason', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkcancreason', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkstopuntil', -1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apifw', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apilogmode', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apilogflush', 7 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apimaxfail', 20 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'securehashkey', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'uiradio', 'ios' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkshowimages', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'phoneprefix', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'update_extra_fields', 0 );

UPDATE `#__vikrestaurants_config` SET `setting`='1.7' WHERE `param`='version' LIMIT 1;