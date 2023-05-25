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
 * VikRestaurants custom fields view.
 *
 * @since 1.0
 */
class VikRestaurantsViewcustomf extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('customf', 'ordering', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.customf.keysearch', 'keysearch', '', 'string');
		$filters['group']     = $app->getUserStateFromRequest('vre.customf.group', 'group', 0, 'uint');
		$filters['type']	  = $app->getUserStateFromRequest('vre.customf.type', 'type', '', 'string');
		$filters['rule']	  = $app->getUserStateFromRequest('vre.customf.rule', 'rule', 0, 'uint');
		$filters['status'] 	  = $app->getUserStateFromRequest('vre.customf.status', 'status', -1, 'int');

		// make sure the group is supported
		$filters['group'] = JHtml::_('vrehtml.admin.getgroup', $filters['group']);

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut = "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrestaurants_custfields'))
			->where($dbo->qn('group') . ' = ' . (int) $filters['group'])
			->order($dbo->qn('group') . ' ASC')
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->where($dbo->qn('name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"));
		}

		if (!empty($filters['type']))
		{
			$q->where($dbo->qn('type') . ' = ' . $dbo->q($filters['type']));
		}

		if ($filters['rule'])
		{
			$q->where($dbo->qn('rule') . ' = ' . $filters['rule']);
		}

		if ($filters['status'] != -1)
		{
			$q->where($dbo->qn('required') . ' = ' . $filters['status']);
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

		foreach ($rows as &$field)
		{
			$field['languages'] = array($def_lang);

			$q = $dbo->getQuery(true)
				->select($dbo->qn('tag'))
				->from($dbo->qn('#__vikrestaurants_lang_customf'))
				->where($dbo->qn('id_customf') . ' = ' . $field['id']);
			
			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// merge default language with translation (filter to obtain a list with unique elements)
				$field['languages'] = array_unique(array_merge($field['languages'], $dbo->loadColumn()));
			}
		}

		$new_type = OrderingManager::getSwitchColumnType('customf', $ordering['column'], $ordering['type'], array(1, 2));
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWCUSTOMFS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('customf.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('customf.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'customf.delete', JText::_('VRDELETE'));
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
		return (!empty($this->filters['type'])
			|| $this->filters['rule']
			|| $this->filters['status'] != -1);
	}
}
