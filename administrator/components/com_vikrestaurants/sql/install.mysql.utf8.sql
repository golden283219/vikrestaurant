CREATE TABLE IF NOT EXISTS `#__vikrestaurants_reservation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_table` int(10) NOT NULL,
  `id_payment` int(10) DEFAULT 0,
  `coupon_str` varchar(64) DEFAULT '',
  `checkin_ts` int(11) NOT NULL,
  `stay_time` int(6) DEFAULT 0,
  `people` int(4) NOT NULL,
  `purchaser_nominative` varchar(128) DEFAULT '',
  `purchaser_mail` varchar(64) NOT NULL DEFAULT '',
  `purchaser_phone` varchar(32) DEFAULT '',
  `purchaser_prefix` varchar(10) DEFAULT '',
  `purchaser_country` varchar(2) DEFAULT '',
  `langtag` varchar(8) DEFAULT '',
  `custom_f` text DEFAULT NULL,
  `bill_closed` tinyint(1) NOT NULL DEFAULT 0,
  `bill_value` decimal(10,2) DEFAULT 0.0,
  `deposit` decimal(10,2) DEFAULT 0.0,
  `tot_paid` decimal(10,2) DEFAULT 0.0,
  `discount_val` decimal(10,2) DEFAULT 0.0,
  `tip_amount` decimal(10,2) DEFAULT 0.0,
  `status` varchar(16) DEFAULT 'PENDING',
  `rescode` int(4) DEFAULT 0,
  `arrived` tinyint(1) DEFAULT NULL,
  `locked_until` int(12) DEFAULT 0,
  `sid` varchar(16) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_on` int(11) DEFAULT 0,
  `created_by` int(10) DEFAULT 0,
  `modified_on` int(11) DEFAULT 0,
  `id_user` int(10) DEFAULT 0,
  `id_operator` int(10) DEFAULT 0,
  `conf_key` varchar(12) DEFAULT '',
  `cc_details` text DEFAULT NULL,
  `need_notif` tinyint(1) DEFAULT 0 COMMENT '1 if the record requires to be notified',
  `closure` tinyint(1) DEFAULT 0,
  `id_parent` int(10) unsigned DEFAULT 0 COMMENT 'the reservation ID to which this record belongs (@see clusters)',
  `payment_log` text DEFAULT NULL,
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_table` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `min_capacity` int(3) NOT NULL,
  `max_capacity` int(3) NOT NULL,
  `multi_res` tinyint(1) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `design_data` text DEFAULT NULL,
  `id_room` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_table_cluster` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `id_table_1` int(6) NOT NULL,
  `id_table_2` int(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` text DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(128) DEFAULT '',
  `graphics_properties` text DEFAULT NULL,
  `ordering` int(10) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_room_closure` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_room` int(10) unsigned NOT NULL,
  `start_ts` int(11) NOT NULL,
  `end_ts` int(11) NOT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_operator` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `phone_number` varchar(20) DEFAULT '',
  `email` varchar(64) DEFAULT '',
  `can_login` tinyint(1) NOT NULL DEFAULT 0,
  `keep_track` tinyint(1) NOT NULL DEFAULT 1,
  `mail_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `allres` tinyint(1) DEFAULT 0,
  `assign` tinyint(1) DEFAULT 1,
  `rooms` varchar(128) DEFAULT NULL COMMENT 'supported room IDs, comma separated',
  `products` varchar(512) DEFAULT NULL COMMENT 'supported product tags, comma separated',
  `manage_coupon` tinyint(1) NOT NULL DEFAULT 0,
  `group` tinyint(2) unsigned DEFAULT 0,
  `jid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_operator_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_operator` int(10) unsigned NOT NULL,
  `id_reservation` int(10) unsigned DEFAULT 0,
  `log` varchar(256) NOT NULL COMMENT 'since 1.8 might contain the language key',
  `content` varchar(2048) DEFAULT '' COMMENT 'a JSON containing the log data',
  `createdon` int(11) NOT NULL,
  `group` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_shifts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `from` int(4) NOT NULL,
  `to` int(4) NOT NULL,
  `days` varchar(16) DEFAULT NULL,
  `group` tinyint(1) DEFAULT 1,
  `showlabel` tinyint(1) DEFAULT 1,
  `label` varchar(32) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.0,
  `image` varchar(128) DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `choosable` tinyint(1) NOT NULL DEFAULT 1,
  `special_day` tinyint(1) NOT NULL DEFAULT 0,
  `working_shifts` varchar(128) DEFAULT '',
  `days_filter` varchar(64) DEFAULT '',
  `ordering` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_menus_section` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `published` tinyint(1) DEFAULT 0,
  `highlight` tinyint(1) DEFAULT 1,
  `orderdishes` tinyint(1) DEFAULT 1,
  `ordering` int(10) unsigned NOT NULL,
  `image` varchar(128) DEFAULT '',
  `id_menu` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_section_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(128) DEFAULT '',
  `price` decimal(10,2) DEFAULT 0.0,
  `published` tinyint(1) DEFAULT 0,
  `tags` varchar(256) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT 0,
  `ordering` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_section_product_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `inc_price` decimal(10,2) DEFAULT 0.0, 
  `id_product` int(10) NOT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_section_product_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_section` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `charge` decimal(10,2) DEFAULT 0.0,
  `ordering` int(10) unsigned NOT NULL,
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

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_res_menus_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_reservation` int(10) unsigned NOT NULL,
  `id_menu` int(10) unsigned NOT NULL,
  `quantity` int(4) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_res_prod_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_reservation` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_option` int(10) NOT NULL DEFAULT 0,
  `name` varchar(64) DEFAULT '',
  `quantity` int(4) DEFAULT 1,
  `price` decimal(10,2) DEFAULT 0,
  `notes` varchar(128) DEFAULT '',
  `rescode` int(4) DEFAULT 0,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_specialdays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `start_ts` int(11),
  `end_ts` int(11),
  `working_shifts` varchar(128) DEFAULT '',
  `days_filter` varchar(64) DEFAULT '',
  `askdeposit` int(4) DEFAULT 1,
  `depositcost` decimal(10,2) NOT NULL,
  `perpersoncost` tinyint(1) NOT NULL DEFAULT 0,
  `peopleallowed` int(4) DEFAULT -1,
  `ignoreclosingdays` tinyint(1) NOT NULL DEFAULT 1,
  `markoncal` tinyint(1) NOT NULL DEFAULT 1,
  `choosemenu` tinyint(1) NOT NULL DEFAULT 0,
  `freechoose` tinyint(1) NOT NULL DEFAULT 1,
  `priority` tinyint(1) NOT NULL DEFAULT 1,
  `minorder` decimal(10,2) DEFAULT 0.0,
  `delivery_service` tinyint(1) DEFAULT -1,
  `delivery_areas` varchar(64) DEFAULT NULL COMMENT 'JSON array containing the accepted delivery areas',
  `group` tinyint(1) DEFAULT 1,
  `images` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_sd_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_spday` int(10) NOT NULL,
  `id_menu` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_custfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'text',
  `choose` text DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `required_delivery` tinyint(1) NOT NULL DEFAULT 0,
  `ordering` int(10) NOT NULL DEFAULT 1,
  `rule` tinyint(2) NOT NULL DEFAULT 0,
  `poplink` varchar(256) DEFAULT NULL,
  `multiple` tinyint(1) DEFAULT 0,
  `group` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 for restaurant, 1 for take-away',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_gpayments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `file` varchar(64) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `enablecost` decimal(8,2) DEFAULT 0,
  `trust` int(4) unsigned DEFAULT 0,
  `prenote` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `charge` decimal(8,4) DEFAULT NULL,
  `percentot` tinyint(1) DEFAULT 2,
  `setconfirmed` tinyint(1) NOT NULL DEFAULT 0,
  `selfconfirm` tinyint(1) NOT NULL DEFAULT 0,
  `icontype` tinyint(1) DEFAULT 0,
  `icon` varchar(128) DEFAULT '',
  `params` varchar(512) DEFAULT NULL,
  `group` tinyint(1) DEFAULT 0,
  `position` varchar(64) DEFAULT '',
  `ordering` int(10) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `percentot` tinyint(1) NOT NULL DEFAULT 1,
  `value` decimal(12,2) DEFAULT NULL,
  `datevalid` varchar(64) DEFAULT NULL,
  `minvalue` decimal(10,2) DEFAULT 1,
  `usages` int(6) DEFAULT 0,
  `maxusages` int(6) DEFAULT 0,
  `maxperuser` int(6) DEFAULT 0,
  `group` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_res_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `icon` varchar(128) DEFAULT '',
  `notes` varchar(1024) DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `rule` varchar(32) DEFAULT NULL,
  `ordering` int(10) unsigned DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `param` varchar(32) NOT NULL DEFAULT 'false',
  `setting` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `param` (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `publish_up` int(11) DEFAULT NULL,
  `publish_down` int(11) DEFAULT NULL,
  `taxes_type` tinyint(1) NOT NULL DEFAULT 0,
  `taxes_amount` decimal(8,2) DEFAULT 0.0,
  `ordering` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_menus_entry` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.0, 
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `ready` tinyint(1) NOT NULL DEFAULT 0,
  `img_path` varchar(128) DEFAULT '',
  `img_extra` varchar(256) DEFAULT NULL,
  `items_in_stock` int(6) unsigned DEFAULT 9999,
  `notify_below` int(6) unsigned DEFAULT 5,
  `stock_notified` tinyint(1) DEFAULT 0,
  `id_takeaway_menu` int(10) NOT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_menus_entry_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `inc_price` decimal(10,2) DEFAULT 0.0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  `items_in_stock` int(6) unsigned DEFAULT 9999,
  `stock_enabled` tinyint(1) DEFAULT 1 COMMENT 'use parent stock params if disabled',
  `notify_below` int(6) unsigned DEFAULT 5,
  `stock_notified` tinyint(1) DEFAULT 0,
  `id_takeaway_menu_entry` int(10) NOT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_menus_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(48) NOT NULL,
  `description` varchar(512) DEFAULT '',
  `published` tinyint(1) DEFAULT 1,
  `icon` varchar(64) DEFAULT '',
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_menus_attr_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_menuentry` int(10) unsigned NOT NULL,
  `id_attribute` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_topping_separator` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(48) NOT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_topping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) DEFAULT '',
  `price` decimal(10, 2) DEFAULT 0.0,
  `published` tinyint(1) DEFAULT 1,
  `id_separator` int(10) DEFAULT -1,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_entry_group_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_entry` int(10) unsigned NOT NULL,
  `id_variation` int(10) DEFAULT -1,
  `title` varchar(64) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `multiple` tinyint(1) DEFAULT 0,
  `min_toppings` tinyint(2) DEFAULT 1,
  `max_toppings` tinyint(2) DEFAULT 1,
  `use_quantity` tinyint(1) DEFAULT 0,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_group_topping_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_group` int(10) unsigned NOT NULL,
  `id_topping` int(10) unsigned NOT NULL,
  `rate` decimal(10, 2) DEFAULT 0.0,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_deal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  `max_quantity` int(4) DEFAULT -1,
  `start_ts` int(11) DEFAULT -1,
  `end_ts` int(11) DEFAULT -1,
  `shifts` varchar(128) DEFAULT '' COMMENT 'JSON list with shifts available',
  `service` tinyint(1) DEFAULT 2 COMMENT '0: pickup, 1: delivery, 2: both',
  `published` tinyint(1) DEFAULT 0,
  `ordering` int(10) unsigned DEFAULT 1,
  `amount` decimal(10,2) unsigned DEFAULT 0.0,
  `percentot` tinyint(1) DEFAULT 1,
  `cart_tcost` decimal(10,2) unsigned DEFAULT 0.0,
  `auto_insert` tinyint(1) DEFAULT 1,
  `min_quantity` int(4) unsigned DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_deal_day_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_deal` int(10) unsigned NOT NULL,
  `id_weekday` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_deal_product_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_deal` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_option` int(10) DEFAULT -1,
  `quantity` int(4) unsigned NOT NULL DEFAULT 1,
  `required` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_deal_free_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_deal` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_option` int(10) DEFAULT -1,
  `quantity` int(4) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_delivery_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `content` text NOT NULL,
  `attributes` varchar(512) DEFAULT '',
  `charge` decimal(8, 2) DEFAULT 0.0,
  `min_cost` decimal(10, 2) DEFAULT 0.0,
  `published` tinyint(1) DEFAULT 1,
  `ordering` int(10) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

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

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_reservation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment` int(10) DEFAULT 0,
  `delivery_service` tinyint(1) NOT NULL DEFAULT 1,
  `coupon_str` varchar(64) DEFAULT '',
  `checkin_ts` int(11) NOT NULL,
  `preparation_ts` int(11) DEFAULT NULL,
  `purchaser_nominative` varchar(128) DEFAULT '',
  `purchaser_mail` varchar(64) NOT NULL DEFAULT '',
  `purchaser_phone` varchar(32) DEFAULT '',
  `purchaser_prefix` varchar(10) DEFAULT '',
  `purchaser_country` varchar(2) DEFAULT '',
  `purchaser_address` varchar(256) DEFAULT '',
  `langtag` varchar(8) DEFAULT '',
  `custom_f` text DEFAULT NULL,
  `total_to_pay` decimal(10,2) DEFAULT 0.0,
  `tot_paid` decimal(10,2) DEFAULT 0.0,
  `taxes` decimal(10,2) DEFAULT 0.0,
  `pay_charge` decimal(10,2) DEFAULT 0.0,
  `delivery_charge` decimal(10,2) DEFAULT 0.0,
  `discount_val` decimal(10,2) DEFAULT 0.0,
  `tip_amount` decimal(10,2) DEFAULT 0.0,
  `status` varchar(16) DEFAULT 'PENDING',
  `rescode` int(4) DEFAULT 0,
  `locked_until` int(12) DEFAULT 0,
  `sid` varchar(16) NOT NULL DEFAULT '000000000000',
  `notes` text DEFAULT NULL,
  `created_on` int(11) DEFAULT 0,
  `created_by` int(10) DEFAULT 0,
  `modified_on` int(11) DEFAULT 0,
  `id_user` int(10) DEFAULT -1,
  `id_operator` int(10) DEFAULT 0,
  `conf_key` varchar(12) DEFAULT '',
  `route` varchar(512) DEFAULT '', 
  `cc_details` text DEFAULT NULL,
  `need_notif` tinyint(1) DEFAULT 0 COMMENT '1 if the record requires to be notified',
  `current` tinyint(1) DEFAULT NULL COMMENT 'flag used to stick the order within the current widget',
  `payment_log` text DEFAULT NULL,
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_res_prod_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_product` int(10) NOT NULL,
  `id_product_option` int(10) NOT NULL DEFAULT 0,
  `id_res` int(10) NOT NULL,
  `quantity` int(5) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.0,
  `taxes` decimal(10,2) DEFAULT 0.0,
  `notes` varchar(256) DEFAULT "",
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_res_prod_topping_assoc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_assoc` int(10) NOT NULL,
  `id_group` int(10) NOT NULL,
  `id_topping` int(10) NOT NULL,
  `units` tinyint(2) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_stock_override` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `items_available` int(6) unsigned NOT NULL,
  `ts` int(12) unsigned NOT NULL,
  `id_takeaway_entry` int(10) unsigned NOT NULL,
  `id_takeaway_option` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_takeaway_avail_override` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `units` int(6) DEFAULT 0,
  `ts` int(12) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL,
  `fields` text DEFAULT NULL,
  `tkfields` text DEFAULT NULL,
  `country_code` varchar(2),
  `billing_name` varchar(64) DEFAULT '',
  `billing_mail` varchar(64) DEFAULT '',
  `billing_phone` varchar(64) DEFAULT '',
  `billing_state` varchar(64) DEFAULT '',
  `billing_city` varchar(64) DEFAULT '',
  `billing_address` varchar(128) DEFAULT '',
  `billing_address_2` varchar(64) DEFAULT '',
  `billing_zip` varchar(12) DEFAULT '',
  `company` varchar(64) DEFAULT '',
  `vatnum` varchar(24) DEFAULT '',
  `ssn` varchar(32) DEFAULT '',
  `notes` varchar(2048) DEFAULT '',
  `image` varchar(128) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_user_delivery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `type` tinyint(1) DEFAULT 1,
  `country` varchar(2) DEFAULT '',
  `state` varchar(64) DEFAULT '',
  `city` varchar(64) DEFAULT '',
  `address` varchar(128) NOT NULL,
  `address_2` varchar(64) DEFAULT '',
  `zip` varchar(12) NOT NULL,
  `note` varchar(1024) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `ordering` int(2) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_name` varchar(48) NOT NULL,
  `country_2_code` varchar(2) NOT NULL,
  `country_3_code` varchar(3) NOT NULL,
  `phone_prefix` varchar(8) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `country_2_code` (`country_2_code`),
  UNIQUE KEY `country_3_code` (`country_3_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jid` int(10) NOT NULL,
  `ipaddr` varchar(24) DEFAULT '',
  `timestamp` int(12) NOT NULL,
  `name` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `title` varchar(64) DEFAULT '',
  `comment` text DEFAULT NULL,
  `rating` int(1) unsigned DEFAULT 0, 
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `langtag` varchar(8) DEFAULT '',
  `id_takeaway_product` int(10) DEFAULT -1,
  `conf_key` varchar(12) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

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

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_order_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_order` int(10) unsigned NOT NULL,
  `id_rescode` int(10) NOT NULL,
  `notes` varchar(1024) DEFAULT '',
  `createdby` int(10) NOT NULL,
  `createdon` int(11) NOT NULL,
  `group` tinyint(1) unsigned NOT NULL COMMENT '1 for restaurant, 2 for take-away',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_stats_widget` (
  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned DEFAULT 0 NOT NULL,
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

/* apis */

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

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_api_login_event_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_login` int(10) unsigned NOT NULL,
  `id_event` varchar(128) NOT NULL,
  `options` text DEFAULT NULL COMMENT 'JSON configuration for event/user relation',
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

/* translations */

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` text DEFAULT NULL,
  `id_room` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `id_menu` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_menus_section` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `id_section` int(10) unsigned NOT NULL,
  `id_parent` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_section_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `id_product` int(10) unsigned NOT NULL,
  -- `id_parent` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_section_product_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `id_option` int(10) unsigned NOT NULL,
  `id_parent` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `id_menu` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_menus_entry` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `id_entry` int(10) unsigned NOT NULL,
  `id_parent` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_menus_entry_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias` varchar(64) DEFAULT NULL,
  `id_option` int(10) unsigned NOT NULL,
  `id_parent` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_menus_entry_topping_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `id_group` int(10) unsigned NOT NULL,
  `id_parent` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_topping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(256) DEFAULT '',
  `id_topping` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_menus_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `id_attribute` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_takeaway_deal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `id_deal` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `note` text DEFAULT NULL,
  `prenote` text DEFAULT NULL,
  `id_payment` int(10) unsigned NOT NULL,
  `tag` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__vikrestaurants_lang_customf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `choose` text DEFAULT NULL,
  `poplink` varchar(256) DEFAULT '',
  `id_customf` int(10) unsigned NOT NULL,
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

INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,0,1,1,0,'CUSTOMF_NAME','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,0,2,1,0,'CUSTOMF_LNAME','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,0,3,2,0,'CUSTOMF_EMAIL','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (0,0,4,3,0,'CUSTOMF_PHONE','text','US');

INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (0,0,1,0,1,'CUSTOMF_TKINFO','separator','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,0,2,1,1,'CUSTOMF_TKNAME','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,0,3,2,1,'CUSTOMF_TKEMAIL','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,0,4,3,1,'CUSTOMF_TKPHONE','text','US');

INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (0,0,5,5,1,'CUSTOMF_TKDELIVERY','separator','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,1,6,4,1,'CUSTOMF_TKADDRESS','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (1,1,7,6,1,'CUSTOMF_TKZIP','text','');
INSERT INTO `#__vikrestaurants_custfields` (`required`,`required_delivery`,`ordering`,`rule`,`group`,`name`,`type`,`choose`) VALUES (0,0,8,11,1,'CUSTOMF_TKNOTE','text','');

INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,1,'Arrived','arrived','arrived.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,2,'Seated','','seated.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,3,'Starters','','starters.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,4,'Main Courses','','main_courses.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,5,'Dessert','','dessert.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,6,'Table Cleared','closebill','table_cleared.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (1,7,'Bill Dropped','leave','bill_dropped.png');

INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`,`notes`) VALUES (2,1,'Preparing','preparing','preparing.png','Your order is in preparation.');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`,`notes`) VALUES (2,2,'Ready','','ready.png','Your order is ready.');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`,`notes`) VALUES (2,3,'Delivered','completed','delivered.png','Your order has been delivered.');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`,`notes`) VALUES (2,4,'Picked','completed','picked.png','The order has been picked up.');

INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (3,1,'Scheduled','','scheduled.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (3,2,'Cooking','cooking','cooking.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (3,3,'Prepared','prepared','prepared.png');
INSERT INTO `#__vikrestaurants_res_code` (`type`,`ordering`,`code`,`rule`,`icon`) VALUES (3,4,'Delivering','waiter','delivering.png');

INSERT INTO `#__vikrestaurants_takeaway_menus_attribute` (`name`, `published`, `icon`, `ordering`) VALUES ('Spicy', 1, 'spicy.png', 1);
INSERT INTO `#__vikrestaurants_takeaway_menus_attribute` (`name`, `published`, `icon`, `ordering`) VALUES ('Vegetarian', 1, 'veggie.png', 2);
INSERT INTO `#__vikrestaurants_takeaway_menus_attribute` (`name`, `published`, `icon`, `ordering`) VALUES ('Contains Nuts', 1, 'nuts.png', 3);

INSERT INTO `#__vikrestaurants_countries` (`country_name`, `country_2_code`, `country_3_code`, `phone_prefix`, `published`) VALUES
('Afghanistan', 'AF', 'AFG', '+93', 1),
('Aland', 'AX', 'ALA', '+358 18', 1),
('Albania', 'AL', 'ALB', '+355', 1),
('Algeria', 'DZ', 'DZA', '+213', 1),
('American Samoa', 'AS', 'ASM', '+1 684', 1),
('Andorra', 'AD', 'AND', '+376', 1),
('Angola', 'AO', 'AGO', '+244', 1),
('Anguilla', 'AI', 'AIA', '+1 264', 1),
('Antarctica', 'AQ', 'ATA', '+6721', 1),
('Antigua and Barbuda', 'AG', 'ATG', '+1 268', 1),
('Argentina', 'AR', 'ARG', '+54', 1),
('Armenia', 'AM', 'ARM', '+374', 1),
('Aruba', 'AW', 'ABW', '+297', 1),
('Ascension Island', 'AC', 'ASC', '+247', 1),
('Australia', 'AU', 'AUS', '+61', 1),
('Austria', 'AT', 'AUT', '+43', 1),
('Azerbaijan', 'AZ', 'AZE', '+994', 1),
('Bahamas', 'BS', 'BHS', '+1 242', 1),
('Bahrain', 'BH', 'BHR', '+973', 1),
('Bangladesh', 'BD', 'BGD', '+880', 1),
('Barbados', 'BB', 'BRB', '+1 246', 1),
('Belarus', 'BY', 'BLR', '+375', 1),
('Belgium', 'BE', 'BEL', '+32', 1),
('Belize', 'BZ', 'BLZ', '+501', 1),
('Benin', 'BJ', 'BEN', '+229', 1),
('Bermuda', 'BM', 'BMU', '+1 441', 1),
('Bhutan', 'BT', 'BTN', '+975', 1),
('Bolivia', 'BO', 'BOL', '+591', 1),
('Bosnia and Herzegovina', 'BA', 'BIH', '+387', 1),
('Botswana', 'BW', 'BWA', '+267', 1),
('Bouvet Island', 'BV', 'BVT', '+47', 0),
('Brazil', 'BR', 'BRA', '+55', 1),
('British Indian Ocean Territory', 'IO', 'IOT', '+246', 1),
('British Virgin Islands', 'VG', 'VGB', '+1 284', 1),
('Brunei', 'BN', 'BRN', '+673', 1),
('Bulgaria', 'BG', 'BGR', '+359', 1),
('Burkina Faso', 'BF', 'BFA', '+226', 1),
('Burundi', 'BI', 'BDI', '+257', 1),
('Cambodia', 'KH', 'KHM', '+855', 1),
('Cameroon', 'CM', 'CMR', '+237', 1),
('Canada', 'CA', 'CAN', '+1', 1),
('Cape Verde', 'CV', 'CPV', '+238', 1),
('Cayman Islands', 'KY', 'CYM', '+1 345', 1),
('Central African Republic', 'CF', 'CAF', '+236', 1),
('Chad', 'TD', 'TCD', '+235', 1),
('Chile', 'CL', 'CHL', '+56', 1),
('China', 'CN', 'CHN', '+86', 1),
('Christmas Island', 'CX', 'CXR', '+61 8964', 1),
('Cocos Islands', 'CC', 'CCK', '+61 8962', 1),
('Colombia', 'CO', 'COL', '+57', 1),
('Comoros', 'KM', 'COM', '+269', 1),
('Cook Islands', 'CK', 'COK', '+682', 1),
('Costa Rica', 'CR', 'CRI', '+506', 1),
('Cote d''Ivoire', 'CI', 'CIV', '+225', 1),
('Croatia', 'HR', 'HRV', '+385', 1),
('Cuba', 'CU', 'CUB', '+53', 1),
('Cyprus', 'CY', 'CYP', '+357', 1),
('Czech Republic', 'CZ', 'CZE', '+420', 1),
('Democratic Republic of the Congo', 'CD', 'COD', '+243', 1),
('Denmark', 'DK', 'DNK', '+45', 1),
('Djibouti', 'DJ', 'DJI', '+253', 1),
('Dominica', 'DM', 'DMA', '+1 767', 1),
('Dominican Republic', 'DO', 'DOM', '+1 809', 1),
('East Timor', 'TL', 'TLS', '+670', 1),
('Ecuador', 'EC', 'ECU', '+593', 1),
('Egypt', 'EG', 'EGY', '+20', 1),
('El Salvador', 'SV', 'SLV', '+503', 1),
('Equatorial Guinea', 'GQ', 'GNQ', '+240', 1),
('Eritrea', 'ER', 'ERI', '+291', 1),
('Estonia', 'EE', 'EST', '+372', 1),
('Ethiopia', 'ET', 'ETH', '+251', 1),
('Falkland Islands', 'FK', 'FLK', '+500', 1),
('Faroe Islands', 'FO', 'FRO', '+298', 1),
('Fiji', 'FJ', 'FJI', '+679', 1),
('Finland', 'FI', 'FIN', '+358', 1),
('France', 'FR', 'FRA', '+33', 1),
('French Austral and Antarctic Territories', 'TF', 'ATF', '+33', 1),
('French Guiana', 'GF', 'GUF', '+594', 1),
('French Polynesia', 'PF', 'PYF', '+689', 1),
('Gabon', 'GA', 'GAB', '+241', 1),
('Gambia', 'GM', 'GMB', '+220', 1),
('Georgia', 'GE', 'GEO', '+995', 1),
('Germany', 'DE', 'DEU', '+49', 1),
('Ghana', 'GH', 'GHA', '+233', 1),
('Gibraltar', 'GI', 'GIB', '+350', 1),
('Greece', 'GR', 'GRC', '+30', 1),
('Greenland', 'GL', 'GRL', '+299', 1),
('Grenada', 'GD', 'GRD', '+1 473', 1),
('Guadeloupe', 'GP', 'GLP', '+590', 1),
('Guam', 'GU', 'GUM', '+1 671', 1),
('Guatemala', 'GT', 'GTM', '+502', 1),
('Guernsey', 'GG', 'GGY', '+44 1481', 1),
('Guinea', 'GN', 'GIN', '+224', 1),
('Guinea-Bissau', 'GW', 'GNB', '+245', 1),
('Guyana', 'GY', 'GUY', '+592', 1),
('Haiti', 'HT', 'HTI', '+509', 1),
('Heard and McDonald Islands', 'HM', 'HMD', '+61', 0),
('Honduras', 'HN', 'HND', '+504', 1),
('Hong Kong', 'HK', 'HKG', '+852', 1),
('Hungary', 'HU', 'HUN', '+36', 1),
('Iceland', 'IS', 'ISL', '+354', 1),
('India', 'IN', 'IND', '+91', 1),
('Indonesia', 'ID', 'IDN', '+62', 1),
('Iran', 'IR', 'IRN', '+98', 1),
('Iraq', 'IQ', 'IRQ', '+964', 1),
('Ireland', 'IE', 'IRL', '+353', 1),
('Isle of Man', 'IM', 'IMN', '+44 1624', 1),
('Israel', 'IL', 'ISR', '+972', 1),
('Italy', 'IT', 'ITA', '+39', 1),
('Jamaica', 'JM', 'JAM', '+1 876', 1),
('Japan', 'JP', 'JPN', '+81', 1),
('Jersey', 'JE', 'JEY', '+44 1534', 1),
('Jordan', 'JO', 'JOR', '+962', 1),
('Kazakhstan', 'KZ', 'KAZ', '+7', 1),
('Kenya', 'KE', 'KEN', '+254', 1),
('Kiribati', 'KI', 'KIR', '+686', 1),
('Kosovo', 'KV', 'KV', '+381', 1),
('Kuwait', 'KW', 'KWT', '+965', 1),
('Kyrgyzstan', 'KG', 'KGZ', '+996', 1),
('Laos', 'LA', 'LAO', '+856', 1),
('Latvia', 'LV', 'LVA', '+371', 1),
('Lebanon', 'LB', 'LBN', '+961', 1),
('Lesotho', 'LS', 'LSO', '+266', 1),
('Liberia', 'LR', 'LBR', '+231', 1),
('Libya', 'LY', 'LBY', '+218', 1),
('Liechtenstein', 'LI', 'LIE', '+423', 1),
('Lithuania', 'LT', 'LTU', '+370', 1),
('Luxembourg', 'LU', 'LUX', '+352', 1),
('Macau', 'MO', 'MAC', '+853', 1),
('Macedonia', 'MK', 'MKD', '+389', 1),
('Madagascar', 'MG', 'MDG', '+261', 1),
('Malawi', 'MW', 'MWI', '+265', 1),
('Malaysia', 'MY', 'MYS', '+60', 1),
('Maldives', 'MV', 'MDV', '+960', 1),
('Mali', 'ML', 'MLI', '+223', 1),
('Malta', 'MT', 'MLT', '+356', 1),
('Marshall Islands', 'MH', 'MHL', '+692', 1),
('Martinique', 'MQ', 'MTQ', '+596', 1),
('Mauritania', 'MR', 'MRT', '+222', 1),
('Mauritius', 'MU', 'MUS', '+230', 1),
('Mayotte', 'YT', 'MYT', '+262', 1),
('Mexico', 'MX', 'MEX', '+52', 1),
('Micronesia', 'FM', 'FSM', '+691', 1),
('Moldova', 'MD', 'MDA', '+373', 1),
('Monaco', 'MC', 'MCO', '+377', 1),
('Mongolia', 'MN', 'MNG', '+976', 1),
('Montenegro', 'ME', 'MNE', '+382', 1),
('Montserrat', 'MS', 'MSR', '+1 664', 1),
('Morocco', 'MA', 'MAR', '+212', 1),
('Mozambique', 'MZ', 'MOZ', '+258', 1),
('Myanmar', 'MM', 'MMR', '+95', 1),
('Namibia', 'NA', 'NAM', '+264', 1),
('Nauru', 'NR', 'NRU', '+674', 1),
('Nepal', 'NP', 'NPL', '+977', 1),
('Netherlands', 'NL', 'NLD', '+31', 1),
('Netherlands Antilles', 'AN', 'ANT', '+599', 1),
('New Caledonia', 'NC', 'NCL', '+687', 1),
('New Zealand', 'NZ', 'NZL', '+64', 1),
('Nicaragua', 'NI', 'NIC', '+505', 1),
('Niger', 'NE', 'NER', '+227', 1),
('Nigeria', 'NG', 'NGA', '+234', 1),
('Niue', 'NU', 'NIU', '+683', 1),
('Norfolk Island', 'NF', 'NFK', '+6723', 1),
('North Korea', 'KP', 'PRK', '+850', 1),
('Northern Mariana Islands', 'MP', 'MNP', '+1 670', 1),
('Norway', 'NO', 'NOR', '+47', 1),
('Oman', 'OM', 'OMN', '+968', 1),
('Pakistan', 'PK', 'PAK', '+92', 1),
('Palau', 'PW', 'PLW', '+680', 1),
('Palestine', 'PS', 'PSE', '+970', 1),
('Panama', 'PA', 'PAN', '+507', 1),
('Papua New Guinea', 'PG', 'PNG', '+675', 1),
('Paraguay', 'PY', 'PRY', '+595', 1),
('Peru', 'PE', 'PER', '+51', 1),
('Philippines', 'PH', 'PHL', '+63', 1),
('Pitcairn Islands', 'PN', 'PCN', '+649', 1),
('Poland', 'PL', 'POL', '+48', 1),
('Portugal', 'PT', 'PRT', '+351', 1),
('Puerto Rico', 'PR', 'PRI', '+1 787', 1),
('Qatar', 'QA', 'QAT', '+974', 1),
('Republic of the Congo', 'CG', 'COG', '+242', 1),
('Reunion', 'RE', 'REU', '+262', 1),
('Romania', 'RO', 'ROM', '+40', 1),
('Russia', 'RU', 'RUS', '+7', 1),
('Rwanda', 'RW', 'RWA', '+250', 1),
('Saint Helena', 'SH', 'SHN', '+290', 1),
('Saint Kitts and Nevis', 'KN', 'KNA', '+1 869', 1),
('Saint Lucia', 'LC', 'LCA', '+1 758', 1),
('Saint Pierre and Miquelon', 'PM', 'SPM', '+508', 1),
('Saint Vincent and the Grenadines', 'VC', 'VCT', '+1 784', 1),
('Samoa', 'WS', 'WSM', '+685', 1),
('San Marino', 'SM', 'SMR', '+378', 1),
('Sao Tome and Principe', 'ST', 'STP', '+239', 1),
('Saudi Arabia', 'SA', 'SAU', '+966', 1),
('Senegal', 'SN', 'SEN', '+221', 1),
('Serbia', 'RS', 'SRB', '+381', 1),
('Seychelles', 'SC', 'SYC', '+248', 1),
('Sierra Leone', 'SL', 'SLE', '+232', 1),
('Singapore', 'SG', 'SGP', '+65', 1),
('Sint Maarten', 'SX', 'SXM', '+1 721', 1),
('Slovakia', 'SK', 'SVK', '+421', 1),
('Slovenia', 'SI', 'SVN', '+386', 1),
('Solomon Islands', 'SB', 'SLB', '+677', 1),
('Somalia', 'SO', 'SOM', '+252', 1),
('South Africa', 'ZA', 'ZAF', '+27', 1),
('South Georgia and the South Sandwich Islands', 'GS', 'SGS', '+44', 1),
('South Korea', 'KR', 'KOR', '+82', 1),
('South Sudan', 'SS', 'SSD', '+211', 1),
('Spain', 'ES', 'ESP', '+34', 1),
('Sri Lanka', 'LK', 'LKA', '+94', 1),
('Sudan', 'SD', 'SDN', '+249', 1),
('Suriname', 'SR', 'SUR', '+597', 1),
('Svalbard and Jan Mayen Islands', 'SJ', 'SJM', '+47', 0),
('Swaziland', 'SZ', 'SWZ', '+268', 1),
('Sweden', 'SE', 'SWE', '+46', 1),
('Switzerland', 'CH', 'CHE', '+41', 1),
('Syria', 'SY', 'SYR', '+963', 1),
('Taiwan', 'TW', 'TWN', '+886', 1),
('Tajikistan', 'TJ', 'TJK', '+992', 1),
('Tanzania', 'TZ', 'TZA', '+255', 1),
('Thailand', 'TH', 'THA', '+66', 1),
('Togo', 'TG', 'TGO', '+228', 1),
('Tokelau', 'TK', 'TKL', '+690', 1),
('Tonga', 'TO', 'TON', '+676', 1),
('Trinidad and Tobago', 'TT', 'TTO', '+1 868', 1),
('Tunisia', 'TN', 'TUN', '+216', 1),
('Turkey', 'TR', 'TUR', '+90', 1),
('Turkmenistan', 'TM', 'TKM', '+993', 1),
('Turks and Caicos Islands', 'TC', 'TCA', '+1 649', 1),
('Tuvalu', 'TV', 'TUV', '+688', 1),
('U.S. Virgin Islands', 'VI', 'VIR', '+1 340', 1),
('Uganda', 'UG', 'UGA', '+256', 1),
('Ukraine', 'UA', 'UKR', '+380', 1),
('United Arab Emirates', 'AE', 'ARE', '+971', 1),
('United Kingdom', 'GB', 'GBR', '+44', 1),
('United States', 'US', 'USA', '+1', 1),
('Uruguay', 'UY', 'URY', '+598', 1),
('Uzbekistan', 'UZ', 'UZB', '+998', 1),
('Vanuatu', 'VU', 'VUT', '+678', 1),
('Vatican City', 'VA', 'VAT', '+379', 1),
('Venezuela', 'VE', 'VEN', '+58', 1),
('Vietnam', 'VN', 'VNM', '+84', 1),
('Wallis and Futuna', 'WF', 'WLF', '+681', 1),
('Western Sahara', 'EH', 'ESH', '+212 28', 1),
('Yemen', 'YE', 'YEM', '+967', 1),
('Zambia', 'ZM', 'ZMB', '+260', 1),
('Zimbabwe', 'ZW', 'ZWE', '+263', 1);

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
('', 'orders'      , 'center', 'takeaway', 'dashboard', ''     , 4),
('', 'times'       , 'center', 'takeaway', 'dashboard', 'small', 5);

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'restname', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enablerestaurant', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enabletakeaway', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'adminemail', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'senderemail', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mailcustwhen', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mailoperwhen', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mailadminwhen',  2);
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mailtmpl', 'customer_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'adminmailtmpl', 'admin_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'cancmailtmpl', 'cancellation_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'companylogo', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'dateformat', 'm/d/Y' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'timeformat', 'h:i A' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currencysymb', '&euro;' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currencyname', 'EUR' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'symbpos', '1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currdecimalsep', '.' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currthousandssep', ',' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'currdecimaldig', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'reservationreq', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'opentimemode', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'minuteintervals', 30 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'averagetimestay', 60 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mindate', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'maxdate', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'minimumpeople', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'maximumpeople', 20 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'largepartylbl', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'largepartyurl', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'safedistance', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'safefactor', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'askdeposit', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'resdeposit', 10 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'costperperson', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tablocktime', 20 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'phoneprefix', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'loadjquery', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'uiradio', 'ios' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'hourfrom', 14 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'hourto', 23 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'bookrestr', 30 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'closingdays', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'showfooter', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'version', '1.8.5' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'bcv', '1.8.5' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'loginreq', '1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enablereg', '1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'refreshdash', 30 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'ondashboard', '1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'defstatus', 'CONFIRMED' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'selfconfirm', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'choosemenu', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enablecanc', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'cancreason', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'canctime', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'cancmins', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'orderfood', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'editfood', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'applycoupon', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'stopuntil', -1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'firstconfig', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'firstmediaconfig', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'multilanguage', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mainmenustatus', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mediaprop', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'invoiceobj', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'taxesratio', '0.0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'usetaxes', '0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'printorderstext', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'update_extra_fields', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'listablecols', 'id,sid,checkin_ts,people,tname,customer,mail,phone,info,deposit,billval,billclosed,rescode,status' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'listablecf', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'gdpr', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'policylink', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'exportresparams', '{}' );

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'googleapikey', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'googleapiplaces', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'googleapidirections', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'googleapistaticmap', 1 );

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mincostperorder', 4.0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkminint', 15 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'asapafter', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'mealsperint', 10 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmealsbackslots', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkordperint', 10 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkordmaxser', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'deliveryservice', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'dsprice', '3.5' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'dspercentot', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'pickupprice', '0.0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'pickuppercentot', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'freedelivery', 20 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tklocktime', 15 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tknote', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkshowimages', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkshowtimes', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tktaxesratio', '0.0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkusetaxes', '0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkshowtaxes', '0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkloginreq', '1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkenablereg', '1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkdefstatus', 'CONFIRMED' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkselfconfirm', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkenablecanc', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkcancreason', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkcanctime', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkcancmins', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmaxitems', 100 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkallowdate', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmindate', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmaxdate', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkwhenopen', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkpreorder', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkuseoverlay', 2 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmailcustwhen', 1);
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmailoperwhen', 1);
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmailadminwhen', 2);
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkmailtmpl', 'takeaway_customer_email_tmpl.php');
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkadminmailtmpl', 'takeaway_admin_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkcancmailtmpl', 'takeaway_cancellation_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkreviewmailtmpl', 'takeaway_review_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tklistablecols', 'id,sid,checkin_ts,delivery,customer,mail,phone,info,totpay,taxes,rescode,status' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkenablestock', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkstockmailtmpl', 'takeaway_stock_email_tmpl.php' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkaddrorigins', '[]' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkproddesclength', 256 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkstopuntil', -1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkdefaultservice', 'delivery' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkenablegratuity', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tkdefgratuity', '10:1' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'tklistablecf', '' );

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'enablereviews', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revtakeaway', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revleavemode', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revcommentreq', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revminlength', 48 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revmaxlength', 512 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revlimlist', 5 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revlangfilter', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'revautopublished', 0 );

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apifw', 0 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apilogmode', 1 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apilogflush', 7 );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'apimaxfail', 20 );

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'securehashkey', '' );

INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smsapi', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smsapiwhen', '3' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smsapito', '0' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smsapiadminphone', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smsapifields', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smstextcust', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smstmplcust', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smstmpladmin', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smstmpltkcust', '' );
INSERT INTO `#__vikrestaurants_config`( `param`, `setting` ) VALUES( 'smstmpltkadmin', '' );

INSERT INTO `#__vikrestaurants_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`ordering`) VALUES ('Pay on Arrival','bank_transfer','0','','0.00','1','1');
INSERT INTO `#__vikrestaurants_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`ordering`) VALUES ('PayPal','paypal','0','','0.00','0','2');
INSERT INTO `#__vikrestaurants_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`ordering`) VALUES ('Offline Credit Card','offline_credit_card','0','','0.00','0','3');
