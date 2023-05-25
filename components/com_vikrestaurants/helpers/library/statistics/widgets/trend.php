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
 * Widget class used to support the "trend" statistics
 * for the restaurant reservations and the take-away orders.
 *
 * Displays a LINE chart showing the total revenue earned month by month
 * for the selected range (by default, the last 6 months will be used).
 *
 * @since 1.8
 */
class VREStatisticsWidgetTrend extends VREStatisticsWidget
{
	/**
	 * @override
	 * Returns the form parameters required to the widget.
	 *
	 * @return 	array
	 */
	public function getForm()
	{
		$form = array();

		/**
		 * The entity of the chart (reservations count or total earning).
		 *
		 * @var select
		 */
		$form['valuetype'] = array(
			'type'     => 'select',
			'label'    => JText::_('VRE_STATS_WIDGET_TREND_VALUE_TYPE_FIELD'),
			'help'     => JText::_('VRE_STATS_WIDGET_TREND_VALUE_TYPE_FIELD_HELP'),
			'default'  => 'earning',
			'options'  => array(
				'earning' => JText::_('VRE_STATS_WIDGET_TREND_VALUE_TYPE_OPT_EARNING'),
				'count'   => JText::_('VRE_STATS_WIDGET_TREND_VALUE_TYPE_OPT_COUNT'),
			),
		);

		// add support for guests count in case of restaurant
		if ($this->isGroup('restaurant'))
		{
			$form['valuetype']['options']['guests'] = JText::_('VRE_STATS_WIDGET_TREND_VALUE_TYPE_OPT_GUESTS');
		}

		/**
		 * The initial date to take when a new session starts.
		 *
		 * @var select
		 */
		$form['range'] = array(
			'type'     => 'select',
			'label'    => JText::_('VRE_STATS_WIDGET_TREND_INITIAL_RANGE_FIELD'),
			'help'     => JText::_('VRE_STATS_WIDGET_TREND_INITIAL_RANGE_FIELD_HELP'),
			'default'  => '-5 months',
			'options'  => array(
				'-2 months'  => JText::sprintf('VRE_STATS_WIDGET_TREND_INITIAL_RANGE_LAST_N_MONTHS', 3),
				'-5 months'  => JText::sprintf('VRE_STATS_WIDGET_TREND_INITIAL_RANGE_LAST_N_MONTHS', 6),
				'-8 months'  => JText::sprintf('VRE_STATS_WIDGET_TREND_INITIAL_RANGE_LAST_N_MONTHS', 9),
				'-11 months' => JText::sprintf('VRE_STATS_WIDGET_TREND_INITIAL_RANGE_LAST_N_MONTHS', 12),
			),
		);
			
		/**
		 * The initial date of the range.
		 *
		 * The parameter is VOLATILE because, every time the session
		 * ends, we need to restore the field to an empty value, just
		 * to obtain the current date.
		 *
		 * @var calendar
		 */
		$form['datefrom'] = array(
			'type'     => 'calendar',
			'label'    => JText::_('VRMANAGESPDAY2'),
			'volatile' => true,
		);

		/**
		 * The ending date of the range.
		 *
		 * The parameter is VOLATILE because, every time the session
		 * ends, we need to restore the field to an empty value, just
		 * to obtain the current date.
		 *
		 * @var calendar
		 */
		$form['dateto'] = array(
			'type'     => 'calendar',
			'label'    => JText::_('VRMANAGESPDAY3'),
			'volatile' => true,
		);

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
		$form['shift'] = array(
			'type'     => 'select',
			'label'    => JText::_('VRRESERVATIONSHIFTFILTER'),
			'default'  => '',
			'volatile' => true,
			'options'  => array(
				'0'    	=> JText::_('VRRESERVATIONSHIFTSEARCH'),
				'5-16' 	=> JText::_('VRSTATSSHIFTLUNCH'),
				'17-4' 	=> JText::_('VRSTATSSHIFTDINNER'),
			),
		);

		/**
		 * The color to be used when displaying the chart.
		 * By default, an azure color will be used.
		 *
		 * @var color
		 */
		$form['color'] = array(
			'type'    => 'color',
			'label'   => JText::_('VRE_UISVG_COLOR'),
			'default' => '32acd1',
		);

		return $form;
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
		$filters['valuetype'] = $this->getOption('valuetype');
		$filters['datefrom']  = $this->getOption('datefrom');
		$filters['dateto']    = $this->getOption('dateto');
		$filters['shift']     = $this->getOption('shift');

		// use default range in case one of the specified dates is empty
		if (empty($filters['datefrom']) || $filters['datefrom'] == $dbo->getNullDate()
			|| empty($filters['dateto']) || $filters['dateto'] == $dbo->getNullDate())
		{
			// get current time
			$now = getdate(VikRestaurants::now());

			// use the end of this month as delimiter
			$end_ts = mktime(0, 0, 0, $now['mon'] + 1, 1, $now['year']) - 1;
			// go back by the number of specified months
			$start_ts = strtotime(
				$this->getOption('range') . ' 00:00:00',
				// start from the beginning of this month
				mktime(0, 0, 0, $now['mon'], 1, $now['year'])
			);
		}
		else
		{
			// convert specified dates to timestamps
			$start_ts = VikRestaurants::createTimestamp($filters['datefrom'], 0, 0);
			$end_ts   = VikRestaurants::createTimestamp($filters['dateto'], 23, 59);
		}

		// init data
		$data = array();

		$dt_start = getdate($start_ts);
		$dt_end   = getdate($end_ts);

		// Check whether the specified dates are in different years.
		// In case they are, the labels format should be "M Y", 
		// otherwise just "M" could be used.
		$label_format = $dt_start['year'] == $dt_end['year'] ? 'M' : 'M Y';

		// always start from the first of the month to avoid skipping a month
		$dt = mktime(0, 0, 0, $dt_start['mon'], 1, $dt_start['year']);

		// iterate as long as the date is lower than the ending month
		while ($dt < $end_ts)
		{
			// format label (use current timezone because we are working with UNIX timestamps)
			$label = JHtml::_('date', $dt, $label_format, date_default_timezone_get());

			$data[$label] = 0;

			// increase date by one month
			$dt = strtotime('+1 month', $dt);
		}

		// build query
		$q = $dbo->getQuery(true);

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%Y-%%m\') AS %s',
			$dbo->qn('checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('ym')
		));

		if ($this->isGroup('restaurant'))
		{
			// check if we should calculate the total earning
			if ($filters['valuetype'] == 'earning')
			{
				// take TOTAL PAID only if higher than BILL AMOUNT, otherwise take the last one
				$q->select(sprintf('SUM(IF (%1$s > %2$s, %1$s, %2$s)) AS %3$s', $dbo->qn('bill_value'), $dbo->qn('tot_paid'), $dbo->qn('total')));
			}
			// check if we should sum the total count of guests
			else if ($filters['valuetype'] == 'guests')
			{
				$q->select('SUM(' . $dbo->qn('people') . ') AS ' . $dbo->qn('total'));
			}
			// otherwise just sum the total count of reservations
			else
			{
				$q->select('COUNT(1) AS ' . $dbo->qn('total'));
			}

			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation'));

			// exclude closures
			$q->where($dbo->qn('closure') . ' = 0');
		}
		else
		{
			// check if we should calculate the total earning
			if ($filters['valuetype'] == 'earning')
			{
				$q->select(sprintf('SUM(%s) AS %s', $dbo->qn('total_to_pay'), $dbo->qn('total')));
			}
			// otherwise just sum the total count of orders
			else
			{
				$q->select('COUNT(1) AS ' . $dbo->qn('total'));
			}

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
		
		$q->group($dbo->qn('ym'));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				// retrieve data key (use current timezone because we are working with UNIX timestamps)
				$key = JHtml::_('date', $row->ym, $label_format, date_default_timezone_get());

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
