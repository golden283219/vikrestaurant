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
 * Wrapper class to handle the properties of a special day.
 *
 * @since 1.8
 */
class VRESpecialDay implements JsonSerializable
{
	/**
	 * The special day ID.
	 *
	 * @var integer
	 */
	protected $id;

	/**
	 * The special day name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The special day group (1: restaurant, 2: takeaway).
	 *
	 * @var integer
	 */
	protected $group;

	/**
	 * The priority level.
	 * The higher the number, the higher the priority.
	 *
	 * @var integer
	 */
	protected $priority;

	/**
	 * The publishing start date of the special day.
	 *
	 * @var null|integer
	 */
	protected $startDate;

	/**
	 * The publishing end date of the special day.
	 *
	 * @var null|integer
	 */
	protected $endDate;

	/**
	 * Flag used to check whether the special day
	 * ignores the closing days.
	 *
	 * @var boolean
	 */
	protected $ignoreClosingDays;

	/**
	 * Flag used to check whether the special day
	 * should be highlighted on a calendar.
	 *
	 * @var boolean
	 */
	protected $markoncal;

	/**
	 * An array of working shifts available for the special day.
	 * If empty, all the special days have to be considered as
	 * available.
	 *
	 * @var array
	 */
	protected $shifts;

	/**
	 * An array of week days available for the special day.
	 * If empty, all the week days have to be considered as
	 * available.
	 *
	 * @var array
	 */
	protected $days;

	/**
	 * An array of images.
	 *
	 * @var array
	 */
	protected $images;

	/**
	 * A list of menus assigned to the special day.
	 *
	 * @var array
	 */
	protected $menus = null;

	/**
	 * Creates a new special day instance according
	 * to its group (restaurant or takeaway).
	 *
	 * @param 	mixed  $args  Either an array or an object containing
	 * 						  the properties of the special day record.
	 *
	 * @return  VRESpecialDay
	 */
	public static function getInstance($args)
	{
		$args = (object) $args;

		try
		{
			if (empty($args->group))
			{
				// missing group, raise error
				throw new RuntimeException('Missing group', 400);
			}

			$group = $args->group == 1 ? 'restaurant' : 'takeaway';

			// try to load children
			if (!VRELoader::import('library.specialdays.classes.' . $group))
			{
				// children file not found, raise error
				throw new RuntimeException(sprintf('Children [%s] special day not found', $group), 404);
			}

			// make sure the class exists
			$class = 'VRESpecialDay' . ucfirst($group);

			if (!class_exists($class))
			{
				// children not found, raise error
				throw new RuntimeException(sprintf('Children [%s] special day class not found', $class), 404);
			}

			// instantiate class
			$obj = new $class($args);

			// make sure the class is a valid instance
			if (!$obj instanceof VRESpecialDay)
			{
				// invalid instance, raise error
				throw new RuntimeException(sprintf('Children [%s] is not a valid instance', $class), 500);
			}
		}
		catch (Exception $e)
		{
			// catch any errors and use the default
			// class instead of breaking the flow
			$obj = new static($args);
		}

		return $obj;
	}

	/**
	 * Class constructor.
	 *
	 * @param 	mixed  $args  Either an array or an object containing
	 * 						  the properties of the special day record.
	 */
	public function __construct($args)
	{
		$config = VREFactory::getConfig();

		$args = (object) $args;

		// get ID
		$this->id = (int) $args->id;

		// get name
		$this->name = $args->name;

		// get group
		$this->group = (int) $args->group;

		// get start date
		$this->startDate = $args->start_ts > 0 ? date($config->get('dateformat'), $args->start_ts) : null;

		// get end date
		$this->endDate = $args->end_ts > 0 ? date($config->get('dateformat'), $args->end_ts) : null;

		// check if the special day should ignore closures
		$this->ignoreClosingDays = (bool) $args->ignoreclosingdays;

		// check if the special day should be marked on a calendar
		$this->markoncal = (bool) $args->markoncal;

		// get priority level
		$this->priority = (int) $args->priority;

		// get working shifts
		if ($args->working_shifts)
		{
			$this->shifts = explode(',', $args->working_shifts);

			// retrieve working shifts details
			$this->shifts = array_map(function($shift)
			{
				// from shift ID to time
				return JHtml::_('vikrestaurants.timeofshift', (int) $shift);
			}, $this->shifts);

			$this->shifts = array_filter($this->shifts);
			$this->shifts = array_values($this->shifts);
		}
		else
		{
			$this->shifts = array();
		}

		// get days filter
		if (strlen($args->days_filter))
		{
			$this->days = explode(',', $args->days_filter);
			$this->days = array_map('trim', $this->days);
			$this->days = array_map('intval', $this->days);
			// do not filter because 0 is an accepted value
			$this->days = array_values($this->days);
		}
		else
		{
			$this->days = array();
		}

		// get images
		if ($args->images)
		{
			$this->images = explode(';;', $args->images);
			$this->images = array_map('trim', $this->images);
			$this->images = array_filter($this->images);
			$this->images = array_values($this->images);
		}
		else
		{
			$this->images = array();
		}

		$dispatcher = VREFactory::getEventDispatcher();

		/**
		 * Trigger event to let external plugins construct
		 * the special day with those properties that they
		 * might have added.
		 *
		 * @param 	VRESpecialDay  &$day  The special day instance.
		 * @param 	object 		   $args  The database record of the special day.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.3
		 */
		$dispatcher->trigger('onInitSpecialDay', array(&$this, $args));
	}

	/**
	 * Magic method used to access internal properties.
	 *
	 * @param 	string 	$name  The property name.
	 *
	 * @return 	mixed 	The property value.
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);

		// check if we have a getter method first
		if (method_exists($this, $method))
		{
			return $this->{$method}();
		}
		// make sure the property exists, which cannot start with "_"
		else if (substr($name, 0, 1) !== '_' && property_exists($this, $name))
		{
			return $this->{$name};
		}

		// throw an exception
		throw new RuntimeException(sprintf('Missing [%s] special day property', $name), 404);
	}

	/**
	 * Checks whether the special day is available for
	 * the specified date.
	 *
	 * @param 	mixed 	 $date  Either a date string or a UNIX timestamp.
	 *
	 * @return 	boolean  True if available, false otherwise.
	 */
	public function isAvailableOnDate($date)
	{
		if ($this->startDate === null || $this->endDate === null)
		{
			// special day always available
			return true;
		}

		if (is_string($date))
		{
			// convert date string to timestamp
			$date = VikRestaurants::createTimestamp($date, 0, 0);
		}

		$start = VikRestaurants::createTimestamp($this->startDate, 0, 0);
		$end   = VikRestaurants::createTimestamp($this->endDate, 23, 59);

		// make sure the date is between the publishing dates
		return $start <= $date && $date <= $end;
	}

	/**
	 * Checks whether the special day is available for
	 * the specified day of the week.
	 *
	 * @param 	mixed 	 $day  Either a date string or a UNIX timestamp.
	 *
	 * @return 	boolean  True if available, false otherwise.
	 */
	public function isAvailableOnDay($day)
	{
		if (!$this->days)
		{
			// no days filtering, available for any week days
			return true;
		}

		// check if we received the day of the week [0-6]
		if (preg_match("/^[0-6]$/", $day))
		{
			$day = (int) $day;
		}
		else
		{
			if (is_string($day))
			{
				// convert date string to timestamp
				$day = VikRestaurants::createTimestamp($day, 0, 0);
			}

			// get day of the week from date
			$day = (int) date('w', $day);
		}

		// make sure the day is specified within the list
		return in_array($day, $this->days);
	}

	/**
	 * Checks whether the special day is available at
	 * the specified time.
	 *
	 * @param 	mixed 	 $time  Either a time string or a UNIX timestamp.
	 *
	 * @return 	boolean  True if available, false otherwise.
	 */
	public function isAvailableAtTime($time)
	{
		if (!$this->shifts)
		{
			// no working shifts, available for any time
			return true;
		}

		// check if we received a time string
		if (!preg_match("/^[0-9]{1,2}:[0-9]{1,2}$/", $time))
		{
			// extract time from timestamp
			$time = date('H:i', $time);
		}
		
		// extract hour and minutes from time
		list($hour, $min) = explode(':', $time);

		// calculate time in minutes
		$ts = (int) $hour * 60 + (int) $min;

		// iterate working shifts
		foreach ($this->shifts as $shift)
		{
			// make sure the time is included within the shift range
			if ($shift->from <= $ts && $ts <= $shift->to)
			{
				return true;
			}
		}

		// time not compatible
		return false;
	}

	/**
	 * Returns a list of menus assigned to the special day.
	 *
	 * @return 	array
	 */
	public function getMenus()
	{
		// check if the menus were already loaded
		if ($this->menus === null)
		{
			$dbo = JFactory::getDbo();

			// try to retrieve special day menus
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id_menu'))
				->from($dbo->qn('#__vikrestaurants_sd_menus'))
				->where($dbo->qn('id_spday') . ' = ' . $this->id)
				->order($dbo->qn('id_menu') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// get loaded menus (ID only)
				$this->menus = $dbo->loadColumn();
			}
			else
			{
				// no available menus
				$this->menus = array();
			}
		}

		return $this->menus;
	}

	/**
	 * Creates a standard object, containing all the supported properties,
	 * to be used when this class is passed to "json_encode()".
	 *
	 * @return  object
	 *
	 * @see     JsonSerializable
	 */
	public function jsonSerialize()
	{
		$obj = new stdClass;

		// get object variables
		foreach (get_object_vars($this) as $k => $v)
		{
			$obj->{$k} = $v;

			// Decomment the following line in case we need
			// to wake up the properties using the magic getter.
			// I prefer to directly obtain the property just to 
			// avoid querying the menus, which seem to be not
			// needed when encoding the special days in JSON format.
			// $obj->{$k} = $this->__get($k);
		}

		return $obj;
	}
}
