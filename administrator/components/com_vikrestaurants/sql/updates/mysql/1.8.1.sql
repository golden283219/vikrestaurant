ALTER TABLE `#__vikrestaurants_gpayments`
ADD COLUMN `selfconfirm` tinyint(1) NOT NULL DEFAULT 0 AFTER `setconfirmed`;

UPDATE `#__vikrestaurants_config` SET `setting`='1.8.1' WHERE `param`='version' LIMIT 1;