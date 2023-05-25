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
 * VikRestaurants take-away menu entry toppings group table.
 *
 * @since 1.8
 */
class VRETableTkentrygroup extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_entry_group_assoc', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'title';
		$this->_requiredFields[] = 'id_entry';
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

		// fetch ordering for new groups
		if ($src['id'] == 0 && empty($src['ordering']))
		{
			$src['ordering'] = $this->getNextOrder();
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

		// delete entry toppings groups
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete group toppings
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc'))
			->where($dbo->qn('id_group') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete group languages
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry_topping_group'))
			->where($dbo->qn('id_group') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Method to delete all the toppings that are not included
	 * within the specified list.
	 *
	 * @param   array   $toppings  A list of existing IDs.
	 * @param 	mixed 	$group 	   The group entry ID. If not specified
	 * 							   it will be retrieved from the table.
	 *
	 * @return  boolean  True on success.
	 */
	public function deleteDetachedToppings(array $toppings = array(), $group = null)
	{
		$dbo = JFactory::getDbo();

		if (is_null($group))
		{
			// use internal ID property
			$group = $this->id;
		}

		// delete all toppings assigned to the specified group
		// but there are not included within the toppings list
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc'))
			->where($dbo->qn('id_group') . ' = '. (int) $group);

		// delete select toppings only if the list if not empty,
		// otherwise delete all assigned toppings
		if ($toppings)
		{
			$q->where($dbo->qn('id_topping') . ' NOT IN (' . implode(',', array_map('intval', $toppings)) . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
