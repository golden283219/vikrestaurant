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

/**
 * Event used to generate a ICS string.
 * Acts as a subscription URL to keep external calendars up-to-date.
 *
 * @since 1.7
 */
class IcsSync extends EventAPIs
{
	/**
	 * The custom action that the event have to perform.
	 * This method should not contain any exit or die function, 
	 * otherwise the event won't be stopped properly.
	 *
	 * All the information to return, should be echoed instead.
	 *
	 * @param 	array 		  $args 	 The provided arguments for the event.
	 * @param 	ResponseAPIs  $response  The response object for admin.
	 *
	 * @return 	mixed         The response to output or the error message (ErrorAPIs).
	 */
	protected function doAction(array $args, ResponseAPIs &$response)
	{
		$response->setStatus(1);

		if (!$args)
		{
			$input = JFactory::getApplication()->input;
			
			// No payload found, recover arguments from request.
			// Get requested type (0: restaurant, 1: take-away).
			$args['type'] = $input->getUint('type', 0);
		}
		else
		{
			// make sure we have a valid type
			if (!isset($args['type']))
			{
				// use default one
				$args['type'] = 0;
			}
		}

		/**
		 * Use ICS export driver.
		 *
		 * @since 1.8
		 */
		VRELoader::import('library.order.export.factory');

		try
		{
			// get ICS export driver
			$driver = VREOrderExportFactory::getInstance('ics', $args['type'] == 0 ? 'restaurant' : 'takeaway', $args);
		}
		catch (Exception $e)
		{
			// driver not found, register response
			$response->setStatus(0)->setContent($e->getMessage());

			// rethrow exception
			throw $e;
		}

		// download calendar
		$driver->download();
	}

	/**
	 * @override
	 * Returns the title of the event.
	 *
	 * @return 	string 	The title of the event.
	 */
	public function getTitle()
	{
		return 'ICS Sync';
	}

	/**
	 * @override
	 * Returns the description of the plugin.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		/**
		 * Read the description HTML from a layout.
		 *
		 * @since 1.8
		 */
		return JLayoutHelper::render('apis.plugins.ics_sync', array('plugin' => $this));
	}
}
