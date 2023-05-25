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

VRELoader::import('library.mail.template');

/**
 * Factory class used to instantiate the right
 * notification provider.
 *
 * @since 1.8
 */
abstract class VREMailFactory
{
	/**
	 * Returns an instance of the notification provider.
	 *
	 * @param 	string 	$group 	  The group in which search for the class.
	 * @param 	string 	$id 	  The id/alias of the provider to load.
	 * @param 	mixed 	$args...  Some additional arguments to use when
	 * 							  instantiating the provider.
	 *
	 * @return 	VREMailTemplate
	 */
	public static function getInstance($group, $id)
	{
		// try to load the file
		if (!VRELoader::import('library.mail.' . $group . '.' . $id))
		{
			// file not found
			throw new Exception(sprintf('Mail template [%s.%s] not found', $group, $id), 404);
		}

		// fetch class name
		$classname = 'VREMailTemplate' . ucfirst($group) . ucfirst($id);

		// make sure the class exists
		if (!class_exists($classname))
		{
			// class not found
			throw new Exception(sprintf('Mail template [%s] class not found', $classname), 404);
		}

		// fetch arguments to pass to the constructor
		// by excluding the first 2 arguments
		$args = func_get_args();
		$args = array_splice($args, 2);

		// create reflection of the provider
		$class = new ReflectionClass($classname);
		// instantiate provider class
		$obj = $class->newInstanceArgs($args);

		// make sure the class is a valid instance
		if (!$obj instanceof VREMailTemplate)
		{
			// not a valid instance
			throw new Exception(sprintf('Mail template [%s] class is not a valid instance', $classname), 400);
		}

		return $obj;
	}

	/**
	 * Helper method used to trigger a plugin event before sending the e-mail.
	 * Any other parameter specified after the target will be included as
	 * argument for the plugin event.
	 *
	 * @param 	string 	 $group 	The group to which the class belong (restaurant or takeaway).
	 * @param 	string 	 $id 		The ID of the mail handler (filename).
	 * @param 	string 	 $what 		Either "subject" or "content", depending on what it is needed to edit.
	 * @param 	string 	 &$target 	The content of the target to be edited.
	 *
	 * @return 	boolean  False in case the e-mail sending has been prevented, true otherwise.
	 */
	public static function letPluginsManipulateMail($group, $id, $what, &$target)
	{
		// get event dispatcher
		$dispatcher = VREFactory::getEventDispatcher();

		// fetch event name based on what we need to fetch, group and mail ID
		$event = 'onBeforeSendMail' . ucfirst($what) . ucfirst($group) . ucfirst($id);

		// get all arguments
		$args = func_get_args();
		// keep only the additional arguments
		$args = array_splice($args, 4);

		// merge target within arguments
		$args = array_merge(array(&$target), $args);

		try
		{
			/**
			 * Triggers an event to let the plugins be able to handle
			 * the subject of the e-mail and the HTML contents of the
			 * related template.
			 *
			 * The event name is built as:
			 * onBeforeSendMail[Subject|Content][Restaurant|Takeaway][Class]
			 *
			 * The event might specified additional arguments, such as the
			 * details of the reservation/order.
			 *
			 * @param 	string 	 &$target  Either the subject or the HTML content,
			 * 							   depending on the $what arguments that
			 * 							   was passed to this method.
			 *
			 * @return 	boolean  Return false to prevent e-mail sending.
			 *
			 * @since 	1.8
			 */
			$res = $dispatcher->trigger($event, $args);
		}
		catch (Exception $e)
		{
			// do not break the process because of a plugin error
			$res = array();
		}

		// check if at least a plugin returned FALSE to prevent e-mail sending 
		return !in_array(false, $res, true);
	}
}
