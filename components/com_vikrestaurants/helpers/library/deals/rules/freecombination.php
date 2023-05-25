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
 * Class used to apply the "Free Item with Combination" deal type.
 *
 * @since 	1.8
 */
class DealRuleFreeCombination extends DealRule
{
	/**
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		return 3;
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

		$items_list = $cart->getItemsList();

		foreach ($deal['products'] as $prod)
		{
			$occurrency = 0;

			foreach ($items_list as $item)
			{
				$opt_id = $item->getVariationID();
				
				if (empty($opt_id))
				{
					$opt_id = -1;
				}
				
				if( 
					//$item->getPrice() > 0 && @deprecated
					($item->getPrice() > 0 || $item->getDealID() != -1) && 
					$item->getItemID() == $prod['id_product'] && 
					($opt_id == $prod['id_option'] || $opt_id == -1 || $prod['id_option'] <= 0) && 
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
			}
		}
		
		if ($required_min_occurrency == -1)
		{
			$required_min_occurrency = $atleast_count;
		}

		$min_occurrency = min(array($required_min_occurrency, $atleast_count));
		
		$MIN_QUANTITY_TO_PUSH = $deal['min_quantity'];
		$min_occurrency = intval($min_occurrency / $MIN_QUANTITY_TO_PUSH);

		/**
		 * Try to sort the items by price ASC, so that cheaper products
		 * will be fetched and discounted first.
		 *
		 * @since 1.7.4
		 */
		usort($items_list, function($a, $b)
		{
			return $a->getOriginalPrice() - $b->getOriginalPrice();
		});
		
		$gift_count = 0;

		foreach ($items_list as $item)
		{
			$found = false;

			for ($k = 0; $k < count($deal['gifts']) && !$found; $k++)
			{
				$prod = $deal['gifts'][$k];

				$found = (
					$prod['id_product'] == $item->getItemID() &&
					($prod['id_option'] == -1 || $prod['id_option'] == $item->getVariationID()) &&
					$prod['quantity'] <= $item->getQuantity()
				);
				
				if ($found)
				{
					$units_to_add = $item->getQuantity();
					
					if ($units_to_add <= 0 || $units_to_add > $min_occurrency - $gift_count)
					{
						$units_to_add = max(array(1, $min_occurrency - $gift_count));
					}
					
					if ($item->getDealID() == $deal['id'])
					{
						if ($min_occurrency - ($gift_count + $units_to_add) >= 0
							&& ($deal['max_quantity'] == -1 || $gift_count + $units_to_add <= $deal['max_quantity']))
						{
							/**
							 * @todo 	Should we check the number of applies after entering within this statement?
							 * 			Because currently, if the number of units exceeds the limit, the system 
							 * 			executes the else statement, which unsets the current deal.
							 * 			
							 *			Some tests are required.
							 */
							$item->setDealQuantity($units_to_add);
						}
						else
						{
							$item->setPrice($item->getOriginalPrice());
							$item->setDealQuantity(0);
							$item->setDealID(-1);
							$item->setRemovable(true);
						}
					}
					else if ($min_occurrency - ($gift_count + $units_to_add) >= 0
						&& ($deal['max_quantity'] == -1 || $gift_count + $units_to_add <= $deal['max_quantity']))
					{
						$item->setDealQuantity($units_to_add);
						$item->setDealID($deal['id']);
						$item->setPrice(0.0);
					}

					$gift_count += $item->getDealQuantity();
				}
			}
		}

		if ($deal['auto_insert'])
		{
			for ($k = 0; $k < count($deal['gifts']) && ($min_occurrency - $gift_count > 0); $k++)
			{
				$gift = $deal['gifts'][$k];
				
				$units = intval(($min_occurrency - $gift_count) / $gift['quantity']);
			   
				$new_item = new TakeAwayItem(
					$gift['id_takeaway_menu'],
					$gift['id_product'], 
					$gift['id_option'], 
					$gift['product_name'], 
					$gift['option_name'], 
					floatval($gift['product_price']) + floatval($gift['option_price']), 
					$units, 
					$gift['ready'],
					0, // no taxes
					"" // notes
				);
					
				if ($deal['max_quantity'] == -1 || $gift_count + $units <= $deal['max_quantity'])
				{
					$new_item->setDealID($deal['id']);
					$new_item->setDealQuantity($units);
					$new_item->setPrice(0.0);
					$new_item->setRemovable(false);
					
					$gift_count += $units;
				
					$cart->addItem($new_item);
				}
			}
		}

		return (bool) $gift_count;
	}
}
