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
 * Utility class working with an abstract configuration.
 *
 * @method 	integer 	getInt()		getInt($name, $default = null)		Get a signed integer.
 * @method 	integer 	getUint()		getUint($name, $default = null)		Get an unsigned integer.
 * @method 	float 		getFloat()		getFloat($name, $default = null)	Get a floating-point number.
 * @method 	float 		getDouble()		getDouble($name, $default = null)	Get a floating-point number.
 * @method 	boolean 	getBool()		getBool($name, $default = null)		Get a boolean.
 * @method 	string 		getString()		getString($name, $default = null)	Get a string.
 * @method 	array 		getArray()		getArray($name, $default = null)	Decode a JSON string and get an array.
 * @method 	mixed 		getObject()		getObject($name, $default = null)	Decode a JSON string and get an object.
 * @method 	mixed 		getJson()		getJson($name, $default = null)		Decode a JSON string and get an object.
 *
 * @since  	1.7
 * @since 	1.8.2 Renamed from UIConfigWrapper
 */
abstract class VREConfigWrapper
{
	/**
	 * The map containing all the settings retrieved.
	 * @protected to be accessible from all children classes.
	 *
	 * @var array
	 */
	protected static $pool = array();

	/**
	 * Returns the value of the specified setting.
	 *
	 * @param   string  $key  		Name of the setting.
	 * @param   mixed  	$default  	Default value in case the setting is empty.
	 * @param   string  $filter		Filter to apply to the value (string by default).
	 *
	 * @return  mixed 	The filtered value of the setting.
	 */
	public function get($key, $default = null, $filter = 'string')
	{
		// if the setting is alread loaded
		if (!array_key_exists($key, self::$pool))
		{
			// otherwise read it from the apposite handler
			$value = $this->retrieve($key);

			// if the returned value is false
			if ($value === false)
			{
				// return the default specified value
				return $default;
			}

			// register the value into the pool
			self::$pool[$key] = $value;	
		}

		// always filter the value
		return $this->_clean(self::$pool[$key], $filter);
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string 	$name       The name of the function. The string next to "get" word will be used as filter.
	 *								For example, getInt will use a "int" filter.
	 * @param   array  	$arguments  Array containing arguments to retrieve the setting.
	 *								Contains name of the key and the default value.
	 *
	 * @return  mixed 	The filtered value of the setting.
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get') {

			$key 		= '';
			$default 	= null;
			$filter 	= substr($name, 3);

			if (isset($arguments[0])) {
				$key = $arguments[0];
			}

			if (isset($arguments[1])) {
				$default = $arguments[1];
			}

			return $this->get($key, $default, $filter);
		}

		throw new RuntimeException('Call to undefined method '.__CLASS__.'::'.$name.'()');
	}

	/**
	 * Custom filter implementation.
	 *
	 * @param   string   $value 	The value to clean.
	 * @param   string   $filter 	The type of the value.
	 *
	 * @return  mixed 	The filtered value.
	 */
	protected function _clean($value, $filter)
	{
		switch (strtolower($filter)) {
			case 'int': 
				$value = intval($value); 
				break;

			case 'uint':
				$value = abs(intval($value));
				break;

			case 'float':
			case 'double':
				$value = floatval($value);
				break;

			case 'bool':
				$value = (bool) $value;
				break;

			case 'array':
				$value = (is_array($value) ? $value : (is_string($value) && strlen($value) ? json_decode($value, true) : array()));
				break;

			case 'json':
			case 'object':
				$value = (is_object($value) ? $value : (is_string($value) && strlen($value) ? json_decode($value) : new stdClass));
				break;

			default:
				$value = (string) $value;
		}

		return $value;
	}

	/**
	 * Retrieve the value of the setting from the instance in which it is stored. 
	 *
	 * @param   string   $key 	The name of the setting.
	 *
	 * @return  mixed 	The value of the setting if exists, otherwise false.
	 */
	protected abstract function retrieve($key);

	/**
	 * Store the value of the specified setting.
	 *
	 * @param   string  $key 	The name of the setting.
	 * @param   mixed   $val 	The value of the setting.
	 *
	 * @return  self 	This object to support chaining.
	 */
	public function set($key, $val)
	{	
		// if the registration of the setting went fine
		if ($this->register($key, $val)) {
			// overwrite/push the value of the setting
			self::$pool[$key] = $val;
		}

		return $this;
	}

	/**
	 * Register the value of the setting into the instance in which should be stored.
	 *
	 * @param   string  $key 	The name of the setting.
	 * @param   mixed   $val 	The value of the setting.
	 *
	 * @return  bool 	True in case of success, otherwise false.
	 */
	protected abstract function register($key, $val);
}
