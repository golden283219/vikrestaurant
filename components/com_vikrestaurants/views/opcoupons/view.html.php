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
 * VikRestaurants operator reservations view.
 *
 * @since 1.6
 */
class VikRestaurantsViewopcoupons extends JViewVRE
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
		
		////// LOGIN //////

		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin() && $operator->canRead('coupon');
		
		if (!$access)
		{
			$itemid = $input->get('Itemid', 0, 'uint');

			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}

		$ordering    = $app->getUserStateFromRequest('vropcoupon.ordering', 'filter_order', 'id', 'string');
		$orderingDir = $app->getUserStateFromRequest('vropcoupon.direction', 'filter_order_Dir', 'DESC', 'string');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart(array());
		$navbut = "";
		
		$coupons = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_coupons'))
			->order($dbo->qn($ordering) . ' ' . $orderingDir);

		if ($operator->get('group') > 0)
		{
			$q->where($dbo->qn('group') . ' = ' . ($operator->get('group') - 1));
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$coupons = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = $pageNav->getPagesLinks();
		}
		
		$this->operator    = &$operator;
		$this->coupons     = &$coupons;
		$this->navbut      = &$navbut;
		$this->ordering    = &$ordering;
		$this->orderingDir = &$orderingDir;

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

		// display the template
		parent::display($tpl);
	}
}
