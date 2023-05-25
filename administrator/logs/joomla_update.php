#
#<?php die('Forbidden.'); ?>
#Date: 2023-04-19 09:25:59 UTC
#Software: Joomla! 4.2.9 Stable [ Uaminifu ] 14-March-2023 15:00 GMT

#Fields: datetime	priority clientip	category	message
2023-04-19T09:25:59+00:00	INFO 77.57.184.37	update	Update started by user Christoph Wenger (837). Old version is 4.2.9.
2023-04-19T09:26:00+00:00	INFO 77.57.184.37	update	Downloading update file from https://s3-us-west-2.amazonaws.com/joomla-official-downloads/joomladownloads/joomla4/Joomla_4.3.0-Stable-Update_Package.zip?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIA6LXDJLNUINX2AVMH%2F20230419%2Fus-west-2%2Fs3%2Faws4_request&X-Amz-Date=20230419T092547Z&X-Amz-Expires=60&X-Amz-SignedHeaders=host&X-Amz-Signature=f0e9b454d547784b1eed35a374618d18f75ba781eca8b0c9072cde445127d67c.
2023-04-19T09:26:06+00:00	INFO 77.57.184.37	update	File Joomla_4.3.0-Stable-Update_Package.zip downloaded.
2023-04-19T09:26:06+00:00	INFO 77.57.184.37	update	Starting installation of new version.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Finalising installation.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Start of SQL updates.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	The current database version (schema) is 4.2.9-2023-03-07.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2022-09-23. Query text: UPDATE `#__extensions` SET `params` = REPLACE(`params`, '}', ',"difference":"Sid.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2022-11-06. Query text: DELETE FROM `#__assets` WHERE `name` LIKE '#__ucm_content.%';.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-01-30. Query text: UPDATE `#__extensions`    SET `params` = REPLACE(`params`, '"negotiate_tls":1', .
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-01-30. Query text: UPDATE `#__extensions`    SET `params` = REPLACE(`params`, '"negotiate_tls":0', .
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-01-30. Query text: UPDATE `#__extensions`    SET `params` = REPLACE(`params`, '"encryption":"none"'.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-01-30. Query text: UPDATE `#__extensions`    SET `params` = REPLACE(`params`, '"host":"ldaps:\\/\\/.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: CREATE TABLE IF NOT EXISTS `#__guidedtours` (   `id` int NOT NULL AUTO_INCREMENT.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: INSERT IGNORE INTO `#__guidedtours` (`id`, `title`, `description`, `ordering`, `.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: CREATE TABLE IF NOT EXISTS `#__guidedtour_steps` (   `id` int NOT NULL AUTO_INCR.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: INSERT IGNORE INTO `#__guidedtour_steps` (`id`, `tour_id`, `title`, `published`,.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, .
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `c.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-15. Query text: INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES (LAST_INSERT_ID(), 0.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-02-25. Query text: ALTER TABLE `#__banners` MODIFY `clickurl` VARCHAR(2048) NOT NULL DEFAULT '';.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-07. Query text: UPDATE `#__guidedtour_steps` SET `target` = '#jform_description,#jform_descripti.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-07. Query text: UPDATE `#__guidedtour_steps` SET `target` = '#jform_articletext,#jform_articlete.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-09. Query text: UPDATE `#__guidedtour_steps` SET `target` = '#jform_published' WHERE `target` = .
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-09. Query text: UPDATE `#__guidedtour_steps` SET `target` = '#jform_sendEmail0' WHERE `target` =.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-09. Query text: UPDATE `#__guidedtour_steps` SET `target` = '#jform_block0' WHERE `target` = '#j.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-09. Query text: UPDATE `#__guidedtour_steps` SET `target` = '#jform_requireReset0' WHERE `target.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-10. Query text: UPDATE `#__guidedtour_steps` SET `type` = 2, `interactive_type` = 2 WHERE `id` I.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-10. Query text: UPDATE `#__guidedtour_steps` SET `type` = 2, `interactive_type` = 3 WHERE `id` I.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-10. Query text: UPDATE `#__guidedtour_steps` SET `target` = 'joomla-field-fancy-select .choices .
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-10. Query text: UPDATE `#__guidedtour_steps` SET `target` = 'joomla-field-fancy-select .choices[.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-10. Query text: UPDATE `#__guidedtour_steps` SET `target` = 'joomla-field-fancy-select .choices[.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-28. Query text: ALTER TABLE `#__guidedtours` DROP COLUMN `asset_id` ;.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-28. Query text: DELETE FROM `#__assets` WHERE `name` LIKE 'com_guidedtours.tour.%';.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-29. Query text: UPDATE `#__guidedtour_steps` SET `target` = 'joomla-field-fancy-select .choices'.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Ran query from file 4.3.0-2023-03-29. Query text: UPDATE `#__guidedtour_steps` SET `target` = 'joomla-field-fancy-select .choices[.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	End of SQL updates.
2023-04-19T09:26:14+00:00	INFO 77.57.184.37	update	Deleting removed files and folders.
2023-04-19T09:26:16+00:00	INFO 77.57.184.37	update	Cleaning up after installation.
2023-04-19T09:26:17+00:00	INFO 77.57.184.37	update	Update to version 4.3.0 is complete.
