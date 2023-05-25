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
 * Class used to apply the "Free Item with Total" deal type.
 *
 * @since 	1.8
 */
class DealRuleFreeTotal extends DealRule
{
	/**
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		return 4;
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
		$deal_count = 0;

		foreach ($cart->getItemsList() as $item_index => $item)
		{
			if ($item->getDealID() == $deal['id'] && $cart->getTotalCost() >= $deal['amount'] && $deal['max_quantity'] > $deal_count)
			{
				$deal_count += $item->getDealQuantity();
				
				$free_space = $deal['max_quantity'] - $deal_count;

				if ($item->getQuantity() - $item->getDealQuantity() <= $free_space)
				{
					$deal_count += ($item->getQuantity() - $item->getDealQuantity());
					
					$item->setDealQuantity($item->getQuantity());
				}
				else if ($free_space > 0)
				{
					$deal_count += min(array($item->getQuantity(), $free_space));
					
					$item->setDealQuantity($item->getDealQuantity() + min(array($item->getQuantity(), $free_space)));
				}
			}
			else
			{
				$found = $to_remove = false;
				
				if ($item->getDealID() == $deal['id'])
				{
					$item->setDealQuantity(0);
					$item->setPrice($item->getOriginalPrice());
					$item->setDealID(-1);
					$to_remove = true;
				}
		
				for ($k = 0; $k < count($deal['gifts']) && !$found; $k++)
				{
					$prod = $deal['gifts'][$k];

					$found = (
						$prod['id_product'] == $item->getItemID() &&
						($prod['id_option'] == -1 || $prod['id_option'] == $item->getVariationID()) && 
						$prod['quantity'] <= $item->getQuantity()
					);
					
					if ($found && $deal['max_quantity'] > $deal_count && $cart->getTotalCost() - $item->getPrice() * $prod['quantity'] >= $deal['amount'])
					{
						// apply discount to item
						$item->setDealID($deal['id']);
						$item->setDealQuantity($prod['quantity']);
						$item->setPrice(0.0);
						
						$deal_count += $item->getDealQuantity();
						
						$free_space = $deal['max_quantity'] - $deal_count;

						if ($item->getQuantity()-$item->getDealQuantity() <= $free_space)
						{
							$deal_count += $item->getQuantity() - $item->getDealQuantity();
							
							$item->setDealQuantity($item->getQuantity());
						}
						else if ($free_space > 0)
						{
							$deal_count += min(array($item->getQuantity(), $free_space));
							
							$item->setDealQuantity($item->getDealQuantity() + min(array($item->getQuantity(), $free_space)));
						}
					}
				}

				if ($to_remove && $item->getDealID() == -1)
				{
					$item->setQuantity(0);
				}
			}
		}
		
		if ($cart->getTotalCost() >= $deal['amount'] && $deal['auto_insert'] && $deal['max_quantity'] > $deal_count)
		{
			foreach ($deal['gifts'] as $gift)
			{
				$new_item = new TakeAwayItem(
					$gift['id_takeaway_menu'],
					$gift['id_product'], 
					$gift['id_option'], 
					$gift['product_name'], 
					$gift['option_name'], 
					floatval($gift['product_price'])+floatval($gift['option_price']), 
					$gift['quantity'], 
					$gift['ready'], 
					0, // no taxes
					"" // notes
				);
					
				if ($deal['max_quantity'] > $deal_count)
				{
					$new_item->setDealID($deal['id']);
					$new_item->setDealQuantity($gift['quantity']);
					$new_item->setPrice(0.0);
					$new_item->setRemovable(false);
					$deal_count += $gift['quantity'];
				
					$cart->addItem($new_item);
				}
			}
		}

		return (bool) $deal_count;
	}
}
