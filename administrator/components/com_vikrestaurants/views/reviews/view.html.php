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
 * VikRestaurants reviews view.
 *
 * @since 1.6
 */
class VikRestaurantsViewreviews extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('reviews', 'r.id', 2);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.reviews.keysearch', 'keysearch', '', 'string');
		$filters['status'] 	  = $app->getUserStateFromRequest('vre.reviews.status', 'status', -1, 'int');
		$filters['verified']  = $app->getUserStateFromRequest('vre.reviews.verified', 'verified', -1, 'int');
		$filters['stars'] 	  = $app->getUserStateFromRequest('vre.reviews.stars', 'stars', 0, 'uint');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS `r`.*')
			->select($dbo->qn('e.name', 'takeaway_product_name'))
			->from($dbo->qn('#__vikrestaurants_reviews', 'r'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('r.id_takeaway_product'))
			->where(1)
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		if ($filters['keysearch'])
		{
			$q->andWhere(array(
				$dbo->qn('r.name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('r.title') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('e.name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			));
		}

		if ($filters['status'] != -1)
		{
			$q->where($dbo->qn('r.published') . ' = ' . $filters['status']);
		}

		if ($filters['verified'] != -1)
		{
			$q->where($dbo->qn('r.verified') . ' = ' . $filters['verified']);
		}

		if ($filters['stars'])
		{
			$q->where($dbo->qn('r.rating') . ' = ' . $filters['stars']);
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

		$new_type = OrderingManager::getSwitchColumnType('reviews', $ordering['column'], $ordering['type'], array(1, 2));
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWREVIEWS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('review.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('review.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();

			JToolbarHelper::publishList('review.publish', JText::_('VRPUBLISH'));
			JToolbarHelper::spacer();
			
			JToolbarHelper::unpublishList('review.unpublish', JText::_('VRUNPUBLISH'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'review.delete', JText::_('VRDELETE'));
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
		return ($this->filters['stars']
			|| $this->filters['status'] != -1
			|| $this->filters['verified'] != -1);
	}
}
