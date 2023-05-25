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
 * VikRestaurants update program controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerUpdateprogram extends VREControllerAdmin
{
	/**
	 * AJAX end-point used to validate the current version
	 * of VikRestaurants against the latest one stored on
	 * the manufacturer servers.
	 *
	 * @return 	void
	 */
	public function checkversion()
	{
		/**
		 * Get internal event dispatcher to automatically
		 * include the parameters array, which will be used
		 * to fetch the version of the program.
		 *
		 * @see   VREFactory
		 *
		 * @since 1.8
		 */
		$dispatcher = VREFactory::getEventDispatcher();

		// make request
		$result = $dispatcher->triggerOnce('onCheckVersion');

		if (!$result)
		{
			// use empty "failure" placeholder
			$result = new stdClass;
			$result->status = 0;
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * AJAX end-point used to perform the update to the
	 * latest version available.
	 *
	 * @return 	void
	 */
	public function launch()
	{
		$json = new stdClass;
		$json->status = false;
		
		// check user permissions
		if (JFactory::getUser()->authorise('core.admin', 'com_vikrestaurants'))
		{
			/**
			 * Get internal event dispatcher to automatically
			 * include the parameters array, which will be used
			 * to fetch the version of the program.
			 *
			 * @see   VREFactory
			 *
			 * @since 1.8
			 */
			$dispatcher = VREFactory::getEventDispatcher();

			try
			{
				// trigger update and search for a positive result
				$json->status = $dispatcher->is('onDoUpdate');

				if (!$json->status)
				{
					// The plugin never returns FALSE, because
					// in case of errors an exception is thrown.
					// For this reason, if we are here, probably
					// the plugin hasn't been enabled.
					$json->error = 'Updater plugin disabled.';
				}

			}
			catch (Exception $e)
			{
				// an error occurred while updating
				$json->status = false;
				$json->error = $e->getMessage();
			}
		}
		else
		{
			// not authorised to launch the update
			$json->error = JText::_('JERROR_ALERTNOAUTHOR');
		}

		echo json_encode($json);
		exit;
	}

	/**
	 * Redirects the users to the configuration page.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.1
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants');
	}
}
