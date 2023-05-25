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
 * Used to handle the take-away item group into the cart.
 * This class wraps a list of toppings.
 *
 * @since 	1.7
 */
class TakeAwayItemGroup
{	
	/**
	 * The ID of the group.
	 *
	 * @var integer
	 */
	private $id_group;
	
	/**
	 * The title of the group.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * If has multiple choises or only one.
	 *
	 * @var boolean
	 */
	private $multiple;

	/**
	 * Flag used to check whether the toppings can be
	 * picked more than once.
	 *
	 * @var boolean
	 *
	 * @since 1.8.2
	 */
	private $useQuantity;
	
	/**
	 * The list of topping chosen.
	 *
	 * @var array
	 */
	private $toppings = array();
	
	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id_group 	The group ID.
	 * @param 	string 	 $title 	The group title.
	 * @param 	boolean  $multiple 	True if group is multiple, otherwise is single.
	 * @param 	boolean  $quantity  True if the toppings can be picked more than once.
	 */
	public function __construct($id_group, $title, $multiple, $quantity = false)
	{
		$this->id_group = $id_group;
		$this->title 	= $title;
		$this->multiple = (bool) $multiple;
		$this->useQuantity = (bool) $quantity;
	}
	
	/**
	 * Get the ID of the group.
	 *
	 * @return 	integer  The group ID.
	 */
	public function getGroupID()
	{
		return $this->id_group;
	}
	
	/**
	 * Get the title of the group.
	 *
	 * @return 	string 	The group title.
	 */
	public function getTitle()
	{
		/**
		 * Try to translate the toppings group.
		 *
		 * @since 1.8.2
		 */
		$translator = VREFactory::getTranslator();

		// translate topping group
		$tx = $translator->translate('tkentrygroup', $this->getGroupID());

		if ($tx)
		{
			// return the translation found
			return $tx->title;
		}

		// use default title
		return $this->title;
	}
	
	/**
	 * Check if the group is multiple: allow the selection of multiple toppings.
	 *
	 * @return 	boolean  True if multiple, otherwise false.
	 */
	public function isMultiple()
	{
		return $this->multiple;
	}

	/**
	 * Check if the group is single: allow the selection of only one topping.
	 *
	 * @return 	boolean  True if single, otherwise false.
	 *
	 * @uses 	isMultiple()
	 *
	 * @since 	1.7 
	 */
	public function isSingle()
	{
		return !$this->isMultiple();
	}

	/**
	 * Checks whether the toppings can be picked multiple times.
	 *
	 * @return 	boolean
	 *
	 * @since 	1.8.2
	 */
	public function useQuantity()
	{
		return $this->useQuantity;
	}
	
	/**
	 * Get the total cost of the group by summing the cost of each topping in the list.
	 *
	 * @return 	float 	The group total cost.
	 */
	public function getTotalCost()
	{
		$tcost = 0;

		foreach ($this->toppings as $t)
		{
			$tcost += $t->getRate();
		}

		return $tcost;
	}

	/**
	 * Get the index of the specified topping.
	 *
	 * @param 	TakeAwayItemGroupTopping  $topping 	The topping to search for.
	 *
	 * @return 	integer 	The index of the topping on success, otherwise -1.
	 */
	public function indexOf(TakeAwayItemGroupTopping $topping)
	{
		foreach ($this->toppings as $k => $t)
		{
			if ($t->equalsTo($topping))
			{
				return $k;
			}
		}

		return -1;
	}

	/**
	 * Push the specified topping into the list.
	 * It is possible to push a topping only if it is not yet contained in the list.
	 *
	 * @param 	TakeAwayItemGroupTopping  $topping 	The topping to insert.
	 *
	 * @return 	boolean 	True on success, otherwise false.
	 *
	 * @uses 	indexOf()
	 */
	public function addTopping(TakeAwayItemGroupTopping $topping)
	{
		if ($this->indexOf($topping) === -1)
		{
			$this->toppings[] = $topping;

			return true;
		}

		return false;
	}

	/**
	 * Reset the list by removing all the toppings.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function emptyToppings()
	{
		$this->toppings = array();

		return $this;
	}
	
	/**
	 * Get the list containing all the toppings.
	 *
	 * @return 	array 	The list of toppings.
	 */
	public function getToppingsList()
	{
		return $this->toppings;
	}
	
	/**
	 * Check if this object is equal to the specified group.
	 * Two groups are equal if they have the same ID and the 
	 * toppings contained in both the lists are the same.
	 *
	 * @param 	TakeAwayItemGroup 	$group 	The group to check.
	 *
	 * @return 	boolean 	True if the 2 objects are equal, otherwise false.
	 *
	 * @uses 	getGroupID()
	 * @uses 	getToppingsList()
	 */
	public function equalsTo(TakeAwayItemGroup $group)
	{
		if ($this->getGroupID() != $group->getGroupID())
		{
			return false;
		}

		$l1 = $this->getToppingsList();
		$l2 = $group->getToppingsList();

		if (count($l1) != count($l2))
		{
			return false;
		}

		$ok = true;

		// repeat until the count is reached or there is a different topping.
		for ($i = 0; $i < count($l1) && $ok; $i++)
		{
			$inner_ok = false;
			// repeat until the topping is found.
			for ($j = 0; $j < count($l2) && !$inner_ok; $j++)
			{
				// if true, break the statement
				$inner_ok = $l1[$i]->equalsTo($l2[$j]);
			}

			// update the main flag with the last result.
			// if true, continue with the search, otherwise break the for.
			$ok = $inner_ok;
		}

		return $ok;
	}
	
	/**
	 * Magic toString method to debug the group contents.
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
