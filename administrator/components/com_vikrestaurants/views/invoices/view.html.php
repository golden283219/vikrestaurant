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
 * VikRestaurants invoices view.
 *
 * @since 1.7
 */
class VikRestaurantsViewinvoices extends JViewVRE
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
		
		// set the toolbar
		$this->addToolBar();
		
		$year  = $app->getUserStateFromRequest('vre.invoices.year', 'year', 0, 'uint');
		$month = $app->getUserStateFromRequest('vre.invoices.month', 'month', 0, 'uint');

		if (empty($year) || empty($month))
		{
			$d = getdate();
			$year  = $d['year'];
			$month = $d['mon'];
		}

		$filters = array();
		$filters['group'] 	  = $app->getUserStateFromRequest('vre.invoices.group', 'group', '', 'string');
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.invoices.keysearch', 'keysearch', '', 'string');

		// make sure the group is supported
		$filters['group'] = JHtml::_('vrehtml.admin.getgroup', $filters['group']);

		// get invoices
		$invoices	= array();
		$loadedAll 	= true;
		$limit 		= 30;
		$maxLimit 	= 0;

		$start_ts = mktime(0, 0, 0, $month, 1, $year);
		$end_ts   = mktime(0, 0, 0, $month + 1, 1, $year) - 1;

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrestaurants_invoice'))
			->where($dbo->qn('inv_date') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts)
			->order(array(
				// Order by day of the month, ascending.
				// Do not need to use month and year too because we are already
				// filtering the invoices by month.
				sprintf('DATE_FORMAT(FROM_UNIXTIME(%s), \'%%d\') ASC', $dbo->qn('inv_date')),
				$dbo->qn('id_order') . ' ASC',
			));

		if (strlen($filters['group']))
		{
			$q->where($dbo->qn('group') . ' = ' . (int) $filters['group']);
		}

		if ($filters['keysearch'])
		{
			$q->andWhere(array(
				$dbo->qn('file') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('inv_number') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			), 'OR');
		}

		$dbo->setQuery($q, 0, $limit);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$invoices = $dbo->loadAssocList();

			$dbo->setQuery('SELECT FOUND_ROWS();');

			if (($maxLimit = (int) $dbo->loadResult()) > count($invoices))
			{
				$loadedAll = false;
			}
		}

		// build tree
		
		$tree = array();

		$q = $dbo->getQuery(true)
			->select(sprintf('DATE_FORMAT(FROM_UNIXTIME(%s), \'%%Y\') AS %s', $dbo->qn('inv_date'), $dbo->qn('year')))
			->select(sprintf('DATE_FORMAT(FROM_UNIXTIME(%s), \'%%c\') AS %s', $dbo->qn('inv_date'), $dbo->qn('mon')))
			->from($dbo->qn('#__vikrestaurants_invoice'))
			->group($dbo->qn('year'))
			->group($dbo->qn('mon'))
			->order(sprintf('CAST(%s AS unsigned) DESC', $dbo->qn('year')))
			->order(sprintf('CAST(%s AS unsigned) DESC', $dbo->qn('mon')));
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $r)
			{
				if (empty($r->year))
				{
					$r->year = $r->mon = -1;
				}
				
				if (empty($tree[$r->year]))
				{
					$tree[$r->year] = array();
				}

				$tree[$r->year][] = $r->mon;
			}
		}

		$seek = array(
			'year'  => $year,
			'month' => $month,
		);
		
		$this->invoices = &$invoices;
		$this->tree     = &$tree;
		$this->filters  = &$filters;
		$this->seek     = &$seek;

		$this->limit     = &$limit;
		$this->maxLimit  = &$maxLimit;
		$this->loadedAll = &$loadedAll;
		
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWINVOICES'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('invoice.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('invoice.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		JToolBarHelper::custom('invoice.download', 'download', 'download', JText::_('VRDOWNLOAD'), true);
		JToolbarHelper::divider();

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'invoice.delete', JText::_('VRDELETE'));
		}
	}
}
