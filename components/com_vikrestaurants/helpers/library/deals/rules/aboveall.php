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
 * Class used to apply the "Above All" deal type.
 *
 * @since 	1.8
 */
class DealRuleAboveAll extends DealRule
{
	/**
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		return 1;
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
		$required_min_occurrency = -1;
		$atleast_count = 0;

		// flag used to check whether there's at least an optional target food
		$has_at_least = false;

		foreach ($deal['products'] as $prod)
		{
			$occurrency = 0;

			foreach ($cart->getItemsList() as $item)
			{
				$opt_id = $item->getVariationID();
				
				/**
				 * Auto consider all the variations that belong to the selected parent item
				 * by using this statement: $prod['id_option'] <= 0.
				 * 
				 * @since 1.8.5
				 */

				if( 
					$item->getPrice() > 0 && 
					$item->getItemID() == $prod['id_product'] && 
					($opt_id == $prod['id_option'] || $opt_id <= 0 || $prod['id_option'] <= 0) && 
					$item->getQuantity() >= $prod['quantity']
				) {
					$occurrency += intval($item->getQuantity() / $prod['quantity']);
				}
			}
			
			if ($prod['required'] == 1)
			{
				if ($required_min_occurrency == -1 || $occurrency < $required_min_occurrency)
				{
					$required_min_occurrency = $occurrency;
				}
			}
			else
			{
				$atleast_count += $occurrency;

				// at least an optional food
				$has_at_least = true;
			}
		}

		/**
		 * Do not apply discount in case the deal expects at least 
		 * an optional food and the cart doesn't contain them.
		 *
		 * @since 1.8.5
		 */
		if ($atleast_count == 0 && $has_at_least)
		{
			return false;
		}
		
		if ($required_min_occurrency == -1)
		{
			$required_min_occurrency = $atleast_count;
		} 
		else if ($required_min_occurrency > 0 && $atleast_count == 0)
		{
			/**
			 * Condition needed to accept deals without AT_LEAST products.
			 *
			 * @since 1.7
			 */
			$atleast_count = $required_min_occurrency;
		}

		$min_occurrency = min(array($required_min_occurrency, $atleast_count));
		
		$MIN_QUANTITY_TO_PUSH = $deal['min_quantity'];
		$min_occurrency = intval($min_occurrency / $MIN_QUANTITY_TO_PUSH);
		
		if ($deal['max_quantity'] != -1 && $min_occurrency > $deal['max_quantity'])
		{
			$min_occurrency = $deal['max_quantity'];
		}
		
		$discount = new TakeAwayDiscount($deal['id'], $deal['amount'], $deal['percentot'], $min_occurrency);

		if ($min_occurrency > 0)
		{
			$index = $cart->deals()->indexOf($discount);

			if ($index != -1)
			{
				$discount->removeQuantity($cart->deals()->get($index)->getQuantity());
			}

			// add discount
			$cart->deals()->insert($discount);

			return true;
		}
		
		// remove discount if already applied
		$cart->deals()->remove($discount);
		
		return false;
	}
}
