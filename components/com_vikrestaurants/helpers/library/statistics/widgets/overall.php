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
 * Widget class used to calculate a few "overall" statistics about the
 * restaurant reservations and the take-away orders.
 *
 * Displays only text.
 *
 * @since 1.8
 */
class VREStatisticsWidgetOverall extends VREStatisticsWidget
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
		 * It is possible to filter the reservations by working
		 * shift. Since we are fetching records for several dates,
		 * the shifts dropdown would contain repeated options,
		 * specially in case of special days.
		 *
		 * For this reason, we need to use pre-built working shifts:
		 * - Lunch   05:00 - 16:59
		 * - Dinner  17:00 - 04:59
		 *
		 * @var select
		 */
		$form['shift'] = array(
			'type'     => 'select',
			'label'    => JText::_('VRRESERVATIONSHIFTFILTER'),
			'default'  => '',
			'options'  => array(
				'0'    	=> JText::_('VRRESERVATIONSHIFTSEARCH'),
				'5-16' 	=> JText::_('VRSTATSSHIFTLUNCH'),
				'17-4' 	=> JText::_('VRSTATSSHIFTDINNER'),
			),
		);
			
		if ($this->isGroup('restaurant'))
		{
			/**
			 * Flag used to check whether the people count should be
			 * displayed or not (restaurant only).
			 *
			 * @var checkbox
			 */
			$form['people'] = array(
				'type'     => 'checkbox',
				'label'    => JText::_('VRE_STATS_WIDGET_TREND_SHOW_PEOPLE_FIELD'),
				'help'     => JText::_('VRE_STATS_WIDGET_TREND_SHOW_PEOPLE_FIELD_HELP'),
				'default'  => true,
			);
		}

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

		$filters['shift']  = $this->getOption('shift');
		$filters['people'] = $this->getOption('people');

		// build query
		$q = $dbo->getQuery(true);

		if ($this->isGroup('restaurant'))
		{
			// take TOTAL PAID only if higher than BILL AMOUNT, otherwise take the last one
			$q->select(sprintf('SUM(IF (%1$s > %2$s, %1$s, %2$s)) AS %3$s', $dbo->qn('bill_value'), $dbo->qn('tot_paid'), $dbo->qn('total')));
			// count total number of reservations
			$q->select('COUNT(1) AS ' . $dbo->qn('count'));

			if ($filters['people'])
			{
				// sum total number of guests
				$q->select('SUM(' . $dbo->qn('people') . ') AS ' . $dbo->qn('guests'));
			}

			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation'));

			// exclude closures
			$q->where($dbo->qn('closure') . ' = 0');
		}
		else
		{
			// sum total earning
			$q->select(sprintf('SUM(%s) AS %s', $dbo->qn('total_to_pay'), $dbo->qn('total')));
			// count total number of orders
			$q->select('COUNT(1) AS ' . $dbo->qn('count'));

			// load take-away orders
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation'));
		}

		$q->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'));
		
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

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no relevant data
			return null;
		}

		// load data
		$data = $dbo->loadObject();

		// format details
		$data->formattedTotal = VREFactory::getCurrency()->format($data->total);

		if ($this->isGroup('restaurant'))
		{
			$data->formattedCount = JText::plural('VRE_N_RESERVATIONS', $data->count);

			if ($filters['people'])
			{
				$data->formattedGuests = JText::plural('VRE_N_PEOPLE', $data->guests);
			}
		}
		else
		{
			$data->formattedCount = JText::plural('VRE_N_ORDERS', $data->count);
		}

		return $data;
	}
}
