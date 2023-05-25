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
 * VikRestaurants tags view.
 *
 * @since 1.8
 */
class VikRestaurantsViewtags extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('tags', 't.ordering', 1);

		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.tags.keysearch', 'keysearch', '', 'string');
		$filters['group']     = $app->getUserStateFromRequest('vre.tags.group', 'group', '', 'string');

		// set the toolbar
		$this->addToolBar($filters['group']);

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS t.*')
			->from($dbo->qn('#__vikrestaurants_tag', 't'))
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['group'] == 'products')
		{
			$count = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_section_product', 'p'))
				->where(array(
					// only one tag
					$dbo->qn('p.tags') . ' = ' . $dbo->qn('t.name'),
					// tag in the middle
					$dbo->qn('p.tags') . ' LIKE CONCAT(\'%,\', ' . $dbo->qn('t.name') . ', \',%\')',
					// first tag available
					$dbo->qn('p.tags') . ' LIKE CONCAT(' . $dbo->qn('t.name') . ', \',%\')',
					// last tag available
					$dbo->qn('p.tags') . ' LIKE CONCAT(\'%,\', ' . $dbo->qn('t.name') . ')',
				), 'OR');
		}
		else
		{
			$count = null;
		}

		if ($count)
		{
			$q->select('(' . $count . ') AS ' . $dbo->qn('count'));
		}
		else
		{
			$q->select($dbo->q('/') . ' AS ' . $dbo->qn('count'));
		}

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('t.name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}

		if ($filters['group'])
		{
			$q->where($dbo->qn('t.group') . ' = ' . $dbo->q($filters['group']));
		}

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		// assert limit used for list query
		$this->assertListQuery($lim0, $lim);

		if ($dbo->getNumRows() )
		{
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}

		$new_type = OrderingManager::getSwitchColumnType('tags', $ordering['column'], $ordering['type'], array(1, 2));
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
	 * @param 	string 	$group  The selected group.
	 *
	 * @return 	void
	 */
	private function addToolBar($group)
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWTAGS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('tag.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('tag.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'tag.delete', JText::_('VRDELETE'));
		}

		switch ($group)
		{
			case 'products':
				$task = 'menusproduct.cancel';
				break;

			default:
				$task = 'configuration.dashboard';
		}

		JToolbarHelper::cancel($task, JText::_('VRCANCEL'));
	}
}
