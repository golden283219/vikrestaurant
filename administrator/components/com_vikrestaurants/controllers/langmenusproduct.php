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
 * VikRestaurants menus product language controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerLangmenusproduct extends VREControllerAdmin
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
		$app->setUserState('vre.langmenusproduct.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$id_product = $app->input->getUint('id_product');

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managelangmenusproduct&id_product=' . $id_product);

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
		$app->setUserState('vre.langmenusproduct.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managelangmenusproduct&cid[]=' . $cid[0]);

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
			// recover product ID from request
			$id_product = JFactory::getApplication()->input->getUint('id_product');

			$url = 'index.php?option=com_vikrestaurants&task=langmenusproduct.add';

			if ($id_product)
			{
				$url .= '&id_product=' . $id_product;
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
		$args['description'] = $input->get('description', '', 'raw');
		$args['id_product']  = $input->get('id_product', 0, 'uint');
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
		$langprod = JTableVRE::getInstance('langmenusproduct', 'VRETable');

		// try to save arguments
		if (!$langprod->save($args))
		{
			// get string error
			$error = $langprod->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managelangmenusproduct';

			if ($langprod->id)
			{
				$url .= '&cid[]=' . $langprod->id;
			}
			else
			{
				$url .= '&id_product=' . $args['id_product'];
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// save options
		$options_id      = $input->get('option_id', array(), 'uint');
		$options_lang_id = $input->get('option_lang_id', array(), 'uint');
		$options_name    = $input->get('option_name', array(), 'string');

		$langoption = JTableVRE::getInstance('langproductoption', 'VRETable');

		for ($i = 0; $i < count($options_id); $i++)
		{
			$option = array();
			$option['id']        = $options_lang_id[$i];
			$option['id_option'] = $options_id[$i];
			$option['name']      = $options_name[$i];
			$option['id_parent'] = $langprod->id;
			$option['tag']       = $langprod->tag;

			$langoption->save($option);
			$langoption->reset();
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=langmenusproduct.edit&cid[]=' . $langprod->id);

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
		JTableVRE::getInstance('langmenusproduct', 'VRETable')->delete($cid);

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
		// recover product ID from request
		$id_product = JFactory::getApplication()->input->getUint('id_product');

		$url = 'index.php?option=com_vikrestaurants&view=langmenusproducts';

		if ($id_product)
		{
			$url .= '&id_product=' . $id_product;
		}

		$this->setRedirect($url);
	}
}
