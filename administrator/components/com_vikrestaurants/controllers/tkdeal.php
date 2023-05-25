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
 * VikRestaurants take-away deal controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkdeal extends VREControllerAdmin
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
		$app->setUserState('vre.tkdeal.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkdeal');

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
		$app->setUserState('vre.tkdeal.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkdeal&cid[]=' . $cid[0]);

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
			$this->setRedirect('index.php?option=com_vikrestaurants&task=tkdeal.add');
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
		$user  = JFactory::getUser();
		
		$args = array();
		$args['name'] 			= $input->get('name', '', 'string');
		$args['description'] 	= $input->get('description', '', 'raw');
		$args['start_ts']		= $input->get('start_ts', '', 'string');
		$args['end_ts'] 		= $input->get('end_ts', '', 'string');
		$args['max_quantity'] 	= $input->get('max_quantity', 0, 'int');
		$args['published'] 		= $input->get('published', 0, 'uint');
		$args['shifts']         = $input->get('shifts', array(), 'uint');
		$args['service'] 		= $input->get('service', 2, 'uint');
		$args['type'] 			= $input->get('type', 0, 'uint');
		$args['amount'] 		= $input->get('amount', 0.0, 'float');
		$args['percentot'] 		= $input->get('percentot', 0, 'uint');
		$args['auto_insert'] 	= $input->get('auto_insert', 0, 'uint');
		$args['min_quantity'] 	= $input->get('min_quantity', 0, 'uint');
		$args['cart_tcost'] 	= $input->get('cart_tcost', 0.0, 'float');
		$args['id'] 			= $input->get('id', 0, 'int');

		$days = $input->get('days', array(), 'uint');

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$deal = JTableVRE::getInstance('tkdeal', 'VRETable');

		// try to save arguments
		if (!$deal->save($args))
		{
			// update user state data by injecting the selected days
			$data = $app->getUserState('vre.tkdeal.data', array());
			$data['days'] = $days;
			$app->setUserState('vre.tkdeal.data', $data);

			// get string error
			$error = $deal->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managetkdeal';

			if ($deal->id)
			{
				$url .= '&cid[]=' . $deal->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// set deal available week days
		$deal->setAvailabilityDays($days);

		// get deal product table
		$dealprod = JTableVRE::getInstance('tkdealproduct', 'VRETable');

		$food = $input->get('deal_food', array(), 'array');

		if ($food)
		{
			for ($i = 0; $i < count($food['id']); $i++)
			{
				// explode product/option IDs
				list($id_product, $id_option) = explode(':', $food['id_prod_option'][$i]);

				$prod = array(
					'id'         => (int) $food['id'][$i],
					'id_deal'    => $deal->id,
					'id_product' => (int) $id_product,
					'id_option'  => (int) $id_option,
					'required'   => (int) $food['required'][$i],
					'quantity'   => (int) $food['quantity'][$i],
				);

				// save deal product relation
				$dealprod->save($prod);
				$dealprod->reset();
			}
		}

		// delete previous products, if any
		$delete_food = $input->get('delete_deal_food', array(), 'uint');

		if ($delete_food)
		{
			$dealprod->delete($delete_food);
		}

		// get deal free product table
		$dealfree = JTableVRE::getInstance('tkdealfree', 'VRETable');

		$food = $input->get('free_food', array(), 'array');

		if ($food)
		{
			for ($i = 0; $i < count($food['id']); $i++)
			{
				// explode product/option IDs
				list($id_product, $id_option) = explode(':', $food['id_prod_option'][$i]);

				$prod = array(
					'id'         => (int) $food['id'][$i],
					'id_deal'    => $deal->id,
					'id_product' => (int) $id_product,
					'id_option'  => (int) $id_option,
					'quantity'   => (int) $food['quantity'][$i],
				);

				// save deal product relation
				$dealfree->save($prod);
				$dealfree->reset();
			}
		}

		// delete previous products, if any
		$delete_food = $input->get('delete_free_food', array(), 'uint');

		if ($delete_food)
		{
			$dealfree->delete($delete_food);
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=tkdeal.edit&cid[]=' . $deal->id);

		return true;
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'uint');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		JTableVRE::getInstance('tkdeal', 'VRETable')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Publishes the selected records.
	 *
	 * @return 	boolean
	 */
	public function publish()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'uint');
		$task = $app->input->get('task', null);

		$state = $task == 'unpublish' ? 0 : 1;

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// change state of selected records
		JTableVRE::getInstance('tkdeal', 'VRETable')->publish($cid, $state);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=tkdeals');
	}
}
