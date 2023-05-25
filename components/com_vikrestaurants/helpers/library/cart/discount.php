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
 * Used to handle the take-away discount into the cart.
 *
 * @since 	1.7
 */
class TakeAwayDiscount
{	
	/**
	 * The ID of the deal.
	 *
	 * @var integer
	 */
	private $id_deal;

	/**
	 * The amount of the discount.
	 *
	 * @var float
	 */
	private $amount;

	/**
	 * The amount type of the discount.
	 * There are 2 accepted values: 1 for PERCENTAGE, 2 FOR TOTAL.
	 * 
	 * @var integer
	 */
	private $percentot;

	/**
	 * The quantity of this type of deal.
	 *
	 * @var integer
	 */
	private $quantity;

	/**
	 * The type of the deal.
	 * Null in case the type does not exist or it is not relevant.
	 * 
	 * @var integer
	 */
	private $type = null;
	
	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id_deal 	 The ID of the deal.
	 * @param 	float 	 $amount 	 The amount of the deal.
	 * @param 	integer  $percentot  The amount type of the deal.
	 * @param 	integer  $quantity 	 The quantity of the deal.
	 */
	public function __construct($id_deal, $amount, $percentot, $quantity = 1, $type = null)
	{
		$this->id_deal   = $id_deal;
		$this->amount    = $amount;
		$this->percentot = $percentot;
		$this->quantity  = max(array(1, abs($quantity)));
		$this->type      = $type;
	}
	
	/**
	 * Get the ID of the deal.
	 *
	 * @return 	integer  The deal ID.
	 */
	public function getDealID()
	{
		return $this->id_deal;
	}

	/**
	 * Get the amount of the deal.
	 *
	 * @return 	float 	The deal amount.
	 */
	public function getAmount()
	{
		return $this->amount;
	}
	
	/**
	 * Get the amount type of the deal.
	 *
	 * @return 	integer  The deal amont type.
	 */
	public function getPercentOrTotal()
	{
		return $this->percentot;
	}

	/**
	 * Check if the amount type of the deal is percentage.
	 *
	 * @return 	boolean  True if percentage, otherwise false.
	 *
	 * @since 	1.7
	 */
	public function isPercent()
	{
		return $this->percentot == self::PERCENTAGE_AMOUNT_TYPE;
	}

	/**
	 * Check if the amount type of the deal is total.
	 *
	 * @return 	boolean  True if total, otherwise false.
	 *
	 * @since 	1.7
	 */
	public function isTotal()
	{
		return $this->percentot == self::TOTAL_AMOUNT_TYPE;
	}
	
	/**
	 * Get the quantity of the deal.
	 *
	 * @return 	integer  The deal quantity.
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	/**
	 * Set the quantity of the deal.
	 *
	 * @param 	integer  The quantity of the deal.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = max(array(0, $quantity));

		return $this;
	}
	
	/**
	 * Add the specified units to the existing quantity of the deal.
	 *
	 * @param 	units 	The units of the deal to add.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function addQuantity($units = 1)
	{
		$this->quantity += abs($units);

		return $this;
	}
	
	/**
	 * Remove the specified units from the existing quantity of the deal.
	 *
	 * @param 	units 	The units of the deal to remove.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function removeQuantity($units = 1)
	{
		$this->quantity -= abs($units);

		if ($this->quantity < 0)
		{
			$this->quantity = 0;
		}

		return $this;
	}

	/**
	 * Set the type of the deal.
	 *
	 * @param 	integer  The deal type. Specify null if not relevant.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get the type of the deal.
	 *
	 * @return 	integer  The deal type, null if not relevant.
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Check if this object is equal to the specified discount.
	 * Two deals are equal if they have the same ID.
	 *
	 * @param 	TakeAwayDiscount  $discount  The discount to check.
	 *
	 * @return 	boolean  		  True if the 2 objects are equal, otherwise false.
	 *
	 * @uses 	getDealID()
	 */
	public function equalsTo(TakeAwayDiscount $discount)
	{
		return $this->getDealID() == $discount->getDealID();
	} 

	/**
	 * Check if this object has same type of the specified discount.
	 *
	 * @param 	TakeAwayDiscount  $discount  The discount to check.
	 *
	 * @return 	boolean 		  True if the 2 objects have same type, otherwise false.
	 *
	 * @uses 	getType()
	 */
	public function sameType(TakeAwayDiscount $discount)
	{
		return $this->type !== null && $this->getType() == $discount->getType();
	}
	
	/**
	 * Magic toString method to debug the discount contents.
	 *
	 * @return  string  The debug string of this object.
	 *
	 * @since   1.7
	 */
	public function __toString()
	{
		return '<pre>' . print_r($this, true) . '</pre>';
	}

	/**
	 * PERCENTAGE amount type identifier.
	 *
	 * @var integer
	 *
	 * @since 1.7
	 */
	const PERCENTAGE_AMOUNT_TYPE = 1;

	/**
	 * TOTAL amount type identifier.
	 *
	 * @var integer
	 *
	 * @since 1.7
	 */
	const TOTAL_AMOUNT_TYPE = 2;
}
