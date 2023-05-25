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
 * VikRestaurants restaurant reservations view.
 *
 * @since 1.0
 */
class VikRestaurantsViewreservations extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('reservations', 'r.id', 2);

		// set the toolbar
		$this->addToolBar();
		
		$filters = array();
		$filters['datefilter']  = $app->getUserStateFromRequest('vre.reservations.datefilter', 'datefilter', '', 'string');
		$filters['shift']       = $app->getUserStateFromRequest('vre.reservations.shift', 'shift', '', 'string');
		$filters['keysearch']   = $app->getUserStateFromRequest('vre.reservations.keysearch', 'keysearch', '', 'string');
		$filters['ordstatus']   = $app->getUserStateFromRequest('vre.reservations.ordstatus', 'ordstatus', '', 'string');
		$filters['id_room']     = $app->getUserStateFromRequest('vre.reservations.id_room', 'id_room', 0, 'uint');
		$filters['id_operator'] = $app->getUserStateFromRequest('vre.reservations.id_operator', 'id_operator', 0, 'uint');

		// this filters comes only from details info, when a shared table hosts more than one reservation
		$filters['ids']	= $app->getUserStateFromRequest('vre.reservations.ids', 'ids', array(), 'uint');

		$rows = array();

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$inner = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikrestaurants_order_status', 'os'))
			->where($dbo->qn('os.id_order') . ' = ' . $dbo->qn('r.id'))
			->where($dbo->qn('os.group') . ' = 1');

		$cluster = $dbo->getQuery(true)
			->select('GROUP_CONCAT(' . $dbo->qn('ti.name') . ')')
			->from($dbo->qn('#__vikrestaurants_reservation', 'ri'))
			->leftjoin($dbo->qn('#__vikrestaurants_table', 'ti') . ' ON ' . $dbo->qn('ri.id_table') . ' = ' . $dbo->qn('ti.id'))
			->where($dbo->qn('ri.id_parent') . ' = ' . $dbo->qn('r.id'));

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS r.*')
			->select($dbo->qn('t.name', 'table_name'))
			->select($dbo->qn('p.name', 'payment_name'))
			->select($dbo->qn('u.name', 'createdby_name'))
			->select(array(
				$dbo->qn('c.code'),
				$dbo->qn('c.icon', 'code_icon'),
			))
			->select('(' . $inner . ') AS ' . $dbo->qn('order_status_count'))
			->select('(' . $cluster . ') AS ' . $dbo->qn('cluster'))
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_gpayments', 'p') . ' ON ' . $dbo->qn('r.id_payment') . ' = ' . $dbo->qn('p.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_res_code', 'c') . ' ON ' . $dbo->qn('r.rescode') . ' = ' . $dbo->qn('c.id'))
			->leftjoin($dbo->qn('#__users', 'u') . ' ON ' . $dbo->qn('r.created_by') . ' = ' . $dbo->qn('u.id'))
			->where(1)
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		/**
		 * Always hide records that belong to a parent reservation
		 *
		 * @since 1.8
		 */
		$q->where($dbo->qn('r.id_parent') . ' = 0');

		/**
		 * In case the room should be displayed within the list
		 * join the rooms table.
		 *
		 * @since 1.8
		 */
		if (in_array('rname', VikRestaurants::getListableFields()))
		{
			$q->select($dbo->qn('rm.name', 'room_name'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('rm.id') . ' = ' . $dbo->qn('t.id_room'));
		}

		/**
		 * In case we are sorting the reservations by BILL VALUE or 
		 * PURCHASER NAME, we should hide all the CLOSURES as they
		 * might distort the first records (when ascending).
		 *
		 * @since 1.8
		 */
		if (in_array($ordering['column'], array('r.bill_value', 'r.purchaser_nominative')))
		{
			// display only NON-CLOSURE reservations
			$q->where($dbo->qn('r.closure') . ' = 0');
		}
		
		if ($filters['datefilter'])
		{
			$start = VikRestaurants::createTimestamp($filters['datefilter'],  0,  0);
			$end   = VikRestaurants::createTimestamp($filters['datefilter'], 23, 59);

			$q->where($dbo->qn('r.checkin_ts') . ' BETWEEN ' . $start . ' AND ' . $end);

			if ($filters['shift'])
			{
				// get shift time
				$time = JHtml::_('vikrestaurants.timeofshift', $filters['shift']);

				// Do not include MINUTES in query.
				// Make sure the UNIX timestamps are converted to the timezone
				// used by the server, so that the hours won't be shifted.
				$q->where(sprintf(
					'DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(%s), @@session.time_zone, \'%s\'), \'%%H\') BETWEEN %d AND %d',
					$dbo->qn('r.checkin_ts'),
					date('P'), // returns the string offset '+02:00'
					$time->fromhour,
					$time->tohour
				));
			}
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

		if ($filters['ordstatus'])
		{
			if ($filters['ordstatus'] == 'CLOSURE')
			{
				$q->where($dbo->qn('r.closure') . ' = 1');
			}
			else
			{
				$q->where($dbo->qn('r.status') . ' = ' . $dbo->q($filters['ordstatus']));
				$q->where($dbo->qn('r.closure') . ' = 0');
			}
		}

		/**
		 * Filter reservations by selected room.
		 *
		 * @since 1.8
		 */
		if ($filters['id_room'])
		{
			$q->where($dbo->qn('t.id_room') . ' = ' . $filters['id_room']);
		}

		/**
		 * Filter reservations by selected operator.
		 *
		 * @since 1.8
		 */
		if ($filters['id_operator'])
		{
			$q->where($dbo->qn('r.id_operator') . ' = ' . $filters['id_operator']);
		}

		// Check if the IDs filter is not empty.
		// Also make sure that the first element is not equals to 0,
		// otherwise it would mean that we cleared the filters.
		if (count($filters['ids']) && $filters['ids'][0] > 0)
		{
			// reset WHERE
			$q->clear('where')->where($dbo->qn('r.id') . ' IN (' . implode(',', $filters['ids']) . ')');

			// reset filters
			foreach ($filters as $k => $v)
			{
				if ($k != 'ids')
				{
					$filters[$k] = '';
				}
			}
		}
		else
		{
			// unset IDs filter
			$filters['ids'] = null;
		}

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		// assert limit used for list query
		$this->assertListQuery($lim0, $lim);

		if ($dbo->getNumRows())
		{
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}

		// get custom fields

		$mask = VRCustomFields::FILTER_EXCLUDE_REQUIRED_CHECKBOX | VRCustomFields::FILTER_EXCLUDE_SEPARATOR;
		$custom_fields = VRCustomFields::getList(0, $mask);
		
		$new_type = OrderingManager::getSwitchColumnType('reservations', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);
		
		$this->rows     = &$rows;
		$this->navbut   = &$navbut;
		$this->filters  = &$filters;
		$this->ordering = &$ordering;

		$this->customFields = &$custom_fields;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWRESERVATION'), 'vikrestaurants');

		$user = JFactory::getUser();
		
		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('reservation.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{	
			JToolbarHelper::editList('reservation.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
			
			JToolbarHelper::custom('reservation.editbill', 'tag-2', 'tag-2', JText::_('VRBILL'), true, false);
			JToolbarHelper::spacer();
		}
			
		JToolbarHelper::custom('exportres.add', 'out', 'out', JText::_('VREXPORT'), false, false);
		JToolbarHelper::divider();
		
		JToolbarHelper::custom('statistics', 'chart', 'chart', JText::_('VRSTAT'), false, false);
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('printorders', 'print', 'print', JText::_('VRPRINT'), true, false);
		JToolbarHelper::spacer();

		if ($user->authorise('core.edit', 'com_vikrestaurants') || $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolBarHelper::custom('invoice.generate', 'vcard', 'vcard', JText::_('VRINVOICE'), true);
			JToolBarHelper::spacer();
		}

		if ($this->isApiSmsConfigured())
		{
			JToolbarHelper::custom('reservation.sendsms', 'comment', 'comment', JText::_('VRSENDSMS'), true);
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'reservation.delete', JText::_('VRDELETE'));    
		}
	}

	/**
	 * Checks whether the SMS provider has been configured.
	 *
	 * @return 	boolean
	 */
	protected function isApiSmsConfigured()
	{
		// first of all, check ACL
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			return false;
		}
		
		try
		{
			// try to instantiate the SMS API provider
			$provider = VREApplication::getInstance()->getSmsInstance();
		}
		catch (Exception $e)
		{
			// SMS provider not configured
			return false;
		}

		// provider (probably) configured
		return true;
	}

	/**
	 * Checks for advanced filters set in the request.
	 *
	 * @return 	boolean  True if active, otherwise false.
	 *
	 * @since 	1.8
	 */
	protected function hasFilters()
	{
		return ($this->filters['ordstatus']
			|| $this->filters['datefilter']
			|| $this->filters['id_room']
			|| $this->filters['id_operator']);
	}
}
