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
 * VikRestaurants take-away menu controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkmenu extends VREControllerAdmin
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
		$app->setUserState('vre.tkmenu.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkmenu');

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
		$app->setUserState('vre.tkmenu.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkmenu&cid[]=' . $cid[0]);

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
			$this->setRedirect('index.php?option=com_vikrestaurants&task=tkmenu.add');
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
		$args['title']        = $input->get('title', '', 'string');
		$args['alias']        = $input->get('alias', '', 'string');
		$args['description']  = $input->get('description', '', 'raw');
		$args['published']    = $input->get('published', 0, 'uint');
		$args['publish_up']   = $input->get('publish_up', '', 'string');
		$args['publish_down'] = $input->get('publish_down', '', 'string');
		$args['taxes_type']   = $input->get('taxes_type', 0, 'uint');
		$args['taxes_amount'] = $input->get('taxes_amount', 0.0, 'float');
		$args['id']           = $input->get('id', 0, 'uint');

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
		$menu = JTableVRE::getInstance('tkmenu', 'VRETable');

		// try to save arguments
		if (!$menu->save($args))
		{
			// get string error
			$error = $menu->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managetkmenu';

			if ($menu->id)
			{
				$url .= '&cid[]=' . $menu->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// get entries table
		$entry     = JTableVRE::getInstance('tkentry', 'VRETable');
		$option    = JTableVRE::getInstance('tkentryoption', 'VRETable');
		$attribute = JTableVRE::getInstance('tkentryattribute', 'VRETable');

		$entry_id          = $input->get('entry_id', array(), 'uint');
		$entry_tmp_id      = $input->get('entry_tmp_id', array(), 'uint');
		$entry_name        = $input->get('entry_name', array(), 'string');
		$entry_alias       = $input->get('entry_alias', array(), 'string');
		$entry_description = $input->get('entry_description', array(), 'raw');
		$entry_price       = $input->get('entry_price', array(), 'float');
		$entry_published   = $input->get('entry_published', array(), 'uint');
		$entry_ready       = $input->get('entry_ready', array(), 'uint');
		$entry_image       = $input->get('entry_image', array(), 'string');
		$entry_attributes  = $input->get('entry_attributes', array(), 'string');

		$option_id    = $input->get('option_id', array(), 'array');
		$option_name  = $input->get('option_name', array(), 'array');
		$option_price = $input->get('option_price', array(), 'array');

		for ($i = 0; $i < count($entry_id); $i++)
		{
			$tmp_id = $entry_tmp_id[$i];

			$data = array(
				'id'               => $entry_id[$i],
				'name'             => $entry_name[$i],
				'alias'            => $entry_alias[$i],
				'description'      => $entry_description[$i],
				'price'            => $entry_price[$i],
				'published'        => $entry_published[$i],
				'ready'            => $entry_ready[$i],
				'img_path'         => $entry_image[$i],
				'id_takeaway_menu' => $menu->id,
				'ordering'         => $i + 1,
			);

			// Auto wrap separated blocks in different paragraphs to be properly displayed in HTML pages.
			// Proceed only in case the description doesn't own HTML tags and contains at least an empty
			// line (2 or more contiguous EOL).
			if ($data['description'] == strip_tags($data['description']) && preg_match("/\R{2,}/", $data['description']))
			{
				// split the description
				$chunks = preg_split("/\R{2,}/", $data['description']);

				// auto-p the blocks
				$chunks = array_map(function($p)
				{
					return '<p>' . $p . '</p>';
				}, $chunks);

				// join HTML blocks with a single new line
				$data['description'] = implode("\n", $chunks);
			}

			$data['img_path'] = json_decode($data['img_path']);

			// save product
			if ($entry->save($data))
			{
				// save attributes
				$attribute->setRelation($entry->id, (array) json_decode($entry_attributes[$i]));

				// make sure the options have been assigned to the entry
				if (isset($option_id[$tmp_id]))
				{
					// save variations
					for ($j = 0; $j < count($option_id[$tmp_id]); $j++)
					{
						$opt = array(
							'id'                     => (int) $option_id[$tmp_id][$j],
							'name'                   => $option_name[$tmp_id][$j],
							'inc_price'              => (float) $option_price[$tmp_id][$j],
							'ordering'               => $j + 1,
							'id_takeaway_menu_entry' => $entry->id,
						);

						$option->save($opt);
						$option->reset();
					}
				}
			}

			$entry->reset();
		}

		// delete entries
		$delete_entries = $input->get('delete_entry', array(), 'uint');

		if ($delete_entries)
		{
			$entry->delete($delete_entries);
		}

		// delete options
		$delete_options = $input->get('delete_option', array(), 'uint');

		if ($delete_options)
		{
			$option->delete($delete_options);
		}
		
		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=tkmenu.edit&cid[]=' . $menu->id);

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
		JTableVRE::getInstance('tkmenu', 'VRETable')->delete($cid);

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
		JTableVRE::getInstance('tkmenu', 'VRETable')->publish($cid, $state);

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
		$this->setRedirect('index.php?option=com_vikrestaurants&view=tkmenus');
	}
}
