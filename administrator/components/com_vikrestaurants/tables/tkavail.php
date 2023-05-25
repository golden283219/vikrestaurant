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
 * VikRestaurants take-away availability override table.
 *
 * @since 1.8.3
 */
class VRETableTkavail extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_avail_override', 'id', $db);

		// always free some space with old records
		$this->delete(true);
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

		if (empty($src['ts']))
		{
			// generate timestamp from given date and time
			list($hour, $min) = explode(':', $src['hourmin']);
			$src['ts'] = VikRestaurants::createTimestamp($src['date'], $hour, $min);
		}

		$dbo = JFactory::getDbo();

		// try to retrieve the override record if already exists
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn($this->getTableName()));

		if (empty($src['id']))
		{
			// search by timestamp
			$q->where($dbo->qn('ts') . ' = ' . (int) $src['ts']);
		}
		else
		{
			// search by ID
			$q->where($dbo->qn('id') . ' = ' . (int) $src['id']);
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// prepare for update
			$override = $dbo->loadObject();

			// add/subtract the available units to the current value
			$src['units'] += $override->units;
			// set ID to force the update
			$src['id'] = $override->id;
		}
		else
		{
			// set empty ID for insert
			$src['id'] = 0;
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

		$dbo = JFactory::getDbo();

		if ($ids === true)
		{
			$threshold = strtotime('-14 days');

			// delete availability overrides older than 2 weeks
			$q = $dbo->getQuery(true)
				->delete($dbo->qn($this->getTableName()))
				->where($dbo->qn('ts') . ' < ' . $threshold);
		}
		else
		{
			$ids = (array) $ids;

			// delete specified availability overrides
			$q = $dbo->getQuery(true)
				->delete($dbo->qn($this->getTableName()))
				->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
