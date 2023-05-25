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
 * VikRestaurants take-away menu group topping table.
 *
 * @since 1.8
 */
class VRETableTkgrouptopping extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_group_topping_assoc', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_group';
		$this->_requiredFields[] = 'id_topping';
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

		// fetch ordering for new toppings groups
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

		// delete group toppings
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Mass update the rate of all the toppings assigned to groups.
	 *
	 * @param 	array  $data          An array containing the data to update.
	 * @param 	mixed  $current_rate  The previous rate, if specified.
	 *
	 * @return 	void
	 */
	public function updateToppingsRate($data, $current_rate)
	{
		$dbo = JFactory::getDbo();

		// update rate topping associations
		$q = $dbo->getQuery(true)
			->update($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc'))
			->set($dbo->qn('rate') . ' = ' . (float) @$data['rate'])
			->where($dbo->qn('id_topping') . ' = ' . (int) @$data['id_topping']);

		if ($current_rate)
		{
			// match all associations with the same rate
			$q->where($dbo->qn('rate') . ' = ' . (float) $current_rate);
		}

		$dbo->setQuery($q);
		$dbo->execute();
	}
}
