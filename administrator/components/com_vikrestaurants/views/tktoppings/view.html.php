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
 * VikRestaurants take-away toppings view.
 *
 * @since 1.6
 */
class VikRestaurantsViewtktoppings extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('tktoppings', 't.ordering', 1);

		// set the toolbar
		$this->addToolBar();
		
		$filters = array();
		$filters['keysearch']    = $app->getUserStateFromRequest('vre.tktoppings.keysearch', 'keysearch', '', 'string');
		$filters['status']       = $app->getUserStateFromRequest('vre.tktoppings.status', 'status', '', 'string');
		$filters['id_separator'] = $app->getUserStateFromRequest('vre.tktoppings.id_separator', 'id_separator', 0, 'uint');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS t.*')
			->select($dbo->qn('s.title', 'separator'))
			->from($dbo->qn('#__vikrestaurants_takeaway_topping', 't'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_topping_separator', 's') . ' ON ' . $dbo->qn('t.id_separator') . ' = ' . $dbo->qn('s.id'))
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('t.name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}

		if (strlen($filters['status']))
		{
			$q->where($dbo->qn('t.published') . ' = ' . (int) $filters['status']);
		}

		if ($filters['id_separator'])
		{
			$q->where($dbo->qn('t.id_separator') . ' = ' . $filters['id_separator']);
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

		foreach ($rows as &$topping)
		{
			$topping['languages'] = array($def_lang);

			$q = $dbo->getQuery(true)
				->select($dbo->qn('tag'))
				->from($dbo->qn('#__vikrestaurants_lang_takeaway_topping'))
				->where($dbo->qn('id_topping') . ' = ' . $topping['id']);
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// merge default language with translation (filter to obtain a list with unique elements)
				$topping['languages'] = array_unique(array_merge($topping['languages'], $dbo->loadColumn()));
			}
		}

		// get all separators
		$separators = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('title', 'text'))
			->from($dbo->qn('#__vikrestaurants_takeaway_topping_separator'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$separators = $dbo->loadObjectList();
		}

		$new_type = OrderingManager::getSwitchColumnType('tktoppings', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);
		
		$this->rows       = &$rows;
		$this->navbut     = &$navbut;
		$this->separators = &$separators;
		$this->ordering   = &$ordering;
		$this->filters    = &$filters;
		
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWTKTOPPINGS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('tktopping.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('tktopping.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
			
			JToolbarHelper::publishList('tktopping.publish', JText::_('VRPUBLISH'));
			JToolbarHelper::divider();
			
			JToolbarHelper::unpublishList('tktopping.unpublish', JText::_('VRUNPUBLISH'));
			JToolbarHelper::spacer();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'tktopping.delete', JText::_('VRDELETE'));
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
		return (strlen($this->filters['status']) || $this->filters['id_separator']);
	}
}
