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
 * VikRestaurants take-away menu entry language controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerLangtkentry extends VREControllerAdmin
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
		$app->setUserState('vre.langtkentry.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$id_entry = $app->input->getUint('id_entry');
		$id_menu  = $app->input->getUint('id_takeaway_menu');

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managelangtkproduct&id_entry=' . $id_entry . '&id_takeaway_menu=' . $id_menu);

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
		$app->setUserState('vre.langtkentry.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managelangtkproduct&cid[]=' . $cid[0]);

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
			// recover entry ID from request
			$id_entry = JFactory::getApplication()->input->getUint('id_entry');
			$id_menu  = JFactory::getApplication()->input->getUint('id_takeaway_menu');

			$url = 'index.php?option=com_vikrestaurants&task=langtkentry.add';

			if ($id_entry)
			{
				$url .= '&id_entry=' . $id_entry;
			}

			if ($id_menu)
			{
				$url .= '&id_takeaway_menu=' . $id_menu;
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
		$args['name']        = $input->get('name', '', 'string');
		$args['alias']       = $input->get('alias', '', 'string');
		$args['description'] = $input->get('description', '', 'raw');
		$args['id_entry']    = $input->get('id_entry', 0, 'uint');
		$args['id_parent']   = $input->get('id_takeaway_menu', 0, 'uint');
		$args['id'] 	     = $input->get('id', 0, 'uint');
		$args['tag']         = $input->get('tag', '', 'string');
		
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
		$langentry = JTableVRE::getInstance('langtkentry', 'VRETable');

		// try to save arguments
		if (!$langentry->save($args))
		{
			// get string error
			$error = $langentry->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managelangtkproduct';

			if ($langentry->id)
			{
				$url .= '&cid[]=' . $langentry->id;
			}
			else
			{
				$url .= '&id_entry=' . $args['id_entry'] . '&id_takeaway_menu' . $args['id_parent'];
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// save options
		$options_id      = $input->get('option_id', array(), 'uint');
		$options_lang_id = $input->get('option_lang_id', array(), 'uint');
		$options_name    = $input->get('option_name', array(), 'string');
		$options_alias   = $input->get('option_alias', array(), 'string');

		$langoption = JTableVRE::getInstance('langtkentryoption', 'VRETable');

		for ($i = 0; $i < count($options_id); $i++)
		{
			$option = array();
			$option['id']        = $options_lang_id[$i];
			$option['id_option'] = $options_id[$i];
			$option['name']      = $options_name[$i];
			$option['alias']     = $options_alias[$i];
			$option['id_parent'] = $langentry->id;
			$option['tag']       = $langentry->tag;

			$langoption->save($option);
			$langoption->reset();
		}

		// save toppings
		$groups_id      = $input->get('group_id', array(), 'uint');
		$groups_lang_id = $input->get('group_lang_id', array(), 'uint');
		$groups_name    = $input->get('group_name', array(), 'string');
		$groups_desc    = $input->get('group_description', array(), 'string');

		$langgroup = JTableVRE::getInstance('langtkentrygroup', 'VRETable');

		for ($i = 0; $i < count($groups_id); $i++)
		{
			$group = array();
			$group['id']          = $groups_lang_id[$i];
			$group['id_group']    = $groups_id[$i];
			$group['name']        = $groups_name[$i];
			$group['description'] = $groups_desc[$i];
			$group['id_parent']   = $langentry->id;
			$group['tag']         = $langentry->tag;

			$langgroup->save($group);
			$langgroup->reset();
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=langtkentry.edit&cid[]=' . $langentry->id);

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
		JTableVRE::getInstance('langtkentry', 'VRETable')->delete($cid);

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
		// recover entry ID from request
		$id_entry = JFactory::getApplication()->input->getUint('id_entry');
		$id_menu  = JFactory::getApplication()->input->getUint('id_takeaway_menu');

		$url = 'index.php?option=com_vikrestaurants&view=langtkproducts';

		if ($id_entry)
		{
			$url .= '&id_entry=' . $id_entry;
		}

		if ($id_menu)
		{
			$url .= '&id_takeaway_menu=' . $id_menu;
		}

		$this->setRedirect($url);
	}
}
