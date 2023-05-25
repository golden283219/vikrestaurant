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
 * Widget class used to support the monthly "Rate of Growth"
 * for the restaurant reservations and the take-away orders.
 *
 * Displays only text.
 *
 * @since 1.8
 */
class VREStatisticsWidgetRog extends VREStatisticsWidget
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
			 * The month to fetch.
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to restore the field to an empty value, just
			 * to obtain the current date.
			 *
			 * @var select
			 */
			'month' => array(
				'type'     => 'select',
				'label'    => JText::_('VRE_STATS_WIDGET_ROG_MONTH_FIELD'),
				'default'  => date('n'),
				'volatile' => true,
				'options'  => JHtml::_('vikrestaurants.months'),
			),

			/**
			 * The year to which the selected month belongs.
			 *
			 * The parameter is VOLATILE because, every time the session
			 * ends, we need to restore the field to an empty value, just
			 * to obtain the current date.
			 *
			 * @var select
			 */
			'year' => array(
				'type'     => 'select',
				'label'    => JText::_('VRE_STATS_WIDGET_ROG_YEAR_FIELD'),
				'default'  => date('Y'),
				'volatile' => true,
				'options'  => JHtml::_('vikrestaurants.years', -10, 0),
			),

			/**
			 * When enabled, the total earning of the month will be proportionally
			 * estimated depending on the money already earned and the remaining
			 * days (applies only for the current month).
			 *
			 * @var checkbox
			 */
			'prop' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_STATS_WIDGET_ROG_PROP_FIELD'),
				'help'    => JText::_('VRE_STATS_WIDGET_ROG_PROP_FIELD_HELP'),
				'default' => true,
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
		$filters['month'] = $this->getOption('month');
		$filters['year']  = $this->getOption('year');
		$filters['prop']  = $this->getOption('prop');

		// create timestamp delimiters
		$start_ts = mktime(0, 0, 0, $filters['month'] - 1, 1, $filters['year']);
		$end_ts   = mktime(0, 0, 0, $filters['month'] + 1, 1, $filters['year']) - 1;

		$dt = new JDate();

		// prepare return data
		$data = array(
			'prevMonth'   => $dt->monthToString(date('n', $start_ts), true),
			'prevYear'    => date('Y', $start_ts),
			'prevEarning' => 0,
			'currMonth'   => $dt->monthToString(date('n', $end_ts), true),
			'currYear'    => date('Y', $end_ts),
			'currEarning' => 0,
			'rog'         => 0,
		);

		// build query
		$q = $dbo->getQuery(true);

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%Y%%m\') AS %s',
			$dbo->qn('checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('ym')
		));

		if ($this->isGroup('restaurant'))
		{
			// take TOTAL PAID only if higher than BILL AMOUNT, otherwise take the last one
			$q->select(sprintf('SUM(IF (%1$s > %2$s, %1$s, %2$s)) AS %3$s', $dbo->qn('bill_value'), $dbo->qn('tot_paid'), $dbo->qn('total')));

			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation'));

			// exclude closures
			$q->where($dbo->qn('closure') . ' = 0');
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
		
		$q->group($dbo->qn('ym'));

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// mark "not enough data" flag
			$data['nodata'] = true;

			return $data;
		}

		foreach ($dbo->loadObjectList() as $r)
		{
			$prev = date('Ym', $start_ts);

			if ($r->ym == $prev)
			{
				$data['prevEarning'] = $r->total;
			}
			else
			{
				$data['currEarning'] = $r->total;
			}
		}

		if ($data['prevEarning'] == 0)
		{
			// mark "not enough data" flag
			$data['nodata'] = true;

			return $data;
		}

		$now = VikRestaurants::now();

		// check if we should proportionally extend the total
		// earning of the current month
		if ($filters['prop'] && $now < $end_ts)
		{
			// get current day and last day in month
			$curr_day = date('j', $now);
			$end_day  = date('j', $end_ts);

			// CURRENT_DAY : TOTAL_EARNED = LAST_DAY : PROP_EARNING
			// PROP_EARNING = TOTAL_EARNED * LAST_DAY / CURRENT_DAY
			$data['currEarning'] = round((float) $data['currEarning'] * $end_day / $curr_day, 2);
		}

		// calculate RoG
		$data['rog'] = ($data['currEarning'] - $data['prevEarning']) / $data['prevEarning'];
		$data['rogPercent'] = round($data['rog'] * 100, 2);

		return $data;
	}
}
