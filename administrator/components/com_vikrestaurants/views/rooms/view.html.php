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
 * VikRestaurants rooms view.
 *
 * @since 1.0
 */
class VikRestaurantsViewrooms extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		$ordering = OrderingManager::getColumnToOrder('rooms', 'ordering', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.rooms.keysearch', 'keysearch', '', 'string');
		$filters['status']    = $app->getUserStateFromRequest('vre.rooms.status', 'status', -1, 'int');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$now = VikRestaurants::now();

		$rows = array();

		$closure = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikrestaurants_room_closure', 'c'))
			->where(array(
				$dbo->qn('c.id_room') . ' = ' . $dbo->qn('r.id'),
				$now . ' BETWEEN ' . $dbo->qn('c.start_ts') . ' AND ' . $dbo->qn('c.end_ts'),
			));

		$tables = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikrestaurants_table', 't'))
			->where($dbo->qn('t.id_room') . ' = ' . $dbo->qn('r.id'));

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS `r`.*')
			->select('(' . $closure . ') AS ' . $dbo->qn('is_closed'))
			->select('(' . $tables . ') AS ' . $dbo->qn('tables_count'))
			->from($dbo->qn('#__vikrestaurants_room', 'r'))
			->order($dbo->qn('r.' . $ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('r.name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}

		if ($filters['status'] == 0)
		{
			$q->having(array(
				$dbo->qn('r.published') . ' = 0',
				$dbo->qn('is_closed') . ' = 1',
			), 'OR');
		}
		else if ($filters['status'] == 1)
		{
			$q->having(array(
				$dbo->qn('r.published') . ' = 1',
				$dbo->qn('is_closed') . ' = 0',
			), 'AND');
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

		$def_lang = VikRestaurants::getDefaultLanguage();

		foreach ($rows as &$room)
		{
			$room['languages'] = array($def_lang);

			$q = $dbo->getQuery(true)
				->select($dbo->qn('tag'))
				->from($dbo->qn('#__vikrestaurants_lang_room'))
				->where($dbo->qn('id_room') . ' = ' . $room['id']);
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// merge default language with translation (filter to obtain a list with unique elements)
				$room['languages'] = array_unique(array_merge($room['languages'], $dbo->loadColumn()));
			}
		}

		$new_type = OrderingManager::getSwitchColumnType('rooms', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);

		$this->rows     = &$rows;
		$this->navbut   = &$navbut;
		$this->ordering = &$ordering;
		$this->filters  = &$filters;
		
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWROOMS'), 'vikrestaurants');

		$user = JFactory::getUser();
		
		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('room.add', JText::_('VRNEW'));
			JToolbarHelper::divider();  
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('room.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();

			JToolbarHelper::custom('roomclosures', 'calendar', 'calendar', JText::_('VRMANAGECLOSURES'), false);
			JToolbarHelper::spacer();
		}

		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'room.delete', JText::_('VRDELETE'));
		}
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
		return ($this->filters['status'] != -1);
	}
}
