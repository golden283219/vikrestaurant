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
 * Class used to apply the "Discount with Total Cost" deal type.
 *
 * @since 	1.8
 */
class DealRuleDiscountTotal extends DealRule
{
	/**
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		return 6;
	}

	/**
	 * Executes the rule before start checking for deals to apply.
	 * Always remove this deal from the cart before re-checking it.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 *
	 * @return 	void
	 */
	public function preflight(&$cart)
	{
		// check if this deal is already applied
		while (($deal = $cart->deals()->indexOfType(6)) != -1)
		{
			// remove deal from cart
			$cart->deals()->removeAt($deal);
		}
	}

	/**
	 * Applies the deal to the cart instance, if needed.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 * @param 	array 		  $deal   The deal to apply.
	 *
	 * @return 	boolean 	  True if applied, false otherwise.
	 */
	public function apply(&$cart, $deal)
	{
		if (($total_cost = $cart->getTotalCost()) >= $deal['cart_tcost'])
		{
			$discount = new TakeAwayDiscount($deal['id'], $deal['amount'], $deal['percentot'], 1);
			$discount->setType($deal['type']);

			if (($index = $cart->deals()->indexOfType($discount)) !== -1)
			{
				$discount_2 = $cart->deals()->get($index);

				$off_1 = $discount->getAmount();

				if ($discount->getPercentOrTotal() == 1)
				{
					$off_1 = $total_cost * $off_1 / 100;
				}

				$off_2 = $discount_2->getAmount();

				if ($discount_2->getPercentOrTotal() == 1)
				{
					$off_2 = $total_cost * $off_2 / 100;
				}

				if ($off_1 > $off_2)
				{
					$cart->deals()->set($discount, $index);
				}
			}
			else
			{
				$cart->deals()->insert($discount);
			}

			return true;
		}

		return false;
	}
}
