<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_deals
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

VikRestaurants::loadCartLibrary();
VikRestaurants::loadDealsLibrary();

// backward compatibility

$options = array(
	'version' => '1.2.3',
);

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_takeaway_deals/mod_vikrestaurants_takeaway_deals.css', $options);

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');

// get cart instance to recover check-in date
$cart = TakeAwayCart::getInstance();

// get available deals
$deals = DealsHandler::getAvailableDeals($params->get('date_filtering') == 1 ? $cart->getCheckinTimestamp() : -1);
$deals = DealsHandler::reOrderActiveDeals($deals);

// translate deals
$translations = VikRestaurants::translateTakeawayDeals($deals);

// load tmpl/default.php

require JModuleHelper::getLayoutPath('mod_vikrestaurants_takeaway_deals', $params->get('layout'));
