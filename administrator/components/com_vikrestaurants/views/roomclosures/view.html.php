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
 * VikRestaurants room closures view.
 *
 * @since 1.5
 */
class VikRestaurantsViewroomclosures extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('roomclosures', 'c.id', 2);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['id_room'] = $app->getUserStateFromRequest('vre.roomclosures.id_room', 'id_room', 0, 'uint');
		$filters['date']    = $app->getUserStateFromRequest('vre.roomclosures.date', 'date', '', 'string');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS `c`.*')
			->select($dbo->qn('r.name'))
			->from($dbo->qn('#__vikrestaurants_room', 'r'))
			->rightjoin($dbo->qn('#__vikrestaurants_room_closure', 'c') . ' ON ' . $dbo->qn('r.id') . ' = ' . $dbo->qn('c.id_room'))
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['id_room'])
		{
			$q->where($dbo->qn('r.id') . ' = ' . $filters['id_room']);
		}

		if ($filters['date'])
		{
			$date = VikRestaurants::createTimestamp($filters['date']);
			$q->where($dbo->q($date) . ' BETWEEN ' . $dbo->qn('c.start_ts') . ' AND ' . $dbo->qn('c.end_ts'));
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
			$navbut = "<table align=\"center\"><tr><td>" . $pageNav->getListFooter() . "</td></tr></table>";
		}

		$new_type = OrderingManager::getSwitchColumnType('roomclosures', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);

		// get rooms

		$rooms = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('name', 'text'))
			->from($dbo->qn('#__vikrestaurants_room'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();
		
		if ($dbo->getNumRows())
		{
			$rooms = $dbo->loadObjectList();
		}
		
		$this->rows 	= &$rows;
		$this->rooms    = &$rooms;
		$this->navbut 	= &$navbut;
		$this->filters 	= &$filters;
		$this->ordering = &$ordering;
		
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWROOMCLOSURES'), 'vikrestaurants');

		$user = JFactory::getUser();
		
		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('roomclosure.add', JText::_('VRNEW'));
			JToolbarHelper::divider();  
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('roomclosure.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'roomclosure.delete', JText::_('VRDELETE'));
		}

		JToolbarHelper::cancel('room.cancel', JText::_('VRCANCEL'));		
	}
}
