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
 * VikRestaurants menus view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmenus extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('menus', 'ordering', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.menus.keysearch', 'keysearch', '', 'string');
		$filters['status']    = $app->getUserStateFromRequest('vre.menus.status', 'status', -1, 'int');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut = "";
		
		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrestaurants_menus'))
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}

		if ($filters['status'] != -1)
		{
			$q->where($dbo->qn('published') . ' = ' . $filters['status']);
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

		foreach ($rows as &$menu)
		{
			$menu['languages'] = array($def_lang);

			$q = $dbo->getQuery(true)
				->select($dbo->qn('tag'))
				->from($dbo->qn('#__vikrestaurants_lang_menus'))
				->where($dbo->qn('id_menu') . ' = ' . $menu['id']);
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// merge default language with translation (filter to obtain a list with unique elements)
				$menu['languages'] = array_unique(array_merge($menu['languages'], $dbo->loadColumn()));
			}
		}

		$new_type = OrderingManager::getSwitchColumnType('menus', $ordering['column'], $ordering['type'], array(1, 2));
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
	 * @param 	integer  $status  The status filter set.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWMENUS'), 'vikrestaurants');
		
		$user = JFactory::getUser();

		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('menu.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('menu.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'menu.delete', JText::_('VRDELETE'));
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
