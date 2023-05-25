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
 * VikRestaurants take-away menu entry table.
 *
 * @since 1.8
 */
class VRETableTkentry extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_menus_entry', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'name';
		$this->_requiredFields[] = 'id_takeaway_menu';
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

		// fetch ordering for new products
		if ($src['id'] == 0)
		{
			$src['ordering'] = $this->getNextOrder();
		}

		// generate alias in case it is empty when creating or updating
		if (empty($src['alias']) && (empty($src['id']) || isset($src['alias'])))
		{
			// generate unique alias starting from name
			$src['alias'] = $src['name'];
		}
		
		// check if we are going to update an empty alias
		if (isset($src['alias']) && strlen($src['alias']) == 0)
		{
			// avoid to update an empty alias by using a uniq ID
			$src['alias'] = uniqid();
		}

		if (!empty($src['alias']))
		{
			VRELoader::import('library.sef.helper');
			// make sure the alias is unique
			$src['alias'] = VRESefHelper::getUniqueAlias($src['alias'], 'tkentry', $src['id'], $src['id_takeaway_menu']);
		}

		// check if the image attribute is an array
		if (isset($src['img_path']) && is_array($src['img_path']))
		{
			$images = $src['img_path'];

			// take the first element as main image
			$src['img_path'] = (string) array_shift($images);
			// assign the remaining elements to the extra images
			$src['img_extra'] = $images;
		}

		// check if the extra images attribute is an array
		if (isset($src['img_extra']) && is_array($src['img_extra']))
		{
			// JSON encode the extra images
			$src['img_extra'] = json_encode($src['img_extra']);
		}

		// bind the details before save
		return parent::bind($src, $ignore);
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

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		// delete menu products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete products variations
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option'))
			->where($dbo->qn('id_takeaway_menu_entry') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete product attributes
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'))
			->where($dbo->qn('id_menuentry') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete product stock overrides
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_stock_override'))
			->where($dbo->qn('id_takeaway_entry') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// get assigned toppings groups
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc'))
			->where($dbo->qn('id_entry') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// delete product toppings
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc'))
				->where($dbo->qn('id_group') . ' IN (' . implode(',', $dbo->loadColumn()) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete product toppings groups
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc'))
				->where($dbo->qn('id_entry') . ' IN (' . implode(',', $ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows(); 
		}

		// retrieve all products languages
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry'))
			->where($dbo->qn('id_entry') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$entries = $dbo->loadColumn();

			// delete products languages
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry'))
				->where($dbo->qn('id_entry') . ' IN (' . implode(',', $ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		
			// delete lang variations beloning to retrieved products
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry_option'))
				->where($dbo->qn('id_parent') . ' IN (' . implode(',', $entries) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete lang groups beloning to retrieved products
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry_topping_group'))
				->where($dbo->qn('id_parent') . ' IN (' . implode(',', $entries) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		}

		return $aff;
	}

	/**
	 * Returns the details of the specified product.
	 *
	 * @param 	integer  $id  The product ID.
	 *
	 * @return 	mixed 	 The product object on success, null otherwise.
	 */
	public function getProduct($id)
	{
		$dbo    = JFactory::getDbo();
		$config = VREFactory::getConfig();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// product not found
			return null;
		}

		$entry = $dbo->loadObject();

		// check if the stock is enabled
		if ($config->getBool('tkenablestock'))
		{
			// look for item remaining quantities
			$entry->stock = VikRestaurants::getTakeawayItemRemainingInStock($entry->id);
		}

		// get variations
		$entry->variations = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option'))
			->where($dbo->qn('id_takeaway_menu_entry') . ' = ' . $entry->id)
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$entry->variations = $dbo->loadObjectList();

			// check if the stock is enabled
			if ($config->getBool('tkenablestock'))
			{
				foreach ($entry->variations as &$var)
				{
					if ($var->stock_enabled)
					{
						// calculate variation remaining stock
						$var->stock = VikRestaurants::getTakeawayItemRemainingInStock($entry->id, $var->id);
					}
					else
					{
						// otherwise use parent stock
						$var->stock = $entry->stock;
					}
				}
			}
		}

		// get attributes
		$entry->attributes = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_attribute'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'))
			->where($dbo->qn('id_menuentry') . ' = ' . $entry->id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$entry->attributes = array_unique($dbo->loadColumn());
		}

		// get toppings groups
		$entry->groups = array();

		$q = $dbo->getQuery(true)
			->select('g.*')
			->select($dbo->qn('t.name', 'topping_name'))
			->select($dbo->qn('t.ordering', 'topping_ord'))
			->select(array(
				$dbo->qn('a.id_topping'),
				$dbo->qn('a.id', 'topping_group_assoc_id'),
				$dbo->qn('a.rate', 'topping_rate'),
			))
			->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc', 'g'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc', 'a') . ' ON ' . $dbo->qn('a.id_group') . ' = ' . $dbo->qn('g.id'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_topping', 't') . ' ON ' . $dbo->qn('a.id_topping') . ' = ' . $dbo->qn('t.id'))
			->where($dbo->qn('g.id_entry') . ' = ' . $entry->id)
			->order($dbo->qn('g.ordering') . ' ASC')
			->order($dbo->qn('a.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $group)
			{
				if (!isset($entry->groups[$group->id]))
				{
					$group->toppings = array();

					/**
					 * Added support for toppings groups description.
					 *
					 * @since 1.8.2
					 */
					if (!$group->description)
					{
						// use group title as description
						$group->description = $group->title;
					}

					$entry->groups[$group->id] = $group;
				}
				
				if (!empty($group->topping_group_assoc_id))
				{
					$topping = new stdClass;
					$topping->id_assoc = $group->topping_group_assoc_id;
					$topping->id       = $group->id_topping;
					$topping->name     = $group->topping_name;
					$topping->rate     = $group->topping_rate;
					$topping->ordering = $group->topping_ord;

					$entry->groups[$group->id]->toppings[] = $topping;
				}
			}

			// do not use array keys
			$entry->groups = array_values($entry->groups);
		}

		return $entry;
	}
}
