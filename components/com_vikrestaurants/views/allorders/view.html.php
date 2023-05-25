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
 * VikRestaurants global profile view.
 * Here the customers can log in to see the history
 * of reservations/orders made.
 *
 * @since 1.5
 */
class VikRestaurantsViewallorders extends JViewVRE
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
		$user  = JFactory::getUser();

		if (!$user->guest)
		{
			$lim     = 5;
			$lim0    = $tklim0 = $input->get('limitstart', 0, 'uint');
			$ordtype = $input->get('ordtype', 0, 'int');

			$orders_navigation 	 = '';
			$tkorders_navigation = '';
			
			if (empty($ordtype))
			{
				$tklim0 = $input->get('prevlim', 0, 'uint');
			}
			else
			{
				$lim0 = $input->get('prevlim', 0, 'uint');
			}
			
			// get restaurant reservations
			
			$orders = array();
			
			$q = $dbo->getQuery(true)
				->select('SQL_CALC_FOUND_ROWS r.*')
				->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
				->leftjoin($dbo->qn('#__vikrestaurants_users', 'u') . ' ON ' . $dbo->qn('r.id_user') . ' = ' . $dbo->qn('u.id'))
				->where($dbo->qn('u.jid') . ' = ' . $user->id)
				->where($dbo->qn('r.id_parent') . ' = 0')
				->where($dbo->qn('r.status') . ' <> ' . $dbo->q('REMOVED'))
				->order($dbo->qn('r.id') . ' DESC');

			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$orders = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');

				jimport('joomla.html.pagination');
				$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
				$pageNav->setAdditionalUrlParam('prevlim', $tklim0); // prev takeaway lim
				$pageNav->setAdditionalUrlParam('ordtype', 0); // order type
				
				$orders_navigation = $pageNav->getPagesLinks();
			}

			$tkorders = array();

			$q = $dbo->getQuery(true)
				->select('SQL_CALC_FOUND_ROWS r.*')
				->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'))
				->leftjoin($dbo->qn('#__vikrestaurants_users', 'u') . ' ON ' . $dbo->qn('r.id_user') . ' = ' . $dbo->qn('u.id'))
				->where($dbo->qn('u.jid') . ' = ' . $user->id)
				->where($dbo->qn('r.status') . ' <> ' . $dbo->q('REMOVED'))
				->order($dbo->qn('r.id') . ' DESC');

			$dbo->setQuery($q, $tklim0, $lim);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$tkorders = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');

				jimport('joomla.html.pagination');
				$pageNav = new JPagination($dbo->loadResult(), $tklim0, $lim);
				$pageNav->setAdditionalUrlParam('prevlim', $lim0); // prev restaurant lim
				$pageNav->setAdditionalUrlParam('ordtype', 1); // order type

				$tkorders_navigation = $pageNav->getPagesLinks();
			}
		
			$this->orders   = &$orders;
			$this->tkorders = &$tkorders;
			
			$this->ordersNavigation   = &$orders_navigation;
			$this->tkordersNavigation = &$tkorders_navigation;
		}
		else
		{
			// user not logged in, use the login/registration layout
			$this->setLayout('login');
		}

		$this->user = &$user;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}
}
