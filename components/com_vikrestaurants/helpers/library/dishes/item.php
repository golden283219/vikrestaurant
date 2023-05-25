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
 * Used to handle the restaurant dish into the cart.
 *
 * @since 1.8
 */
class VREDishesItem
{
	/**
	 * Registry containing the item details.
	 *
	 * @var JObject
	 */
	protected $data;

	/**
	 * The ID of the record stored in the database.
	 *
	 * @var integer
	 */
	protected $id_record = 0;

	/**
	 * The ID of the selected variation.
	 *
	 * @var integer
	 */
	protected $id_option = 0;

	/**
	 * A list of supported variations.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The total number of units.
	 *
	 * @var integer
	 */
	protected $quantity = 1;

	/**
	 * Any additional notes required for the purchase.
	 *
	 * @var string
	 */
	protected $notes = '';

	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id         The ID of the item to load.
	 * @param 	mixed    $id_option  The optional ID of the variation.
	 */
	public function __construct($id, $id_option = null)
	{
		$dbo = JFactory::getDbo();

		// load product details from database
		$q = $dbo->getQuery(true)
			->select('p.*')
			->select($dbo->qn('a.id', 'id_assoc'))
			->select($dbo->qn('a.charge'))
			->select($dbo->qn('o.id', 'option_id'))
			->select($dbo->qn('o.name', 'option_name'))
			->select($dbo->qn('o.inc_price', 'option_price'))
			->from($dbo->qn('#__vikrestaurants_section_product_assoc', 'a'))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product', 'p') . ' ON ' . $dbo->qn('a.id_product') . ' = ' . $dbo->qn('p.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product_option', 'o') . ' ON ' . $dbo->qn('o.id_product') . ' = ' . $dbo->qn('p.id'))
			->where($dbo->qn('a.id') . ' = ' . (int) $id)
			->order($dbo->qn('o.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// item not found, throw exception
			throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
		}

		$rows = $dbo->loadObjectList();

		// set up internal details
		$this->data = new JObject($rows[0]);

		// set up supported variations
		foreach ($rows as $opt)
		{
			if ($opt->option_id)
			{
				$tmp = new stdClass;
				$tmp->id    = $opt->option_id;
				$tmp->name  = $opt->option_name;
				$tmp->price = (float) $opt->option_price;

				$this->options[$opt->option_id] = $tmp;
			}
		}

		// check if we have at least a variation
		if ($this->options)
		{
			if (!$id_option)
			{
				// use first variation available
				$this->id_option = $this->data->get('option_id');
			}
			else
			{
				// set specified variation
				$this->id_option = $id_option;
			}

			// validate selected variation
			if (!isset($this->options[$this->id_option]))
			{
				// variation not found, throw exception
				throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
			}
		}
	}

	/**
	 * Magic method used to access internal properties.
	 *
	 * @param 	string 	$name  The property name.
	 *
	 * @return 	mixed   The property value.
	 */
	public function __get($name)
	{
		if ($name === 'id_option')
		{
			// return selected variation
			return (int) $this->id_option;
		}

		return $this->data->get($name, null);
	}

	/**
	 * Returns the ID used to keep the record stored in
	 * the database.
	 *
	 * @return 	integer
	 */
	public function getRecordID()
	{
		return $this->id_record;
	}

	/**
	 * Sets the ID used to keep the record stored in
	 * the database.
	 *
	 * @param 	integer  $id  The record ID.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setRecordID($id)
	{
		$this->id_record = (int) $id;

		return $this;
	}

	/**
	 * Returns the item base name.
	 *
	 * @return 	string  The item name.
	 */
	public function getName()
	{
		// get base name
		$name = $this->name;

		$translator = VREFactory::getTranslator();

		// get item translation
		$tx = $translator->translate('menusproduct', $this->id);

		if ($tx)
		{
			// overwrite original name with translation
			$name = $tx->name;
		}

		return $name;
	}

	/**
	 * Returns the item description.
	 *
	 * @return 	string  The item description.
	 */
	public function getDescription()
	{
		// get base description
		$description = $this->description;

		$translator = VREFactory::getTranslator();

		// get item translation
		$tx = $translator->translate('menusproduct', $this->id);

		if ($tx)
		{
			// overwrite original description with translation
			$description = $tx->description;
		}

		return $description;
	}
	
	/**
	 * Returns the variation of the item, if any.
	 *
	 * @return 	mixed  The item variation if exists, null otherwise.
	 */
	public function getVariation()
	{
		if ($this->id_option)
		{
			// get current variation
			$var = $this->options[$this->id_option];

			// variation set, translate name and return it
			$translator = VREFactory::getTranslator();
			// get option translation
			$tx = $translator->translate('productoption', $this->id_option);

			if ($tx)
			{
				// overwrite original name with translation
				$var->name = $tx->name;
			}

			return $var;
		}

		// return null
		return null;
	}

	/**
	 * Selects the specified variation, if exists.
	 *
	 * @param 	integer  $id  The variation ID.
	 *
	 * @return 	self 	 This object to supporting chaining.
	 */
	public function setVariation($id)
	{
		if (isset($this->options[$id]))
		{
			// existing option, select it
			$this->id_option = (int) $id;

			// something has changed
			$this->modified();
		}

		return $this;
	}

	/**
	 * Returns a list of variations supported by the item.
	 *
	 * @return 	array
	 */
	public function getVariations()
	{
		$translator = VREFactory::getTranslator();

		// get current language tag
		$lang = JFactory::getLanguage()->getTag();

		// preload translations
		$data = $translator->load('productoption', array_keys($this->options), $lang);

		// get variations
		$variations = $this->options;

		foreach ($variations as &$var)
		{
			// check if we have a translation
			$tx = $data->getTranslation($var->id, $lang);

			if ($tx)
			{
				// overwrite original name with translation
				$var->name = $tx->name;
			}
		}

		return $variations;
	}

	/**
	 * Returns the full name of the item.
	 * Concatenates the item name and the variation name,
	 * separated by the given string.
	 *
	 * @param 	string 	$separator 	The separator string between the names.
	 *
	 * @return 	string 	The item full name.
	 */
	public function getFullName($separator = null)
	{
		if (empty($separator))
		{
			$separator = ' - ';
		}

		// get item base name
		$name = $this->getName();

		// get variation
		$var = $this->getVariation();

		if ($var)
		{
			// concatenate product and variation
			$name .= $separator . $var->name;
		}

		return $name;
	}
	
	/**
	 * Returns the base price of the item.
	 *
	 * @return 	float 	The item base price.
	 */
	public function getPrice()
	{
		return (float) $this->price + (float) $this->charge;
	}

	/**
	 * Returns the total cost of the item, variation included.
	 *
	 * @return 	float 	The real item total cost.
	 */
	public function getTotalCost()
	{
		// get variation
		$var = $this->getVariation();

		// get base price
		$total = $this->getPrice();

		if ($var)
		{
			// increase by the variation price
			$total += $var->price;
		}

		// multiply total cost by the selected quantity
		return $total * $this->getQuantity();
	}
	
	/**
	 * Get the quantity of the item.
	 *
	 * @return 	integer  The item quantity.
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	/**
	 * Sets the quantity of the item.
	 *
	 * @param 	integer  The item quantity.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setQuantity($units)
	{
		$this->quantity = max(array(0, abs($units)));

		// something has changed
		$this->modified();

		return $this;
	}
	
	/**
	 * Increase the quantity of the item by the specified units.
	 *
	 * @param 	integer  $units  The units to add.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function add($units = 1)
	{
		$this->quantity += $units;

		// something has changed
		$this->modified();

		return $this;
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
		$this->quantity -= $units;

		if ($this->quantity < 0)
		{
			$this->quantity = 0;
		}

		// something has changed
		$this->modified();

		return $this->quantity;
	}
	
	/**
	 * Get the additional notes of the item.
	 *
	 * @return 	string 	The item additional notes.
	 */
	public function getAdditionalNotes()
	{
		return $this->notes;
	}
	
	/**
	 * Set the additional notes of the item.
	 *
	 * @param 	string 	The item additional notes.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setAdditionalNotes($notes)
	{
		$this->notes = $notes;

		// something has changed
		$this->modified();

		return $this;
	}

	/**
	 * Checks whether the specified record has been modified.
	 *
	 * @return 	boolean
	 */
	public function isModified()
	{
		return $this->modified;
	}

	/**
	 * Updates the modified status of the item.
	 *
	 * @param 	boolean  $status  The status to set (true by default).
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function modified($status = true)
	{
		$this->modified = (bool) $status;

		return $this;
	}

	/**
	 * Checks whether the specified record can still be
	 * modified or deleted.
	 *
	 * @return 	boolean
	 */
	public function isWritable()
	{
		// check if we have a session item
		if (!$this->getRecordID())
		{
			// session items are always writable
			return true;
		}

		/**
		 * Then look for the global permissions.
		 *
		 * @since 1.8.3
		 */
		$config = VREFactory::getConfig();

		return $config->getBool('editfood');
	}
	
	/**
	 * Check if this object is equal to the specified item.
	 * Two items are equal if they have the same ID, the same variation ID,
	 * and the same additional notes.
	 *
	 * @param 	VREDishesItem  $item  The item to check.
	 *
	 * @return 	boolean  	   True if the 2 objects are equal, otherwise false.
	 */
	public function equalsTo(VREDishesItem $item)
	{
		// compare items
		return $this->getRecordID() == $item->getRecordID()
			&& $this->id == $item->id 
			&& $this->id_option == $item->id_option
			&& $this->getAdditionalNotes() == $item->getAdditionalNotes();
	}
	
	/**
	 * Magic toString method to debug the item contents.
	 *
	 * @return  string  The debug string of this object.
	 */
	public function __toString()
	{
		return '<pre>' . print_r($this, true) . '</pre>';
	}
}
