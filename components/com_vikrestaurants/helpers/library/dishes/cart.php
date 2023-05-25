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

VRELoader::import('library.dishes.item');
VRELoader::import('library.dishes.record');

JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');

/**
 * Used to handle the dishes cart of the program.
 * This class cannot be instantiated manually as we can have only one instance per session.
 *
 * Usage:
 * $cart = VREDishesCart::getInstance();
 *
 * @since 1.8
 */
class VREDishesCart implements Serializable
{	
	/**
	 * The list containing the VREDishesItem objects.
	 *
	 * @var array
	 */
	private $cart = array();

	/**
	 * The reservation ID.
	 *
	 * @var integer
	 */
	private $id;

	/**
	 * A pool of cart instances for each visited reservation.
	 *
	 * @var array
	 */
	private static $instances = array();
	
	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id     The reservation ID.
	 * @param 	array 	 $items  The items list.
	 */
	protected function __construct($id, array $items = array())
	{
		// this method can be accessed only internally.
		$this->id = (int) $id;

		// set up cart items
		$this->setCart($items);
	}

	/**
	 * Class cloner.
	 */
	protected function __clone()
	{
		// this method is not accessible
	}

	/**
	 * Get the instance of the cart object.
	 * If the instance is not yet available, create a new one.
	 * 
	 * @param 	integer  $id  The reservation ID.
	 *
	 * @return 	self 	 The instance of the VREDishesCart.
	 */
	public static function getInstance($id)
	{
		if (!isset(static::$instances[$id]))
		{
			// get cart from session
			$session_cart = JFactory::getSession()->get('vre.dishes.cart.' . $id, null, 'vikrestaurants');

			$items = array();

			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_res_prod_assoc'))
				->where($dbo->qn('id_reservation') . ' = ' . (int) $id)
				->order($dbo->qn('id') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// iterate all records
				foreach ($dbo->loadColumn() as $record_id)
				{
					try
					{
						// init records and push it within the list
						$items[] = new VREDishesRecord($record_id);
					}
					catch (Exception $e)
					{
						// go ahead in case of errors
					}
				}
			}

			if (empty($session_cart))
			{
				// create new cart instance
				$cart = new static($id, $items);
			}
			else
			{
				// unserialize from session
				$cart = unserialize($session_cart);

				// prepend items at the beginning of the cart
				$cart->unshiftItem($items);
			}

			// cache cart instance
			static::$instances[$id] = $cart;
		}

		return static::$instances[$id];
	}

	/**
	 * Stores all the pending items within the database for
	 * being accessed by the kitchen too.
	 *
	 * @return 	self  This object to support chaining.
	 */
	public function transmit()
	{
		// get reservation-product table
		$table = JTableVRE::getInstance('resprod', 'VRETable');

		$total = 0;

		// iterate all items
		foreach ($this->cart as &$item)
		{
			// check if we have a volatile dish
			if ($item->getRecordID() == 0)
			{
				// prepare data to save
				$data = array(
					'id'                => 0,
					'id_reservation'    => $this->id,
					'id_product'        => $item->id,
					'id_product_option' => $item->id_option,
					'name'              => $item->getFullName(),
					'quantity'          => $item->getQuantity(),
					'price'             => $item->getTotalCost() / $item->getQuantity(),
					'notes'             => $item->getAdditionalNotes(),
				);

				// save data
				$table->save($data);

				if ($table->id)
				{
					// Register record ID. When storing the cart
					// this item won't be kept anymore within the
					// session as it owns an ID higher than 0.
					// Then, at the next request the item will be
					// loaded from the database and it will belong
					// to the VREDishesRecord class.
					$item->setRecordID($table->id);

					// increase total by the product price
					$total += $table->price;
				}
			}
		}

		if ($total)
		{
			// get reservation table
			$reservation = JTableVRE::getInstance('reservation', 'VRETable');

			// update bill too
			$reservation->updateBill($this->id, $total);
		}

		return $this;
	}

	/**
	 * Store this instance into the PHP session.
	 *
	 * @return 	self  This object to support chaining.
	 */
	public function store()
	{
		// get reservation-product table
		$table = JTableVRE::getInstance('resprod', 'VRETable');

		$total = 0;

		foreach ($this->cart as $item)
		{
			if ($item->isModified() && $item->getRecordID())
			{
				// get current price stored in database
				$old_price = $table->getPrice($item->getRecordID());

				// prepare data to save
				$data = array(
					'id'                => $item->getRecordID(),
					'name'              => $item->getFullName(),
					'quantity'          => $item->getQuantity(),
					'price'             => $item->getTotalCost(),
					'notes'             => $item->getAdditionalNotes(),
					'id_product_option' => $item->id_option,
				);

				// save data
				if ($table->save($data))
				{
					// update total
					$total += $table->price - $old_price;

					// unset modified state
					$item->modified(false);
				}
			}
		}

		if ($total)
		{
			// get reservation table
			$reservation = JTableVRE::getInstance('reservation', 'VRETable');

			// update bill too
			$reservation->updateBill($this->id, $total);
		}

		// save cart in session
		JFactory::getSession()->set('vre.dishes.cart.' . $this->id, serialize($this), 'vikrestaurants');

		return $this;
	}

	/**
	 * Set the items into the array.
	 *
	 * @param 	array 	$items  The items array.
	 * 							Each element must be an instance of VREDishesItem,
	 * 							otherwise it will be ignored.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setCart(array $items)
	{
		$this->emptyCart();

		$this->addItem($items);

		return $this;
	}
	
	/**
	 * Empty the items and the deals stored in the cart.
	 *
	 * @return 	self  This object to support chaining.
	 */
	public function emptyCart()
	{
		$this->cart = array();

		return $this;
	}
	
	/**
	 * Check if the cart doesn't contain elements.
	 *
	 * @return 	boolean  True if has no element, otherwise false.
	 */
	public function isEmpty()
	{
		if (count($this->cart) == 0)
		{
			return true;
		}
		
		foreach ($this->cart as $i)
		{
			if ($i->getQuantity() > 0)
			{
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Prepends a new item at the beginning of the cart.
	 * 
	 * @param 	mixed  $item  Either the item to prepend or an array.
	 *
	 * @return 	self   This object to support chaining. 
	 */
	public function unshiftItem($item)
	{	
		if (!is_array($item))
		{
			// always treat as array
			$item = array($item);
		}
		else
		{
			// reverse item to prepend them following
			// the current ordering
			$item = array_reverse($item);
		}

		foreach ($item as $i)
		{
			if ($i instanceof VREDishesItem)
			{
				// add item to cart
				array_unshift($this->cart, $i);
			}
		}

		return $this;
	}
	
	/**
	 * Adds a new item into the cart. 
	 * 
	 * @param 	mixed  $item  Either the item to push or an array.
	 *
	 * @return 	self   This object to support chaining. 
	 */
	public function addItem($item)
	{
		if (!is_array($item))
		{
			// always treat as array
			$item = array($item);
		}

		foreach ($item as $i)
		{
			if ($i instanceof VREDishesItem)
			{
				// add item to cart
				$this->cart[] = $i;
			}
		}

		return $this;
	}
	
	/**
	 * Gets the item at the specified position.
	 *
	 * @param 	integer 	   $index  The index of the item.
	 *
	 * @return 	VREDishesItem  The item found on success, otherwise null.
	 */
	public function getItemAt($index)
	{
		if (isset($this->cart[$index]))
		{
			return $this->cart[$index];
		}
		
		return null;
	}

	/**
	 * Removes the item at the specified index.
	 *
	 * @param 	integer  $index	 The index of the item to remove.
	 * @param 	integer  $units  The units of the item to remove.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	public function removeItemAt($index, $units = 1)
	{
		// check if the item exists
		if (($item = $this->getItemAt($index)) !== null && $units > 0)
		{
			// keep total before removing the units
			$total = $item->getTotalCost();

			// decrease the item quantity by the specified number of units
			$remaining = $item->remove($units);

			if ($remaining == 0)
			{
				// no more units left, permanently remove from cart
				unset($this->cart[$index]);

				if ($total && $item->getRecordID())
				{
					// get reservation table
					$reservation = JTableVRE::getInstance('reservation', 'VRETable');

					// update bill too
					$reservation->updateBill($this->id, $total * -1);
				}
			}

			return true;
		}
		
		return false;
	}
	
	/**
	 * Gets the index of the specified item.
	 *
	 * @param 	VREDishesItem  $item  The item to find.
	 *
	 * @return 	integer 	   The index found on success, otherwise -1.
	 */
	public function indexOf(VREDishesItem $item)
	{
		foreach ($this->cart as $k => $i)
		{
			if ($i->equalsTo($item))
			{
				return $k;
			}
		}

		return -1;
	}

	/**
	 * Removes the specified item found.
	 *
	 * @param 	VREDishesItem  $item   The item to remove
	 * @param 	integer 	   $units  The units of the item to remove.
	 *
	 * @return 	boolean 	   True on success, otherwise false.
	 */
	public function removeItem(VREDishesItem $item, $units = 1)
	{
		// check if the item exists
		if (($index = $this->indexOf($item)) != -1)
		{
			// remove item units
			return $this->removeItemAt($index, $units);
		}

		return false;
	}
	
	/**
	 * Returns the total count of items within the cart.
	 *
	 * @return 	integer  The size of the cart.
	 */
	public function getLength()
	{
		return count($this->cart);
	}
	
	/**
	 * Returns the list of all the items within cart.
	 *
	 * @return 	array 	The list of the items.
	 */
	public function getItemsList()
	{	
		return $this->cart;
	}

	/**
	 * Returns the base total cost of the cart by summing
	 * the base cost of each item.
	 *
	 * @return 	float 	The base total cost.
	 */
	public function getTotalCost()
	{
		$total = 0;

		foreach ($this->cart as $item)
		{
			$total += $item->getTotalCost();
		}
		
		return $total;
	}

	/**
	 * String representation of object.
	 *
	 * @return 	string  Returns the string representation of the object or NULL.
	 */
	public function serialize()
	{
		$vars = get_object_vars($this);

		// strip all the items stored in the database
		$vars['cart'] = array_values(array_filter($vars['cart'], function($item)
		{
			return $item->getRecordID() == 0;
		}));

		// re-balance cart keys
		$this->cart = array_values($this->cart);

		// serialize all object vars
		return serialize($vars);
	}

	/**
	 * Constructs the object.
	 *
	 * @param 	string 	$serialized  The string representation of the object.
	 *
	 * @return 	void
	 */
	public function unserialize($serialized)
	{
		// unserialized stringified properties
		$vars = unserialize($serialized);

		// construct the object
		foreach ($vars as $k => $v)
		{
			$this->{$k} = $v;
		}
	}
	
	/**
	 * Magic toString method to debug the cart contents.
	 *
	 * @return  string  The debug string of the cart.
	 */
	public function __toString()
	{
		return '<pre>' . print_r($this, true) . '</pre><br />Total Cost = ' . $this->getTotalCost() . '<br />';
	}
}
