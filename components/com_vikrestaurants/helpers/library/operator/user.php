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
 * Class wrapper for operator record.
 *
 * @since 1.8
 */
class VREOperatorUser extends JObject
{
	/**
	 * A list of self instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Returns a user instance, only creating it if it doesn't
	 * already exist.
	 *
	 * @param 	integer  $user_id  The CMS user ID. If not specified,
	 * 							   the current user will be used.
	 *
	 * @return 	self
	 */
	public static function getInstance($user_id = 0)
	{
		// force INT cast for user ID
		$user_id = (int) $user_id;

		if (!isset(static::$instances[$user_id]))
		{
			// create a new instance
			static::$instances[$user_id] = new static($user_id);
		}

		return static::$instances[$user_id];
	}

	/**
	 * Returns a user instance, only creating it if it doesn't
	 * already exist.
	 *
	 * @param 	mixed  $user_id  The CMS user ID. If not specified,
	 * 							 the current user will be used.
	 *
	 * @return 	self
	 *
	 * @throws 	Exception
	 */
	public function __construct($user_id = 0)
	{
		if (!$user_id)
		{
			// retrieve current user
			$user = JFactory::getUser();

			if ($user->guest)
			{
				// raise exception in case of login
				throw new Exception('Login is mandatory in order to access as operator', 403);
			}

			// keep user ID
			$user_id = $user->id;
		}

		if (is_scalar($user_id))
		{
			// load operator data
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_operator'))
				->where($dbo->qn('jid') . ' = ' . (int) $user_id);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows() == 0)
			{
				throw new Exception('The specified user is not an operator', 403);
			}

			$data = $dbo->loadObject();
		}
		else
		{
			// the specified argument was an object
			$data = $user_id;
		}
		
		// construct parent with operator details
		parent::__construct($data);

		// decode products list
		$products = array_values(array_filter(explode(',', $this->get('products', ''))));
		// re-assign array of products
		$this->set('products', $products);
	}

	/**
	 * Magic method used to access internal properties.
	 *
	 * @param 	string  $name  The property name.
	 *
	 * @return  mixed   The preoprty value if exists, null otherwise.  
	 */
	public function __get($name)
	{
		return $this->get($name, null);
	}

	/**
	 * Checks whether the operator can login within the front-end private area.
	 *
	 * @return 	boolean  True if allowed, false otherwise.
	 */
	public function canLogin()
	{
		return $this->get('can_login', false);
	}

	/**
	 * Checks whether the operator is allowed to access the restaurant section.
	 *
	 * @return 	boolean  True if allowed, false otherwise.
	 */
	public function isRestaurantAllowed()
	{
		// get group
		$group = $this->get('group', null);

		// check if restaurant is allowed (0: all, 1: restaurant)
		return $group == 0 || $group == 1;
	}

	/**
	 * Checks whether the operator is allowed to access the take-away section.
	 *
	 * @return 	boolean  True if allowed, false otherwise.
	 */
	public function isTakeawayAllowed()
	{
		// get group
		$group = $this->get('group', null);

		// check if takeaway is allowed (0: all, 2: takeaway)
		return $group == 0 || $group == 2;
	}

	/**
	 * Checks whether the operator is allowed to access the specified group.
	 *
	 * @param 	integer  $group  The group to check (1: restaurant, 2: take-away).
	 *
	 * @return 	boolean  True if allowed, false otherwise.
	 */
	public function isGroupAllowed($group)
	{
		if ($group == 1)
		{
			return $this->isRestaurantAllowed();
		}

		return $this->isTakeawayAllowed();
	}

	/**
	 * Checks whether the operator actions should be tracked.
	 *
	 * @return 	boolean  True if trackable, false otherwise.
	 */
	public function isTrackable()
	{
		return (bool) $this->get('keep_track', true);
	}

	/**
	 * Checks whether the operator should be notified when a new
	 * reservation/order is made.
	 *
	 * @return 	boolean  True if notifiable, false otherwise.
	 */
	public function shouldBeNotified()
	{
		return (bool) $this->get('mail_notifications', false);
	}

	/**
	 * Checks whether the operator is allowed to read from the specified task.
	 * 
	 * @param 	string 	 $task  The task to look for.
	 *
	 * @return 	boolean  True if readable, false otherwise.
	 */
	public function canRead($task)
	{
		// 0: denied, 1: see only, 2: allowed
		return (int) $this->get('manage_' . $task, 0) > 0;
	}

	/**
	 * Checks whether the operator is allowed to manage the specified task.
	 * 
	 * @param 	string 	 $task  The task to look for.
	 *
	 * @return 	boolean  True if readable, false otherwise.
	 */
	public function canManage($task)
	{
		// 0: denied, 1: see only, 2: allowed
		return (int) $this->get('manage_' . $task, 0) == 2;
	}

	/**
	 * Checks whether the operator can see all the reservations.
	 *
	 * @return 	boolean  True if allowed, false otherwise.
	 */
	public function canSeeAll()
	{
		return $this->get('allres', false);
	}

	/**
	 * Checks whether the operator is allowed to handle a
	 * self-assignment. In case an ID is provided, it will
	 * check whether that reservation is assignable.
	 * 
	 * @param 	mixed 	 $id  An optional reservation ID.
	 *
	 * @return 	boolean  True if assignable, false otherwise.
	 */
	public function canAssign($id = null)
	{
		// first of all check permissions
		if (!$this->get('assign'))
		{
			// the operator is unable to handle self-assignments
			return false;
		}

		if ($id)
		{
			// check if the specified reservation can be taken
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn('id_operator'))
				->select($dbo->qn('id_table'))
				->from($dbo->qn('#__vikrestaurants_reservation'))
				->where($dbo->qn('id') . ' = ' . (int) $id);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				// reservation not found
				return false;
			}

			$reservation = $dbo->loadObject();

			if (!in_array($reservation->id_operator, array(0, (int) $this->get('id'))))
			{
				// reservation already assigned to a different operator
				return false;
			}

			if (!$this->canAccessTable($reservation->id_table))
			{
				// the room in which the table is located cannot be accessed
				return false;
			}
		}

		// operator can handle the assignment
		return true;
	}

	/**
	 * Checks whether the operator is allowed to access
	 * the room in which the specified table is placed.
	 * 
	 * @param 	mixed 	 $id  The table ID to check.
	 *
	 * @return 	boolean  True if accessible, false otherwise.
	 */
	public function canAccessTable($id)
	{
		if (!$id || !$this->isRestaurantAllowed())
		{
			// restaurant not allowed
			return false;
		}

		// retrieve room of the table
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_room'))
			->from($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// table not found
			return false;
		}

		// check if the room is accessible
		return $this->canAccessRoom((int) $dbo->loadResult());
	}

	/**
	 * Checks whether the operator is allowed to access
	 * the specified room.
	 * 
	 * @param 	mixed 	 $id  The room ID to check.
	 *
	 * @return 	boolean  True if accessible, false otherwise.
	 */
	public function canAccessRoom($id)
	{
		if (!$id || !$this->isRestaurantAllowed())
		{
			// restaurant not allowed
			return false;
		}

		// get supported rooms
		$rooms = $this->get('rooms');

		if (!$rooms)
		{
			// all rooms allowed
			return true;
		}

		// look for the room in the list
		return in_array((int) $id, explode(',', $rooms));
	}

	/**
	 * Returns a list of rooms to which the operator can access.
	 *
	 * @return 	array 	A list of rooms.
	 */
	public function getRooms()
	{
		if (!$this->isRestaurantAllowed())
		{
			// restaurant not allowed
			return array();
		}

		// load all the rooms (unpublished too)
		$list = JHtml::_('vikrestaurants.rooms', true);

		// get assigned rooms
		$rooms = $this->get('rooms');

		if (!$rooms)
		{
			// operator can access all rooms
			return $list;
		}

		// explode rooms to obtain a list
		$rooms = explode(',', $rooms);

		// filter the list to keep only the assigned rooms
		$list = array_filter($list, function($room) use ($rooms)
		{
			// make sure the room is included
			return in_array($room->value, $rooms);
		});

		return array_values($list);
	}

	/**
	 * Checks whether the tags of the specified product
	 * are accessible by the operator.
	 *
	 * @param 	string 	 $tags  A comma-separated list of tags.
	 *
	 * @return 	boolean  True in case it is possible to access tags, false otherwise.
	 */
	public function canSeeProduct($tags)
	{
		if (!$tags)
		{
			// the product haven't been assigned to any tags
			return true;
		}

		if (!$this->get('products'))
		{
			// operator can access any products
			return true;
		}

		// explode tags
		$tags = explode(',', $tags);

		// check whether the intersection between the specified tags
		// the the tags assigned to the operator returns at least a tag
		return (bool) array_intersect($tags, $this->get('products'));
	}
}
