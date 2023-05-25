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
 * Template file used to display a summary of
 * the selected check-in details. 
 *
 * @since 1.8
 */

$config = VREFactory::getConfig();

$data = array(
	/**
	 * The check-in formatted date.
	 *
	 * @var string
	 */
	'date' => date($config->get('dateformat'), $this->checkinTime->ts),

	/**
	 * The check-in formatted time.
	 *
	 * @var string
	 */
	'time' => $this->checkinTime->format,

	/**
	 * The selected number of participants.
	 *
	 * @var integer
	 */
	'people' => $this->args['people'],

	/**
	 * The total deposit to leave.
	 *
	 * @var float
	 */
	'deposit' => $this->totalDeposit,

	/**
	 * An optional class suffix.
	 *
	 * @var string
	 */
	'suffix' => ' confirmation',
);

// get reservation requirements
$resreq = $config->getUint('reservationreq');

if ($resreq != 2)
{
	// show room in case of table/room selection
	$data['room'] = $this->table->room->name;

	if ($resreq == 0)
	{
		// show table in case of table selection
		$data['table'] = $this->table->name;
	}
}

/**
 * The step bar is displayed from the layout below:
 * /components/com_vikrestaurants/layouts/blocks/summary.php
 *
 * @since 1.8
 */
echo JLayoutHelper::render('blocks.summary', $data);
