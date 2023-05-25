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
 * Event used to perform a test connection between
 * the caller and this end-point.
 *
 * @since 1.7
 */
class ConnectionPing extends EventAPIs
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
		// connection ping done correctly
		$response->setStatus(1);

		/**
		 * Include some details about the program, 
		 * such as the version and the platform id.
		 *
		 * @since 1.8.4
		 */	

		// prepare response
		$obj = new stdClass;
		$obj->status   = 1;
		$obj->version  = VIKRESTAURANTS_SOFTWARE_VERSION;
		$obj->platform = VersionListener::getPlatform();

		/**
		 * Let the application framework safely output the response.
		 *
		 * @since 1.8.4
		 */
		return $obj;
	}

	/**
	 * @override
	 * Returns true if the plugin is always authorised, otherwise false.
	 * When this value is false, the system will need to authorise the plugin 
	 * through the ACL of the user.
	 *
	 * @return 	boolean  Always false. To allow always this plugin,
	 *					 override this method from the child class.
	 */
	public function alwaysAllowed()
	{
		// the plugin is always allowed
		return true;
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
		return JLayoutHelper::render('apis.plugins.connection_ping', array('plugin' => $this));
	}
}
