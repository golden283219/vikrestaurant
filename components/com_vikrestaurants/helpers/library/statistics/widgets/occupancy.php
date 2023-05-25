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
 * Widget class used to calculate the total restaurant occupancy for
 * a given date and time.
 *
 * Displays a "PIE" chart showing the percentage of the total occupancy.
 *
 * @since 1.8
 */
class VREStatisticsWidgetOccupancy extends VREStatisticsWidget
{
	/**
	 * @override
	 * Returns the form parameters required to the widget.
	 *
	 * @return 	array
	 */
	public function getForm()
	{
		return array(
			/**
			 * The date and time to use to check the total occupancy.
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to restore the field to an empty value, just
			 * to obtain the current date and time.
			 *
			 * @var calendar
			 */
			'datetime' => array(
				'type'     => 'calendar',
				'label'    => JText::_('VRMANAGERESERVATION13'),
				'volatile' => true,
				// use attributes to be passed within calendar
				'attributes' => array(
					'showTime' => true,
				),
			),

			/**
			 * The color to be used when displaying the chart.
			 * By default, a blue color will be used.
			 *
			 * @var color
			 */
			'color' => array(
				'type'    => 'color',
				'label'   => JText::_('VRE_UISVG_COLOR'),
				'default' => '307bbb',
			),
		);
	}

	/**
	 * @override
	 * Checks whether the specified group is supported
	 * by the widget. Children classes can override this
	 * method to drop the support for a specific group.
	 *
	 * This widget supports only the "restaurant" group.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return $group == 'restaurant' ? true : false;
	}

	/**
	 * @override
	 * Loads the dataset(s) that will be recovered asynchronously
	 * for being displayed within the widget.
	 *
	 * It is possible to return an array of records to be passed
	 * to a chart or directly the HTML to replace.
	 *
	 * @return 	mixed
	 */
	public function getData()
	{
		$dbo    = JFactory::getDbo();
		$config = VREFactory::getConfig();

		// get date from request
		$filters = array();
		$filters['datetime'] = $this->getOption('datetime');

		// use default date if invalid
		if (empty($filters['datetime']) || $filters['datetime'] == $dbo->getNullDate())
		{
			// use current date and time
			$ts = VikRestaurants::now();

			// fetch date
			$date = date($config->get('dateformat'), $ts);
			// fetch time
			$time = date('H:i', $ts);
		}
		else
		{
			// explode date from time
			list($date, $time) = explode(' ', $filters['datetime']);
		}

		// create search parameters
		$search = new VREAvailabilitySearch($date, $time);

		// count guests as admin, in order to include also
		// the customers assigned to unpublished tables/rooms
		$search->setAdmin(true);

		// get tables occurrences
		$tables = $search->getTablesOccurrence();

		// calculate total number of guests
		$guests = array_sum(array_values($tables));

		// DO NOT look as admin to exclude tables that
		// are currently unpublished
		$search->setAdmin(false);

		// calculate total number of seats
		$seats = $search->getSeatsCount();

		if (!$seats)
		{
			// in case of no available seats, just use the
			// number of guests in order to return an
			// occupancy of 100%
			$seats = $guests;
		}
	
		// calculate percentage occupancy (do not divide by 0)
		$occupancy = $seats ? $guests * 100 / $seats : 0;

		if ($occupancy > 99)
		{
			// do not risk to round 99.xx% to 100%
			$occupancy = floor($occupancy);
		}
		else
		{
			// always round to the next integer
			$occupancy = ceil($occupancy);
		}

		// make sure the occupancy didn't exceed the [0-100] range
		$occupancy = max(array(  0, $occupancy));
		$occupancy = min(array(100, $occupancy));

		$ts = VikRestaurants::createTimestamp($search->get('date'), $search->get('hour'), $search->get('min'));

		// prepare return data
		$data = array(
			'occupancy' => $occupancy,
			'guests'    => $guests,
			'seats'     => $seats,
			'datetime'  => $ts,
			'date'      => JHtml::_('date', JDate::getInstance($ts), JText::_('DATE_FORMAT_LC3'), date_default_timezone_get()),
			'time'      => date($config->get('timeformat'), $ts),
		);

		return $data;
	}
}
