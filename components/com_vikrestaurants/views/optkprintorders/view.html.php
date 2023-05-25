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
 * VikRestaurants operator take-away print orders view.
 *
 * @since 1.6
 */
class VikRestaurantsViewoptkprintorders extends JViewVRE
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
		
		////// LOGIN //////

		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin() && $operator->isTakeawayAllowed();
		
		if (!$access)
		{
			$itemid = $input->get('Itemid', 0, 'uint');

			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}

		VRELoader::import('library.mail.factory');
		
		$cid = $input->get('cid', array(), 'uint');

		$langtag = JFactory::getLanguage()->getTag();

		$orders = array();

		foreach ($cid as $id)
		{
			try
			{
				// instantiate order
				$order = VREOrderFactory::getOrder($id, $langtag);

				// instantiate mail
				$mail = VREMailFactory::getInstance('takeaway', 'customer', $order, $langtag);

				// register template within order object
				$order->templateHTML = $mail->getHtml();

				// push order within the list
				$orders[] = $order;
			}
			catch (Exception $e)
			{
				// order not found
			}
		}

		if (!$orders)
		{
			throw new Exception('No orders found', 404);
		}

		/**
		 * This array doesn't contain anymore mail objects.
		 * The list contains objects with the details of the orders.
		 *
		 * @since 1.8.2
		 */
		$this->orders = &$orders;
		
		// display the template
		parent::display($tpl);
	}
}
