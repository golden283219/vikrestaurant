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
 * This class represents the elements that can be drawn within the map.
 * 
 * @since 1.7.4
 */
class VREMapElement
{
	/**
	 * The element design configuration.
	 *
	 * @var object
	 */
	protected $options;

	/**
	 * The element tag attributes.
	 *
	 * @var object
	 */
	protected $attributes;

	/**
	 * The element style attributes.
	 *
	 * @var array
	 */
	protected $style = array();

	/**
	 * Class constructor.
	 *
	 * @param 	array|object  $options  The design data.
	 */
	public function __construct($options = array())
	{
		$this->options 		= (object) $options;
		$this->attributes 	= new stdClass;
	}

	/**
	 * Proxy used to access internal attributes.
	 *
	 * @param 	string 	$name 	The property name.
	 *
	 * @return 	mixed 	The property value.
	 */
	public function __get($name)
	{
		if ($name == 'style')
		{
			// stringify style attribute
			$style = '';

			foreach ($this->style as $k => $v)
			{
				$style .= $k . ':' . $v . ';';
			}

			// concat string to current style if specified
			if (!empty($this->attributes->style))
			{
				$style = rtrim($this->attributes->style, ';') . ';' . $style;
			}

			return $style;
		}
		else if (isset($this->attributes->{$name}))
		{
			return $this->attributes->{$name};
		}

		return null;
	}

	/**
	 * Proxy used to set internal attributes.
	 *
	 * @param 	string 	$name 	The property name.
	 * @param 	string 	$value 	The property value.
	 *
	 * @return 	void
	 */
	public function __set($name, $value)
	{
		// make name HTML compliant
		$name = preg_replace("/[^a-zA-Z]/", '', $name);

		$this->attributes->{$name} = $value;
	}

	/**
	 * Proxy used to check if the given attributes is set.
	 *
	 * @param 	string 	 $name 	The property name.
	 *
	 * @return 	boolean  True if set, otherwise false.
	 */
	public function __isset($name)
	{
		return isset($this->attributes->{$name});
	}

	/**
	 * Returns the value of the given design data.
	 *
	 * @param 	string 	$k    The data name.
	 * @param 	string  $def  The default value.
	 *
	 * @return 	string 	The design data value if exists, otherwise the default one.
	 */
	public function getData($k, $def = null)
	{
		if (isset($this->options->{$k}))
		{
			return $this->options->{$k};
		}

		return $def;
	}

	/**
	 * Adds a new style rule.
	 *
	 * @param 	string 	 $k 		 The rule name.
	 * @param 	string   $v 		 The rule value.
	 * @param 	boolean  $important  True to make it important.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function addStyle($k, $v, $important = false)
	{
		$this->style[$k] = rtrim($v, ';') . ($important ? ' !important' : '');

		return $this;
	}

	/**
	 * Returns the value of the given rule.
	 *
	 * @param 	string 	$k    The rule name.
	 * @param 	string  $def  The default value.
	 *
	 * @return 	string 	The rule value if exists, otherwise the default one.
	 */
	public function getStyle($k, $def = '')
	{
		if (isset($this->style[$k]))
		{
			return $this->style[$k];
		}

		return (string) $def;
	}

	/**
	 * Removes the specified rule, if exists.
	 *
	 * @param 	string 	$k    The rule name.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function removeStyle($k)
	{
		if (isset($this->style[$k]))
		{
			unset($this->style[$k]);
		}

		return $this;
	}
} 
