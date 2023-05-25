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
 * Used to handle the take-away item group topping into the cart.
 *
 * @since 	1.7
 */
class TakeAwayItemGroupTopping
{	
	/**
	 * The ID of the topping.
	 *
	 * @var integer
	 */
	private $id_topping;

	/**
	 * The Associative ID of the topping.
	 * This ID is needed to know the parent group.
	 *
	 * @var integer
	 */
	private $id_assoc;

	/**
	 * The name of the topping.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The cost of the topping.
	 *
	 * @var float
	 */
	private $rate;

	/**
	 * The number of units of the topping.
	 *
	 * @var integer
	 *
	 * @since 1.8.2
	 */
	private $units;
	
	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id_topping  The ID of the topping.
	 * @param 	integer  $id_assoc 	  The associative ID of the topping.
	 * @param 	string 	 $name 		  The name of the topping.
	 * @param 	float 	 $rate 		  The cost of the topping.
	 * @param 	integer  $units       An optional number of units.
	 */
	public function __construct($id_topping, $id_assoc, $name, $rate, $units = 1)
	{
		$this->id_topping = (int) $id_topping;
		$this->id_assoc   = (int) $id_assoc;
		$this->name       = $name;
		$this->rate       = (float) $rate;
		$this->units      = (int) $units;
	}
	
	/**
	 * Get the ID of the topping.
	 *
	 * @return 	integer  The topping ID.
	 */
	public function getToppingID()
	{
		return $this->id_topping;
	}

	/**
	 * Get the associative ID of the topping.
	 * This ID chain the topping to its parent group.
	 *
	 * @return 	integer  The topping assoc ID.
	 */
	public function getAssocID()
	{
		return $this->id_assoc;
	}
	
	/**
	 * Get the name of the topping.
	 *
	 * @return 	string 	The topping name.
	 */
	public function getName()
	{
		/**
		 * Try to translate the topping.
		 *
		 * @since 1.8.2
		 */
		$translator = VREFactory::getTranslator();

		// translate topping
		$tx = $translator->translate('tktopping', $this->getToppingID());

		if ($tx)
		{
			// return the translation found
			return $tx->name;
		}

		// use default name
		return $this->name;
	}
	
	/**
	 * Get the cost of the topping.
	 *
	 * @return 	float 	The topping cost.
	 */
	public function getRate()
	{
		/**
		 * Multiply the cost of the topping by the
		 * number of selected units.
		 *
		 * @since 1.8.2
		 */
		return $this->rate * $this->getUnits();
	}

	/**
	 * Returned the number of picked units.
	 *
	 * @return 	integer  The topping units.
	 *
	 * @since 	1.8.2
	 */
	public function getUnits()
	{
		return $this->units;
	}

	/**
	 * Sets the number of picked units.
	 *
	 * @param 	integer  $units  The units to set.
	 *
	 * @return 	self 	 This object to support chaining.
	 *
	 * @since 	1.8.2
	 */
	public function setUnits($units)
	{
		$this->units = max(array(1, (int) $units));

		return $this;
	}

	/**
	 * Increases the number of picked units.
	 *
	 * @param 	integer  $units  The units to increase (1 by default).
	 *
	 * @return 	self 	 This object to support chaining.
	 *
	 * @since 	1.8.2
	 */
	public function addUnits($units = 1)
	{
		return $this->setUnits($this->units + (int) $units);
	}

	/**
	 * Decreases the number of picked units.
	 *
	 * @param 	integer  $units  The units to decrease (1 by default).
	 *
	 * @return 	self 	 This object to support chaining.
	 *
	 * @since 	1.8.2
	 */
	public function removeUnits($units = 1)
	{
		return $this->setUnits($this->units - (int) $units);
	}
	
	/**
	 * Check if this object is equal to the specified topping.
	 * Two toppings are equal if they have the same ID.
	 *
	 * @param 	TakeAwayItemGroupTopping  $topping 	The topping to check.
	 *
	 * @return 	boolean 	True if the 2 objects are equal, otherwise false.
	 *
	 * @uses 	getToppingID()
	 */
	public function equalsTo(TakeAwayItemGroupTopping $topping)
	{
		return $this->getToppingID() == $topping->getToppingID();
	} 
	
	/**
	 * Magic toString method to debug the topping contents.
	 *
	 * @return  string  The debug string of this object.
	 *
	 * @since   1.7
	 */
	public function __toString()
	{
		return '<pre>' . print_r($this, true) . '</pre>';
	}
}
