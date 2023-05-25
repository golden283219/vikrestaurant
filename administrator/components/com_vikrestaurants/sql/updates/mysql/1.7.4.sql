ALTER TABLE `#__vikrestaurants_reservation`
ADD COLUMN `tip_amount` decimal(10,2) DEFAULT 0.0 AFTER `discount_val`,
ADD COLUMN `need_notif` tinyint(1) DEFAULT 0 COMMENT '1 if the record requires to be notified';

ALTER TABLE `#__vikrestaurants_takeaway_reservation`
ADD COLUMN `tip_amount` decimal(10,2) DEFAULT 0.0 AFTER `discount_val`,
ADD COLUMN `need_notif` tinyint(1) DEFAULT 0 COMMENT '1 if the record requires to be notified';

UPDATE `#__vikrestaurants_config` SET `setting`='1.7.4' WHERE `param`='version' LIMIT 1;

INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('listablecf', '');
INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('tklistablecf', '');
INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('tkdefaultservice', 'delivery');
INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('tkenablegratuity', 0);
INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('tkdefgratuity', '10:1');
INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('gdpr', 0);
INSERT INTO `#__vikrestaurants_config` (`param`, `setting`) VALUES ('policylink', '');