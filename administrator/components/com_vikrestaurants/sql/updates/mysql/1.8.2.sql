ALTER TABLE `#__vikrestaurants_specialdays`
ADD COLUMN`delivery_areas` varchar(64) DEFAULT NULL COMMENT 'JSON array containing the accepted delivery areas' AFTER `delivery_service`;

ALTER TABLE `#__vikrestaurants_takeaway_menus_entry`
ADD COLUMN `img_extra` varchar(256) DEFAULT NULL AFTER `img_path`;

ALTER TABLE `#__vikrestaurants_takeaway_entry_group_assoc`
ADD COLUMN `description` varchar(128) DEFAULT NULL AFTER `title`,
ADD COLUMN `use_quantity` tinyint(1) DEFAULT 0 AFTER `max_toppings`;

ALTER TABLE `#__vikrestaurants_takeaway_res_prod_topping_assoc`
ADD COLUMN `units` tinyint(2) DEFAULT 1 AFTER `id_topping`;

ALTER TABLE `#__vikrestaurants_takeaway_reservation`
ADD COLUMN `id_operator` int(10) DEFAULT 0 AFTER `id_user`;

ALTER TABLE `#__vikrestaurants_lang_takeaway_menus_entry_topping_group`
ADD COLUMN `description` varchar(128) DEFAULT NULL AFTER `name`;

INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES
('tkordperint', 10),
('tkordmaxser', 1),
('firstmediaconfig', 0);

UPDATE `#__vikrestaurants_config` SET `setting`='1.8.2' WHERE `param`='version' LIMIT 1;