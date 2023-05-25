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
 * Deal rule encapsulation.
 *
 * @since 	1.8
 */
abstract class DealRule
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
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	abstract public function getID();

	/**
	 * Returns a deal readable name.
	 *
	 * @return 	string
	 */
	public function getName()
	{
		// fetch language key from ID
		$key = 'VRTKDEALTYPE' . $this->getID();
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
	 * Returns the description of the deal.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		// fetch language key from ID
		$key = 'VRTKDEALTYPEDESC' . $this->getID();
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
	 * Executes the rule before start checking for deals to apply.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 *
	 * @return 	void
	 */
	public function preflight(&$cart)
	{
		// do nothing here
	}

	/**
	 * Applies the deal to the cart instance, if needed.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 * @param 	array 		  $deal   The deal to apply.
	 *
	 * @return 	boolean 	  True if applied, false otherwise.
	 */
	abstract public function apply(&$cart, $deal);
}
