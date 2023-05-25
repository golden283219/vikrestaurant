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
 * VikRestaurants take-away menu entries view.
 *
 * @since 1.5
 */
class VikRestaurantsViewtkproducts extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('tkproducts', 'ordering', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['keysearch']        = $app->getUserStateFromRequest('vre.tkproducts.keysearch', 'keysearch', '', 'string');
		$filters['status']           = $app->getUserStateFromRequest('vre.tkproducts.status', 'status', '', 'string');
		$filters['id_takeaway_menu'] = $app->getUserStateFromRequest('vre.tkproducts.id_takeaway_menu', 'id_takeaway_menu', 0, 'uint');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
			->where($dbo->qn('id_takeaway_menu') . ' = ' . $filters['id_takeaway_menu'])
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}

		if (strlen($filters['status']))
		{
			$q->where($dbo->qn('published') . ' = ' . (int) $filters['status']);
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

		foreach ($rows as &$entry)
		{
			$entry['languages'] = array($def_lang);

			$q = $dbo->getQuery(true)
				->select($dbo->qn('tag'))
				->from($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry'))
				->where($dbo->qn('id_entry') . ' = ' . $entry['id']);
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// merge default language with translation (filter to obtain a list with unique elements)
				$entry['languages'] = array_unique(array_merge($entry['languages'], $dbo->loadColumn()));
			}
		}

		$new_type = OrderingManager::getSwitchColumnType('tkproducts', $ordering['column'], $ordering['type'], array(1, 2));
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWTKPRODUCTS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('tkentry.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('tkentry.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'tkentry.delete', JText::_('VRDELETE'));
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
		return (strlen($this->filters['status']));
	}
}
