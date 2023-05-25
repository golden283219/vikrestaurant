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
 * VikRestaurants menu controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerMenu extends VREControllerAdmin
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
		$app->setUserState('vre.menu.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managemenu');

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
		$app->setUserState('vre.menu.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managemenu&cid[]=' . $cid[0]);

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
			$this->setRedirect('index.php?option=com_vikrestaurants&view=menus');
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
			$this->setRedirect('index.php?option=com_vikrestaurants&task=menu.add');
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
		$args['id'] 			= $input->get('id', 0, 'int');
		$args['name'] 			= $input->get('name', '', 'string');
		$args['alias'] 			= $input->get('alias', '', 'string');
		$args['description'] 	= $input->get('description', '', 'raw');
		$args['cost'] 			= $input->get('cost', 0, 'float');
		$args['image'] 			= $input->get('image', '', 'string');
		$args['special_day'] 	= $input->get('special_day', 0, 'int');
		$args['published'] 		= $input->get('published', 0, 'int');
		$args['choosable'] 		= $input->get('choosable', 0, 'int');
		$args['working_shifts'] = $input->get('working_shifts', array(), 'array');
		$args['days_filter'] 	= $input->get('days_filter', array(), 'array');

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
		$menu = JTableVRE::getInstance('menu', 'VRETable');

		// try to save arguments
		if (!$menu->save($args))
		{
			// get string error
			$error = $menu->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managemenu';

			if ($menu->id)
			{
				$url .= '&cid[]=' . $menu->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// get section table
		$sectionTable = JTableVRE::getInstance('menusection', 'VRETable');
		$productTable = JTableVRE::getInstance('sectionproduct', 'VRETable');

		$sec_id 		= $input->get('sec_id', array(), 'int');
		$sec_app_id 	= $input->get('sec_app_id', array(), 'int');
		$sec_name 		= $input->get('sec_name', array(), 'string');
		$sec_desc 		= $input->get('sec_desc', array(), 'array');
		$sec_publ 		= $input->get('sec_publ', array(), 'int');
		$sec_highlight 	= $input->get('sec_highlight', array(), 'int');
		$sec_dishes 	= $input->get('sec_dishes', array(), 'int');
		$sec_image 		= $input->get('sec_image', array(), 'string');
		
		$prod_id 		= $input->get('prod_id', array(), 'array');
		$prod_real_id 	= $input->get('real_prod_id', array(), 'array');
		$prod_charge 	= $input->get('charge', array(), 'array');
		
		$remove_sections = $input->get('remove_section', array(), 'int');
		$remove_products = $input->get('remove_product', array(), 'int');

		if (count($remove_sections))
		{
			$sectionTable->delete($remove_sections);
		}

		if (count($remove_products))
		{
			$productTable->delete($remove_products);
		}

		for ($i = 0; $i < count($sec_id); $i++)
		{
			// use default name if empty
			if (empty($sec_name[$i]))
			{
				$sec_name[$i] = JText::_('VRMANAGEMENU27');
			} 
			
			$section = array( 
				'id'          => max(array(0, $sec_id[$i])),
				'name'        => $sec_name[$i],
				'description' => $sec_desc[$i],
				'published'   => $sec_publ[$i],
				'highlight'   => $sec_highlight[$i],
				'orderdishes' => $sec_dishes[$i],
				'image'       => $sec_image[$i],
				'ordering'    => $i + 1,
				'id_menu'     => $menu->id,
			);
			 
			$sectionTable->save($section);
			$sectionTable->reset();
			 
			$key = $sec_app_id[$i];
			 
			if (!empty($prod_id[$key]))
			{
				for ($j = 0; $j < count($prod_id[$key]); $j++)
				{
					$prod = array( 
						'id'         => max(array(0, (int) $prod_real_id[$key][$j])),
						'id_product' => (int) $prod_id[$key][$j],
						'id_section' => $sectionTable->id,
						'charge'     => (float) $prod_charge[$key][$j],
						'ordering'   => $j + 1,
					);
					
					$productTable->save($prod);
					$productTable->reset();
				}
			}
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=menu.edit&cid[]=' . $menu->id);

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
		JTableVRE::getInstance('menu', 'VRETable')->delete($cid);

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
		JTableVRE::getInstance('menu', 'VRETable')->publish($cid, $state);

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
		$this->setRedirect('index.php?option=com_vikrestaurants&view=menus');
	}
}
