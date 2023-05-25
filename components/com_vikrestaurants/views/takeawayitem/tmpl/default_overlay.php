<?php
/** 
 * @package     VikRestaurants
 * @subpackage  com_vikrestaurants
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Template file used to display the overlay to load
 * asynchronously the details of the selected product.
 * In case the product supports the toppings selection,
 * it will be shown here.
 *
 * @since 1.8
 */

$data = array(
	/**
	 * An optional Itemid to be used when routing a URL.
	 * For the moment, pass null to take the current one.
	 *
	 * @var integer|null
	 */
	'Itemid' => null,
);

/**
 * The overlay used to edit a product from the cart will be
 * generated by the layout file below:
 * /components/com_vikrestaurants/layouts/blocks/tkoverlay.php
 *
 * @since 1.8
 */
echo JLayoutHelper::render('blocks.tkoverlay', $data);