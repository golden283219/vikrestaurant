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
 * VikRestaurants menus product controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerMenusproduct extends VREControllerAdmin
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
		$app->setUserState('vre.menusproduct.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managemenusproduct');

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
		$app->setUserState('vre.menusproduct.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managemenusproduct&cid[]=' . $cid[0]);

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
			$this->setRedirect('index.php?option=com_vikrestaurants&view=menusproducts');
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
			$this->setRedirect('index.php?option=com_vikrestaurants&task=menusproduct.add');
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @param 	boolean  $ajax  True if the request have been made via AJAX.
	 *
	 * @return 	boolean
	 */
	public function save($ajax = false)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['name'] 		 = $input->get('name', '', 'string');
		$args['description'] = $input->get('description', '', 'raw');
		$args['price'] 		 = $input->get('price', 0.0, 'float');
		$args['image'] 		 = $input->get('image', '', 'string');
		$args['tags'] 		 = $input->get('tags', array(), 'string');
		$args['published'] 	 = $input->get('published', 0, 'uint');
		$args['hidden'] 	 = $input->get('hidden', 0, 'uint');
		$args['id'] 		 = $input->get('id', 0, 'int');

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			if ($ajax)
			{
				// not authorised, abort
				UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}

			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$prod = JTableVRE::getInstance('menusproduct', 'VRETable');

		// try to save arguments
		if (!$prod->save($args))
		{
			// get string error
			$error = $prod->getError(null, true);

			if ($ajax)
			{
				// abort with the occurred error
				UIErrorFactory::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error));
			}

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managemenusproduct';

			if ($prod->id)
			{
				$url .= '&cid[]=' . $prod->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// get option table
		$option = JTableVRE::getInstance('productoption', 'VRETable');

		$option_id 	  = $input->get('option_id', array(), 'int');
		$option_name  = $input->get('option_name', array(), 'string');
		$option_price = $input->get('option_inc_price', array(), 'float');
		
		$remove_options = $input->get('delete_option', array(), 'int');
		
		for ($j = 0; $j < count($option_id); $j++)
		{
			$opt = array(
				'id'         => max(array(0, $option_id[$j])),
				'name'       => $option_name[$j],
				'inc_price'  => (float) $option_price[$j],
				'id_product' => $prod->id,
				'ordering'   => $j + 1,
			);
			 
			$option->save($opt);
			$option->reset();
		}

		if (count($remove_options))
		{
			$option->delete($remove_options);
		}

		if ($ajax)
		{
			// end process by returning the saved product
			echo json_encode($prod->getProperties());
			exit;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=menusproduct.edit&cid[]=' . $prod->id);

		return true;
	}

	/**
	 * AJAX end-point used to save the record data set in the request.
	 *
	 * @return 	void
	 */
	 public function saveajax()
	 {
	 	$this->save(true);
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
		JTableVRE::getInstance('menusproduct', 'VRETable')->delete($cid);

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
		JTableVRE::getInstance('menusproduct', 'VRETable')->publish($cid, $state);

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
		$this->setRedirect('index.php?option=com_vikrestaurants&view=menusproducts');
	}
}
