<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_items
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// require autoloader
if (defined('JPATH_SITE') && JPATH_SITE !== 'JPATH_SITE')
{
	require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

// backward compatibility

$options = array(
	'version' => '1.3.3',
);

// backward compatibility

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_items/src/owl.carousel.css', $options);
$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_items/src/owl.theme.css', $options);
$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_items/mod_vikrestaurants_items.css', $options);

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');
$vik->addScript(VREMODULES_URI . 'mod_vikrestaurants_items/src/owl.carousel.min.js', $options);

// get products

$products = VikRestaurantsItemsHelper::getProducts($params);

// load tmpl/default.php

require JModuleHelper::getLayoutPath('mod_vikrestaurants_items', $params->get('layout'));
