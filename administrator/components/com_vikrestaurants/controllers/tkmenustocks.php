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
 * VikRestaurants take-away menu stocks controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkmenustocks extends VREControllerAdmin
{
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

		// check user permissions
		if (!$user->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get tables
		$entry  = JTableVRE::getInstance('tkentry', 'VRETable');
		$option = JTableVRE::getInstance('tkentryoption', 'VRETable');

		$entry_id     = $input->get('product_id', array(), 'uint');
		$entry_stock  = $input->get('product_items_in_stock', array(), 'uint');
		$entry_notify = $input->get('product_notify_below', array(), 'uint');

		$option_id      = $input->get('option_id', array(), 'array');
		$option_enabled = $input->get('option_stock_enabled', array(), 'array');
		$option_stock   = $input->get('option_items_in_stock', array(), 'array');
		$option_notify  = $input->get('option_notify_below', array(), 'array');

		for ($i = 0; $i < count($entry_id); $i++)
		{
			$data = array(
				'id'             => $entry_id[$i],
				'items_in_stock' => $entry_stock[$i],
				'notify_below'   => $entry_notify[$i],
			);

			$entry->save($data);

			// make sure the options have been assigned to the entry
			if (isset($option_id[$entry->id]))
			{
				// save variations
				for ($j = 0; $j < count($option_id[$entry->id]); $j++)
				{
					$data = array(
						'id'             => (int) $option_id[$entry->id][$j],
						'stock_enabled'  => (int) $option_enabled[$entry->id][$j],
						'items_in_stock' => (int) $option_stock[$entry->id][$j],
						'notify_below'   => (int) $option_notify[$entry->id][$j],
					);

					$option->save($data);
				}
			}

			// NOTE: do not reset tables in order to avoid updating
			// records with default values, because "reset" method 
			// just configures the table with the default values of
			// the columns in the database.
		}
		
		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&view=tkmenustocks&id_menu=' . $input->getUint('id_menu'));

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
