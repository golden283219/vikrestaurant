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
extract($displayData);

// clone the order because we don't want to keep
// the changes applied here
$order = clone $order;
// do not display templates
unset($order->template);

if (defined('JSON_PRETTY_PRINT'))
{
	// use pretty print to display the JSON in a
	// readable format
	$json = json_encode($order, JSON_PRETTY_PRINT);
}
else
{
	// pretty print not supported, it will be hard to
	// read the response without using an external parser
	$json = json_encode($order);
}
?>

<pre><?php echo htmlentities($json); ?></pre>
