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
 * The APIs event (plugin) representation.
 * The classname of a plugin must follow the standard below:
 * e.g. File = plugin.php   		Class = Plugin
 * e.g. File = plugin_name.php   	Class = PluginName
 *
 * @see 	ResponseAPIs
 *
 * @since  	1.7
 */
abstract class EventAPIs
{
	/**
	 * The name of the event. Usually equal to the filename.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Internal configuration registry.
	 *
	 * @var JObject
	 * @since 1.8.4
	 */
	private $options;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$name 	  The name of the event.
	 * @param 	array   $options  A configuration array/object.
	 */
	public function __construct($name = '', $options = array())
	{
		$this->name = strlen($name) ? $name : uniqid();

		// create configuration registry
		$this->options = new JObject($options);
	}

	/**
	 * Returns the name of the event.
	 *
	 * @return 	string 	The name of the event.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the title of the event, a more readable representation of the plugin name.
	 *
	 * @return 	string 	The title of the event.
	 */
	public function getTitle()
	{
		return ucwords(str_replace('_', ' ', $this->name));
	}

	/**
	 * Returns the description of the plugin.
	 *
	 * @return 	string 	An empty string. To display a description,
	 *					override this method from the child class.
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * Returns true if the plugin is always authorised, otherwise false.
	 * When this value is false, the system will need to authorise the plugin 
	 * through the ACL of the user.
	 *
	 * @return 	boolean  Always false. To allow always this plugin,
	 *					 override this method from the child class.
	 */
	public function alwaysAllowed()
	{
		return false;
	}

	/**
	 * Event configuration getter.
	 *
	 * @param 	string  $key  The setting name.
	 * @param 	mixed   $def  The default value to use.
	 *
	 * @return 	mixed   The setting value or the default one.
	 *
	 * @since 	1.8.4
	 */
	public function get($key, $def = null)
	{
		return $this->options->get($key, $def);
	}

	/**
	 * Event configuration setter.
	 *
	 * @param 	string  $key  The setting name.
	 * @param 	mixed   $val  The setting value.
	 *
	 * @return 	self    This object to support chaining.
	 *
	 * @since 	1.8.4
	 */
	public function set($key, $val)
	{
		return $this->options->set($key, $val);
	}

	/**
	 * Returns the event configuration as array.
	 *
	 * @return 	array
	 *
	 * @since 	1.8.4
	 */
	public function getOptions()
	{
		return $this->options->getProperties();
	}

	/**
	 * Perform the action of the event.
	 *
	 * @param 	array 		  $args 	 The provided arguments for the event.
	 * @param 	ResponseAPIs  $response  The response object for admin.
	 *
	 * @return 	mixed         The response to output or the error message (ErrorAPIs).
	 *
	 * @uses 	doAction()
	 */
	public function run(array $args, ResponseAPIs &$response)
	{
		return $this->doAction($args, $response);
	}

	/**
	 * The custom action that the event have to perform.
	 * This method should not contain any exit or die function, 
	 * otherwise the event won't be stopped properly.
	 *
	 * All the information to return, should be echoed instead.
	 *
	 * @usedby 	EventAPIs::run()
	 *
	 * @param 	array 		  $args 	 The provided arguments for the event.
	 * @param 	ResponseAPIs  $response  The response object for admin.
	 *
	 * @return 	mixed         The response to output or the error message (ErrorAPIs).
	 */
	abstract protected function doAction(array $args, ResponseAPIs &$response);
}
