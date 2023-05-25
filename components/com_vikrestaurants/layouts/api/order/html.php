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

VRELoader::import('library.mail.factory');

// get notification e-mail for admin
$adminMail = VREMailFactory::getInstance(
	$args->get('type') == 0 ? 'restaurant' : 'takeaway', 'admin',
	$order->id,
	$args->get('langtag')
);

// display e-mail template
echo $adminMail->getHtml();
