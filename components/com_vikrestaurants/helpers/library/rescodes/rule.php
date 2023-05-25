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
 * Reservation codes rule encapsulation.
 *
 * @since 	1.8
 */
abstract class ResCodesRule implements JsonSerializable
{
	/**
	 * A configuration array.
	 *
	 * @var JRegistry
	 */
	private $options;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	$options  A configuration registry.
	 */
	public function __construct($options = array())
	{
		// set-up configuration
		$this->options = new JRegistry($options);
	}

	/**
	 * Returns the reservation code rule identifier.
	 *
	 * @return 	string
	 */
	public function getID()
	{
		return strtolower(preg_replace("/^ResCodesRule/i", '', get_class($this)));
	}

	/**
	 * Returns a code readable name.
	 *
	 * @return 	string
	 */
	public function getName()
	{
		// fetch language key from ID
		$key = 'VRRESCODESRULE' . strtoupper($this->getID());
		// try to translate name
		$text = JText::_($key);

		if ($key !== $text)
		{
			// translation found
			return $text;
		}

		// return class name
		return get_class($this);
	}

	/**
	 * Returns the description of the reservation code.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		// fetch language key from ID
		$key = 'VRRESCODESRULEDESC' . strtoupper($this->getID());
		// try to translate description
		$text = JText::_($key);

		if ($key !== $text)
		{
			// translation found
			return $text;
		}

		// return empty description
		return '';
	}

	/**
	 * Checks whether the specified group is supported
	 * by the rule. Children classes can override this
	 * method to drop the support for a specific group.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return true;
	}

	/**
	 * Executes the rule.
	 *
	 * @param 	mixed  $record  The record to dispatch.
	 *
	 * @return 	void
	 */
	abstract public function execute($record);

	/**
	 * Creates a standard object, containing all the supported properties,
	 * to be used when this class is passed to "json_encode()".
	 *
	 * @return  object
	 *
	 * @see     JsonSerializable
	 */
	public function jsonSerialize()
	{
		// create empty object
		$json = new stdClass;
		// register rule ID
		$json->id = $this->getID();
		// register rule name
		$json->name = $this->getName();
		// register rule description
		$json->description = $this->getDescription();
		// register empty groups
		$json->groups = array();

		// test restaurant group
		if ($this->isSupported('restaurant'))
		{
			$json->groups[] = 'restaurant';
		}

		// test take-away group
		if ($this->isSupported('takeaway'))
		{
			$json->groups[] = 'takeaway';
		}

		// test food group
		if ($this->isSupported('food'))
		{
			$json->groups[] = 'food';
		}

		return $json;
	}
}
