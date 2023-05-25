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
 * VikRestaurants take-away menu entry controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkentry extends VREControllerAdmin
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
		$id_menu = $app->input->getUint('id_takeaway_menu');

		if ($id_menu)
		{
			$data['id_takeaway_menu'] = $id_menu;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.tkentry.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkentry');

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
		$app->setUserState('vre.tkentry.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkentry&cid[]=' . $cid[0]);

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
			// recover menu from request
			$id_menu = JFactory::getApplication()->input->getUint('id_takeaway_menu');

			$url = 'index.php?option=com_vikrestaurants&task=tkentry.add';

			if ($id_menu)
			{
				$url .= '&id_takeaway_menu=' . $id_menu;
			}

			$this->setRedirect($url);
		}
	}

	/**
	 * Task used to save the record data as a copy of the current item.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	void
	 */
	public function savecopy()
	{
		$this->save(true);
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @param 	boolean  $copy  True to save the record as a copy.
	 *
	 * @return 	boolean
	 */
	public function save($copy = false)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['name']             = $input->get('name', '', 'string');
		$args['alias']            = $input->get('alias', '', 'string');
		$args['price']            = $input->get('price', 0.0, 'float');
		$args['img_path']         = $input->get('img_path', array(), 'string');
		$args['published']        = $input->get('published', 0, 'uint');
		$args['ready']            = $input->get('ready', 0, 'uint');
		$args['description']      = $input->get('description', '', 'raw');
		$args['id_takeaway_menu'] = $input->get('id_takeaway_menu', 0, 'uint');
		$args['id']               = $copy ? 0 : $input->get('id', 0, 'int');

		// inject stock data too, if enabled
		if ($is_stock = VikRestaurants::isTakeAwayStockEnabled())
		{
			$args['items_in_stock'] = $input->get('items_in_stock', 9999, 'uint');
			$args['notify_below']   = $input->get('notify_below', 5, 'uint');
		}

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
		$entry = JTableVRE::getInstance('tkentry', 'VRETable');

		// try to save arguments
		if (!$entry->save($args))
		{
			// get string error
			$error = $entry->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managetkentry';

			if ($entry->id)
			{
				$url .= '&cid[]=' . $entry->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// set entry attributes
		$attributes = $input->get('attributes', array(), 'uint');

		JTableVRE::getInstance('tkentryattribute', 'VRETable')->setRelation($entry->id, $attributes);

		// save variations
		$option = JTableVRE::getInstance('tkentryoption', 'VRETable');

		$option_id        = $input->get('option_id', array(), 'uint');
		$option_name      = $input->get('option_name', array(), 'string');
		$option_alias     = $input->get('option_alias', array(), 'string');
		$option_price     = $input->get('option_inc_price', array(), 'float');
		$option_published = $input->get('option_published', array(), 'uint');
		$option_stock     = $input->get('option_stock_enabled', array(), 'uint');
		$option_items     = $input->get('option_items_in_stock', array(), 'uint');
		$option_notify    = $input->get('option_notify_below', array(), 'uint');

		$delete_options = $input->get('delete_option', array(), 'int');

		for ($j = 0; $j < count($option_id); $j++)
		{
			$opt = array(
				'id'                     => $copy ? 0 : $option_id[$j],
				'name'                   => $option_name[$j],
				'alias'                  => $option_alias[$j],
				'inc_price'              => $option_price[$j],
				'published'              => $option_published[$j],
				'id_takeaway_menu_entry' => $entry->id,
				'ordering'               => $j + 1,
			);

			if ($is_stock)
			{
				// update stocks data too, if enabled
				$opt['stock_enabled']  = $option_stock[$j];
				$opt['items_in_stock'] = $option_items[$j];
				$opt['notify_below']   = $option_notify[$j];
			}
			 
			$option->save($opt);
			$option->reset();
		}

		if (!$copy && count($delete_options))
		{
			$option->delete($delete_options);
		}

		// save toppings
		$entryGroup = JTableVRE::getInstance('tkentrygroup', 'VRETable');

		$groups = array();
		$groups['id']           = $input->get('group_id', array(), 'uint');
		$groups['id_tmp']       = $input->get('group_tmp_id', array(), 'uint');
		$groups['id_variation'] = $input->get('group_id_variation', array(), 'uint');
		$groups['title']        = $input->get('group_title', array(), 'string');
		$groups['description']  = $input->get('group_description', array(), 'string');
		$groups['multiple']     = $input->get('group_multiple', array(), 'uint');
		$groups['min_toppings'] = $input->get('group_min_toppings', array(), 'uint');
		$groups['max_toppings'] = $input->get('group_max_toppings', array(), 'uint');
		$groups['use_quantity'] = $input->get('group_use_quantity', array(), 'uint');

		$groupTopping = JTableVRE::getInstance('tkgrouptopping', 'VRETable');

		$toppings = array();
		$toppings['id']         = $input->get('topping_id_assoc', array(), 'array');
		$toppings['id_topping'] = $input->get('topping_id', array(), 'array');
		$toppings['rate']       = $input->get('topping_rate', array(), 'array');

		for ($i = 0; $i < count($groups['id']); $i++)
		{
			$grp = array( 
				'id'           => $copy ? 0 : $groups['id'][$i],
				'id_entry'     => $entry->id,
				'id_variation' => $groups['id_variation'][$i],
				'title'        => $groups['title'][$i],
				'description'  => $groups['description'][$i],
				'multiple'     => $groups['multiple'][$i],
				'min_toppings' => $groups['min_toppings'][$i],
				'max_toppings' => $groups['max_toppings'][$i],
				'use_quantity' => $groups['use_quantity'][$i],
				'ordering'     => $i + 1,
			);

			// keep temporary assoc key
			$key = $groups['id_tmp'][$i];

			if ($entryGroup->save($grp))
			{
				// make sure the group was assigned to a topping
				if (isset($toppings['id'][$key]))
				{
					for ($j = 0; $j < count($toppings['id'][$key]); $j++)
					{
						$topping = array(
							'id'         => $copy ? 0 : intval($toppings['id'][$key][$j]),
							'id_topping' => intval($toppings['id_topping'][$key][$j]),
							'id_group'   => $entryGroup->id,
							'rate'       => floatval($toppings['rate'][$key][$j]),
							'ordering'   => $j + 1,
						);
						
						$groupTopping->save($topping);
						$groupTopping->reset();
					}
				}
			}
			else
			{
				// an error occurred while saving the group, get error (if any)
				$error = $entryGroup->getError($last = null, $string = true);

				if ($error)
				{
					$app->enqueueMessage($error, 'error');
				}
			}

			// delete toppings that are no more assigned to the group
			// but there were previously attached
			$entryGroup->deleteDetachedToppings(isset($toppings['id_topping'][$key]) ? $toppings['id_topping'][$key] : array());

			$entryGroup->reset();
		}

		if (!$copy)
		{
			// delete toppings groups
			$delete_groups = $input->get('delete_group', array(), 'uint');

			if (count($delete_groups))
			{
				$entryGroup->delete($delete_groups);
			}
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=tkentry.edit&cid[]=' . $entry->id);

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
		JTableVRE::getInstance('tkentry', 'VRETable')->delete($cid);

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
		JTableVRE::getInstance('tkentry', 'VRETable')->publish($cid, $state);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Changes the "ready" parameter of the selected records.
	 *
	 * @return 	boolean
	 */
	public function ready()
	{
		$app   = JFactory::getApplication();
		$cid   = $app->input->get('cid', array(), 'uint');
		$state = $app->input->get('state', 0, 'int');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// change state of selected records
		$shift = JTableVRE::getInstance('tkentry', 'VRETable');

		$shift->setColumnAlias('published', 'ready');
		$shift->publish($cid, $state);

		// back to records list
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
		$url = 'index.php?option=com_vikrestaurants&view=tkproducts';

		// recover menu from request
		$id_menu = JFactory::getApplication()->input->getUint('id_takeaway_menu');

		if ($id_menu)
		{
			$url .= '&id_takeaway_menu=' . $id_menu;
		}

		$this->setRedirect($url);
	}
}
