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
 * VikRestaurants order code status table.
 *
 * @since 1.8
 */
class VRETableRescodeorder extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_order_status', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_order';
		$this->_requiredFields[] = 'id_rescode';
		$this->_requiredFields[] = 'group';
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

		if (empty($src['id']))
		{
			if (empty($src['createdon']))
			{
				$src['createdon'] = VikRestaurants::now();
			}

			if (empty($src['createdby']))
			{
				$src['createdby'] = JFactory::getUser()->id;
			}

			$dbo = JFactory::getDbo();

			// check if we have an order status with the specified code
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_order_status'))
				->where($dbo->qn('id_order') . ' = ' . (int) $src['id_order'])
				->where($dbo->qn('id_rescode') . ' = ' . (int) $src['id_rescode'])
				->where($dbo->qn('group') . ' = ' . (int) $src['group']);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// the reservation code already exists, update it
				// in order to avoid duplicate records
				$src['id'] = (int) $dbo->loadResult();
			}
		}

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		// invoke parent to store the record
		if (!parent::store($updateNulls))
		{
			// do not proceed in case of error
			return false;
		}

		VRELoader::import('library.rescodes.handler');

		try
		{
			switch ($this->group)
			{
				case 1:
					$group = 'restaurant';
					break;

				case 2:
					$group = 'takeaway';
					break;

				case 3:
					$group = 'food';
					break;

				default:
					$group = null;
			}

			// trigger code change to dispatch the rule action, if any
			ResCodesHandler::trigger($this->id_rescode, $this->id_order, $group);
		}
		catch (Exception $e)
		{
			// suppress error silently...
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

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();
			
		// delete order statuses
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_order_status'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
