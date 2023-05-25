<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_cart
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

$input = JFactory::getApplication()->input;

// backward compatibility

$options = array(
	'version' => '1.5.2',
);

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_takeaway_cart/mod_vikrestaurants_takeaway_cart.css', $options);

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');
// load VikRestaurants utils
$vik->addScript(VREASSETS_URI . 'js/vikrestaurants.js');

// load JS currency helper
JHtml::_('vrehtml.assets.currency');

/**
 * Use FontAwesome to display the icons.
 *
 * @since 1.5
 */
JHtml::_('vrehtml.assets.fontawesome');

$itemid = $params->get('itemid', 0);

// setup environment vars
$TAKEAWAY_CONFIRM_URL = JRoute::_('index.php?option=com_vikrestaurants&view=takeawayconfirm' . ($itemid ? '&Itemid=' . $itemid : ''), false);

$_TAKEAWAY_ = 0;
$_TAKEAWAY_CONFIRM_ = 0;

if (in_array($input->get('view'), array('takeaway', 'takeawayitem')))
{
	$_TAKEAWAY_ = 1;
	// make cart scrollable for takeaway menus and takeaway item pages
	$vik->addScript(VREMODULES_URI . 'mod_vikrestaurants_takeaway_cart/mod_vikrestaurants_takeaway_cart.js', $options);
}
else if ($input->get('view') == 'takeawayconfirm')
{
	$_TAKEAWAY_CONFIRM_ = 1;
}

/**
 * Use an helper method to calculate the minimum cost 
 * needed to proceed with the purchase.
 *
 * @since 1.5.2
 */
$minCostPerOrder = Vikrestaurants::getTakeAwayMinimumCostPerOrder();

// load tmpl/default.php
require JModuleHelper::getLayoutPath('mod_vikrestaurants_takeaway_cart', $params->get('layout'));
