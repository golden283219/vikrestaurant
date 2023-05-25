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

VRELoader::import('library.operator.user');

/**
 * Helper class used to track the operator logs.
 *
 * Whenever a method asks to specify a group, it is possible to use
 * only one of the following values:
 * - "0" for restaurant reservations;
 * - "1" for take-away orders.
 *
 * @since 1.8
 */
class VREOperatorLogger
{
	/**
	 * An array containing the cached reservations.
	 * The first-level children can be only the groups:
	 * - 0 restaurants;
	 * - 1 take-away.
	 *
	 * @var array
	 */
	protected $cache = array();

	/**
	 * The singleton instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Returns an instance of this class, only creating it
	 * if it doesn't already exist.
	 *
	 * @return 	self
	 */
	public static function getInstance()
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Caches internally the specified order details.
	 *
	 * @param 	mixed    $order  Either the order ID or the record.
	 * @param 	integer  $group  The group to which the order belong.
	 *
	 * @return 	self 	 This object to support chaining.
	 *
	 * @uses 	load()
	 */
	public function cache($order, $group)
	{
		if (is_scalar($order))
		{
			// retrieve order details is the ID was passed
			$order = $this->load($order, $group);
		}

		// validate specified group
		$group = $group == 1 ? 1 : 0;

		// make sure the order exists before caching it
		if ($order)
		{
			// make sure the group is set
			if (!isset($this->cache[$group]))
			{
				$this->cache[$group] = array();
			}

			// cache the order
			$this->cache[$group][$order->id] = $order;
		}

		return $this;
	}

	/**
	 * Checks if the specified order is already cached and returns it.
	 *
	 * @param 	mixed    $order  Either the order ID or the record.
	 * @param 	integer  $group  The group to which the order belong.
	 *
	 * @return 	mixed    The  
	 */
	public function getCached($order, $group)
	{
		if ($order)
		{
			if (!is_scalar($order))
			{
				$order = $order->id;
			}

			// validate specified group
			$group = $group == 1 ? 1 : 0;

			// check if the order is set
			if (isset($this->cache[$group][$order]))
			{
				// returned cached
				return $this->cache[$group][$order];
			}
		}

		return null;
	}

	/**
	 * Loads from the database the specified order/reservations.
	 *
	 * @param 	integer  $id     The order ID.
	 * @param 	integer  $group  The group to which the order belong.
	 *
	 * @return 	mixed 	 The order object is exists, null otherwise.
	 */
	public function load($id, $group)
	{
		if ((int) $id <= 0)
		{
			// do not need to query in case of invalid ID
			return null;
		}

		// ignore cache when retrieving the order because the same details might
		// have been already retrieved to keep the previous information

		if ($group == 1)
		{
			// get take-away order
			$order = VREOrderFactory::getOrder($id, null, array('ignore_cache' =>  true, 'translate' => false));
		}
		else
		{
			// get restaurant reservation
			$order = VREOrderFactory::getReservation($id, null, array('ignore_cache' =>  true, 'translate' => false));
		}

		return $order;
	}

	/**
	 * Generates a log based on the specified order details.
	 * The fetched log will be automatically stored and assigned
	 * to the current operator.
	 *
	 * @param 	mixed    $order  Either the order ID or the record.
	 * @param 	integer  $group  The group to which the order belong.
	 * @param 	mixed    $log    The log subject. If not specified, it
	 * 							 will be automatically fetched.
	 *
	 * @return 	mixed    The log ID on success, false otherwise.
	 *
	 * @uses 	store()
	 */
	public function generate($order, $group, $log = null)
	{
		// get cached order, if any
		$prev = $this->getCached($order, $group);

		// load order in case an ID was passed
		if (is_scalar($order))
		{
			// load order
			$order = $this->load($order, $group);
		}

		try
		{
			// retrieve operator data
			$operator = VREOperatorUser::getInstance();
		}
		catch (Exception $e)
		{
			// do not proceed because the current user is not an operator
			return false;
		}

		if (!$operator->isTrackable())
		{
			// do not proceed in case the operator shouldn't be tracked
			return false;
		}

		$data = array();

		// import order comparator helper class
		VRELoader::import('library.order.comparator');

		// fetch differences
		$diff = VREOrderComparator::diff($order, $prev);

		if ($diff)
		{
			// add differences to JSON data
			$data['diff'] = $diff;
		}

		// find items differences
		$items = VREOrderComparator::diffItems($order, $prev);

		if ($items)
		{
			// add items differences to JSON data
			$data['items'] = $items;
		}

		// fetch log if empty
		if (!$log)
		{
			// try to detect a log subject for the restaurant
			if ($group == 0)
			{
				// if the previous order is null, the order didn't exist
				if ($prev == null)
				{
					// reservation created
					$log = 'VROPLOGRESTAURANTINSERT';
				}
				// if the current order is null, the order has probably been deleted
				else if ($order == null)
				{
					// reservation deleted
					$log = 'VROPLOGRESTAURANTDELETE';
				}
				// use specific text in case the "diff" attribute contains only the "table" key
				else if (isset($data['diff']) && array_keys($data['diff']) == array('table'))
				{
					// table changed
					$log = 'VROPLOGRESTAURANTTABLECHANGED';
				}
				// use specific text in case the "diff" attribute contains only the "status" key (set to CONFIRMED)
				else if (isset($data['diff']) && array_keys($data['diff']) == array('status') && $order->status == 'CONFIRMED')
				{
					// reservation confirmed
					$log = 'VROPLOGRESTAURANTCONFIRMED';
				}
				// fallback to update
				else
				{
					// reservation updated
					$log = 'VROPLOGRESTAURANTUPDATE';
				}
			}
			// try to detect a log subject for the take-away
			else
			{
				// if the previous order is null, the order didn't exist
				if ($prev == null)
				{
					// order created
					$log = 'VROPLOGTAKEAWAYINSERT';
				}
				// if the current order is null, the order has probably been deleted
				else if ($order == null)
				{
					// order deleted
					$log = 'VROPLOGTAKEAWAYDELETE';
				}
				// use specific text in case the "diff" attribute contains only the "status" key (set to CONFIRMED)
				else if (isset($data['diff']) && array_keys($data['diff']) == array('status') && $order->status == 'CONFIRMED')
				{
					// order confirmed
					$log = 'VROPLOGTAKEAWAYCONFIRMED';
				}
				// fallback to update
				else
				{
					// order updated
					$log = 'VROPLOGTAKEAWAYUPDATE';
				}
			}
		}

		// check if the log exceeds the maximum limit of 256 characters
		if (strlen($log) > 256)
		{
			// include the remaining log subject
			$data['readmore'] = substr($log, 256);
			// keep first 256 characters in subject
			$log = substr($log, 0, 256);
		}

		// update order cache after storing the log
		$this->cache($order, $group);

		return $this->store($operator->id, $log, $group, $order->id, $data);
	}

	/**
	 * Stores the log.
	 *
	 * @param 	integer  $operator  The operator ID.
	 * @param 	string   $log       The log subject.
	 * @param 	integer  $group     The group of the log.
	 * @param 	mixed 	 $order     The order ID.
	 * @param 	mixed    $data      The order data.
	 *
	 * @return 	mixed    The log ID on success, false otherwise.
	 */
	public function store($operator, $log, $group, $order = 0, $data = null)
	{
		$record = new stdClass;
		// set operator ID
		$record->id_operator = $operator;
		// set order ID
		$record->id_reservation = (int) $order;
		// set log subject (MAX 256 characters)
		$record->log = $log;
		// set log to current time
		$record->createdon = VikRestaurants::now();
		// set group
		$record->group = $group == 1 ? 2 : 1;
		
		if ($data)
		{
			// set the order JSON data
			$record->content = is_string($data) ? $data : json_encode($data);
		}

		// save log
		$res = JFactory::getDbo()->insertObject('#__vikrestaurants_operator_log', $record, 'id');

		if ($res && !empty($record->id))
		{
			// return log ID on success
			return $record->id;
		}

		// an error occurred
		return false;
	}
}
