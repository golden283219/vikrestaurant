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
 * VikRestaurants take-away menu table.
 *
 * @since 1.8
 */
class VRETableTkmenu extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_menus', 'id', $db);

		// register name as required field
		$this->_requiredFields[] = 'title';
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

		$dbo = JFactory::getDbo();

		if (isset($src['publish_up']))
		{
			// convert start publishing to UNIX timestamp
			if (!empty($src['publish_up']) && $src['publish_up'] != $dbo->getNullDate())
			{
				list($date, $time) = explode(' ', $src['publish_up']);
				list($hour, $min)  = explode(':', $time);

				// calculate timestamp
				$src['publish_up'] = VikRestaurants::createTimestamp($date, $hour, $min);
			}
			else
			{
				// unset start publishing
				$src['publish_up'] = -1;
			}
		}

		if (isset($src['publish_down']))
		{
			// convert finish publishing to UNIX timestamp
			if (!empty($src['publish_down']) && $src['publish_down'] != $dbo->getNullDate())
			{
				list($date, $time) = explode(' ', $src['publish_down']);
				list($hour, $min)  = explode(':', $time);

				// calculate timestamp
				$src['publish_down'] = VikRestaurants::createTimestamp($date, $hour, $min);
			}
			else
			{
				// unset finish publishing
				$src['publish_down'] = -1;
			}
		}

		/**
		 * Unset publishing dates in case the start publishing is
		 * after the finish publishing.
		 *
		 * @since 1.8.3
		 */
		if (!empty($src['publish_up']) && !empty($src['publish_down'])
			&& $src['publish_up'] != -1 && $src['publish_down'] != -1
			&& $src['publish_up'] > $src['publish_down'])
		{
			unset($src['publish_up']);
			unset($src['publish_down']);
		}

		// generate alias in case it is empty when creating or updating
		if (empty($src['alias']) && (empty($src['id']) || isset($src['alias'])))
		{
			// generate unique alias starting from title
			$src['alias'] = $src['title'];
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
			$src['alias'] = VRESefHelper::getUniqueAlias($src['alias'], 'tkmenu', $src['id']);
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

		// delete all menus
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_menus'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete all lang menus
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// retrieve all menus entries
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
			->where($dbo->qn('id_takeaway_menu') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$entries = $dbo->loadColumn();

			// delete menu products
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
				->where($dbo->qn('id_takeaway_menu') . ' IN (' . implode(',', $ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete products variations
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option'))
				->where($dbo->qn('id_takeaway_menu_entry') . ' IN (' . implode(',', $entries) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete product attributes
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'))
				->where($dbo->qn('id_menuentry') . ' IN (' . implode(',', $entries) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete product stock overrides
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_stock_override'))
				->where($dbo->qn('id_takeaway_entry') . ' IN (' . implode(',', $entries) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// get assigned toppings groups
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc'))
				->where($dbo->qn('id_entry') . ' IN (' . implode(',', $entries) . ')');

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
					->where($dbo->qn('id_entry') . ' IN (' . implode(',', $entries) . ')');

				$dbo->setQuery($q);
				$dbo->execute();

				$aff = $aff || $dbo->getAffectedRows(); 
			}

			// retrieve all products languages
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry'))
				->where($dbo->qn('id_entry') . ' IN (' . implode(',', $entries) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$langentries = $dbo->loadColumn();

				// delete products languages
				$q = $dbo->getQuery(true)
					->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry'))
					->where($dbo->qn('id_entry') . ' IN (' . implode(',', $entries) . ')');

				$dbo->setQuery($q);
				$dbo->execute();

				$aff = $aff || $dbo->getAffectedRows();
			
				// delete lang variations beloning to retrieved products
				$q = $dbo->getQuery(true)
					->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry_option'))
					->where($dbo->qn('id_parent') . ' IN (' . implode(',', $langentries) . ')');

				$dbo->setQuery($q);
				$dbo->execute();

				$aff = $aff || $dbo->getAffectedRows();

				// delete lang groups beloning to retrieved products
				$q = $dbo->getQuery(true)
					->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry_topping_group'))
					->where($dbo->qn('id_parent') . ' IN (' . implode(',', $langentries) . ')');

				$dbo->setQuery($q);
				$dbo->execute();

				$aff = $aff || $dbo->getAffectedRows();
			}
		}

		return $aff;
	}
}
