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
 * @var  mixed      $order    The order details.
 * @var  JRegistry  $args     The event arguments.
 * @var  boolean    $logo     True to show the logo, false otherwise.
 * @var  boolean    $company  True to show the restaurant name, false otherwise.
 * @var  boolean    $details  True to show the order details, false otherwise.
 * @var  boolean    $items    True to show the ordered items, false otherwise.
 * @var  boolean    $total    True to show the total lines, false otherwise.
 * @var  boolean    $billing  True to show the billing details, false otherwise.
 */

$displayData['logo']    = isset($displayData['logo'])    ? $displayData['logo']    : true;
$displayData['company'] = isset($displayData['company']) ? $displayData['company'] : true;
$displayData['details'] = isset($displayData['details']) ? $displayData['details'] : true;
$displayData['items']   = isset($displayData['items'])   ? $displayData['items']   : true;
$displayData['total']   = isset($displayData['total'])   ? $displayData['total']   : true;
$displayData['billing'] = isset($displayData['billing']) ? $displayData['billing'] : true;

// dispatch the sublayout able to handle the given group
echo $this->sublayout($displayData['args']->get('type') == 0 ? 'restaurant' : 'takeaway', $displayData);
