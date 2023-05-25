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

if (!$this->ACCESS)
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

// prepare widget layout data
$data = array(
	'widget'   => 'reservations',
	'group'    => 'restaurant',
	'config'   => array(
		'items'    => 10,
		'latest'   => 1,
		'incoming' => 1,
		'current'  => 1,
	),
	'timer'    => 60,
);

// display widget by using an apposite layout
echo JLayoutHelper::render('oversight.widget', $data);
