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
 * VikRestaurants operators view.
 *
 * @since 1.6
 */
class VikRestaurantsViewoperators extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('operators', 'lastname', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['group'] 	  = $app->getUserStateFromRequest('vre.operators.group', 'group', 0, 'uint');
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.operators.keysearch', 'keysearch', '', 'string');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS `o`.*')
			->select($dbo->qn('u.username'))
			->from($dbo->qn('#__vikrestaurants_operator', 'o'))
			->leftjoin($dbo->qn('#__users', 'u') . ' ON ' . $dbo->qn('o.jid') . ' = ' . $dbo->qn('u.id'))
			->where(1)
			->order($dbo->qn('o.' . $ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($ordering['column'] == 'lastname')
		{
			$q->order($dbo->qn('o.firstname') . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));
		}

		if ($filters['group'])
		{
			$q->where($dbo->qn('o.group') . ' IN (0, ' . $filters['group'] . ')');
		}

		if ($filters['keysearch'])
		{
			$q->andWhere(array(
				sprintf('CONCAT_WS(\' \', %s, %s) LIKE %s', $dbo->qn('o.firstname'), $dbo->qn('o.lastname'), $dbo->q("%{$filters['keysearch']}%")),
				sprintf('CONCAT_WS(\' \', %s, %s) LIKE %s', $dbo->qn('o.lastname'), $dbo->qn('o.firstname'), $dbo->q("%{$filters['keysearch']}%")),
				$dbo->qn('o.email') . ' LIKE ' . $dbo->q("{$filters['keysearch']}"),
				$dbo->qn('o.code') . ' = ' . $dbo->q($filters['keysearch']),
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
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}

		$new_type = OrderingManager::getSwitchColumnType('operators', $ordering['column'], $ordering['type'], array(1, 2));
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWOPERATORS'), 'vikrestaurants');

		$user = JFactory::getUser();
		
		if ($user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('operator.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('operator.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if ($user->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'operator.delete', JText::_('VRDELETE'));	
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
