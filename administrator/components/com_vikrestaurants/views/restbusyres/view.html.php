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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants reservations busy table view.
 *
 * @since 1.7
 */
class VikRestaurantsViewrestbusyres extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		// force blank component layout
		$input->set('tmpl', 'component');
		
		$filters = array();
		$filters['date']     = $input->get('date', '', 'string');
		$filters['time']     = $input->get('time', '', 'string');
		$filters['interval'] = $app->getUserStateFromRequest('vre.restbusyres.interval', 'interval', 60, 'uint');
		$filters['id_room']  = $app->getUserStateFromRequest('vre.restbusyres.id_room', 'id_room', 0, 'uint');

		$time = explode(':', $filters['time']);

		if (count($time) < 2)
		{
			$time = array(0, 0);
		}

		$arr = getdate(VikRestaurants::createTimestamp($filters['date'], $time[0], $time[1]));

		$ts1 = mktime($arr['hours'], $arr['minutes'] - $filters['interval'], 0, $arr['mon'], $arr['mday'], $arr['year']);
		$ts2 = mktime($arr['hours'], $arr['minutes'] + $filters['interval'], 0, $arr['mon'], $arr['mday'], $arr['year']);
		
		$rows = array();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn(array(
				'r.id', 'r.sid', 'r.checkin_ts', 'r.people', 'r.status',
				'r.locked_until', 'r.bill_value', 'r.purchaser_nominative',
				'r.purchaser_mail', 'r.purchaser_prefix', 'r.purchaser_phone',
			)));

		$q->select($dbo->qn('t.name', 'table_name'));
		$q->select($dbo->qn('rm.name', 'room_name'));
		$q->select($dbo->qn('c.icon', 'code_icon'));
		$q->select($dbo->qn('c.code'));

		$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_res_code', 'c') . ' ON ' . $dbo->qn('r.rescode') . ' = ' . $dbo->qn('c.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));

		$q->where($dbo->qn('r.checkin_ts') . ' BETWEEN ' . $ts1 . ' AND ' . $ts2);

		$q->andWhere(array(
			// get confirmed orders
			$dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'),
			// or pending orders that can be confirmed (locked_until in the future)
			$dbo->qn('r.status') . ' = ' . $dbo->q('PENDING') . ' AND ' . $dbo->qn('locked_until') . ' > ' . VikRestaurants::now(),
		), 'OR');

		if ($filters['id_room'])
		{
			$q->where($dbo->qn('rm.id') . ' = ' . $filters['id_room']);
		}

		$q->order($dbo->qn('r.checkin_ts') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$rows = $dbo->loadAssocList();
		}
		
		$this->rows    = &$rows;
		$this->filters = &$filters;

		// display the template
		parent::display($tpl);
	}
}
