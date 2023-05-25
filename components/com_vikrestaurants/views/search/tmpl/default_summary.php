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

$data = array(
	/**
	 * The check-in formatted date.
	 *
	 * @var string
	 */
	'date' => date(VREFactory::getConfig()->get('dateformat'), $this->checkinTime->ts),

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
	 * An optional class suffix.
	 *
	 * @var string
	 */
	'suffix' => ' search',
);

/**
 * The step bar is displayed from the layout below:
 * /components/com_vikrestaurants/layouts/blocks/summary.php
 *
 * @since 1.8
 */
echo JLayoutHelper::render('blocks.summary', $data);
