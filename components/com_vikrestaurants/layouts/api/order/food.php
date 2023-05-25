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
 * Layout variables
 * -----------------
 * @var  mixed      $order  The order details.
 * @var  JRegistry  $args   The event arguments.
 */

// hide totals and billing details
$displayData['total']   = false;
$displayData['billing'] = false;

// render plain layout by excluding the blocks not needed
echo JLayoutHelper::render('api.order.plain', $displayData);
