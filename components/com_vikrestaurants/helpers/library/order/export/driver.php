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
 * Driver class used to handle a specific export function
 * for the take-away orders and the restaurant reservations.
 *
 * @since 1.8
 */
abstract class VREOrderExportDriver
{
	/**
	 * Group identifier ('restaurant' or 'takeaway').
	 * The property is private so that it cannot be
	 * modified at runtime by the children classes.
	 *
	 * @var string
	 */
	private $group;

	/**
	 * A registry of options.
	 *
	 * @var JObject
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param 	string  $group    The section to which the reservations
	 * 							  belong ('restaurant' or 'takeaway').
	 * @param 	mixed 	$options  Either an array or an object of options to be passed 
	 * 							  to the order instance.
	 */
	public function __construct($group, $options = array())
	{
		$this->group   = $group;
		$this->options = new JObject($options);
	}

	/**
	 * Returns the driver name/identifier.
	 *
	 * @return 	string
	 */
	public function getName()
	{
		// get current class name
		$class = get_class($this);

		// extract driver name from class
		if (preg_match("/^VREOrderExportDriver([a-z0-9_]+)$/i", $class, $match))
		{
			// return driver name (lowercase)
			return strtolower(end($match));
		}

		// the driver doesn't follow the standard notation, return full class name
		return $class;
	}

	/**
	 * Returns the driver title.
	 * By default, the title is a translatable string built
	 * in the following format: VRE_ORDER_EXPORT_DRIVER_[NAME].
	 *
	 * @return 	string
	 */
	public function getTitle()
	{
		// get driver name (UPPERCASE)
		$driver = strtoupper($this->getName());

		// build language key
		$key = 'VRE_ORDER_EXPORT_DRIVER_' . $driver;

		// try to translate the title
		$title = JText::_($key);

		// check if the description is equals to language key
		if ($title === $key)
		{
			// missing translation, return driver name
			return $driver;
		}

		// return translated title instead
		return $title;
	}

	/**
	 * Returns the driver description.
	 * By default, the description is a translatable string built
	 * in the following format: VRE_ORDER_EXPORT_DRIVER_[NAME]_DESC.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		// build language key
		$key = 'VRE_ORDER_EXPORT_DRIVER_' . strtoupper($this->getName()) . '_DESC';

		// try to translate the description
		$desc = JText::_($key);

		// check if the description is equals to language key
		if ($desc === $key)
		{
			// missing translation, return empty description
			return '';
		}

		// return translated description instead
		return $desc;
	}

	/**
	 * Checks whether the specified group matches the one set
	 * in the driver properties.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if matching, false otherwise.
	 */
	public function isGroup($group)
	{
		return !strcasecmp($this->group, $group);
	}

	/**
	 * Updates the options of the registry.
	 *
	 * @param 	mixed 	$options  Either an array or an object of options to be passed 
	 * 							  to the order instance.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setOptions($options = array())
	{
		$this->options->setProperties($options);

		return $this;
	}

	/**
	 * Updates or insert a value within the configuration.
	 *
	 * @param 	string 	$key  The option key.
	 * @param 	mixed 	$val  The option value.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setOption($key, $val)
	{
		$this->options->set($key, $val);

		return $this;
	}

	/**
	 * Returns the configuration options
	 *
	 * @return 	array
	 */
	public function getOptions()
	{
		// get all properties
		return $this->options->getProperties();
	}

	/**
	 * Returns a configuration option.
	 *
	 * @param 	string 	$key  The option key.
	 * @param 	mixed 	$def  The default value.
	 *
	 * @return 	mixed 	The option value if exists, the default value otherwise.
	 */
	public function getOption($key, $def = null)
	{
		return $this->options->get($key, $def);
	}

	/**
	 * Override this method to return a list of
	 * arguments required to driver.
	 *
	 * @return 	array
	 */
	public function getForm()
	{
		return array();
	}

	/**
	 * Returns the value of the parameters used the
	 * last time this driver was invoked.
	 *
	 * @return 	array
	 */
	public function getParams()
	{
		// get JSON drivers params
		$params = VREFactory::getConfig()->getArray('exportresparams');

		// get driver identifier
		$driver = $this->getName();	

		// check if the driver was ever used
		if (!isset($params[$driver]))
		{
			// driver never used
			return array();
		}

		// first of all, take driver params only
		$params = $params[$driver];

		// check if the driver was used for the specified group
		if (isset($params[$this->group]))
		{
			// return configuration
			return $params[$this->group];
		}

		// otherwise use first available configuration
		return (array) reset($params);
	}

	/**
	 * Saves the last used parameters of the driver
	 * within the configuration.
	 *
	 * @return 	void
	 */
	public function saveParams()
	{
		$config = VREFactory::getConfig();

		// get JSON drivers params
		$params = $config->getArray('exportresparams');

		if (!is_array($params))
		{
			// create from scratch
			$params = array();
		}

		// get driver identifier
		$driver = $this->getName();

		// check if the driver was ever used
		if (!isset($params[$driver]))
		{
			// create array for this driver
			$params[$driver] = array();
		}

		// create/update array for current group
		$params[$driver][$this->group] = array();

		// iterate the driver form to save only the internal parameters
		foreach ($this->getForm() as $k => $field)
		{
			// set driver param
			$params[$driver][$this->group][$k] = $this->getOption($k);
		}

		// update configuration
		$config->set('exportresparams', $params);
	}

	/**
	 * Megic method to return the driver name
	 * when the object is casted to string.
	 *
	 * @return 	string
	 */
	public function __toString()
	{
		return $this->getName();
	}

	/**
	 * Exports the reservations in the given format.
	 *
	 * @return 	string 	The resulting export string.
	 */
	abstract public function export();

	/**
	 * Downloads the reservations in a file compatible with the given format.
	 *
	 * @param 	string 	$filename 	The name of the file that will be downloaded.
	 *
	 * @return 	void
	 */
	abstract public function download($filename = null);
}
