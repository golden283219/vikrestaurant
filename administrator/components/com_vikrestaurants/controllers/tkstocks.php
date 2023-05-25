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
 * VikRestaurants take-away items stock overrides/refills controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkstocks extends VREControllerAdmin
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

		$args = array();
		$args['eid']      = $input->get('id_product', array(), 'uint');
		$args['oid']      = $input->get('id_option', array(), 'uint');
		$args['original'] = $input->get('original_stock', array(), 'uint');
		$args['override'] = $input->get('stock_override', array(), 'uint');
		$args['factor']   = $input->get('stock_factor', array(), 'int');

		$map = array();

		for ($i = 0; $i < count($args['eid']); $i++)
		{
			// exclude in case the override was not specified
			if ($args['override'][$i] > 0)
			{
				// use entry-option key to avoid duplicate
				// queries in case of chained variations
				$key = $args['eid'][$i] . '-' . $args['oid'][$i];

				// create/replace map record
				$map[$key] = array(
					'id_takeaway_entry'  => $args['eid'][$i],
					'id_takeaway_option' => $args['oid'][$i],
					'items_available'    => $args['override'][$i] * $args['factor'][$i],
					'items_in_stock'     => $args['original'][$i],
				);
			}
		}

		// get stocks table
		$stock = JTableVRE::getInstance('tkstock', 'VRETable');

		$prodTable = JTableVRE::getInstance('tkentry', 'VRETable');
		$optTable  = JTableVRE::getInstance('tkentryoption', 'VRETable');

		// iterate maps
		foreach ($map as $data)
		{
			// save stock data (do not care of errors)
			$stock->save($data);

			// reset to make sure NULL properties are considered 
			// after creating/updating a record with that values filled in
			$stock->reset();

			/**
			 * Reset notification flag for the product/variation.
			 *
			 * @since 1.8.4
			 */
			$notified = array();
			$notified['stock_notified'] = 0;

			if ($data['id_takeaway_option'] > 0)
			{
				// use variation ID as primary key
				$notified['id'] = $data['id_takeaway_option'];

				// set variation as not-notified
				$optTable->save($notified);
			}
			else
			{
				// use product ID as primary key
				$notified['id'] = $data['id_takeaway_entry'];

				// set product as not-notified
				$prodTable->save($notified);
			}
		}
		
		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&view=tkstocks');

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=tkreservations');
	}
}
