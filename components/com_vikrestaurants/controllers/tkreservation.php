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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants take-away order controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkreservation extends VREControllerAdmin
{
	/**
	 * Adds the selected product within the cart.
	 *
	 * @return 	void
	 */
	public function addtocartajax()
	{
		$dbo    = JFactory::getDbo();
		$input  = JFactory::getApplication()->input;
		$config = VREFactory::getConfig();
		
		$id_entry  = $input->getUint('id_entry', 0);
		$id_option = $input->getInt('id_option', 0);
		$index 	   = $input->getInt('item_index', -1);
		
		// these parameters are provided by the tkadditem overlay
		$quantity = $input->getUint('quantity', 1);
		$notes    = $input->getString('notes');
		$toppings = $input->get('topping', array(), 'array');
		$units    = $input->get('topping_units', array(), 'array');
		
		// validate quantity
		if ($quantity <= 0)
		{
			$quantity = 1;
		}
		
		// get product details
		$entry = $this->getProduct($id_entry, $id_option);
		
		if (!$entry)
		{
			// the product does not exist
			UIErrorFactory::raiseError(404, JText::_('VRTKCARTROWNOTFOUND'));
		}
		
		// get cart instance
		VikRestaurants::loadCartLibrary();
		
		$cart = TakeAwayCart::getInstance();

		$cart->setMaxSize($config->getInt('tkmaxitems'));
		
		if ($index < 0)
		{
			// create take-away cart item
			$item = new TakeAwayItem(
				$entry->id_takeaway_menu,
				$entry->id, 
				$entry->option ? $entry->option->id : 0, 
				$entry->name, 
				$entry->option ? $entry->option->name : '',
				$entry->totalPrice,
				$quantity, 
				$entry->ready,
				$entry->taxes_amount,
				$notes
			);            
		}
		else
		{
			// update existign record
			$item = $cart->getItemAt($index);

			if ($item === null)
			{
				// the record does not exist
				UIErrorFactory::raiseError(404, JText::_('VRTKCARTROWNOTFOUND'));
			}
			
			$item->setQuantity($quantity);
			$item->setAdditionalNotes($notes);
		}
		
		// refresh toppings
		$item->emptyGroups();
		
		// validate toppings against groups
		foreach ($entry->toppings as $group)
		{	
			// create take-away cart item group
			$item_group = new TakeAwayItemGroup($group->id, $group->title, $group->multiple, $group->use_quantity);
			
			if (!isset($toppings[$group->id]))
			{
				$toppings[$group->id] = array();
			}
			
			$to_remove = array();

			$toppingsCount = 0;

			// validate selected toppings
			for ($i = 0; $i < count($toppings[$group->id]); $i++)
			{
				$found = false;

				for ($j = 0; $j < count($group->list) && !$found; $j++)
				{
					$id_topping = $toppings[$group->id][$i];

					if ($id_topping == $group->list[$j]->assoc_id)
					{
						$found = true;

						$toppingUnits = 1;

						/**
						 * Count the selected toppings by considering the total number
						 * of picked units, always if the group supports them.
						 *
						 * @since 1.8.2
						 */
						if ($item_group->useQuantity() && !empty($units[$group->id][$id_topping]))
						{
							// use the specified units
							$toppingUnits = $units[$group->id][$id_topping];
						}

						// increase toppings counter
						$toppingsCount += $toppingUnits;
					}
				}

				if (!$found)
				{
					$to_remove[] = $i;
				}
			}
			
			// remove wrong toppings
			foreach ($to_remove as $i)
			{
				unset($toppings[$group->id][$i]);
			}

			// remove repeated toppings
			$toppings[$group->id] = array_values(array_unique($toppings[$group->id]));
			
			// check selected quantity toppings
			if ($group->min_toppings > $toppingsCount || $toppingsCount > $group->max_toppings)
			{
				// invalid quantity
				UIErrorFactory::raiseError(400, JText::_('VRTKADDITEMERR1'));
			}
			
			// get toppings objects
			for ($i = 0; $i < count($toppings[$group->id]); $i++)
			{
				$found = false;

				for ($j = 0; $j < count($group->list) && !$found; $j++)
				{
					if ($toppings[$group->id][$i] == $group->list[$j]->assoc_id)
					{		
						// create take-away cart item group
						$item_group_topping = new TakeAwayItemGroupTopping(
							$group->list[$j]->id,
							$group->list[$j]->assoc_id,
							$group->list[$j]->name,
							$group->list[$j]->rate
						);

						$id_topping = $item_group_topping->getAssocID();

						/**
						 * Check if the customer was allowed to specify the units.
						 *
						 * @since 1.8.2
						 */
						if ($item_group->useQuantity() && !empty($units[$group->id][$id_topping]))
						{
							// use the specified units
							$item_group_topping->setUnits($units[$group->id][$id_topping]);
						}

						$item_group->addTopping($item_group_topping);
						
						$found = true;
					}
				}
			}

			$item->addToppingsGroup($item_group);
		}
		
		if ($index < 0)
		{
			// search for a similar item already added into the cart
			$index = $cart->indexOf($item);

			if ($index !== -1)
			{
				// a similar item already exists, update it
				$item = $cart->getItemAt($index);
				
				$item->setQuantity($item->getQuantity() + $quantity);
				$item->setAdditionalNotes($notes);
			}
			else
			{
				// add the new item
				$index = $cart->addItem($item);

				if ($index === false)
				{
					// unable to add the item
					UIErrorFactory::raiseError(400, JText::sprintf('VRTKMAXSIZECARTERR', $cart->getMaxSize()));
				}
			}
		}

		// check max quantity for update or merge functions
		if ($cart->getPreparationItemsQuantity() > $cart->getMaxSize())
		{
			UIErrorFactory::raiseError(400, JText::sprintf('VRTKMAXSIZECARTERR', $cart->getMaxSize()));
		}

		$msg = null;

		// get item again after insert/update
		$stock = $cart->getItemAt($index);

		// check item remaining quantity in stock
		$in_stock = VikRestaurants::getTakeawayItemRemainingInStock($stock->getItemID(), $stock->getVariationID());

		// make sure the stock system is enabled before to proceed
		if ($in_stock != -1)
		{
			// get total number of the same items within the cart
			$item_quantity = $cart->getQuantityItems($stock->getItemID(), $stock->getVariationID());
			
			// make sure the total number of purchased items doesn't exceed the remaining stock
			if ($in_stock - $item_quantity < 0)
			{
				// remove exceeding items
				$removed_items = $item_quantity - $in_stock;
				$stock->remove($removed_items);

				$msg = new stdClass;

				if ($quantity == $removed_items)
				{
					// no more items in stock
					UIErrorFactory::raiseError(404, JText::sprintf('VRTKSTOCKNOITEMS', $item->getFullName()));
				}
				else
				{
					// only a few items were added
					$msg->text = JText::sprintf('VRTKSTOCKREMOVEDITEMS', $item->getFullName(), $removed_items);
					$msg->status = 2;
				}
			}
		}
		
		/**
		 * Reset cart to handle correctly deal_quantities.
		 *
		 * @since 1.7
		 */
		VikRestaurants::resetDealsInCart($cart);
		VikRestaurants::checkForDeals($cart);
		
		$cart->store();
		
		$response = new stdClass;
		$response->total      = $cart->getTotalCost();
		$response->discount   = $cart->getTotalDiscount();
		$response->finalTotal = $cart->getRealTotalCost();
		$response->items      = array();
		$response->message    = $msg;

		// prepare for JSON
		foreach ($cart->getItemsList() as $item_index => $item)
		{
			$std = new stdClass;
			$std->item_name      = $item->getItemName();
			$std->var_name       = $item->getVariationName();
			$std->price          = $item->getTotalCost();
			$std->original_price = $item->getTotalCostNoDiscount();
			$std->quantity       = $item->getQuantity();
			$std->index          = $item_index;
			$std->removable      = $item->canBeRemoved();

			$response->items[] = $std;
		}
		
		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX end-point used to remove an item from
	 * the user cart.
	 *
	 * @return 	void
	 */
	public function removefromcartajax()
	{
		$this->removefromcart(true);
	}

	/**
	 * Task used to remove an item from the user cart.
	 *
	 * @param 	boolean  $ajax  True if the request was made via AJAX.
	 *
	 * @return 	void
	 */
	public function removefromcart($ajax = false)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		$index = $input->getUint('index');
		
		// get cart instance
		VikRestaurants::loadCartLibrary();
		
		$cart = TakeAwayCart::getInstance();
		
		// get selected item
		$item = $cart->getItemAt($index);

		if ($item !== null)
		{
			$item->remove($item->getQuantity());
			
			/**
			 * Reset cart to handle correctly deal_quantities.
			 *
			 * @since 1.8.5
			 */
			VikRestaurants::resetDealsInCart($cart);
			VikRestaurants::checkForDeals($cart);

			$cart->store();
			
			if ($ajax)
			{
				// prepare AJAX response
				$response = new stdClass;
				$response->total      = $cart->getTotalCost();
				$response->discount   = $cart->getTotalDiscount();
				$response->finalTotal = $cart->getRealTotalCost();
				$response->items      = array();

				// prepare for JSON
				foreach ($cart->getItemsList() as $item_index => $item)
				{
					$std = new stdClass;
					$std->item_name      = $item->getItemName();
					$std->var_name       = $item->getVariationName();
					$std->price          = $item->getTotalCost();
					$std->original_price = $item->getTotalCostNoDiscount();
					$std->quantity       = $item->getQuantity();
					$std->index          = $item_index;
					$std->removable      = $item->canBeRemoved();

					$response->items[] = $std;
				}

				echo json_encode($response);
				exit;
			}
		}
		else
		{
			if ($ajax)
			{
				// the product does not exist
				UIErrorFactory::raiseError(404, JText::_('VRTKCARTROWNOTFOUND'));
			}
			else
			{
				$app->enqueueMessage(JText::_('VRTKCARTROWNOTFOUND'), 'error');
			}
		}
		
		$itemid = $input->get('Itemid', null, 'uint');
		
		// back to confirmation page
		$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=takeawayconfirm' . ($itemid ? '&Itemid=' . $itemid : ''), false));
	}

	/**
	 * Removes all the items that are currently stored
	 * within the user session.
	 *
	 * @return 	void
	 */
	public function emptycartajax()
	{
		VikRestaurants::loadCartLibrary();
		
		// remove all items stored within the cart	
		TakeAwayCart::getInstance()
			->emptyCart()
			->store();
		
		// response not needed
		exit;
	}

	/**
	 * Returns an object containing the details of the
	 * take-away product and the related option, if requested.
	 *
	 * @param 	integer  $id_entry   The product ID.
	 * @param 	integer  $id_option  The variation ID, if supported.
	 *
	 * @return 	mixed 	 An object in case the product exists, null otherwise.
	 */
	protected function getProduct($id_entry, $id_option = 0)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('e.*');
		$q->select($dbo->qn('o.id', 'oid'));
		$q->select($dbo->qn('o.name', 'oname'));
		$q->select($dbo->qn('o.inc_price', 'oprice'));
		$q->select($dbo->qn('m.taxes_type'));
		$q->select($dbo->qn('m.taxes_amount'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id_takeaway_menu_entry') . ' = ' . $dbo->qn('e.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('e.id_takeaway_menu') . ' = ' . $dbo->qn('m.id'));

		$q->where($dbo->qn('e.id') . ' = ' . (int) $id_entry);
		$q->where($dbo->qn('e.published') . ' = 1');
		$q->where($dbo->qn('m.published') . ' = 1');

		if ((int) $id_option > 0)
		{
			$q->where($dbo->qn('o.id') . ' = ' . (int) $id_option);
			$q->where($dbo->qn('o.published') . ' = 1');
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// product not found
			return null;
		}

		$item = $dbo->loadObject();

		// In case the request didn't specify the option for
		// an item that supports options, the first one will
		// be automatically taken.

		if ($item->oid)
		{
			// build option object
			$item->option = new stdClass;
			$item->option->id    = $item->oid;
			$item->option->name  = $item->oname;
			$item->option->price = $item->oprice;
		}
		else
		{
			$item->option = null;
		}

		// calculate total cost
		$item->totalPrice = $item->price + (float) $item->oprice;

		// apply product translation
		VikRestaurants::translateTakeawayProducts($item);

		if ($item->option)
		{
			// apply variation translation
			VikRestaurants::translateTakeawayProductOptions($item->option);
		}

		// fetch full name (after translation)
		$item->fullName = $item->name . ($item->option ? ' - ' . $item->option->name : '');

		if ($item->taxes_type == 0)
		{
			// use global taxes
			$item->taxes_amount = VREFactory::getConfig()->getFloat('tktaxesratio');
		}

		$item->toppings = array();

		// fetch toppings groups

		$q = $dbo->getQuery(true);

		$q->select('g.*');
		$q->select($dbo->qn('a.id', 'topping_group_assoc_id'));
		$q->select($dbo->qn('a.id_topping'));
		$q->select($dbo->qn('a.rate', 'topping_rate'));
		$q->select($dbo->qn('t.name', 'topping_name'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc', 'g'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc', 'a') . ' ON ' . $dbo->qn('a.id_group') . ' = ' . $dbo->qn('g.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_topping', 't') . ' ON ' . $dbo->qn('a.id_topping') . ' = ' . $dbo->qn('t.id'));

		$q->where($dbo->qn('g.id_entry') . ' = ' . $item->id);
		$q->where($dbo->qn('t.published') . ' = 1');

		if ($item->option)
		{
			$q->andWhere(array(
				$dbo->qn('g.id_variation') . ' <= 0',
				$dbo->qn('g.id_variation') . ' = ' . $item->option->id,
			), 'OR');
		}
		
		$q->order($dbo->qn('g.ordering') . ' ASC');
		$q->order($dbo->qn('a.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// no toppings available
			return $item;
		}
		
		foreach ($dbo->loadObjectList() as $group)
		{
			if (!isset($item->toppings[$group->id]))
			{
				$tmp = new stdClass;
				$tmp->id           = $group->id;
				$tmp->title        = $group->title;
				$tmp->multiple     = $group->multiple;
				$tmp->min_toppings = $group->min_toppings;
				$tmp->max_toppings = $group->max_toppings;
				$tmp->use_quantity = $group->use_quantity;
				$tmp->list         = array();

				$item->toppings[$group->id] = $tmp;
			}
			
			if (!empty($group->topping_group_assoc_id))
			{
				$topping = new stdClass;
				$topping->id       = $group->id_topping;
				$topping->assoc_id = $group->topping_group_assoc_id;
				$topping->name     = $group->topping_name;
				$topping->rate     = $group->topping_rate;

				$item->toppings[$group->id]->list[] = $topping;
			}
		}

		return $item;
	}
}
