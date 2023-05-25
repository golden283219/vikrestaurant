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
 * VikRestaurants working shiftst view.
 *
 * @since 1.0
 */
class VikRestaurantsViewshifts extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('shifts', 'id', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.shifts.keysearch', 'keysearch', '', 'string');
		$filters['group']    = $app->getUserStateFromRequest('vre.shifts.group', 'group', 0, 'uint');

		// make sure the group is supported
		$filters['group'] = JHtml::_('vrehtml.admin.getgroup', $filters['group'], array(1, 2), true);

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrestaurants_shifts'))
			->where(1)
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['group'] > 0)
		{
			$q->where($dbo->qn('group') . ' = ' . $filters['group']);
		}

		if ($filters['keysearch'])
		{
			$q->andWhere(array(
				$dbo->qn('name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('label') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			));
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

		$new_type = OrderingManager::getSwitchColumnType('shifts', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);

		$this->rows 	= &$rows;
		$this->navbut 	= &$navbut;
		$this->ordering = &$ordering;
		$this->filters 	= &$filters;
		
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWSHIFTS'), 'vikrestaurants');

		$user = JFactory::getUser();
		
		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('shift.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}
		
		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('shift.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}
		
		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'shift.delete', JText::_('VRDELETE'));
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
		return ($this->filters['group']);
	}
}
