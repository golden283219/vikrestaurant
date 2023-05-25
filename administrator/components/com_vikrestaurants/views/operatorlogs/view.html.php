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
 * VikRestaurants operator logs view.
 *
 * @since 1.5
 */
class VikRestaurantsViewoperatorlogs extends JViewVRE
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

		// set the toolbar
		$this->addToolBar();
		
		$filters = array();
		$filters['id_operator'] = $input->get('id', 0, 'uint');
		$filters['keysearch'] 	= $app->getUserStateFromRequest('vre.operatorlogs.keysearch', 'keysearch', '', 'string');
		$filters['date'] 		= $app->getUserStateFromRequest('vre.operatorlogs.date', 'date', '', 'string');
		$filters['group']       = $app->getUserStateFromRequest('vre.operatorlogs.group', 'group', 0, 'uint');
		$filters['day']         = $app->getUserStateFromRequest('vre.operatorlogs.day', 'day', '', 'string');

		if ($filters['date'] || $filters['day'] == $dbo->getNullDate())
		{
			// do not use day filter in case a date interval was set
			$filters['day'] = '';
		}

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS l.*')
			->from($dbo->qn('#__vikrestaurants_operator_log', 'l'))
			->where(1)
			->order($dbo->qn('l.createdon') . ' DESC');

		if ($filters['id_operator'])
		{
			$q->where($dbo->qn('l.id_operator') . ' = ' . $filters['id_operator']);
		}

		if ($filters['group'])
		{
			$q->where($dbo->qn('l.group') . ' = ' . $filters['group']);
		}

		if ($filters['keysearch'])
		{
			$where = array();
			$where[] = $dbo->qn('l.log') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%");

			// check if the searched key contains only numbers
			if (preg_match("/^\d+$/", $filters['keysearch']) && (int) $filters['keysearch'] > 0)
			{
				// search by reservation ID too
				$where[] = $dbo->qn('l.id_reservation') . ' = ' . (int) $filters['keysearch'];
			}

			$q->andWhere($where, 'OR');
		}

		if ($filters['date'])
		{
			$q->where($dbo->qn('l.createdon') . ' >= ' . $dbo->q($filters['date']));
		}
		else if ($filters['day'])
		{
			$start = VikRestaurants::createTimestamp($filters['day'], 0, 0);
			$end   = VikRestaurants::createTimestamp($filters['day'], 23, 59);

			$q->where($dbo->qn('l.createdon') . ' BETWEEN ' . $start . ' AND ' . $end);	
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
		
		$this->rows 	= &$rows;
		$this->navbut 	= &$navbut;
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWOPERATORLOGS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'operator.deletelogs', JText::_('VRDELETE'));

			/**
			 * Trash logs older than a specific date.
			 *
			 * @since 1.8
			 */
			JToolbarHelper::trash('operator.trashlogs', JText::_('JTOOLBAR_TRASH'), false);
		}
		
		JToolbarHelper::cancel('operator.cancel', JText::_('VRCANCEL'));
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
		return ($this->filters['group']
			|| $this->filters['date']
			|| $this->filters['day']);
	}
}
