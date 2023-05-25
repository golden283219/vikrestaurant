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
 * VikRestaurants take-away order product table.
 *
 * @since 1.8
 */
class VRETableTkresprod extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_res_prod_assoc', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_product';
		$this->_requiredFields[] = 'id_res';
	}

	/**
	 * Method to bind an associative array or object to the Table instance. This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($src, $ignore = array())
	{
		$src = (array) $src;

		$dbo    = JFactory::getDbo();
		$config = VREFactory::getConfig();

		$this->_defaultItem = null;

		// get default product
		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('p.id', 'p.name', 'p.price')))
			->select($dbo->qn('o.id', 'id_option'))
			->select($dbo->qn('o.name', 'option_name'))
			->select($dbo->qn('o.inc_price', 'option_price'))
			->select($dbo->qn(array('m.taxes_type', 'm.taxes_amount')))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'p'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id_takeaway_menu_entry') . ' = ' . $dbo->qn('p.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('p.id_takeaway_menu') . ' = ' . $dbo->qn('m.id'))
			->where($dbo->qn('p.id') . ' = ' . (int) @$src['id_product']);

		if (!empty($src['id_product_option']))
		{
			$q->where($dbo->qn('o.id') . ' = ' . (int) $src['id_product_option']);
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// register default item properties
			$this->_defaultItem = $dbo->loadObject();

			if ($this->_defaultItem->taxes_type == 0)
			{
				// use default taxes amount
				$this->_defaultItem->taxes_amount = $config->getFloat('tktaxesratio');
			}

			if (isset($src['quantity']))
			{
				// make sure the quantity is 1 or higher
				$src['quantity'] = max(array(1, $src['quantity']));
			}
			else
			{
				// use default quantity
				$src['quantity'] = 1;
			}

			if (!isset($src['price']))
			{
				// set default price if not specified
				$src['price'] = ($this->_defaultItem->price + (int) $this->_defaultItem->option_price);
			}

			// always multiply the single item price per quantity
			$src['price'] *= $src['quantity'];

			// calculate taxes if not specified
			if (!isset($src['taxes']))
			{
				if ($config->getUint('tkusetaxes') == 0)
				{
					// calculate included taxes
					$src['taxes'] = $src['price'] - $src['price'] * 100 / (100 + $this->_defaultItem->taxes_amount);
				}
				else
				{
					// calculate excluded taxes
					$src['taxes'] = $src['price'] * $this->_defaultItem->taxes_amount / 100.0;
				}

				/**
				 * Always round the calculated amount to 2 decimals, in order
				 * to avoid roundings when saving the amount in the database.
				 *
				 * Use PHP_ROUND_HALF_UP to avoid stealing any cents to the state!
				 * It is better to pay a bit more rather of VAT than having taxes problems...
				 *
				 * @since 1.8
				 */
				$src['taxes'] = round($src['taxes'], 2, PHP_ROUND_HALF_UP);
			}
		}

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to
	 * ensure they are safe to store in the database.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// check integrity using parent
		if (!parent::check())
		{
			return false;
		}
		
		// make sure the product exists
		if (empty($this->_defaultItem))
		{
			// register error message
			$this->setError(JText::_('VRTKCARTROWNOTFOUND'));

			// invalid product
			return false;
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed    $ids  Either the record ID or a list of records.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($ids = null)
	{
		if (!$ids)
		{
			return false;
		}

		$dbo = JFactory::getDbo();
			
		// delete order products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc'))
			->where($dbo->qn('id') . ' IN (' . implode(',', (array) $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete order products toppings
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc'))
			->where($dbo->qn('id_assoc') . ' IN (' . implode(',', (array) $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Returns the default details of the saved item.
	 *
	 * @return 	mixed
	 */
	public function getDefaultItem()
	{
		return !empty($this->_defaultItem) ? $this->_defaultItem : null;
	}

	/**
	 * Returns the price and taxes of the given order product.
	 *
	 * @param 	integer  $id  The record ID.
	 *
	 * @return 	object   The resulting prices.
	 */
	public function getPrice($id)
	{
		$def = new stdClass;
		$def->price = 0;
		$def->taxes = 0;

		if ((int) $id <= 0)
		{
			// return default object to avoid fatal errors
			return $def;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('price'))
			->select($dbo->qn('taxes'))
			->from($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObject();
		}

		// return default object to avoid fatal errors
		return $def;
	}

	/**
	 * Returns the details of the specified item.
	 *
	 * @param 	integer  $id  The item ID.
	 *
	 * @return 	mixed 	 The item object on success, null otherwise.
	 */
	public function getItem($id)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('i.*')
			->select($dbo->qn('t.id_group'))
			->select($dbo->qn('t.id_topping'))
			->select($dbo->qn('t.units'))
			->from($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc', 'i'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc', 't') . ' ON ' . $dbo->qn('t.id_assoc') . ' = ' . $dbo->qn('i.id'))
			->where($dbo->qn('i.id') . ' = ' . (int) $id);

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return null;
		}

		$rows = $dbo->loadObjectList();

		$item = $rows[0];
		$item->toppingGroupsRel = array();

		foreach ($rows as $row)
		{
			if (!empty($row->id_group) && !isset($item->toppingGroupsRel[$row->id_group]))
			{
				$item->toppingGroupsRel[$row->id_group] = array();
			}

			if (!empty($row->id_topping))
			{
				$item->toppingGroupsRel[$row->id_group][$row->id_topping] = $row->units;
			}
		}

		$item->price /= $item->quantity;

		return $item;
	}

	/**
	 * Assigns all the specified toppings to the item.
	 * The toppings that were already assigned and are not reported
	 * within the list will be permanently deleted.
	 *
	 * Note it is needed to bind the table first in order to have the
	 * item ID accessible.
	 *
	 * @param 	array 	 $groups       A list of group-topping relations.
	 * @param 	array 	 $unitsLookup  A lookup containing the units of the toppings.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function setAttachedToppings(array $groups = array(), array $unitsLookup = array())
	{
		if (!$this->id)
		{
			throw new Exception('Missing Item ID', 400);
		}

		$dbo = JFactory::getDbo();

		// get existing records

		$existing = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_group'))
			->select($dbo->qn('id_topping'))
			->from($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc'))
			->where($dbo->qn('id_assoc') . ' = ' . (int) $this->id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// retrieve a list of arrays
			$existing = $dbo->loadAssocList();
		}

		// insert new records

		$has = $aff = false;

		$q = $dbo->getQuery(true)
			->insert($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc'))
			->columns($dbo->qn(array('id_assoc', 'id_group', 'id_topping', 'units')));

		foreach ($groups as $r)
		{
			/**
			 * Fetch number of units.
			 *
			 * @since 1.8.2
			 */
			if (isset($unitsLookup[$r['id_group']][$r['id_topping']]))
			{
				$units = (int) $unitsLookup[$r['id_group']][$r['id_topping']];
			}
			else
			{
				$units = 1;
			}

			// make sure the record to push doesn't exist yet
			if (!in_array($r, $existing))
			{
				$q->values((int) $this->id . ', ' . (int) $r['id_group'] . ', ' . (int) $r['id_topping'] . ', ' . $units);
				$has = true;
			}
			else
			{
				/**
				 * Otherwise try to update number of units.
				 *
				 * @since 1.8.2
				 */
				$update = $dbo->getQuery(true)
					->update($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc'))
					->set($dbo->qn('units') . ' = ' . $units)
					->where(array(
						$dbo->qn('id_assoc') . ' = ' . $this->id,
						$dbo->qn('id_group') . ' = ' . $r['id_group'],
						$dbo->qn('id_topping') . ' = ' . $r['id_topping'],
					));

				$dbo->setQuery($update);
				$dbo->execute();
			}
		}

		if ($has)
		{
			$dbo->setQuery($q);
			$dbo->execute();

			$aff = (bool) $dbo->getAffectedRows();
		}

		// delete records

		$delete = array();

		foreach ($existing as $r)
		{
			// make sure the records to delete is not contained in the selected records
			if (!in_array($r, $groups))
			{
				$delete[] = $r;
			}
		}

		// detach previous elements, if any
		foreach ($delete as $d)
		{
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc'))
				->where(array(
					$dbo->qn('id_assoc') . ' = ' . (int) $this->id,
					$dbo->qn('id_group') . ' = ' . (int) $d['id_group'],
					$dbo->qn('id_topping') . ' = ' . (int) $d['id_topping'],
				));

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		}	

		return $aff;
	}
}
