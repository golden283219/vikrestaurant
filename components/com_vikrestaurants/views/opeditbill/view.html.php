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
 * VikRestaurants operator bill management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewopeditbill extends JViewVRE
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
		$access = $operator && $operator->canLogin() && $operator->isRestaurantAllowed();

		$itemid = $input->get('Itemid', 0, 'uint');
		
		if (!$access)
		{
			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}
		
		$id = $input->getUint('cid', array(0), 'uint');

		// make sure the operator can access the bill of the reservation
		if (!$operator->canSeeAll() && !$operator->canAssign($id[0]))
		{
			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}

		try
		{
			// get order details
			$order = VREOrderFactory::getReservation($id[0], JFactory::getLanguage()->getTag());
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('VRORDERRESERVATIONERROR'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}

		/**
		 * The bill management should be accessed
		 * only in case the bill is not yet closed.
		 *
		 * @since 1.8.1
		 */
		if ($order->bill_closed)
		{
			$this->setLayout('closed');
		}

		// load available menus

		$menus = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_menus'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$menus = $dbo->loadObjectList();
		}

		$hidden = new stdClass;
		$hidden->id    = 0;
		$hidden->name  = JText::_('VROTHER');
		$hidden->image = '';

		// push menu for hidden products
		$menus[] = $hidden;

		$this->operator = &$operator;
		$this->order    = &$order;
		$this->menus    = &$menus;

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);
		
		// display the template
		parent::display($tpl);
	}
}
