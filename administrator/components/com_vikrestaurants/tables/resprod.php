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
 * VikRestaurants restaurant reservation product (bill cart items) table.
 *
 * @since 1.8
 */
class VRETableResprod extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_res_prod_assoc', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_product';
		$this->_requiredFields[] = 'id_reservation';
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

		$dbo = JFactory::getDbo();

		$this->_defaultItem = null;

		if (isset($src['id_product']))
		{
			// get default product
			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('p.id', 'p.name', 'p.price')))
				->select($dbo->qn('o.id', 'id_option'))
				->select($dbo->qn('o.name', 'option_name'))
				->select($dbo->qn('o.inc_price', 'option_price'))
				->from($dbo->qn('#__vikrestaurants_section_product', 'p'))
				->leftjoin($dbo->qn('#__vikrestaurants_section_product_option', 'o') . ' ON ' . $dbo->qn('o.id_product') . ' = ' . $dbo->qn('p.id'))
				->where($dbo->qn('p.id') . ' = ' . (int) $src['id_product']);

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

				if (!empty($src['charge']))
				{
					// charge specified, apply surcharge/discount to the base price
					$src['price'] = max(array(0, $src['price'] + (float) $src['charge']));
				}

				if (empty($src['name']))
				{
					// set up item name
					$src['name'] = $this->_defaultItem->name . ($this->_defaultItem->option_name ? ' - ' . $this->_defaultItem->option_name : '');
				}

				// always multiply the single item price per quantity
				$src['price'] *= $src['quantity'];
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
		if (empty($this->_defaultItem) && empty($this->id))
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
			
		// delete reservations
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_res_prod_assoc'))
			->where($dbo->qn('id') . ' IN (' . implode(',', (array) $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Returns the price of the given reservation product.
	 *
	 * @param 	integer  $id  The record ID.
	 *
	 * @return 	float    The resulting price.
	 */
	public function getPrice($id)
	{
		if ((int) $id <= 0)
		{
			return 0;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('price'))
			->from($dbo->qn('#__vikrestaurants_res_prod_assoc'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return (float) $dbo->loadResult();
		}

		return 0;
	}
}
