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
 * Helper class used to perform some availability checks
 * when trying to search for a free table.
 *
 * @since 1.8
 */
class VREAvailabilitySearch extends JObject
{
	/**
	 * A list of all room tables.
	 *
	 * @var array
	 */
	protected static $_tables = null;

	/**
	 * The availability search date.
	 * The date must be formatted according to the format
	 * specified in configuration.
	 *
	 * @var string
	 */
	protected $date;

	/**
	 * The availability search hour (24H format).
	 *
	 * @var integer
	 */
	protected $hour;

	/**
	 * The availability search minute.
	 *
	 * @var integer
	 */
	protected $min;

	/**
	 * The availability search people.
	 *
	 * @var integer
	 */
	protected $people;

	/**
	 * Flag used to check whether the user is an administrator.
	 *
	 * @var boolean
	 */
	protected $admin;

	/**
	 * A forced time of stay expressed in minutes.
	 *
	 * @var integer
	 */
	protected $staytime;

	/**
	 * A list of allowed statuses that represents a valid reservation.
	 *
	 * @var array
	 */
	protected $statuses = array(
		'PENDING',
		'CONFIRMED',
	);

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	 $date    Either a date string or a UNIX timestamp.
	 * @param 	mixed 	 $time    Either a hourmin string (e.g. 20:30) or an array
	 * 							  containing the hour and the minutes.
	 * @param 	integer  $people  The number of participants.
	 * @param 	boolean  $admin   True to exclude publishing filters. If not specified,
	 * 							  this value will be based on the current client.
	 *
	 * @uses 	setDate()
	 * @uses 	setTime()
	 * @uses 	setPeople()
	 * @uses 	setAdmin()
	 */
	public function __construct($date, $time, $people = 2, $admin = null)
	{
		// set arguments
		$this->setDate($date)
			->setTime($time)
			->setPeople($people)
			->setAdmin($admin);
	}

	/**
	 * Updates the searched date.
	 *
	 * @param 	mixed 	Either a date string or a UNIX timestamp.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setDate($date)
	{
		if (preg_match("/^\d+$/", $date))
		{
			// we have a UNIX timestamp, convert it to a formatted date
			$date = date(VREFactory::getConfig()->get('dateformat'), $date);
		}

		// register property
		$this->set('date', $date);

		return $this;
	}

	/**
	 * Updates the searched time.
	 *
	 * @param 	mixed 	$time   Either a hourmin string (e.g. 20:30) or an array
	 * 							containing the hour and the minutes. In case of array,
	 * 						    it must specify the "hour" and "min" attributes.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setTime($time)
	{
		if (is_array($time))
		{
			// extract hour and minutes from array
			extract($time);
		}
		else
		{
			// explode hour and minutes from time string
			list($hour, $min) = explode(':', $time);
		}

		// register properties
		$this->set('hour', (int) $hour);
		$this->set('min' , (int) $min );

		return $this;
	}

	/**
	 * Updates the searched number of people.
	 *
	 * @param 	integer  $people  The number of participants.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setPeople($people)
	{
		// do not accept a number lower than 1
		$this->set('people', max(array(1, $people)));

		return $this;
	}

	/**
	 * Sets the permissions of the user (admin or customer).
	 *
	 * @param 	boolean  $admin  True to exclude publishing filters. If not specified,
	 * 							 this value will be based on the current client.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setAdmin($admin)
	{
		if (is_null($admin))
		{
			// check current client
			$admin = JFactory::getApplication()->isClient('administrator');
		}

		// register property
		$this->set('admin', (bool) $admin);

		return $this;
	}

	/**
	 * Checks whether the user is an administrator.
	 *
	 * @return 	boolean
	 */
	public function isAdmin()
	{
		return (bool) $this->get('admin', false);
	}

	/**
	 * Forces the time of stay when checking the availability of a specific reservation.
	 *
	 * @param 	integer  $min  The time of stay in minutes.
	 *
	 * @return 	self 	 This object to support chaining.
	 *
	 * @since 	1.8.2
	 */
	public function setStayTime($min)
	{
		// register property
		$this->set('staytime', abs($min));

		return $this;
	}

	/**
	 * Returns the specified time of stay or the default one, in case it was not forced.
	 *
	 * @return 	integer
	 * 
	 * @since 	1.8.2
	 */
	public function getStayTime($default = null)
	{
		// get specified time of stay
		$staytime = (int) $this->get('staytime', 0);

		// make sure the value is valid
		if ($staytime <= 0)
		{
			// use the default value
			$staytime = (int) $default;
		}

		// validate again
		if ($staytime <= 0)
		{
			// use the configuration value
			$staytime = VREFactory::getConfig()->getUint('averagetimestay');
		}

		return $staytime;
	}

	/**
	 * Returns the details of the specified tables.
	 *
	 * @param 	array  $ids  The array of IDs.
	 *
	 * @return 	array  The table details list.
	 */
	public function getTables(array $ids = array())
	{
		$tmp = array();

		// iterate tables
		foreach (self::loadTables() as $table)
		{
			// check if the table ID has been requested
			if (in_array($table->id, $ids))
			{
				$tmp[] = $table;
			}
		}

		return $tmp;
	}

	/**
	 * Returns the details of the tables that
	 * belong to the specified room.
	 *
	 * @param 	integer  $id  The ID of the room.
	 *
	 * @return 	array    The table details list.
	 */
	public function getRoomTables($id)
	{
		$tmp = array();

		// iterate tables
		foreach (self::loadTables() as $table)
		{
			// check if the table room matches the specified one
			if ($table->id_room == $id)
			{
				$tmp[] = $table;
			}
		}

		return $tmp;
	}

	/**
	 * Returns the details of the specified table.
	 *
	 * @param 	integer  $id  The table ID.
	 *
	 * @return 	mixed    The details of the table if exists, null otherwise.
	 *
	 * @uses 	getTables()
	 */
	public function getTable($id)
	{
		// get tables list
		$tables = $this->getTables(array((int) $id));

		// return first element found (NULL in case it was not found)
		return array_shift($tables);
	}

	/**
	 * Returns the minimum and maximum capacity supported by
	 * the given cluster of tables.
	 *
	 * @param 	array 	$cluster  A list of table IDs.
	 * @param 	mixed 	$min      The total number of tables that will
	 * 							  be used to sum the minimum capacity.
	 *
	 * @return 	object  An object containing the min and max capacity.
	 *
	 * @uses 	getTables()
	 */
	public function getClusterCapacity(array $cluster, $min = null)
	{
		$capacity = new stdClass;
		$capacity->min = array();
		$capacity->max = 0;

		foreach ($this->getTables($cluster) as $t)
		{
			// sum table maximum capacity to total
			$capacity->max += (int) $t->max_capacity;

			// push minimum capacity within the list
			$capacity->min[] = (int) $t->min_capacity;
		}

		if (is_null($min))
		{
			// sum all capacities
			$capacity->min = array_sum($capacity->min);
		}
		else
		{
			// sort minimum capacities (ASC)
			sort($capacity->min, SORT_NUMERIC);

			// sum the first N ($min) lowest minimum capacities
			$capacity->min = array_sum(array_splice($capacity->min, 0, $min));
		}

		return $capacity;
	}

	/**
	 * Finds all the possible combination of a cluster able to
	 * host the specified number of people.
	 *
	 * @param 	array 	$cluster  A list of tables.
	 *
	 * @return 	array 	A list of sub-clusters.
	 */
	protected function generateClustersCombination(array $cluster)
	{
		// initialize by adding the empty set to start the inner loop
	    $results = array(array());

	    // iterate all elements within the list
	    foreach ($cluster as $element)
	    {
	    	// Recursively generate a combination of the existing
	    	// elements and the current one. The foreach won't cause
	    	// a loop because, even if we are injecting new elements
	    	// within the source array ($results), its copy is used
	    	// as iterator.
	        foreach ($results as $combination)
	        {
	        	// extend combination
	            $results[] = array_merge(array($element), $combination);
	        }
	    }

	    // pop first empty element
	    array_shift($results);

	    $clusters = array();

	    foreach ($results as $r)
	    {
	    	// get cluster capacity
	    	$capacity = $this->getClusterCapacity($r);

	    	// make sure the cluster supports the requested capacity
	    	if ($capacity->min <= $this->people && $this->people <= $capacity->max)
	    	{
	    		// keep generated combination
	    		$clusters[] = $r;
	    	}
	    }

	    return $clusters;
	}

	/**
	 * Finds the most optimal combination that can handle the required
	 * number of people within the given cluster tables.
	 * 
	 * This method takes the cluster with the lowest count of wasted seats.
	 * Among a cluster with a waste of 2 seats (10 seats of 12) and a cluster
	 * with a waste of 4 seats (10 seats of 14), the first one will be taken.
	 *
	 * In case of a tie in terms of waste, the method will take the cluster
	 * with the lowest number of used resources (tables). Among a cluster that
	 * uses 3 tables to reach a capacity of 12 seats and a cluster with 2 tables
	 * to reach the same capacity, the second one will be taken.
	 *
	 * @param 	array 	$clusters  A list of clusters (bi-dimensional).
	 *
	 * @return 	array 	The best tables combination.
	 */
	protected function findClusterOptimalCombination(array $clusters)
	{
	    $comb  = null;
	    $waste = null;

	    // iterate all combinations found
	    foreach ($clusters as $cluster)
	    {
	    	// get cluster capacity
	    	$capacity = $this->getClusterCapacity($cluster);

	    	// make sure the cluster supports the requested capacity
	    	if ($capacity->min <= $this->people && $this->people <= $capacity->max)
	    	{
	    		// calculate waste (number of seats that are going to be lost)
	    		$tmp = $capacity->max - $this->people;

	    		// Register new combination in the following cases:
	    		// - this is the first combination available;
	    		// - the waste of seats is lower than the previous one;
	    		// - we have the same waste count but with lower resources (tables).
	    		if (is_null($comb) || $tmp < $waste || ($tmp == $waste && count($cluster) < count($comb)))
	    		{
	    			// found a temporary optimal cluster
	    			$comb = $cluster;
	    			// register new waste
	    			$waste = $tmp;
	    		}
	    	}
	    }

	    return $comb;
	}

	/**
	 * CHECK IF TABLE IS AVAILABLE
	 */

	/**
	 * Checks whether the specified table is available for the searched arguments.
	 *
	 * @param 	integer  $id_table  The table to check for.
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 * @param 	array 	 &$cluster  A list containing all the tables that needs to
	 * 								be booked in addition to the specified one.
	 *
	 * @return 	boolean  True if the table is available, false otherwise.
	 *
	 * @uses 	isDefaultTableAvailable()
	 * @uses 	isSharedTableAvailable()
	 */
	public function isTableAvailable($id_table, $id_res = null, &$cluster = null)
	{
		// get table details
		$table = $this->getTable($id_table);

		if (!$table)
		{
			// table not found
			return false;
		}

		if ($table->multi_res)
		{
			// check whether the SHARED table if available
			return $this->isSharedTableAvailable($id_table, $id_res);
		}

		if ($table->min_capacity <= $this->people && $this->people <= $table->max_capacity)
		{

			// use default availability check
			return $this->isDefaultTableAvailable($id_table, $id_res);
		}

		// check if the specified table is available as a cluster
		$cluster = $this->getAvailableClusters($id_res, $id_table);

		if ($cluster)
		{
			// take only the first one available
			$cluster = $cluster[0];

			// cluster available, find index of the requested table
			$index = array_search($id_table, $cluster);
			// remove table from cluster
			array_splice($cluster, $index, 1);

			return true;
		}

		// table is not available
		return false;
	}

	/**
	 * Checks whether the specified NON SHARED table is available for the searched arguments.
	 *
	 * @param 	integer  $id_table  The table to check for.
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 *
	 * @return 	boolean  True if the table is available, false otherwise.
	 */
	protected function isDefaultTableAvailable($id_table, $id_res = null)
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		$q = $dbo->getQuery(true);

		// SELECT data
		$q->select(1);

		// load tables and rooms from DB
		$q->from($dbo->qn('#__vikrestaurants_table', 't'));
		$q->from($dbo->qn('#__vikrestaurants_room', 'rm'));

		// join the tables with the rooms
		$q->where($dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));
		$q->where($dbo->qn('t.id') . ' = ' . (int) $id_table);

		// exclude multi reservation table
		$q->where($dbo->qn('t.multi_res') . ' = 0');

		/**
		 * Use publishing restrictions only for front-end users.
		 *
		 * @since 1.7.4
		 */
		if (!$this->isAdmin())
		{
			$q->where($dbo->qn('rm.published') . ' = 1');
			$q->where($dbo->qn('t.published') . ' = 1');
			$q->where((int) $this->people . ' BETWEEN ' . $dbo->qn('t.min_capacity') . ' AND ' . $dbo->qn('t.max_capacity'));

			/**
			 * Search for room closure.
			 *
			 * @since 1.8
			 */
			$closureQuery = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
				->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('rm.id'))
				->where($in_ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

			$q->where('(' . $closureQuery . ') = 0');
		}

		$availQuery = $dbo->getQuery(true)
			->select($dbo->qn('t.id')) 
			->from($dbo->qn('#__vikrestaurants_reservation', 'r')) 
			->where($dbo->qn('t.id') . ' = ' . $dbo->qn('r.id_table'))
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')')
			->andWhere(array(
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$in_ts}  <  " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <  {$out_ts} AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " >= {$in_ts}  AND {$out_ts} >= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
			), 'OR');

		if ($id_res)
		{
			// ignore the specified reservation so that it won't be considered while
			// checking the availability of the table
			$availQuery->where($dbo->qn('r.id') . ' <> ' . (int) $id_res);
			$availQuery->where($dbo->qn('r.id_parent') . ' <> ' . (int) $id_res);
		}

		// exclude if reserved
		$q->where('NOT EXISTS(' . $availQuery . ')');

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		// available in case the query returned something
		return (bool) $dbo->getNumRows();
	}

	/**
	 * Checks whether the specified SHARED table is available for the searched arguments.
	 *
	 * @param 	integer  $id_table  The table to check for.
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 *
	 * @return 	boolean  True if the table is available, false otherwise.
	 */
	protected function isSharedTableAvailable($id_table, $id_res = null)
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		// calculate total number of people hosted by the table
		$occupancyQuery = $dbo->getQuery(true)
			->select('SUM(' . $dbo->qn('r.people') . ')')
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->where($dbo->qn('t.id') . ' = ' . $dbo->qn('r.id_table'))
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')')
			->andWhere(array(
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$in_ts}  <  " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <  {$out_ts} AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " >= {$in_ts}  AND {$out_ts} >= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
			), 'OR');

		if ($id_res)
		{
			// ignore the specified reservation so that it won't be considered while
			// checking the availability of the table
			$occupancyQuery->where($dbo->qn('r.id') . ' <> ' . (int) $id_res);
			$occupancyQuery->where($dbo->qn('r.id_parent') . ' <> ' . (int) $id_res);
		}

		$q = $dbo->getQuery(true);

		// SELECT current number of people hosted in the table
		$q->select('IFNULL((' . $occupancyQuery . '), 0) AS ' . $dbo->qn('total'));
		$q->select($dbo->qn('t.min_capacity'));
		$q->select($dbo->qn('t.max_capacity'));

		// load tables and rooms from DB
		$q->from($dbo->qn('#__vikrestaurants_table', 't'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));

		// filter by table ID
		$q->where($dbo->qn('t.id') . ' = ' . (int) $id_table);

		// exclude single reservation table
		$q->where($dbo->qn('t.multi_res') . ' = 1');

		/**
		 * Use publishing restrictions only for front-end users.
		 *
		 * @since 1.7.4
		 */
		if (!$this->isAdmin())
		{
			$q->where($dbo->qn('rm.published') . ' = 1');
			$q->where($dbo->qn('t.published') . ' = 1');

			/**
			 * Search for room closure.
			 *
			 * @since 1.8
			 */
			$closureQuery = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
				->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('rm.id'))
				->where($in_ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

			$q->where('(' . $closureQuery . ') = 0');
		}

		// make sure the table is capable of hosting the specified number of people
		$q->having($dbo->qn('t.min_capacity') . ' <= ' . (int) $this->people);
		$q->having('(' . $dbo->qn('total') . ' + ' . (int) $this->people . ') <= ' . $dbo->qn('t.max_capacity'));

		$dbo->setQuery($q);
		$dbo->execute();

		// available in case the query returned the current occupancy
		return (bool) $dbo->getNumRows();
	}

	/**
	 * Returns a list of available tables for the searched arguments.
	 *
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 *
	 * @return 	array 	 A list of available tables.
	 *
	 * @uses 	getFreeTables()
	 * @uses 	getAvailableSharedTables()
	 */
	public function getAvailableTables($id_res = null)
	{
		// get free tables first
		$tables = $this->getFreeTables($id_res);

		/**
		 * Always search for a cluster, otherwise certain rooms might
		 * not be able to host reservations as long as other rooms
		 * have free single tables.
		 *
		 * @since 1.8.3
		 */

		// find all the optimal clusters in each room
		foreach ($this->getAvailableClusters($id_res) as $cluster)
		{
			// merge tables found with the previous results
			$tables = array_merge($tables, $cluster);
		}

		// then get available shared tables
		$shared = $this->getAvailableSharedTables($id_res);

		// marge all fetched results
		$tables = array_merge($tables, $shared);
		// avoid duplicate records and reset keys
		$tables = array_values(array_unique($tables));

		// get table details
		$tables = $this->getTables($tables);

		return $tables;
	}

	/**
	 * Returns a list of tables that are not occupied for the searched arguments.
	 *
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 * @param 	boolean  $capacity  False to ignore the capacity of the table.
	 *
	 * @return 	array 	 A list of available tables.
	 */
	protected function getFreeTables($id_res = null, $capacity = true)
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		$q = $dbo->getQuery(true);

		// SELECT data
		$q->select($dbo->qn('t.id'));

		// load tables and rooms from DB
		$q->from($dbo->qn('#__vikrestaurants_table', 't'));
		$q->from($dbo->qn('#__vikrestaurants_room', 'rm'));

		// join the tables with the rooms
		$q->where($dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));

		/**
		 * Use publishing restrictions only for front-end users.
		 *
		 * @since 1.7.4
		 */
		if (!$this->isAdmin())
		{
			$q->where($dbo->qn('rm.published') . ' = 1');
			$q->where($dbo->qn('t.published') . ' = 1');

			/**
			 * Search for room closure.
			 *
			 * @since 1.8
			 */
			$closureQuery = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
				->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('rm.id'))
				->where($in_ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

			$q->where('(' . $closureQuery . ') = 0');
		}

		$availQuery = $dbo->getQuery(true)
			->select($dbo->qn('t.id')) 
			->from($dbo->qn('#__vikrestaurants_reservation', 'r')) 
			->where($dbo->qn('t.id') . ' = ' . $dbo->qn('r.id_table'))
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')')
			->andWhere(array(
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$in_ts}  <  " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <  {$out_ts} AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " >= {$in_ts}  AND {$out_ts} >= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
			), 'OR');

		if ($id_res)
		{
			// ignore the specified reservation so that it won't be considered while
			// checking the availability of the table
			$availQuery->where($dbo->qn('r.id') . ' <> ' . (int) $id_res);
			$availQuery->where($dbo->qn('r.id_parent') . ' <> ' . (int) $id_res);
		}

		// exclude shared tables
		$q->where($dbo->qn('t.multi_res') . ' = 0');

		if ($capacity)
		{
			// make sure the table can host the given number of people
			$q->where((int) $this->people . ' BETWEEN ' . $dbo->qn('t.min_capacity') . ' AND ' . $dbo->qn('t.max_capacity'));
		}

		// exclude if reserved
		$q->where('NOT EXISTS(' . $availQuery . ')');

		// NON SHARED tables comes first
		$q->order($dbo->qn('t.multi_res') . ' ASC');
		// then take the tables with lower capacity
		$q->order($dbo->qn('t.max_capacity') . ' ASC');
		// finally use the rooms ordering
		$q->order($dbo->qn('rm.ordering') . ' ASC');
		// keep the natural ordering of the tables to avoid random results
		$q->order($dbo->qn('t.id') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// return tables found
			return $dbo->loadColumn();
		}

		// no available tables
		return array();
	}

	/**
	 * Returns a list of SHARED tables that are still available for the searched arguments.
	 *
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 *
	 * @return 	array 	 A list of available tables.
	 */
	protected function getAvailableSharedTables($id_res = null)
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		// calculate total number of people hosted by the table
		$occupancyQuery = $dbo->getQuery(true)
			->select('SUM(' . $dbo->qn('r.people') . ')')
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->where($dbo->qn('t.id') . ' = ' . $dbo->qn('r.id_table'))
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')')
			->andWhere(array(
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$in_ts}  <  " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <  {$out_ts} AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " >= {$in_ts}  AND {$out_ts} >= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
			), 'OR');

		if ($id_res)
		{
			// ignore the specified reservation so that it won't be considered while
			// checking the availability of the table
			$occupancyQuery->where($dbo->qn('r.id') . ' <> ' . (int) $id_res);
			$occupancyQuery->where($dbo->qn('r.id_parent') . ' <> ' . (int) $id_res);
		}

		$q = $dbo->getQuery(true);

		// SELECT current number of people hosted in the table
		$q->select('IFNULL((' . $occupancyQuery . '), 0) AS ' . $dbo->qn('total'));
		$q->select($dbo->qn('t.min_capacity'));
		$q->select($dbo->qn('t.max_capacity'));
		$q->select($dbo->qn('t.id'));

		// load tables and rooms from DB
		$q->from($dbo->qn('#__vikrestaurants_table', 't'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));

		// exclude single reservation table
		$q->where($dbo->qn('t.multi_res') . ' = 1');

		/**
		 * Use publishing restrictions only for front-end users.
		 *
		 * @since 1.7.4
		 */
		if (!$this->isAdmin())
		{
			$q->where($dbo->qn('rm.published') . ' = 1');
			$q->where($dbo->qn('t.published') . ' = 1');

			/**
			 * Search for room closure.
			 *
			 * @since 1.8
			 */
			$closureQuery = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
				->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('rm.id'))
				->where($in_ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

			$q->where('(' . $closureQuery . ') = 0');
		}

		// make sure the table is capable of hosting the specified number of people
		$q->having($dbo->qn('t.min_capacity') . ' <= ' . (int) $this->people);
		$q->having('(' . $dbo->qn('total') . ' + ' . (int) $this->people . ') <= ' . $dbo->qn('t.max_capacity'));

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no available tables
			return array();
		}

		$tables = array();

		// take only the table ID
		foreach ($dbo->loadObjectList() as $t)
		{
			$tables[] = $t->id;
		}

		return $tables;
	}

	/**
	 * Returns a list of tables that can be merged together in
	 * order to cover the specified number of participants.
	 *
	 * @param 	integer  $id_res    The reservation ID to exclude while checking.
	 * @param 	integer  $id_table  If specified, the cluster must contain the
	 * 								specified table ID.
	 *
	 * @return 	array    A list containing the clusters.
	 */
	protected function getAvailableClusters($id_res = null, $id_table = null)
	{
		// return a list of tables available for the selected
		// date and time (skip capacity restrictions)
		$free = $this->getFreeTables($id_res, $capacity = false);

		// get details of the tables found
		$freeTables = $this->getTables($free);

		$clusters = array();

		// get available clusters
		foreach ($freeTables as $t)
		{
			// register cluster if not empty and it hasn't been
			// included yet within the list
			if ($t->cluster && !in_array($t->cluster, $clusters))
			{
				// in case the table ID is specified, make sure that the
				// cluster contains the given ID
				if (is_null($id_table) || in_array($id_table, $t->cluster))
				{
					// strip tables (from cluster) that are not available
					$clusters[] = array_values(array_filter($t->cluster, function($t2) use ($free)
					{
						// make sure the table is currently available
						return in_array($t2, $free);
					}));
				}
			}
		}

		$self = $this;

		// take only the clusters that support the requested number of people
		$clusters = array_values(array_filter($clusters, function($cluster) use ($self)
		{
			// Get minimum and maximum capacity of the cluster.
			// 2 indicates the minimum number of tables needed to
			// form a cluster.
			$capacity = $self->getClusterCapacity($cluster, 2);
			$people   = (int) $self->get('people', 0);

			// make sure the selected number of people stays between the
			// cluster capacity range
			return $capacity->min <= $people && $people <= $capacity->max;
		}));

		if (!$clusters)
		{
			// no clusters found, don't need to go ahead
			return array();
		}

		$rooms = array();

		// decompose found clusters in sub-clusters to have
		// all the possible combinations of tables
		foreach ($clusters as $cluster)
		{
			// get all combinations
			$list = $this->generateClustersCombination($cluster);

			// join previous combinations with the new ones
			foreach ($list as $tmp)
			{
				// in case the table ID is specified, make sure that the
				// combination contains the given ID
				if (is_null($id_table) || in_array($id_table, $tmp))
				{
					// get room to which the first table of the cluster belongs
					$id_room = $this->getTable($tmp[0])->id_room;

					// split clusters in rooms
					if (!isset($rooms[$id_room]))
					{
						$rooms[$id_room] = array();
					}

					$rooms[$id_room][] = $tmp;
				}
			}
		}

		$tables = array();

		// iterate combinations
		foreach ($rooms as $id_room => $combinations)
		{
			// find the most optimal combination of tables for the current room
			$tables[] = $this->findClusterOptimalCombination($combinations);
		}

		if (!$tables)
		{
			// something went wrong...
			return array();
		}

		return $tables;
	}

	/**
	 * Counts the total number of participants for each table on
	 * the specified search arguments.
	 *
	 * @return 	array 	An associative array of occurrences.
	 */
	public function getTablesOccurrence()
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		// calculate total number of people hosted by the table
		$q = $dbo->getQuery(true)
			->select($dbo->qn('r.id_table'))
			->select('SUM(' . $dbo->qn('r.people') . ') AS ' . $dbo->qn('total'))
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')')
			->andWhere(array(
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$in_ts}  <  " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <  {$out_ts} AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " >= {$in_ts}  AND {$out_ts} >= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
			), 'OR')
			->group($dbo->qn('r.id_table'));

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no occurrences found
			return array();
		}

		$rows = array();

		// create associative array (TABLE ID -> COUNT)
		foreach ($dbo->loadObjectList() as $r)
		{
			$rows[$r->id_table] = (int) $r->total;
		}

		return $rows;
	}

	/**
	 * Returns the total number of seats available for booking.
	 * This is a sum of all the maximum capacities of the
	 * published tables in the requested period.
	 *
	 * @return 	integer  The total seats count.
	 */
	public function getSeatsCount()
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		$q = $dbo->getQuery(true);

		// SELECT data
		$q->select('SUM(' . $dbo->qn('t.max_capacity') . ') AS ' . $dbo->qn('total'));

		// load tables and rooms from DB
		$q->from($dbo->qn('#__vikrestaurants_table', 't'));
		$q->from($dbo->qn('#__vikrestaurants_room', 'rm'));

		// join the tables with the rooms
		$q->where($dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));

		// use publishing restrictions only for front-end users
		if (!$this->isAdmin())
		{
			$q->where($dbo->qn('rm.published') . ' = 1');
			$q->where($dbo->qn('t.published') . ' = 1');

			// search for room closure
			$closureQuery = $dbo->getQuery(true)
				->select('COUNT(1)')
				->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
				->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('rm.id'))
				->where($in_ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

			$q->where('(' . $closureQuery . ') = 0');
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// return total count
			return (int) $dbo->loadResult();
		}

		// no available tables
		return 0;
	}

	/**
	 * Returns a list of reservations for each table.
	 *
	 * @return 	array 	An associative array of reservations (Table -> Reservations[]).
	 */
	public function getReservations()
	{
		$dbo = JFactory::getDbo();

		// get average time of stay
		$avg = VREFactory::getConfig()->getUint('averagetimestay');

		// get reservation duration
		$duration = $this->getStayTime($avg);

		// fetch check-in and check-out
		$in_ts  = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);
		$out_ts = VikRestaurants::createTimestamp($this->date, $this->hour, (int) $this->min + $duration);

		$clusterQuery = $dbo->getQuery(true)
			->select($dbo->qn('ti.name'))
			->from($dbo->qn('#__vikrestaurants_reservation', 'ri'))
			->leftjoin($dbo->qn('#__vikrestaurants_table', 'ti') . ' ON ' . $dbo->qn('ri.id_table') . ' = ' . $dbo->qn('ti.id'))
			->where($dbo->qn('ri.id') . ' = ' . $dbo->qn('r.id_parent'));

		// calculate total number of people hosted by the table
		$q = $dbo->getQuery(true)
			->select('r.*')
			->select($dbo->qn('c.code'))
			->select($dbo->qn('c.icon', 'code_icon'))
			->select($dbo->qn('os.notes', 'code_notes'))
			->select(sprintf(
				'IF (%s = 0, NULL, (%s)) AS %s',
				$dbo->qn('r.id_parent'),
				$clusterQuery,
				$dbo->qn('parent_table')
			))
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->leftjoin($dbo->qn('#__vikrestaurants_res_code', 'c') . ' ON ' . $dbo->qn('r.rescode') . ' = ' . $dbo->qn('c.id'))
			->leftjoin(
				$dbo->qn('#__vikrestaurants_order_status', 'os') . ' ON ' . $dbo->qn('os.id_order') . ' = ' . $dbo->qn('r.id') . 
				' AND ' . $dbo->qn('os.id_rescode') . ' = ' . $dbo->qn('r.rescode') . 
				' AND ' . $dbo->qn('os.group') . ' = 1'
			)
			->where($dbo->qn('r.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')')
			->andWhere(array(
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$in_ts}  <  " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <  {$out_ts} AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " <= {$in_ts}  AND {$out_ts} <= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
				'(' . $dbo->qn('r.checkin_ts') . " >= {$in_ts}  AND {$out_ts} >= " . $dbo->qn('r.checkin_ts') . " + IF(" . $dbo->qn('r.stay_time') . " > 0, " . $dbo->qn('r.stay_time') . ", {$avg}) * 60)\n",
			), 'OR');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObjectList();	
		}

		// no reservations found
		return array();
	}

	/**
	 * Returns a list of rooms and the related tables.
	 *
	 * @return 	array
	 */
	public function getRooms()
	{
		$dbo = JFactory::getDbo();

		$ts = VikRestaurants::createTimestamp($this->date, $this->hour, $this->min);

		// fetch closure query
		$closure = $dbo->getQuery(true)
			->select('1')
			->from($dbo->qn('#__vikrestaurants_room_closure', 'rc'))
			->where($dbo->qn('rc.id_room') . ' = ' . $dbo->qn('r.id'))
			->where($ts . ' BETWEEN ' . $dbo->qn('rc.start_ts') . ' AND ' . $dbo->qn('rc.end_ts'));

		$q = $dbo->getQuery(true);

		// select room details
		$q->select('r.*');
		// check if the room is closed
		$q->select('(' . $closure . ') AS ' . $dbo->qn('is_closed'));

		// load rooms table
		$q->from($dbo->qn('#__vikrestaurants_room', 'r'));

		// sort rooms
		$q->order($dbo->qn('r.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no available rooms
			return array();
		}

		$rooms = array();

		// iterate rooms
		foreach ($dbo->loadObjectList() as $r)
		{
			// retrieve all the room tables
			$r->tables = $this->getRoomTables($r->id);

			// copy room within the list
			$rooms[] = $r;
		}

		return $rooms;
	}

	/**
	 * Returns a list of suggested times before and after
	 * the selected checkin time.
	 *
	 * @param 	integer  $n  The maximum number of hints to display.
	 *
	 * @return 	array    A list of hints.
	 */
	public function getSuggestedTimes($n = 2)
	{
		$index = null;
		$shift = $this->getTimeShift($index);

		if (!$shift || is_null($index))
		{
			// no suggested hints
			return array();
		}

		$before = $after = array();

		$tmp = $index;

		// iterate backward as long as it is possible and
		// until we fetched the first N available times
		while (count($before) !== $n && !empty($shift[$tmp - 1]))
		{
			$tmp--;
			// check if prev slot is available 
			$search = new static($this->date, $shift[$tmp]->value, $this->people);
			// find available tables
			$avail = $search->getAvailableTables();

			// free space
			unset($search);

			// make sure there is at least a standard table available
			if ($avail && !$avail[0]->multi_res)
			{
				// register time (at the beginning) as hint
				array_unshift($before, $shift[$tmp]->value);
			}
		}

		// push NULL value at the beginning until
		// the length of the array reached the
		// requested amount
		while (count($before) !== $n)
		{
			array_unshift($before, null);
		}

		$tmp = $index;

		// iterate afterward as long as it is possible and
		// until we fetched the first N available times
		while (count($after) !== $n && !empty($shift[$tmp + 1]))
		{
			$tmp++;
			// check if prev slot is available 
			$search = new static($this->date, $shift[$tmp]->value, $this->people);
			// find available tables
			$avail = $search->getAvailableTables();

			// free space
			unset($search);

			// make sure there is at least a standard table available
			if ($avail && !$avail[0]->multi_res)
			{
				// register time (at the end) as hint
				array_push($after, $shift[$tmp]->value);
			}
		}

		// push NULL value at the end until
		// the length of the array reached the
		// requested amount
		while (count($after) !== $n)
		{
			array_push($after, null);
		}

		// merge fetched hints
		$hints = array_merge($before, $after);

		// get current date and time
		$now = VikRestaurants::now();

		// unset hints that are in the past
		foreach ($hints as &$hint)
		{
			if ($hint)
			{
				// fetch hint timestamp
				list($hour, $min) = explode(':', $hint);
				$ts = VikRestaurants::createTimestamp($this->date, $hour, $min);

				if ($now <= $ts)
				{
					// valid time, obtain a time object
					$hint = JHtml::_('vikrestaurants.min2time', $hour * 60 + $min, $string = false);
					// include timestamp
					$hint->ts = $ts;
				}
				else
				{
					// the hint time is before the current time, unset it
					$hint = null;
				}
			}
		}

		return $hints;
	}

	/**
	 * Returns the shift array to which the time belong.
	 *
	 * @param 	integer  &$index  This arguments will be filled with the
	 * 							  index of the current time.
	 *
	 * @return 	mixed    The shift array on success, null otherwise.
	 */
	protected function getTimeShift(&$index = null)
	{
		// get available times
		$times = JHtml::_('vikrestaurants.times', $restaurant = 1, $this->date);

		// calculate time in minutes
		$hm = (int) $this->hour * 60 + (int) $this->min;

		// iterate all working shifts
		foreach ($times as $k => $shift)
		{
			// iterate all times in shift
			foreach ($shift as $i => $time)
			{
				$tmp = explode(':', $time->value);

				// calculate shift time in minutes
				$hm2 = (int) $tmp[0] * 60 + (int) $tmp[1];

				// check if the times match
				if ($hm == $hm2)
				{
					// register index
					$index = $i;

					// return shift found
					return $shift;
				}
			}
		}

		return null;
	}

	/**
	 * Load tables statically to avoid loading them more than once.
	 *
	 * @return 	array  A list of available tables.
	 */
	protected static function loadTables()
	{
		// check if tables are already cached
		if (static::$_tables === null)
		{
			$dbo = JFactory::getDbo();

			static::$_tables = array();

			$q = $dbo->getQuery(true)
				->select('t.*')
				->select($dbo->qn('r.name', 'room_name'))
				->from($dbo->qn('#__vikrestaurants_table', 't'))
				->from($dbo->qn('#__vikrestaurants_room', 'r'))
				->where($dbo->qn('r.id') . ' = ' . $dbo->qn('t.id_room'))
				->order($dbo->qn('r.ordering') . ' ASC')
				->order($dbo->qn('t.id') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$tables = $dbo->loadObjectList();

				// get all clusters
				$q = $dbo->getQuery(true)
					->select('*')
					->from($dbo->qn('#__vikrestaurants_table_cluster'));

				$dbo->setQuery($q);
				$dbo->execute();

				$clusters = $dbo->getNumRows() ? $dbo->loadObjectList() : array();

				foreach ($tables as $table)
				{
					$table->cluster = array();

					// recover all the tables linked to the current one
					foreach ($clusters as $cl)
					{
						// make sure the current table belongs to this cluster
						if ($cl->id_table_1 == $table->id || $cl->id_table_2 == $table->id)
						{
							$table->cluster[] = $cl->id_table_1;
							$table->cluster[] = $cl->id_table_2;
						}
					}

					// strip duplicated values
					$table->cluster = array_values(array_unique($table->cluster));

					// sort cluster by ascending table ID
					sort($table->cluster, SORT_NUMERIC);

					// register table by ID
					static::$_tables[$table->id] = $table;
				}
			}
		}

		return static::$_tables;
	}

	/**
	 * Returns a cluster containing all the tables that can
	 * be linked to the specified table.
	 *
	 * @param 	integer  $id       The table ID.
	 * @param 	boolean  $include  True to include the requested table.
	 *
	 * @return 	array 	 A list of tables.
	 */
	public static function getTablesCluster($id, $include = false)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select(sprintf(
				'IF (%1$s = %3$s, %2$s, %1$s)',
				$dbo->qn('id_table_1'),
				$dbo->qn('id_table_2'),
				(int) $id
			))
			->from($dbo->qn('#__vikrestaurants_table_cluster'))
			->where(array(
				$dbo->qn('id_table_1') . ' = ' . (int) $id,
				$dbo->qn('id_table_2') . ' = ' . (int) $id,
			), 'OR');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			return array();
		}

		$list = $dbo->loadColumn();

		if ($include)
		{
			// push specified ID as first
			array_unshift($list, (int) $id);
		}

		return array_values(array_unique($list));
	}
}
