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
 * Widget class used to fetch a few statistics about the type of service
 * selected during the booking of a take-away order.
 *
 * Displays a DOUGHNUT chart showing the total count for each type
 * of service (DELIVERY or PICKUP).
 *
 * @since 1.8
 */
class VREStatisticsWidgetService extends VREStatisticsWidget
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
			 * The initial range of dates to take when a new session starts.
			 *
			 * @var select
			 */
			'range' => array(
				'type'     => 'select',
				'label'    => JText::_('VRE_STATS_WIDGET_STATUSRES_INITIAL_RANGE_FIELD'),
				'help'     => JText::_('VRE_STATS_WIDGET_STATUSRES_INITIAL_RANGE_FIELD_HELP'),
				'default'  => 'month',
				'options'  => array(
					'all'      => JText::_('VRE_STATS_WIDGET_STATUSRES_INITIAL_RANGE_OPT_ALL'),
					'month'    => JText::_('VRE_STATS_WIDGET_STATUSRES_INITIAL_RANGE_OPT_CURR_MONTH'),
					'lastmon'  => JText::_('VRE_STATS_WIDGET_STATUSRES_INITIAL_RANGE_OPT_PREV_MONTH'),
				),
			),

			/**
			 * The initial date of the range.
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to restore the field to an empty value, just
			 * to obtain the current date.
			 *
			 * @var calendar
			 */
			'datefrom' => array(
				'type'     => 'calendar',
				'label'    => JText::_('VRMANAGESPDAY2'),
				'volatile' => true,
			),

			/**
			 * The ending date of the range.
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to restore the field to an empty value, just
			 * to obtain the current date.
			 *
			 * @var calendar
			 */
			'dateto' => array(
				'type'     => 'calendar',
				'label'    => JText::_('VRMANAGESPDAY3'),
				'volatile' => true,
			),

			/**
			 * It is possible to filter the reservations by working
			 * shift. Since we are fetching records for several dates,
			 * the shifts dropdown would contain repeated options,
			 * specially in case of special days.
			 *
			 * For this reason, we need to use pre-built working shifts:
			 * - Lunch   05:00 - 16:59
			 * - Dinner  17:00 - 04:59
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to unset the shift filter.
			 *
			 * @var select
			 */
			'shift' => array(
				'type'     => 'select',
				'label'    => JText::_('VRRESERVATIONSHIFTFILTER'),
				'default'  => '',
				'volatile' => true,
				'options'  => array(
					'0'    	=> JText::_('VRRESERVATIONSHIFTSEARCH'),
					'5-16' 	=> JText::_('VRSTATSSHIFTLUNCH'),
					'17-4' 	=> JText::_('VRSTATSSHIFTDINNER'),
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
	 * This widget supports only the "takeaway" group.
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
		$dbo = JFactory::getDbo();

		// get date from request
		$filters = array();
		$filters['datefrom'] = $this->getOption('datefrom');
		$filters['dateto']   = $this->getOption('dateto');
		$filters['shift']    = $this->getOption('shift');

		// use default range in case of empty dates
		if ((empty($filters['datefrom']) || $filters['datefrom'] == $dbo->getNullDate())
			&& (empty($filters['dateto']) || $filters['dateto'] == $dbo->getNullDate()))
		{
			// get current time
			$now = getdate(VikRestaurants::now());

			// fetch default range to use
			switch ($this->getOption('range'))
			{
				case 'month':
					$from = mktime(0, 0, 0, $now['mon'], 1, $now['year']);
					$to   = mktime(0, 0, 0, $now['mon'] + 1, 1, $now['year']) - 1;
					break;

				case 'lastmon':
					$from = mktime(0, 0, 0, $now['mon'] - 1, 1, $now['year']);
					$to   = mktime(0, 0, 0, $now['mon'], 1, $now['year']) - 1;
					break;

				default:
					$from = $to = 0;
			}
		}
		else
		{
			// convert specified dates to timestamps
			$from = VikRestaurants::createTimestamp($filters['datefrom'], 0, 0);
			$to   = VikRestaurants::createTimestamp($filters['dateto'], 23, 59);
		}

		$data = array();

		// build query
		$q = $dbo->getQuery(true);

		// count reservations
		$q->select('COUNT(1) AS ' . $dbo->qn('count'));
		$q->select($dbo->qn('delivery_service'));

		// load take-away orders
		$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation'));

		// retrieve only CONFIRMED orders
		$q->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'));

		if ($from > 0)
		{
			$q->where($dbo->qn('checkin_ts') . ' >= ' . $from);
		}

		if ($to > 0)
		{
			$q->where($dbo->qn('checkin_ts') . ' <= ' . $to);
		}
		
		if ($filters['shift'])
		{
			/**
			 * Since we are fetching records for several dates,
			 * the shifts dropdown would contain repeated
			 * options, specially in case of special days.
			 *
			 * For this reason, we need to use pre-built
			 * working shifts:
			 * - Lunch   05:00 - 16:59
			 * - Dinner  17:00 - 04:59
			 *
			 * @since 1.8
			 */
			list($fromhour, $tohour) = explode('-', $filters['shift']);

			if ((int) $fromhour < (int) $tohour)
			{
				// do not include MINUTES in query
				$q->where('DATE_FORMAT(FROM_UNIXTIME(' . $dbo->qn('checkin_ts') . '), \'%H\') BETWEEN ' . (int) $fromhour . ' AND ' . (int) $tohour);
			}
			else
			{
				// do not include MINUTES in query
				$q->andWhere(array(
					'DATE_FORMAT(FROM_UNIXTIME(' . $dbo->qn('checkin_ts') . '), \'%H\') >= ' . (int) $fromhour,
					'DATE_FORMAT(FROM_UNIXTIME(' . $dbo->qn('checkin_ts') . '), \'%H\') <= ' . (int) $tohour,
				), 'OR');
			}
		}
		
		$q->group($dbo->qn('delivery_service'));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				if (!isset($data[$row->delivery_service]))
				{
					$data[$row->delivery_service] = array(
						'label' => JText::_($row->delivery_service ? 'VRMANAGETKRES14' : 'VRMANAGETKRES15'),
						'count' => 0,
					);
				}

				// increase reservations count
				$data[$row->delivery_service]['count'] += $row->count;
			}
		}

		return $data;
	}
}
