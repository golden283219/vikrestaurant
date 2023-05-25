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
 * VikRestaurants roomtable table.
 *
 * @since 1.8
 */
class VRETableTable extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_table', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'name';
		$this->_requiredFields[] = 'id_room';
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

		// create design data for new tables (if not specified)
		if ($src['id'] == 0 && !isset($src['design_data']))
		{
			$dbo = JFactory::getDbo();

			$dd = null;

			$q = $dbo->getQuery(true)
				->select($dbo->qn('design_data'))
				->from($dbo->qn('#__vikrestaurants_table'))
				->order($dbo->qn('id') . ' DESC');

			if (isset($src['id_room']))
			{
				$q->where($dbo->qn('id_room') . ' = ' . (int) $src['id_room']);
			}

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$dd = json_decode($dbo->loadResult(), true);
			}

			// create table graphics properties
			$src['design_data'] = json_encode($this->createTableProperties($dd));
		}
		
		// stringify design data
		if (isset($src['design_data']) && !is_scalar($src['design_data']))
		{
			$src['design_data'] = json_encode($src['design_data']);
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
		
		// check capacity
		if ((isset($this->min_capacity) || isset($this->max_capacity)) && $this->min_capacity > $this->max_capacity)
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGETABLE2')));

			// invalid capacity
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
		if (!count($ids))
		{
			return false;
		}

		$dbo = JFactory::getDbo();
			
		// delete tables
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Creates the cluster of tables.
	 *
	 * Note it is needed to bind the table first in order to have the
	 * record ID accessible.
	 *
	 * @param 	array 	 $tables  A list of tables to attach.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function setCluster(array $tables = array())
	{
		if (!$this->id)
		{
			throw new Exception('Missing table ID', 400);
		}

		$dbo = JFactory::getDbo();

		// get existing records

		$existing = VREAvailabilitySearch::getTablesCluster($this->id);

		// insert new records

		$has = $aff = false;

		$q = $dbo->getQuery(true)
			->insert($dbo->qn('#__vikrestaurants_table_cluster'))
			->columns($dbo->qn(array('id_table_1', 'id_table_2')));

		foreach ($tables as $r)
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


		foreach ($existing as $r)
		{
			// make sure the records to delete is not contained in the selected records
			if (!in_array($r, $tables))
			{
				$q = $dbo->getQuery(true)
					->delete($dbo->qn('#__vikrestaurants_table_cluster'))
					->where(array(
						$dbo->qn('id_table_1') . ' = ' . (int) $this->id,
						$dbo->qn('id_table_2') . ' = ' . $r,
					))
					->orWhere(array(
						$dbo->qn('id_table_2') . ' = ' . (int) $this->id,
						$dbo->qn('id_table_1') . ' = ' . $r,
					));

				$dbo->setQuery($q);
				$dbo->execute();

				$aff = $aff || $dbo->getAffectedRows();
			}
		}

		return $aff;
	}

	/**
	 * Creates table graphics properties.
	 *
	 * @param 	mixed  $data  The design data.
	 *
	 * @return  array  The graphics properties.
	 */
	protected function createTableProperties($data = null)
	{
		if (!$data)
		{
			$data = array();
			$data['posx'] 		= 40;
			$data['posy']		= 40;
			$data['width'] 		= 100;
			$data['height']		= 100;
			$data['rotate'] 	= 0;
			$data['bgColor'] 	= 'a3a3a3';
			$data['roundness']  = 0;
			$data['class']		= 'UIShapeRect';
		}
		else
		{
			if ($data['class'] == 'UIShapeCircle')
			{
				$w = $h = $data['radius'] * 2;
			}
			else
			{
				$w = $data['width'];
				$h = $data['height'];
			}

			$data = (array) $data;

			$data['posx'] += $w + 30;

			if ($data['posx'] > 800)
			{
				$data['posx'] = 40;
				$data['posy'] += $h + 30;
			}
		}

		return $data;
	}
}
