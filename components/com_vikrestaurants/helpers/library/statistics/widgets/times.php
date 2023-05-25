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
 * Widget class used to display an overview of the available times for
 * take-away orders. Each time slot will show the number of received
 * orders and dishes, with the possibility of increasing the limits.
 *
 * @since 1.8.3
 */
class VREStatisticsWidgetTimes extends VREStatisticsWidget
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
			 * The date to use.
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to restore the field to an empty value, just
			 * to obtain the current date.
			 *
			 * @var calendar
			 */
			'date' => array(
				'type'     => 'calendar',
				'label'    => JText::_('VRMANAGERESERVATION13'),
				'volatile' => true,
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
		return $group == 'takeaway' ? true : false;
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

		// load filters
		$filters = array();
		$filters['date'] = $this->getOption('date');

		$now = VikRestaurants::now();

		// use current date if not specified
		if (empty($filters['date']) || $filters['date'] == $dbo->getNullDate())
		{
			$filters['date'] = date($config->get('dateformat'), $now);
		}

		$data = array();

		$data['filters'] = $filters;

		$data['times'] = array();

		// load cart framework to avoid errors
		VikRestaurants::loadCartLibrary();

		// init search handler
		$search = new VREAvailabilityTakeaway($filters['date']);

		// obtain list of available times
		$times = $search->getTimes();

		// iterate times
		foreach ($times as $group => $shift)
		{
			// exclude times in the past
			$shift = array_filter($shift, function($slot) use ($now, $filters)
			{
				// extract hours and minutes from time
				list($hour, $min) = explode(':', $slot->value);
				// create timestamp
				$ts = VikRestaurants::createTimestamp($filters['date'], $hour, $min);
				
				// take only if higher than current time
				return $ts >= $now;
			});

			// make sure the shift owns at least an available time
			if ($shift)
			{
				// reset array keys and copy new shift within the list
				$data['times'][$group] = array_values($shift);
			}
		}

		// include widget instance
		$data['widget'] = $this;

		// render table with a layout
		return JLayoutHelper::render('statistics.widgets.times.table', $data);;
	}
}
