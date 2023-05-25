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
 * VikRestaurants order code status controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerRescodeorder extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data     = array();
		$id_order = $app->input->getUint('id_order');
		$group    = $app->input->getUint('group');

		if ($id_order)
		{
			$data['id_order'] = $id_order;
		}

		if ($group)
		{
			$data['group'] = $group;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.rescodeorder.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managerescodeorder');

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
		$app->setUserState('vre.rescodeorder.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managerescodeorder&cid[]=' . $cid[0]);

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
			$input = JFactory::getApplication()->input;

			$id_order = $input->getUint('id_order');
			$group    = $input->getUint('group');

			$url = 'index.php?option=com_vikrestaurants&task=rescodeorder.add';

			if ($id_order)
			{
				$url .= '&id_order=' . $id_order;
			}

			if ($group)
			{
				$url .= '&group=' . $group;
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
		$args['group']      = $input->get('group', 1, 'uint');
		$args['id_order']   = $input->get('id_order', 0, 'uint');
		$args['id_rescode'] = $input->get('id_rescode', 0, 'uint');
		$args['notes'] 		= $input->get('notes', '', 'raw');
		$args['id'] 		= $input->get('id', 0, 'int');

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
		$rescodeorder = JTableVRE::getInstance('rescodeorder', 'VRETable');

		// try to save arguments
		if (!$rescodeorder->save($args))
		{
			// get string error
			$error = $rescodeorder->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managerescodeorder';

			if ($rescodeorder->id)
			{
				$url .= '&cid[]=' . $rescodeorder->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// update code from reservation too
		$code = array();
		$code['id']      = $args['id_order'];
		$code['rescode'] = $args['id_rescode'];

		// fetch table according to specified group
		$table = $args['group'] == 1 ? 'reservation' : 'tkreservation';

		// get reservation/order table and save
		JTableVRE::getInstance($table, 'VRETable')->save($code);

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=rescodeorder.edit&cid[]=' . $rescodeorder->id);

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
		JTableVRE::getInstance('rescodeorder', 'VRETable')->delete($cid);

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
		$input = JFactory::getApplication()->input;

		$id_order = $input->getUint('id_order');
		$group    = $input->getUint('group');

		$url = 'index.php?option=com_vikrestaurants&view=rescodesorder';

		if ($id_order)
		{
			$url .= '&id_order=' . $id_order;
		}

		if ($group)
		{
			$url .= '&group=' . $group;
		}

		$this->setRedirect($url);
	}
}
