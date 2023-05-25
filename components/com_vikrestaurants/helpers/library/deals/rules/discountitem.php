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
 * Class used to apply the "Discount Item" deal type.
 *
 * @since 	1.8
 */
class DealRuleDiscountItem extends DealRule
{
	/**
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		return 2;
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
		$deal_curr_quantity = 0;

		$applied = false;

		foreach ($cart->getItemsList() as $item)
		{
			$found = false;
			
			if ($item->getDealID() == $deal['id'])
			{
				$item->setDealQuantity(0);
				$item->setPrice($item->getOriginalPrice());
				$item->setDealID(-1);
			}
			
			for ($k = 0; $k < count($deal['products']) && !$found; $k++)
			{	
				$prod = $deal['products'][$k];

				$found = (
					$prod['id_product'] == $item->getItemID() &&
					($prod['id_option'] <= 0 || $prod['id_option'] == $item->getVariationID()) && 
					$prod['quantity'] <= $item->getQuantity()
				);
				
				if ($found)
				{
					// calculate number of deals to apply to the product
					$deal_quantity = intval($item->getQuantity() / $prod['quantity']);

					/**
					 * Make sure the number of applies doesn't exceed the maximum threshold.
					 *
					 * @since 1.8
					 */
					if ($deal['max_quantity'] != -1 && $deal_curr_quantity + $deal_quantity > $deal['max_quantity'])
					{
						// recalculate quantity to void exceeding the threshold
						$deal_quantity = $deal['max_quantity'] - $deal_curr_quantity;
					}

					// apply discount to item
					$item->setDealQuantity($deal_quantity);

					if ($deal['percentot'] == 1)
					{
						$item->setPrice($item->getPrice() - $item->getPrice() * $deal['amount'] / 100.0);
					}
					else
					{
						$item->setPrice($item->getPrice() - $deal['amount']);
					}

					$item->setDealID($deal['id']);
					
					// increase number of applies by the redeem quantity
					$deal_curr_quantity += $deal_quantity;

					$applied = true;
				}
				
			}

			if ($deal['max_quantity'] != -1 && $deal_curr_quantity >= $deal['max_quantity'])
			{
				// no more deals
				break;
			}
		}

		return $applied;
	}
}
