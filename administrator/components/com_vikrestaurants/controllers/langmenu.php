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
 * VikRestaurants menus language controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerLangmenu extends VREControllerAdmin
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
		$app->setUserState('vre.langmenu.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$id_menu = $app->input->getUint('id_menu');

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managelangmenu&id_menu=' . $id_menu);

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
		$app->setUserState('vre.langmenu.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managelangmenu&cid[]=' . $cid[0]);

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
			// recover menu ID from request
			$id_menu = JFactory::getApplication()->input->getUint('id_menu');

			$url = 'index.php?option=com_vikrestaurants&task=langmenu.add';

			if ($id_menu)
			{
				$url .= '&id_menu=' . $id_menu;
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
		$args['name'] 		 = $input->get('name', '', 'string');
		$args['alias'] 		 = $input->get('alias', '', 'string');
		$args['description'] = $input->get('description', '', 'raw');
		$args['id_menu']     = $input->get('id_menu', 0, 'uint');
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
		$langmenu = JTableVRE::getInstance('langmenu', 'VRETable');

		// try to save arguments
		if (!$langmenu->save($args))
		{
			// get string error
			$error = $langmenu->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managelangmenu';

			if ($langmenu->id)
			{
				$url .= '&cid[]=' . $langmenu->id;
			}
			else
			{
				$url .= '&id_menu=' . $args['id_menu'];
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// save sections
		$sections_id      = $input->get('section_id', array(), 'uint');
		$sections_lang_id = $input->get('section_lang_id', array(), 'uint');
		$sections_name    = $input->get('section_name', array(), 'string');
		$sections_desc    = $input->get('section_description', array(), 'raw');

		$langsection = JTableVRE::getInstance('langmenusection', 'VRETable');

		for ($i = 0; $i < count($sections_id); $i++)
		{
			$option = array();
			$option['id']          = $sections_lang_id[$i];
			$option['id_section']  = $sections_id[$i];
			$option['name']        = $sections_name[$i];
			$option['description'] = $sections_desc[$i];
			$option['id_parent']   = $langmenu->id;
			$option['tag']         = $langmenu->tag;

			$langsection->save($option);
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=langmenu.edit&cid[]=' . $langmenu->id);

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
		JTableVRE::getInstance('langmenu', 'VRETable')->delete($cid);

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
		// recover menu ID from request
		$id_menu = JFactory::getApplication()->input->getUint('id_menu');

		$url = 'index.php?option=com_vikrestaurants&view=langmenus';

		if ($id_menu)
		{
			$url .= '&id_menu=' . $id_menu;
		}

		$this->setRedirect($url);
	}
}
