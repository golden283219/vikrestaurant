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
 * VikRestaurants operator reservations view.
 *
 * @since 1.6
 */
class VikRestaurantsViewopreservations extends JViewVRE
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
		
		////// LOGIN //////

		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin();
		
		if (!$access)
		{
			$itemid = $input->get('Itemid', 0, 'uint');

			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}
		
		$filters = array();
		$filters['keysearch']  = $app->getUserStateFromRequest('vropres.keysearch', 'keysearch', '', 'string');
		$filters['datefilter'] = $app->getUserStateFromRequest('vropres.datefilter', 'datefilter', '', 'string');
		$filters['id_room']    = $app->getUserStateFromRequest('vropres.id_room', 'id_room', 0, 'uint');
		
		if (!$operator->canAccessRoom($filters['id_room']))
		{
			// unset room if not supported
			$filters['id_room'] = 0;
		}

		$ordering    = $app->getUserStateFromRequest('vropres.ordering', 'filter_order', 'r.id', 'string');
		$orderingDir = $app->getUserStateFromRequest('vropres.direction', 'filter_order_Dir', 'DESC', 'string');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut = "";

		$reservations = array();

		$cluster = $dbo->getQuery(true)
			->select('GROUP_CONCAT(' . $dbo->qn('ti.name') . ')')
			->from($dbo->qn('#__vikrestaurants_reservation', 'ri'))
			->leftjoin($dbo->qn('#__vikrestaurants_table', 'ti') . ' ON ' . $dbo->qn('ri.id_table') . ' = ' . $dbo->qn('ti.id'))
			->where($dbo->qn('ri.id_parent') . ' = ' . $dbo->qn('r.id'));
		
		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS r.*')
			->select($dbo->qn('t.name', 'table_name'))
			->select($dbo->qn('rm.name', 'room_name'))
			->select($dbo->qn('rc.code', 'status_code'))
			->select($dbo->qn('rc.icon', 'code_icon'))
			->select('(' . $cluster . ') AS ' . $dbo->qn('cluster'))
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_res_code', 'rc') . ' ON ' . $dbo->qn('r.rescode') . ' = ' . $dbo->qn('rc.id'))
			->where($dbo->qn('r.closure') . ' = 0')
			->order($dbo->qn($ordering) . ' ' . $orderingDir);

		/**
		 * Always hide records that belong to a parent reservation
		 *
		 * @since 1.8
		 */
		$q->where($dbo->qn('r.id_parent') . ' = 0');

		if ($filters['id_room'])
		{
			// filter by selected room (already validated)
			$q->where($dbo->qn('rm.id') . ' = ' . $filters['id_room']);
		}
		else if ($operator->get('rooms'))
		{
			// rooms are already separated by a comma
			$q->where($dbo->qn('rm.id') . ' IN (' . $operator->get('rooms') . ')');
		}

		// check if the operator can see all the reservations
		if (!$operator->canSeeAll())
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

		if ($filters['datefilter'])
		{
			$start = VikRestaurants::createTimestamp($filters['datefilter'],  0,  0);
			$end   = VikRestaurants::createTimestamp($filters['datefilter'], 23, 59);

			$q->where($dbo->qn('r.checkin_ts') . ' BETWEEN ' . $start . ' AND ' . $end);
		}
		
		if ($filters['keysearch'])
		{
			$where = array(
				$dbo->qn('r.purchaser_nominative') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('r.purchaser_mail') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('r.purchaser_phone') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			);

			/**
			 * Reverse the search key in order to try finding
			 * users by name even if it was wrote in the opposite way.
			 * If we searched by "John Smith", the system will search
			 * for "Smith John" too.
			 *
			 * @since 1.8
			 */
			$reverse = preg_split("/\s+/", $filters['keysearch']);
			$reverse = array_reverse($reverse);
			$reverse = implode(' ', $reverse);

			$where[] = $dbo->qn('r.purchaser_nominative') . ' LIKE ' . $dbo->q("%{$reverse}%");

			/**
			 * It is now possible to search reservations by coupon code through
			 * the main key search input, as the coupon input has been removed.
			 *
			 * @since 1.8
			 */
			$where[] = $dbo->qn('r.coupon_str') . ' LIKE ' . $dbo->q("{$filters['keysearch']}%");

			/**
			 * It is now possible to search reservations by ID/SID through
			 * the main key search input, as the ordnum input has been removed.
			 *
			 * @since 1.8
			 */
			if (preg_match("/^[A-Z0-9]{16,16}$/i", $filters['keysearch']))
			{
				// alphanumeric string of 16 characters, we are probably searching for "SID"
				$where[] = $dbo->qn('r.sid') . ' = ' . $dbo->q($filters['keysearch']);
			}
			else if (preg_match("/^\d+\-[A-Z0-9]{16,16}$/i", $filters['keysearch']))
			{
				// we are probably searching for "ID" - "SID"
				$where[] = sprintf('CONCAT_WS(\'-\', %s, %s) = %s', $dbo->qn('r.id'), $dbo->qn('r.sid'), $dbo->q($filters['keysearch']));
			}
			else if (preg_match("/^id:\s*(\d+)/i", $filters['keysearch'], $match))
			{
				// we are searching by ID
				$where[] = $dbo->qn('r.id') . ' = ' . (int) $match[1];
			}

			$q->andWhere($where, 'OR');
		}

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		// assert limit used for list query
		$this->assertListQuery($lim0, $lim);

		if ($dbo->getNumRows())
		{
			$reservations = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = $pageNav->getPagesLinks();
		}
		
		$this->operator 	= &$operator;
		$this->reservations = &$reservations;
		$this->navbut 		= &$navbut;
		$this->filters      = &$filters;
		$this->ordering     = &$ordering;
		$this->orderingDir  = &$orderingDir;

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

		// display the template
		parent::display($tpl);
	}
}
