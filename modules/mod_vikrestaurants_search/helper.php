<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_search
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Search module.
 *
 * @since 1.4.1
 */
class VikRestaurantsSearchHelper
{	
	/**
	 * Returns the values in the query string.
	 *
	 * @return 	array 	The values.
	 */
	public static function getViewHtmlReferences()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		$args = array();

		$args['date'] = $input->getString('date');

		// check if we have arguments set in request
		if ($args['date'])
		{
			if (empty($args['hourmin']))
			{
				/**
				 * Find first available time for the given date.
				 *
				 * @since 1.7.4
				 */
				$args['hourmin'] = VikRestaurants::getClosestTime($args['date'], $next = true);
			}
		}
		else
		{
			/**
			 * Find first available time.
			 * The $date argument is passed by reference and it will
			 * be modified by the method, if needed.
			 *
			 * @since 1.7.4
			 */
			$args['date']    = null;
			$args['hourmin'] = VikRestaurants::getClosestTime($args['date'], $next = true);
		}

		/**
		 * In case date is an integer, convert the timestamp to a date string.
		 *
		 * @since 1.4.1
		 */
		if (is_integer($args['date']))
		{
			$args['date'] = date(VikRestaurants::getDateFormat(), $args['date']);
		}

		$args['people'] = $input->getUint('people', 2);
		
		@list($args['hour'], $args['min']) = explode(':', $args['hourmin']);

		/**
		 * Flag used to check whether the customer already agreed
		 * that all the guests belong to the same family.
		 *
		 * @var   boolean
		 * @since 1.5
		 *
		 * @see   COVID-19
		 */
		$args['family'] = $app->getUserState('vre.search.family', false);
		
		return $args;
	}

	/**
	 * Get the list of special days for the restaurant.
	 *
	 * @return 	array 	The special days.
	 *
	 * @deprecated 1.6 without replacement
	 */
	public static function getSpecialDays()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('*')
			->from($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('group') . ' = 1');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return array();			
		}

		return $dbo->loadAssocList();
	}
}
