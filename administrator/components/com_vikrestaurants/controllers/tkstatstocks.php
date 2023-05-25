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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants take-away items stock statistics controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkstatstocks extends VREControllerAdmin
{
	/**
	 * AJAX end-point used to retrieve some statistics about
	 * the total number of sold items.
	 *
	 * @return 	void
	 */
	public function getchartdata()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		// check user permissions
		if (!JFactory::getUser()->authorise('core.access.tkorders', 'com_vikrestaurants'))
		{
			// raise error, not authorised to access take-away reservations data
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$eid = $input->getUint('id_product', 0);
		$oid = $input->getUint('id_option', 0);

		$start_ts = VikRestaurants::createTimestamp($input->get('start', '', 'string'), 0, 0, true);
		$end_ts   = VikRestaurants::createTimestamp($input->get('end', '', 'string'), 23, 59, true);

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('e.id', 'eid'));
		$q->select($dbo->qn('e.name', 'ename'));
		$q->select($dbo->qn('o.id', 'oid'));
		$q->select($dbo->qn('o.name', 'oname'));

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%w\') AS %s',
			$dbo->qn('r.checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('weekday')
		));

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%c\') AS %s',
			$dbo->qn('r.checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('month')
		));

		// Make sure the UNIX timestamps are converted to the timezone
		// used by the server, so that the hours won't be shifted.
		$q->select(sprintf(
			'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%Y\') AS %s',
			$dbo->qn('r.checkin_ts'),
			date('P'), // returns the string offset '+02:00'
			$dbo->qn('year')
		));

		$q->select('SUM(' . $dbo->qn('i.quantity') . ') AS ' . $dbo->qn('products_used'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc', 'i') . ' ON ' . $dbo->qn('i.id_res') . ' = ' . $dbo->qn('r.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('i.id_product') . ' = ' . $dbo->qn('e.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('i.id_product_option') . ' = ' . $dbo->qn('o.id'));

		$q->where($dbo->qn('i.id_product') . ' = ' . $eid);

		if ($start_ts > 0)
		{
			$q->where($dbo->qn('r.checkin_ts') . ' >= ' . $start_ts);
		}

		if ($end_ts > 0)
		{
			$q->where($dbo->qn('r.checkin_ts') . ' <= ' . $end_ts);
		}

		$q->andWhere(array(
			$dbo->qn('i.id_product_option') . ' = ' . $oid,
			$oid . ' = 0',
		), 'OR');

		$q->andWhere(array(
			$dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'),
			$dbo->qn('r.status') . ' = ' . $dbo->q('PENDING'),
		), 'OR');

		$q->group($dbo->qn('weekday'));
		$q->group($dbo->qn('month'));
		$q->group($dbo->qn('year'));

		$q->order($dbo->qn('year') . ' ASC');
		$q->order($dbo->qn('month') . ' ASC');
		$q->order($dbo->qn('weekday') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// no relevant data
			UIErrorFactory::raiseError(400, JText::_('VRTKSTATSTOCKSNODATA'));
		}

		$rows = $dbo->loadAssocList();

		$tree = array(
			'eid'      => $rows[0]['eid'],
			'oid'      => $rows[0]['oid'],
			'ename'    => $rows[0]['ename'],
			'oname'    => $rows[0]['oname'],
			'years'    => array(),
			'months'   => array(),
			'weekdays' => array(),
			'children' => array(),
		);

		$last_year = $last_month = -1;
		$year_node = $month_node = null;

		foreach ($rows as $r)
		{
			if ($r['year'] != $last_year)
			{
				// update node
				$tree['children'][$r['year']] = array(
					'used'     => 0,
					'children' => array(),
				);

				$year_node = &$tree['children'][$r['year']];

				$last_year = $r['year'];
			}

			if ($r['month'] != $last_month)
			{
				// update node
				$year_node['children'][$r['month']] = array(
					'used'     => 0,
					'children' => array()
				);

				$month_node = &$year_node['children'][$r['month']];

				$last_month = $r['month'];
			}

			$month_node['children'][$r['weekday']] = $r['products_used'];
			
			$year_node['used']  += $r['products_used'];
			$month_node['used'] += $r['products_used'];

			// update root total
			if (empty($tree['years'][$r['year']]))
			{
				$tree['years'][$r['year']] = 0;
			}

			$tree['years'][$r['year']] += $r['products_used'];

			if (empty($tree['months'][$r['month']]))
			{
				$tree['months'][$r['month']] = 0;
			}

			$tree['months'][$r['month']] += $r['products_used'];

			if (empty($tree['weekdays'][$r['weekday']]))
			{
				$tree['weekdays'][$r['weekday']] = 0;
			}

			$tree['weekdays'][$r['weekday']] += $r['products_used'];
		}
		
		echo json_encode($tree);
		exit;
	}
}
