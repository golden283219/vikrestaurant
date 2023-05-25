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
 * Widget class used to calculate the average of reservations/orders received 
 * for each day of the week.
 *
 * Displays a BAR chart showing the average of reservations/orders from Mon to Sun.
 *
 * @since 1.8
 */
class VREStatisticsWidgetAvgdaily extends VREStatisticsWidget
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

			/**
			 * The color to be used when displaying the chart.
			 * By default, a yellow color will be used.
			 *
			 * @var color
			 */
			'color' => array(
				'type'    => 'color',
				'label'   => JText::_('VRE_UISVG_COLOR'),
				'default' => 'b8b351',
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

		// init data
		$data = array();

		$dt = new JDate();

		for ($i = 0; $i < 7; $i++)
		{
			// get translated week day
			$label = $dt->dayToString($i, true);

			$data[$label] = 0;
		}

		// build query
		$q = $dbo->getQuery(true);

		$q->select('COUNT(1) AS ' . $dbo->qn('count'));

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%Y%%m%%d-%%w\') AS %s',
			$dbo->qn('checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('weekday')
		));

		if ($this->isGroup('restaurant'))
		{
			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation'));

			// exclude closures
			$q->where($dbo->qn('closure') . ' = 0');
		}
		else
		{
			// load take-away orders
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation'));
		}

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
		
		$q->group($dbo->qn('weekday'));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$lookup = array();

			foreach ($dbo->loadObjectList() as $row)
			{
				list($ymd, $weekday) = explode('-', $row->weekday);

				// fetch assoc key
				$key = $dt->dayToString($weekday, true);

				if (isset($data[$key]))
				{
					// increase reservations count
					$data[$key] += $row->count;

					if (!isset($lookup[$key]))
					{
						$lookup[$key] = 0;
					}

					// increase by one day
					$lookup[$key]++;
				}
			}

			// calculate average for each day
			foreach ($lookup as $key => $days)
			{
				// always round up
				$data[$key] = ceil($data[$key] / $days);
			}
		}

		return $data;
	}
}
