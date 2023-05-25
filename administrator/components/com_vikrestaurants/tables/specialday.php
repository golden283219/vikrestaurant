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
 * VikRestaurants special day table.
 *
 * @since 1.8
 */
class VRETableSpecialday extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_specialdays', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'group';
		$this->_requiredFields[] = 'name';
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

		if (!empty($src['days_filter']))
		{
			$src['days_filter'] = implode(',', $src['days_filter']);
		}
		else
		{
			$src['days_filter'] = '';
		}

		if (!empty($src['working_shifts']))
		{
			$src['working_shifts'] = implode(',', $src['working_shifts']);
		}
		else
		{
			$src['working_shifts'] = '';
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

		// compact images if specified
		if (isset($src['images']) && is_array($src['images']))
		{
			$src['images'] = implode(';;', $src['images']);
		}

		if (isset($src['depositcost']))
		{
			// cast deposit to float
			$src['depositcost'] = abs((float) $src['depositcost']);

			if (isset($src['askdeposit']) && $src['askdeposit'] == 0)
			{
				// unset deposit cost in case it is disabled
				$src['depositcost'] = 0;
			}
		}

		if (isset($src['delivery_areas']) && is_array($src['delivery_areas']))
		{
			// encode the list of accepted delivery areas in JSON
			$src['delivery_areas'] = json_encode($src['delivery_areas']);
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
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGESPDAY2')));

			// invalid start date
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

		// delete special days
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete relationships between special days and menus
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_sd_menus'))
			->where($dbo->qn('id_spday') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Assigns all the specified menus to the special day.
	 * The menus that were already assigned and are not reported
	 * within the list will be permanently deleted.
	 *
	 * Note it is needed to bind the table first in order to have the
	 * special day ID accessible.
	 *
	 * @param 	array 	 $menus  A list of menus to attach.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function setAttachedMenus(array $menus = array())
	{
		if (!$this->id)
		{
			throw new Exception('Missing special day ID', 400);
		}

		$dbo = JFactory::getDbo();

		// get existing records

		$existing = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_menu'))
			->from($dbo->qn('#__vikrestaurants_sd_menus'))
			->where($dbo->qn('id_spday') . ' = ' . (int) $this->id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$existing = $dbo->loadColumn();
		}

		// insert new records

		$has = $aff = false;

		$q = $dbo->getQuery(true)
			->insert($dbo->qn('#__vikrestaurants_sd_menus'))
			->columns($dbo->qn(array('id_spday', 'id_menu')));

		foreach ($menus as $r)
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
			if (!in_array($r, $menus))
			{
				$delete[] = $r;
			}
		}

		// detach previous elements, if any
		if (count($delete))
		{
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_sd_menus'))
				->where(array(
					$dbo->qn('id_spday') . ' = ' . (int) $this->id,
					$dbo->qn('id_menu') . ' IN (' . implode(',', $delete) . ')',
				));

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		}	

		return $aff;
	}
}
