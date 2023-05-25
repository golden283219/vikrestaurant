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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants operator coupon controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerOpcoupon extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.coupon.data', array());

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=opmanagecoupon');

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.coupon.data', array());

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=opmanagecoupon&cid[]=' . $cid[0]);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->cancel();
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return 	void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

			$url = 'index.php?option=com_vikrestaurants&task=opcoupon.add' . ($itemid ? '&Itemid=' . $itemid : '');

			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}
		
		$args = array();
		$args['code']      = $input->getString('code');
		$args['type']      = $input->getUint('type', 1);
		$args['percentot'] = $input->getUint('percentot', 1);
		$args['value']     = $input->getFloat('value');
		$args['datestart'] = $input->getString('datestart');
		$args['dateend']   = $input->getString('dateend');
		$args['minvalue']  = $input->getFloat('minvalue');
		$args['group']     = $input->getUint('group');
		$args['id']        = $input->getInt('id', 0);

		// check user permissions
		if (!$operator->canManage('coupon'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		if ($args['group'] == 0 && !$operator->isRestaurantAllowed())
		{
			// restaurant not supported, back to take-away
			$args['group'] = 1;
		}

		if ($args['group'] == 1 && !$operator->isTakeawayAllowed())
		{
			// take-away not supported, back to restaurant
			$args['group'] = 0;
		}

		$itemid = $input->get('Itemid', 0, 'uint');

		// get record table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$coupon = JTableVRE::getInstance('coupon', 'VRETable');

		// try to save arguments
		if (!$coupon->save($args))
		{
			// get string error
			$error = $coupon->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=opmanagecoupon' . ($itemid ? '&Itemid=' . $itemid : '');

			if ($coupon->id)
			{
				$url .= '&cid[]=' . $coupon->id;
			}

			// redirect to new/edit page
			$this->setRedirect(JRoute::_($url, false));
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		$url = 'index.php?option=com_vikrestaurants&task=opcoupon.edit&cid[]=' . $coupon->id . ($itemid ? '&Itemid=' . $itemid : '');

		// redirect to edit page
		$this->setRedirect(JRoute::_($url, false));

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @param 	string  $view  The return view.
	 *
	 * @return 	void
	 */
	public function cancel($view = null)
	{
		$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

		$url = 'index.php?option=com_vikrestaurants' . ($itemid ? '&Itemid=' . $itemid : '');

		if (is_null($view))
		{
			$url .= '&view=opcoupons';
		}
		else
		{
			$url .= '&view=' . $view;
		}

		$this->setRedirect(JRoute::_($url, false));
	}
}
