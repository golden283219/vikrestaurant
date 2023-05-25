<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_grid
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
	'version' => '1.4.2',
);

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_takeaway_grid/src/mod_vikrestaurants_takeaway_grid.css', $options);

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');

// get selected products

$products = VikRestaurantsTakeAwayGridHelper::getProducts($params);

// get available and filtered menus

$menus = VikRestaurantsTakeAwayGridHelper::getAllMenus($products);

// in case the rating param is not set to NEVER (0)
// load the rating statistics of each item
if ((int) $params->get('rating'))
{
	// import reviews handler from library

	VRELoader::import('library.reviews.handler');

	$reviewsHandler = new ReviewsHandler();

	foreach ($products as $i => $p)
	{
		// load review statistics for each product
		// contains [count, rating, halfRating]
		$products[$i]->reviewsRatio = $reviewsHandler->takeaway()->getAverageRatio($p->idEntry);
	}
}

if (!count($products))
{
	// do nothing in case there is no product
	return;
}

// load tmpl/default.php

require JModuleHelper::getLayoutPath('mod_vikrestaurants_takeaway_grid', $params->get('layout'));
