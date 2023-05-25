<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_map
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

VikRestaurants::loadGraphics2D();

// backward compatibility

$options = array(
	'version' => '1.1.2',
);

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_takeaway_map/mod_vikrestaurants_takeaway_map.css', $options);

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');

// load JS utils
JHtml::_('vrehtml.assets.utils');

// load maps from VikRestaurants 
VikRestaurants::load_googlemaps();

/**
 * Load the supported locations through the internal module helper.
 *
 * @since 1.1.2
 */
$locations = VikRestaurantsTakeAwayMapHelper::getLocations();

/**
 * Get last searched delivery address for being
 * immediately shown within the map.
 *
 * @since 1.1
 */
$address = JFactory::getSession()->get('delivery_address', null, 'vre');

if ($address)
{
	// validate lat and lng
	if (!isset($address->latitude) || !isset($address->longitude))
	{
		// missing coordinates, unset address
		$address = null;
	}
}

// load tmpl/default.php
require JModuleHelper::getLayoutPath('mod_vikrestaurants_takeaway_map', $params->get('layout'));
