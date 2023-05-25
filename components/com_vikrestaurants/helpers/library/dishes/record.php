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
 * Used to handle the restaurant dish into the database.
 *
 * @since 1.8
 */
class VREDishesRecord extends VREDishesItem
{
	/**
	 * Overloaded item price.
	 *
	 * @var float
	 */
	protected $price;

	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id  The database record ID.
	 */
	public function __construct($id)
	{
		$dbo = JFactory::getDbo();

		// load product details from database
		$q = $dbo->getQuery(true)
			->select('i.*')
			->select($dbo->qn('a.id', 'id_assoc'))
			->from($dbo->qn('#__vikrestaurants_res_prod_assoc', 'i'))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('a.id_product') . ' = ' . $dbo->qn('i.id_product'))
			->where($dbo->qn('i.id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// item not found, throw exception
			throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
		}

		$item = $dbo->loadObject();

		// instantiate product by using parent
		parent::__construct($item->id_assoc, $item->id_product_option);

		// set record ID
		$this->setRecordID($item->id);
		// set specified notes
		$this->setAdditionalNotes($item->notes);
		// set quantity
		$this->setQuantity($item->quantity);

		// register price stored in the database (divide by the selected quantity)
		$this->price = $item->price / $item->quantity;

		if ($var = $this->getVariation())
		{
			// subtract variation cost from base price
			$this->price -= $var->price;
		}
	}
	
	/**
	 * Returns the base price of the item.
	 *
	 * @return 	float 	The item base price.
	 */
	public function getPrice()
	{
		return (float) $this->price;
	}

	/**
	 * Checks whether the specified record can still be
	 * modified or deleted.
	 *
	 * @return 	boolean
	 */
	public function isWritable()
	{
		/**
		 * Look for the global permissions first.
		 *
		 * @since 1.8.3
		 */
		$config = VREFactory::getConfig();

		if (!$config->getBool('editfood'))
		{
			// cannot edit dishes after transmit
			return false;
		}

		$dbo = JFactory::getDbo();

		// do not recover "preparing" flag from internal
		// properties because it might have been cached
		// within the session
		$q = $dbo->getQuery(true)
			->select($dbo->qn('status'))
			->from($dbo->qn('#__vikrestaurants_res_prod_assoc'))
			->where($dbo->qn('id') . ' = ' . $this->getRecordID());

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// dish can be edited only if it is not
			// yet under preparation
			return $dbo->loadResult() === null;
		}

		return true;
	}

	/**
	 * Decrease the quantity of the item by the specified units.
	 *
	 * @param 	integer  $units  The units to remove.
	 *
	 * @return 	integer  The remaining units.
	 */
	public function remove($units = 1)
	{
		// invoke parent to remove specified units
		parent::remove($units);

		if ($this->quantity == 0)
		{
			// remove item from database
			JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');

			// get reservation-product table
			$table = JTableVRE::getInstance('resprod', 'VRETable');

			// permanently delete record from database
			$table->delete($this->getRecordID());
		}

		return $this->quantity;
	}
}
