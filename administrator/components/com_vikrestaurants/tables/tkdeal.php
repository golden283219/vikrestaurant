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
 * VikRestaurants take-away deal table.
 *
 * @since 1.8
 */
class VRETableTkdeal extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_deal', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'name';
		$this->_requiredFields[] = 'type';
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

		// fetch ordering for new separators
		if ($src['id'] == 0)
		{
			$src['ordering'] = $this->getNextOrder();
		}

		// convert start date to UNIX timestamp
		if (strlen($src['start_ts']))
		{
			$src['start_ts'] = VikRestaurants::createTimestamp($src['start_ts'], 0, 0);
		}
		else
		{
			$src['start_ts'] = -1;
		}

		// convert end date to UNIX timestamp
		if (strlen($src['end_ts']))
		{
			$src['end_ts'] = VikRestaurants::createTimestamp($src['end_ts'], 23, 59);
		}
		else
		{
			$src['end_ts'] = -1;
		}

		// unset both dates in case at least one of them is invalid
		if ($src['start_ts'] == -1 || $src['end_ts'] == -1)
		{
			$src['start_ts'] = $src['end_ts'] = -1;
		}

		// make sure the minimum quantity is higher than 0
		if (isset($src['min_quantity']) && $src['min_quantity'] <= 0)
		{
			$src['min_quantity'] = 1;
		}

		// force a single usage for deals based on total cost
		if (isset($src['type']) && ($src['type'] == 4 || $src['type'] == 6))
		{
			$src['max_quantity'] = 1;
		}

		// JSON encode shifts in case of array
		if (isset($src['shifts']) && !is_string($src['shifts']))
		{
			$src['shifts'] = json_encode($src['shifts']);
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

		// make sure start date is equals or lower than end date
		if ((isset($this->start_ts) || isset($this->end_ts)) && $this->start_ts > $this->end_ts)
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGETKDEAL4')));

			// invalid start date
			return false;
		}

		// make sure the type is supported
		if (isset($this->type) && ($this->type < 1 || $this->type > 6))
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGETKDEAL8')));

			// invalid type
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

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		// delete deals
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_deal'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete deals products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_deal_product_assoc'))
			->where($dbo->qn('id_deal') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete deals free products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_deal_free_assoc'))
			->where($dbo->qn('id_deal') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete deals days
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_deal_day_assoc'))
			->where($dbo->qn('id_deal') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete deals languages
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_lang_takeaway_deal'))
			->where($dbo->qn('id_deal') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Assigns all the specified days to the take-away deal.
	 * The days that were already assigned and are not reported
	 * within the list will be permanently deleted.
	 *
	 * Note it is needed to bind the table first in order to have the
	 * deal ID accessible.
	 *
	 * @param 	array 	 $days  A list of days to attach.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function setAvailabilityDays(array $days = array())
	{
		if (!$this->id)
		{
			throw new Exception('Missing deal ID', 400);
		}

		if (!$days)
		{
			// use all days if empty
			$days = range(0, 6);
		}

		$dbo = JFactory::getDbo();

		// get existing records

		$existing = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_weekday'))
			->from($dbo->qn('#__vikrestaurants_takeaway_deal_day_assoc'))
			->where($dbo->qn('id_deal') . ' = ' . (int) $this->id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$existing = $dbo->loadColumn();
		}

		// insert new records

		$has = $aff = false;

		$q = $dbo->getQuery(true)
			->insert($dbo->qn('#__vikrestaurants_takeaway_deal_day_assoc'))
			->columns($dbo->qn(array('id_deal', 'id_weekday')));

		foreach ($days as $r)
		{
			// make sure the record to push doesn't exist yet
			if (!in_array($r, $existing))
			{
				$q->values((int) $this->id . ', ' . (int) $r);
				$has = true;
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
			if (!in_array($r, $days))
			{
				$delete[] = $r;
			}
		}

		// detach previous elements, if any
		if (count($delete))
		{
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_deal_day_assoc'))
				->where(array(
					$dbo->qn('id_deal') . ' = ' . (int) $this->id,
					$dbo->qn('id_weekday') . ' IN (' . implode(',', $delete) . ')',
				));

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		}	

		return $aff;
	}
}
