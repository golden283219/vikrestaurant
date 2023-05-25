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

VRELoader::import('library.specialdays.specialday');

/**
 * Helper class used to handle the special days.
 *
 * @since 1.8
 */
class VRESpecialDaysManager
{
	/**
	 * A cache of results to avoid executing
	 * the same query more the once during
	 * the cycle lifetime.
	 *
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * The group to which the special days belong.
	 *
	 * @var integer|null
	 */
	protected $group;

	/**
	 * The publishing start date as UNIX timestamp.
	 *
	 * @var integer|null
	 */
	protected $startDate = null;

	/**
	 * The publishing end date as UNIX timestamp.
	 *
	 * @var integer|null
	 */
	protected $endDate = null;

	/**
	 * Flag used to check whether the publishing dates 
	 * are strict or not. When this flag is turned on,
	 * the records with empty publishing dates will be
	 * ignored.
	 *
	 * @var boolean
	 */
	protected $strictDates = false;

	/**
	 * The checkin time expressed as HH:mm string.
	 *
	 * @var string|null
	 */
	protected $time = null;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed  $group  The group to which the records belong (1: restaurant, 2: takeaway).
	 */
	public function __construct($group = null)
	{
		if (!is_null($group))
		{
			if ($group == 1 || $group == 'restaurant')
			{
				// restaurant group
				$this->group = 1;
			}
			else
			{
				// take-away group
				$this->group = 2;
			}
		}
		else
		{
			// no group filtering
			$this->group = null;
		}
	}

	/**
	 * Returns the specified group identifier.
	 *
	 * @return 	mixed
	 *
	 * @since 	1.8.2
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Sets the publishing start date.
	 *
	 * @param 	mixed 	 $date    The publishing start date/timestamp.
	 * @param 	boolean  $strict  True to retrieve only records with
	 * 							  a publishing date set.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setStartDate($date = null, $strict = false)
	{
		if ($date)
		{
			if (is_string($date))
			{
				// create start timestamp (at 00:00:00)
				$date = VikRestaurants::createTimestamp($date, 0, 0);
			}

			// set start date
			$this->startDate = $date;

			// enable/disable strict mode
			$this->strictDates = (bool) $strict;
		}
		else
		{
			// turn off strict dates
			$this->startDate   = null;
			$this->strictDates = false;
		}

		return $this;
	}

	/**
	 * Sets the publishing end date.
	 *
	 * @param 	mixed 	 $date    The publishing end date/timestamp.
	 * @param 	boolean  $strict  True to retrieve only records with
	 * 							  a publishing date set.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function setEndDate($date = null, $strict = false)
	{
		if ($date)
		{
			if (is_string($date))
			{
				// create end timestamp (at 23:59:59)
				$date = VikRestaurants::createTimestamp($date, 23, 59);
			}

			// set end date
			$this->endDate = $date;

			// enable/disable strict mode
			$this->strictDates = (bool) $strict;
		}
		else
		{
			// turn off strict dates
			$this->endDate     = null;
			$this->strictDates = false;
		}

		return $this;
	}

	/**
	 * Sets the checkin time to exclude the special days with
	 * working shifts that don't match the specified one.
	 *
	 * @param 	mixed 	$hour  Either the hour or a time string (HH:mm).
	 * @param 	mixed 	$min   The minutes in case the hour was passed.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setCheckinTime($hour, $min = null)
	{
		if (is_null($min))
		{
			// set time string
			$this->time = $hour;
		}
		else
		{
			// build time string
			$this->time = (int) $hour . ':' . (int) $min;
		}
	

		return $this;
	}

	/**
	 * Returns the first available special day.
	 *
	 * @return 	mixed  The special day object, null otherwise.
	 */
	public function getFirst()
	{
		// get list of special days
		$list = $this->getList();

		if ($list)
		{
			// return first one
			return $list[0];
		}

		// none available
		return null;
	}

	/**
	 * Returns a list of special days matching the specified parameters.
	 *
	 * @return 	array   A list of special days.
	 */
	public function getList()
	{
		// generate cache signature
		$sign = serialize(get_object_vars($this));

		if (!isset(static::$cache[$sign]))
		{
			static::$cache[$sign] = array();

			$dispatcher = VREFactory::getEventDispatcher();

			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true);

			$q->select('s.*');
			$q->from($dbo->qn('#__vikrestaurants_specialdays', 's'));
			$q->where(1);

			// filter by group
			if ($this->group)
			{
				$q->where($dbo->qn('s.group') . ' = ' . (int) $this->group);
			}

			// filter by start date
			if ($this->startDate)
			{
				if ($this->strictDates)
				{
					// start date must be included within the publishing dates
					$q->where($this->startDate . ' BETWEEN ' . $dbo->qn('s.start_ts') . ' AND ' . $dbo->qn('s.end_ts'));
				}
				else
				{
					// start date must be included within the publishing dates, or at least empty
					$q->andWhere(array(
						$dbo->qn('s.start_ts') . ' <= 0',
						$this->startDate . ' BETWEEN ' . $dbo->qn('s.start_ts') . ' AND ' . $dbo->qn('s.end_ts'),
					), 'OR');
				}
			}

			// filter by end date
			if ($this->endDate)
			{
				if ($this->strictDates)
				{
					// end date must be included within the publishing dates
					$q->where($this->endDate . ' BETWEEN ' . $dbo->qn('s.start_ts') . ' AND ' . $dbo->qn('s.end_ts'));
				}
				else
				{
					// end date must be included within the publishing dates, or at least empty
					$q->andWhere(array(
						$dbo->qn('s.end_ts') . ' <= 0',
						$this->endDate . ' BETWEEN ' . $dbo->qn('s.start_ts') . ' AND ' . $dbo->qn('s.end_ts'),
					), 'OR');
				}
			}

			// Always exclude expired special days.
			// In case publishing dates are set, the
			// ending date must be higher than the current time.
			$q->andWhere(array(
				$dbo->qn('s.end_ts') . ' <= 0',
				$dbo->qn('s.end_ts') . ' > ' . VikRestaurants::now(),
			), 'OR');

			// always take special days with higher priority first
			$q->order($dbo->qn('s.priority') . ' DESC');
			// take newer special days first
			$q->order($dbo->qn('s.id') . ' DESC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$priority = null;

				foreach ($dbo->loadObjectList() as $day)
				{
					// create new special day object
					$sd = VRESpecialDay::getInstance($day);

					$ok = true;

					// check if we should filter the special days by time
					if (!is_null($this->time) && $this->time !== '')
					{
						$ok = $ok && $sd->isAvailableAtTime($this->time);
					}

					// check if we should filter the special days by week day
					if ($this->startDate && !$this->endDate)
					{
						// filter by week day only if we are not retrieveing a period
						$ok = $ok && $sd->isAvailableOnDay($this->startDate);
					}

					if ($ok)
					{
						/**
						 * Trigger event to let external plugins apply
						 * additional filters while seatching for a
						 * compatible special day.
						 *
						 * @param 	VRESpecialDaysManager  $manager  The manager instance.
						 * @param 	VRESpecialDay          $day      The special day instance.
						 *
						 * @return 	boolean  True to accept the special day, false to discard it.
						 *
						 * @since 	1.8.3
						 */
						$discard = $dispatcher->false('onSearchSpecialDays', array($this, $sd));

						// search for a plugin that returned false
						if ($discard)
						{
							// invalidate special day
							$ok = false;
						}
					}

					/**
					 * Make sure the returned special days own the same priority,
					 * as we cannot merge together LOW special days with HIGH
					 * special days.
					 *
					 * @since 1.7.4
					 */
					if ($ok)
					{
						if ($priority === null || $priority == $sd->priority)
						{
							// update priority
							$priority = $sd->priority;
						}
						else
						{
							// ignore special day
							$ok = false;
						}
					}

					// use special day only if supported
					if ($ok)
					{
						static::$cache[$sign][] = $sd;
					}
				}
			}
		}

		// return cached results
		return static::$cache[$sign];
	}
}
