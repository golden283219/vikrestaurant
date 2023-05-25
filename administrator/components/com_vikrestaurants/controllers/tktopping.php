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
 * VikRestaurants take-away topping controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTktopping extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data  = array();
		$id_separator = $app->input->getUint('id_separator');

		if ($id_separator)
		{
			$data['id_separator'] = $id_separator;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.tktopping.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetktopping');

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
		$app->setUserState('vre.tktopping.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetktopping&cid[]=' . $cid[0]);

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
			// recover separator from request
			$id_separator = JFactory::getApplication()->input->getUint('id_separator');

			$url = 'index.php?option=com_vikrestaurants&task=tktopping.add';

			if ($id_separator)
			{
				$url .= '&id_separator=' . $id_separator;
			}

			$this->setRedirect($url);
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
		$args['name']         = $input->get('name', '', 'string');
		$args['description']  = $input->get('description', '', 'string');
		$args['price']        = $input->get('price', 0.0, 'float');
		$args['published']    = $input->get('published', 0, 'uint');
		$args['id_separator'] = $input->get('id_separator', 0, 'int');
		$args['id']           = $input->get('id', 0, 'int');

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// in case the separator was not specified, try to create a new on
		if (!$args['id_separator'])
		{
			$separator = array();
			$separator['id']    = 0;
			$separator['title'] = $input->get('separator_name', '', 'string');

			// create a new separator if the title is not empty
			if (trim($separator['title']))
			{
				$separatorTable = JTableVRE::getInstance('tktopseparator', 'VRETable');
				$separatorTable->save($separator);

				// set new separator ID for topping
				$args['id_separator'] = $separatorTable->id;

				// inject separator ID in request for being used as
				// redirect parameter (see **savenew** method)
				$input->set('id_separator', $separatorTable->id);
			}
		}

		// get record table
		$topping = JTableVRE::getInstance('tktopping', 'VRETable');

		// try to save arguments
		if (!$topping->save($args))
		{
			// get string error
			$error = $topping->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managetktopping';

			if ($topping->id)
			{
				$url .= '&cid[]=' . $topping->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// check if the price of all the groups-toppings should be updated too
		$update_price = $input->get('update_price', 0, 'uint');

		if ($update_price)
		{
			$group = array(
				'id_topping' => $topping->id,
				'rate'       => $topping->price,
			);

			$current_rate = null;

			if ($update_price == 2)
			{
				// update only the toppings with the same rate
				$current_rate = $input->get('old_price', 0, 'float');
			}

			// update rates
			JTableVRE::getInstance('tkgrouptopping', 'VRETable')
				->updateToppingsRate($group, $current_rate);
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=tktopping.edit&cid[]=' . $topping->id);

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
		JTableVRE::getInstance('tktopping', 'VRETable')->delete($cid);

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
		JTableVRE::getInstance('tktopping', 'VRETable')->publish($cid, $state);

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
		$this->setRedirect('index.php?option=com_vikrestaurants&view=tktoppings');
	}
}
