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
 * Widget class used to fetch the most 10 purchased products.
 *
 * Displays a horizontal BAR chart.
 *
 * @since 1.8
 */
class VREStatisticsWidgetBestproducts extends VREStatisticsWidget
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
			 * The maximum number of items to load.
			 *
			 * @var select
			 */
			'items' => array(
				'type'     => 'select',
				'label'    => JText::_('VRMANAGETKRES22'),
				'default'  => 10,
				'options'  => array(
					5,
					10,
					15,
					20,
				),
			),

			/**
			 * The initial date to take when a new session starts.
			 *
			 * @var select
			 */
			'range' => array(
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
			 * By default, a purple color will be used.
			 *
			 * @var color
			 */
			'color' => array(
				'type'    => 'color',
				'label'   => JText::_('VRE_UISVG_COLOR'),
				'default' => 'ad1a3f',
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
		$filters['valuetype'] = $this->getOption('valuetype');
		$filters['datefrom']  = $this->getOption('datefrom');
		$filters['dateto']    = $this->getOption('dateto');
		$filters['shift']     = $this->getOption('shift');
		$filters['items']     = $this->getOption('items', 10);

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

		// build query
		$q = $dbo->getQuery(true);

		if ($this->isGroup('restaurant'))
		{
			$q->select('SUM(' . $dbo->qn('i.quantity') . ') AS ' . $dbo->qn('quantity'));
			$q->select($dbo->qn('i.name', 'product'));

			// load restaurant reservations and products
			$q->from($dbo->qn('#__vikrestaurants_res_prod_assoc', 'i'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_reservation', 'r') . ' ON ' . $dbo->qn('i.id_reservation') . ' = ' . $dbo->qn('r.id'));
		}
		else
		{
			$q->select('SUM(' . $dbo->qn('i.quantity') . ') AS ' . $dbo->qn('quantity'));
			$q->select(sprintf(
				'IF(%3$s > 0, CONCAT_WS(\' - \', %1$s, %2$s), %1$s) AS %4$s',
				$dbo->qn('e.name'),
				$dbo->qn('o.name'),
				$dbo->qn('i.id_product_option'),
				$dbo->qn('product')
			));

			// load take-away orders and products
			$q->from($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc', 'i'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r') . ' ON ' . $dbo->qn('i.id_res') . ' = ' . $dbo->qn('r.id'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('i.id_product') . ' = ' . $dbo->qn('e.id'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('i.id_product_option') . ' = ' . $dbo->qn('o.id'));
		}

		$q->where($dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'));	
		$q->where($dbo->qn('r.checkin_ts') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts);
		
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
				$q->where('DATE_FORMAT(FROM_UNIXTIME(' . $dbo->qn('r.checkin_ts') . '), \'%H\') BETWEEN ' . (int) $fromhour . ' AND ' . (int) $tohour);
			}
			else
			{
				// do not include MINUTES in query
				$q->andWhere(array(
					'DATE_FORMAT(FROM_UNIXTIME(' . $dbo->qn('r.checkin_ts') . '), \'%H\') >= ' . (int) $fromhour,
					'DATE_FORMAT(FROM_UNIXTIME(' . $dbo->qn('r.checkin_ts') . '), \'%H\') <= ' . (int) $tohour,
				), 'OR');
			}
		}
		
		$q->group($dbo->qn('product'));
		
		$q->order($dbo->qn('quantity') . ' DESC');
		$q->order($dbo->qn('i.id_product') . ' ASC');
		$q->order($dbo->qn('i.id_product_option') . ' ASC');

		$dbo->setQuery($q, 0, $filters['items']);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				$data[$row->product] = $row->quantity;
			}
		}

		return $data;
	}
}
