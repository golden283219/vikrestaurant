<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_worktimes
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Worktimes module.
 *
 * @since 1.1.2
 */
class VikRestaurantsWorktimesHelper
{
	/**
	 * Get the working times of the current week, starting from today.
	 *
	 * @param 	integer  $group  The section to look for (1: restaurant, 2: take-away).
	 *
	 * @return 	array 	 The week worktimes.
	 *
	 * @uses 	getDetailsOnDay() 	Recover the details of a certain day.
	 */
	public static function getDaysWorkTimes($group = 1)
	{
		$days = array();

		$date = getdate();

		for ($i = 0; $i < 7; $i++)
		{
			$days[] = self::getDetailsOnDay($date[0], $group);

			$date = getdate(mktime(0, 0, 0, $date['mon'], $date['mday'] + 1, $date['year']));
		}

		return $days;
	}

	/**
	 * Find the specified working shift within the array.
	 *
	 * @param 	array 	 $arr 	The list of shifts.
	 * @param 	integer  $id 	The shift ID to search.
	 *
	 * @return 	mixed 	 The working shift on success, otherwise false. 
	 */
	public static function findWorkingShiftInArray($arr, $id)
	{
		foreach ($arr as $w)
		{
			if ($w['id'] == $id)
			{
				return $w;
			}
		}

		return false;
	}

	/**
	 * Recover the working details of the specified day.
	 *
	 * @param 	integer  $ts  	 The timestamp of the day.
	 * @param 	integer  $group  The section to look for (1: restaurant, 2: take-away).
	 *
	 * @return 	array 	 The working details.
	 */
	public static function getDetailsOnDay($ts, $group = 1)
	{
		// take care of closing days
		$args = array(
			'closure' => true,
		);

		/**
		 * Use native helper class to recover daily opening times.
		 * Use strict mode to recover a fictitious working shift
		 * in case of continuous opening times.
		 *
		 * @since 1.2
		 */
		$shifts = JHtml::_('vikrestaurants.shifts', $group, $ts, $strict = true, $args);

		// case to array for backward compatibility
		$shifts = array_map(function($sh)
		{
			return (array) $sh;
		}, $shifts);

		return array(
			'timestamp' => $ts,
			'status' 	=> count($shifts),
			'shifts' 	=> $shifts,
		);
	}
}
