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
 * Widget class used to display an overview table for
 * the selected date.
 *
 * Displays a table of daily hours.
 *
 * @since 1.8
 */
class VREStatisticsWidgetOverview extends VREStatisticsWidget
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

			/**
			 * The intervals to display the hours in the table.
			 * For example, in case of 15 minutes, the same hour
			 * will be split in 4 different parts:
			 * HH:00, HH:15, HH:30, HH:45
			 *
			 * @var select
			 */
			'intervals' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_STATS_WIDGET_OVERVIEW_INTERVALS_FIELD'),
				'default' => VREFactory::getConfig()->getUint('minuteintervals', 30),
				'options' => array(
					10 => JText::sprintf('VRE_STATS_WIDGET_OVERVIEW_INTERVALS_OPT', 10),
					15 => JText::sprintf('VRE_STATS_WIDGET_OVERVIEW_INTERVALS_OPT', 15),
					30 => JText::sprintf('VRE_STATS_WIDGET_OVERVIEW_INTERVALS_OPT', 30),
					60 => JText::sprintf('VRE_STATS_WIDGET_OVERVIEW_INTERVALS_OPT', 60),
				),
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

		// load filters
		$filters = array();
		$filters['date']      = $this->getOption('date');
		$filters['intervals'] = $this->getOption('intervals', 30);

		// use current date if not specified
		if (empty($filters['date']) || $filters['date'] == $dbo->getNullDate())
		{
			$filters['date'] = date($config->get('dateformat'));
		}

		if (empty($filters['intervals']))
		{
			$filters['intervals'] = 30;
		}

		// use the current time, just because the time is required
		$filters['time'] = date($config->get('timeformat'));

		// if we are in the front-end, make sure the
		// user is an operator (throws exception)
		if (JFactory::getApplication()->isClient('site'))
		{
			// import operator user helper
			VRELoader::import('library.operator.user');
			// Load operator details. In case the user is
			// not an operator, an exception will be thrown
			$operator = VREOperatorUser::getInstance();
		}
		else
		{
			$operator = null;
		}

		$data = array();

		$data['filters'] = $filters;

		// create availability search
		$data['search'] = new VREAvailabilitySearch($filters['date'], $filters['time']);

		// load rooms
		$data['rooms'] = $data['search']->getRooms();

		// in case the user is an operator, make sure
		// it can access only the specified rooms
		if ($operator && $operator->get('rooms'))
		{
			// get rooms
			$tmp = explode(',', $operator->get('rooms'));

			// filter the rooms
			$data['rooms'] = array_values(array_filter($data['rooms'], function($room) use($tmp)
			{
				return in_array($room->id, $tmp);
			}));
		}

		// look for any hourly closures for the select day
		$this->digHourlyRoomClosures($data['rooms'], $data['search']->get('date'));

		// load available times
		$args = array(
			'step' => $filters['intervals'],
		);

		$data['times'] = JHtml::_('vikrestaurants.times', 1, $filters['date'], $args);

		// load bookings in the selected date
		$data['bookings'] = array();

		$start_ts = VikRestaurants::createTimestamp($filters['date'],  0,  0);
		$end_ts   = VikRestaurants::createTimestamp($filters['date'], 23, 59);

		$q = $dbo->getQuery(true);

		$q->select(sprintf(
			'IF (%2$s = 0, %1$s, %2$s) AS %3$s',
			$dbo->qn('r.id'),
			$dbo->qn('r.id_parent'),
			$dbo->qn('id')
		));

		$q->select($dbo->qn(array(
			'r.checkin_ts', 'r.id_table', 'r.purchaser_nominative', 'r.purchaser_mail', 'r.purchaser_phone',
			'r.status', 'r.rescode', 'r.stay_time', 'r.id_parent', 'r.closure', 'r.notes',
		)));

		$q->select(sprintf(
			'CONCAT_WS(\' \', %s, %s) AS %s',
			$dbo->qn('o.firstname'),
			$dbo->qn('o.lastname'),
			$dbo->qn('operator_name')
		));

		$q->select($dbo->qn('c.code'));
		$q->select($dbo->qn('c.icon', 'code_icon'));

		$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_res_code', 'c') . ' ON ' . $dbo->qn('r.rescode') . ' = ' . $dbo->qn('c.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_operator', 'o') . ' ON ' . $dbo->qn('r.id_operator') . ' = ' . $dbo->qn('o.id'));

		$q->where($dbo->qn('r.checkin_ts') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts);
		$q->andWhere(array(
			$dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'),
			$dbo->qn('r.status') . ' = ' . $dbo->q('PENDING'),
		), 'OR');

		$q->order($dbo->qn('r.id_table') . ' ASC');
		$q->order($dbo->qn('r.checkin_ts') . ' ASC');

		if ($operator && $operator->get('rooms'))
		{
			// join reservation tables
			$q->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'));
			// take only the supported rooms (already comma-separated)
			$q->where($dbo->qn('t.id_room') . ' IN (' . $operator->get('rooms') . ')');
		}

		// check if the operator can see all the reservations
		if ($operator && !$operator->canSeeAll())
		{
			// check if the operator can self-assign reservations
			if ($operator->canAssign())
			{
				// retrieve reservations assigned to this operator and reservations
				// free of assignments
				$q->where($dbo->qn('r.id_operator') . ' IN (0, ' . (int) $operator->get('id') . ')');
			}
			else
			{
				// retrieve only the reservations assigned to the operator
				$q->where($dbo->qn('r.id_operator') . ' = ' . (int) $operator->get('id'));
			}
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $r)
			{
				if (!isset($data['bookings'][$r->id_table]))
				{
					$data['bookings'][$r->id_table] = array();
				}

				// fetch checkout
				$r->stay_time   = $r->stay_time ? $r->stay_time : $config->getUint('averagetimestay');
				$r->checkout_ts = strtotime('+' . $r->stay_time . ' minutes', $r->checkin_ts);
				
				$data['bookings'][$r->id_table][] = $r;
			}
		}

		// include a reference of this widget
		$data['widget'] = $this;

		// include operator instance
		$data['operator'] = $operator;

		// return overview layout
		return JLayoutHelper::render('statistics.widgets.overview.table', $data);
	}

	/**
	 * Searches for any hourly closures included within the
	 * specified date.
	 *
	 * @param 	array 	 &$rooms  A list of rooms.
	 * @param 	string   $date    The selected date.
	 *
	 * @return 	boolean  True in case of closures found, false otherwise.
	 */
	protected function digHourlyRoomClosures(&$rooms, $date)
	{
		// convert date to timestamp
		$start = VikRestaurants::createTimestamp($date, 0, 0);
		// convert date to timestamp
		$end = VikRestaurants::createTimestamp($date, 23, 59);

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_room_closure'))
			->where(array(
				'(' . $dbo->qn('start_ts') . " <= {$start}  AND {$start}  <  " . $dbo->qn('end_ts') . ")\n",
				'(' . $dbo->qn('start_ts') . " <  {$end}    AND {$end}    <= " . $dbo->qn('end_ts') . ")\n",
				'(' . $dbo->qn('start_ts') . " <= {$start}  AND {$end}    <= " . $dbo->qn('end_ts') . ")\n",
				'(' . $dbo->qn('start_ts') . " >= {$start}  AND {$end}    >= " . $dbo->qn('end_ts') . ")\n",
			), 'OR');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// no hourly room closures
			return false;
		}

		$lookup = array();

		// iterate all closures found
		foreach ($dbo->loadObjectList() as $closure)
		{
			if (!isset($lookup[$closure->id_room]))
			{
				$lookup[$closure->id_room] = array();
			}

			$lookup[$closure->id_room][] = $closure;
		}

		foreach ($rooms as &$room)
		{
			if (isset($lookup[$room->id]))
			{
				$room->closures = $lookup[$room->id];
			}
		}

		return true;
	}
}
