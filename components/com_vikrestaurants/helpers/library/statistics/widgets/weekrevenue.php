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
 * Widget class used to support the "week revenue" statistics
 * for the restaurant reservations and the take-away orders.
 *
 * Displays a LINE chart showing the total revenue earned for each day
 * of the selected week (by default, the last 7 days will be used).
 *
 * @since 1.8
 */
class VREStatisticsWidgetWeekrevenue extends VREStatisticsWidget
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
			 * The initial date to take when a new session starts.
			 *
			 * @var select
			 */
			'start' => array(
				'type'     => 'select',
				'label'    => JText::_('VRE_STATS_WIDGET_WEEKRES_INITIAL_DATE_FIELD'),
				'help'     => JText::_('VRE_STATS_WIDGET_WEEKRES_INITIAL_DATE_FIELD_HELP'),
				'default'  => 'today',
				'options'  => array(
					'-1 week' => JText::_('VRE_STATS_WIDGET_WEEKRES_INITIAL_DATE_OPT_LAST_WEEK'),
					'-1 day'  => JText::_('VRE_STATS_WIDGET_WEEKRES_INITIAL_DATE_OPT_PREV_DAY'),
					'today'   => JText::_('VRE_STATS_WIDGET_WEEKRES_INITIAL_DATE_OPT_TODAY'),
					'+1 day'  => JText::_('VRE_STATS_WIDGET_WEEKRES_INITIAL_DATE_OPT_TOMORROW'),
				),
			),
			
			/**
			 * The date to use as week delimiter (ending date).
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

			/**
			 * The color to be used when displaying the chart.
			 * By default, an orange color will be used.
			 *
			 * @var color
			 */
			'color' => array(
				'type'    => 'color',
				'label'   => JText::_('VRE_UISVG_COLOR'),
				'default' => 'e68714',
			),
		);
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
		$filters['date']  = $this->getOption('date');
		$filters['shift'] = $this->getOption('shift');

		// use default date if invalid
		if (empty($filters['date']) || $filters['date'] == $dbo->getNullDate())
		{
			// fetch initial date
			$initial = strtotime($this->getOption('start', 'today'), VikRestaurants::now());

			// convert timestamp to date string
			$filters['date'] = date(VREFactory::getConfig()->get('dateformat'), $initial);
		}

		// create timestamp of specified date
		$end_ts   = VikRestaurants::createTimestamp($filters['date'], 23, 59);
		// go back by 7 days
		$start_ts = strtotime('-6 days 00:00:00', $end_ts);

		// init data
		$data = array();

		// create date iterator variable
		$dt = $start_ts;

		for ($i = 0; $i < 7; $i++)
		{
			// format label (use current timezone because we are working with UNIX timestamps)
			$label = JHtml::_('date', $dt, 'D d', date_default_timezone_get());

			$data[$label] = 0;

			// increase date by one
			$dt = strtotime('+1 day', $dt);
		}

		// build query
		$q = $dbo->getQuery(true);

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%Y-%%m-%%d\') AS %s',
			$dbo->qn('checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('ymd')
		));

		if ($this->isGroup('restaurant'))
		{
			// take TOTAL PAID only if higher than BILL AMOUNT, otherwise take the last one
			$q->select(sprintf('SUM(IF (%1$s > %2$s, %1$s, %2$s)) AS %3$s', $dbo->qn('bill_value'), $dbo->qn('tot_paid'), $dbo->qn('total')));

			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation'));
		}
		else
		{
			// sum totals
			$q->select(sprintf('SUM(%s) AS %s', $dbo->qn('total_to_pay'), $dbo->qn('total')));

			// load take-away orders
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation'));
		}

		$q->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'));	
		$q->where($dbo->qn('checkin_ts') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts);
		
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
		
		$q->group($dbo->qn('ymd'));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				// retrieve data key (use current timezone because we are working with UNIX timestamps)
				$key = JHtml::_('date', $row->ymd, 'D d', date_default_timezone_get());

				if (isset($data[$key]))
				{
					// increase reservations total
					$data[$key] += $row->total;
				}
			}
		}

		return $data;
	}
}
