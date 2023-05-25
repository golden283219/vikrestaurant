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
 * VikRestaurants API logs view.
 *
 * @since 1.5
 */
class VikRestaurantsViewapilogs extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('apilogs', 'l.createdon', 2);

		// set the toolbar
		$this->addToolBar();
		
		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.apiusers.keysearch', 'keysearch', '', 'string');
		$filters['id_login']  = $input->getUint('id_login', 0);

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS l.*')
			->select($dbo->qn(array('u.application', 'u.username')))
			->from($dbo->qn('#__vikrestaurants_api_login_logs', 'l'))
			->leftjoin($dbo->qn('#__vikrestaurants_api_login', 'u') . ' ON ' . $dbo->qn('l.id_login') . ' = ' . $dbo->qn('u.id'))
			->where(1)
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->andWhere(array(
				$dbo->qn('u.application') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('u.username') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('l.content') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			), 'OR');
		}

		if ($filters['id_login'])
		{
			$q->where($dbo->qn('l.id_login') . ' = ' . $filters['id_login']);
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

		$new_type = OrderingManager::getSwitchColumnType('apilogs', $ordering['column'], $ordering['type'], array(1, 2));
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWAPILOGS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'apilog.delete', JText::_('VRDELETE'));

			JToolbarHelper::custom('apilog.truncate', 'trash', 'trash', JText::_('VRDELETEALL'), false);
		}

		JToolbarHelper::cancel('apiuser.cancel', JText::_('VRCANCEL'));
	}
}
