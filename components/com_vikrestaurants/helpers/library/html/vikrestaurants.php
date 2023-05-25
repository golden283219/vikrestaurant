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
 * VikRestaurants HTML utils helper.
 *
 * @since 1.8
 */
abstract class JHtmlVikrestaurants
{
	/**
	 * Lookup used to access readable times of working shifts.
	 *
	 * @param 	integer  $id  The shift ID to access.
	 *
	 * @return 	object   The shift object.
	 */
	public static function timeofshift($id)
	{
		static $shifts = false;

		// check if we have a cached list of working shifts
		if ($shifts === false)
		{
			$dbo = JFactory::getDbo();

			$time_format = VREFactory::getConfig()->get('timeformat');

			// retrieve all working shifts
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_shifts'));

			$dbo->setQuery($q);
			$dbo->execute();

			$shifts = array();

			if ($dbo->getNumRows())
			{
				// cache them
				foreach ($dbo->loadObjectList() as $s)
				{
					if (strlen($s->days))
					{
						// fetch selected days of the week
						$s->days = preg_split("/\s*,\s*/", $s->days);
					}
					else
					{
						// cover all days of the week
						$s->days = range(0, 6);
					}

					// extract from hour and min from TS
					$time = JHtml::_('vikrestaurants.min2time', $s->from, false, $time_format);

					$s->fromhour = $time->hour;
					$s->frommin  = $time->min;

					$s->fromtime = $time->format;

					// extract to hour and min from TS
					$time = JHtml::_('vikrestaurants.min2time', $s->to, false, $time_format);

					$s->tohour = $time->hour;
					$s->tomin  = $time->min;

					$s->totime = $time->format;

					$shifts[$s->id] = $s;
				}
			}
		}

		if (!isset($shifts[$id]))
		{
			// ID not found
			return null;
		}

		return $shifts[$id];
	}

	/**
	 * Converts a time expressed in minutes into hours and minutes.
	 * For example, the number '570' represents '9:30'.
	 *
	 * @param 	integer  $ts      The time in minutes.
	 * @param 	boolean  $string  True to return a formatted time.
	 * 							  Otherwise an object will be returned.
	 * @param 	string   $format  The time format. If not specified, the
	 * 							  default one will be used.
	 *
	 * @return 	mixed    A formatted time.
	 */
	public static function min2time($ts, $string = true, $format = null)
	{
		$time = new stdClass;

		// extract hour and min from TS
		$time->hour = floor($ts / 60);
		$time->min  = floor($ts % 60);

		// fetch time format
		$format = $format ? $format : VREFactory::getConfig()->get('timeformat');

		// format time
		$time->format = date($format, mktime($time->hour, $time->min, 0, 1, 1, 2000));

		if ($string)
		{
			// return formatted time
			return $time->format;
		}

		// return detailed object
		return $time;
	}

	/**
	 * Converts a time object in minutes.
	 * For example, the time '9:30' represents the number '570'.
	 *
	 * @param 	mixed     $time      The time object|string.
	 * @param 	property  $property  The object property to look for.
	 *
	 * @return 	integer  The time in minutes.
	 */
	public static function time2min($time, $property = 'value')
	{
		if (!is_scalar($time))
		{
			// extract time from object
			$time = (object) $time;
			$time = $time->{$property};
		}

		// extract hours and minutes from time
		$hm = explode(':', $time);

		return (int) $hm[0] * 60 + (int) $hm[1];
	}

	/**
	 * Checks whether the specified time is supported by
	 * the list of given shifts.
	 *
	 * @param 	mixed    $hourmin  Either a time object or a string.
	 * @param 	array    $shifts   A list of working shifts.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 *
	 * @since 	1.8.3
	 */
	public static function hastime($hourmin, array $shifts)
	{
		// convert time to ts
		$ts = static::time2min($hourmin);

		// iterate shifts
		foreach ($shifts as $shift)
		{
			// iterate times
			foreach ($shift as $time)
			{
				// compare times
				if (static::time2min($time) == $ts)
				{
					// time supported
					return true;
				}
			}
		}

		// time not supported
		return false;
	}

	/**
	 * Returns a list of supported times for the specified day.
	 *
	 * @param 	integer  $group  The application group (1: restaurant, 2: take-away)
	 * @param 	mixed    $day    The day to look for. If not specified,
	 * 						     the current day will be used.
	 * @param 	array 	 $args   An array of options.
	 *
	 * @return 	array    A list of times.
	 */
	public static function times($group, $day = null, array $args = array())
	{
		// create lookup to avoid fetching the same times more than once
		static $lookup = array();

		// create lookup key
		$sign = serialize(func_get_args());

		if (isset($lookup[$sign]))
		{
			// return cached version of times
			return $lookup[$sign];
		}

		$config = VREFactory::getConfig();

		// fetch minutes intervals
		$step   = $config->getUint($group == 1 ? 'minuteintervals' : 'tkminint');
		$format = $config->get('timeformat');

		// use steps specified in configuration array
		if (!empty($args['step']))
		{
			$step = (int) $args['step'];
		}

		$options = array();
		
		// Get shifts for the specified day.
		// In case of contiguous opening time, a list of
		// fictitious shifts will be returned.
		$shifts = self::shifts($group, $day, $strict = false, $args);

		// sort working whifts by opening time ASC
		usort($shifts, function($a, $b)
		{
			return $a->from - $b->from;
		});

		$dispatcher = VREFactory::getEventDispatcher();

		// iterate shifts
		foreach ($shifts as $shift)
		{
			if ($shift->showlabel)
			{
				$k = $shift->label ? $shift->label : $shift->name;
			}
			else
			{
				$k = 0;
			}

			if (!isset($options[$k]))
			{
				$options[$k] = array();
			}

			// scan times from opening to closing
			for ($ts = $shift->from; $ts <= $shift->to; $ts += $step)
			{
				// extract hour and min from time
				$time = self::min2time($ts, false, $format);

				/**
				 * Trigger event to let external plugins format the text to
				 * show within the options of the time dropdown.
				 *
				 * @param 	object   $time   The object holding the time details.
				 * @param 	integer  $step   The minutes between this time and the next one.
				 * @param 	object   $shift  The object holding details of the current shift.
				 * @param 	integer  $group  The section to which the times refer (1: restaurant, 2: take-away).
				 *
				 * @return 	string   The text to use for the option.
				 *
				 * @since 	1.8.4
				 */
				$text = $dispatcher->triggerOnce('onFormatTimeSelectOption', array($time, $step, $shift, $group));

				if (empty($text))
				{
					// no custom option, use default text
					$text = $time->format;
				}

				$options[$k][] = JHtml::_('select.option', $time->hour . ':' . $time->min, $text);
			}
		}

		// cache results
		$lookup[$sign] = $options;

		return $lookup[$sign];
	}

	/**
	 * Returns a list of supported shifts for the specified day.
	 * In case of contiguous opening time, a fictitious shift
	 * will be used (see $strict argument).
	 *
	 * @param 	integer  $group   The application group (1: restaurant, 2: take-away)
	 * @param 	mixed    $day     The day to look for. If not specified,
	 * 						      the current day will be used.
	 * @param 	boolean  $strict  False to include fictitious shifts in case of 
	 * 							  opening time mode.
	 * @param 	array 	 $args    An array of options.
	 *
	 * @return 	array    A list of shifts.
	 */
	public static function shifts($group, $day = null, $strict = true, array $args = array())
	{
		// extract timestamp from day
		if (is_null($day))
		{
			// use current date
			$day = VikRestaurants::now();
		}

		$shifts = array();

		if (!empty($args['closure']))
		{
			// check if the selected date is closed
			$closure = VikRestaurants::isClosingDay($day);
		}
		else
		{
			// do not look for a closure
			$closure = false;
		}

		// instantiate special days manager
		$sdManager = new VRESpecialDaysManager($group == 1 ? 'restaurant' : 'takeaway');

		// set date filter
		$sdManager->setStartDate($day);

		// get list of available special days
		$sdList = $sdManager->getList();

		if ($closure)
		{
			// iterate special days
			foreach ($sdList as $sd)
			{
				// check if the special day ignores any closing day
				if ($sd->ignoreClosingDays)
				{
					// unset closure
					$closure = false;
				}
			}

			// check if the closure have been overwritten
			if ($closure)
			{
				// the restaurant is closed
				return $shifts;
			}
		}

		// check if we have a contiguous opening
		if (VikRestaurants::isContinuosOpeningTime())
		{
			if ($strict)
			{
				// do not fetch shifts in case of contiguous opening time
				return $shifts;
			}

			$config = VREFactory::getConfig();

			$from = $config->getUint('hourfrom', 12);
			$to   = $config->getUint('hourto', 23);

			// fetch contiguous times
			if ($from <= $to)
			{
				$sh = new stdClass;
				$sh->from = $from * 60;
				$sh->to   = $to * 60;

				// push shift in list
				$shifts[] = $sh;
			}
			// the opening shift exceeds the midnight
			else
			{
				$sh = new stdClass;
				$sh->from = 0;
				$sh->to   = $to * 60;

				// push shift in list
				$shifts[] = $sh;

				$sh = new stdClass;
				$sh->from = $from * 60;
				$sh->to   = 1440 - $config->getUint($group == 1 ? 'minuteintervals' : 'tkminint');

				// push shift in list
				$shifts[] = $sh;
			}

			// create fictitious shifts
			foreach ($shifts as $k => $sh)
			{
				// extract hours and mins from TS
				$t1 = JHtml::_('vikrestaurants.min2time', $sh->from, false, $config->get('timeformat'));
				$t2 = JHtml::_('vikrestaurants.min2time', $sh->to, false, $config->get('timeformat'));

				$sh->id        = 0;
				$sh->name      = '';
				$sh->label     = '';
				$sh->showlabel = 0;
				$sh->group     = $group;

				$sh->fromhour = $t1->hour;
				$sh->frommin  = $t1->min;
				$sh->fromtime = $t1->format;

				$sh->tohour = $t2->hour;
				$sh->tomin  = $t2->min;
				$sh->totime = $t2->format;

				$shifts[$k] = $sh;
			}

			return $shifts;
		}
		
		// iterate special days
		foreach ($sdList as $sd)
		{
			// iterate special day workign shifts, if any
			foreach ($sd->shifts as $s)
			{
				// copy only if not yet set
				if (!isset($shifts[$s->id]))
				{
					$shifts[$s->id] = $s;
				}
			}
		}

		if ($shifts)
		{
			// return working shifts found
			return array_values($shifts);
		}

		// load all working shifts
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_shifts'))
			->where($dbo->qn('group') . ' = ' . (int) $group);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$shifts = $dbo->loadColumn();
		}

		// convert date string to timestamp
		if (!preg_match("/^[\d]+$/", (string) $day))
		{
			// create timestamp
			$day = VikRestaurants::createTimestamp($day, 0, 0);
		}

		// extract week day from timestamp
		$wd = date('w', $day);

		$list = array();

		foreach ($shifts as $s)
		{
			// get shift details
			$tmp = self::timeofshift($s);

			/**
			 * Take only the working shifts that can be used
			 * on the specified day.
			 *
			 * @since 1.8.3
			 */
			if ($tmp && in_array($wd, $tmp->days))
			{
				$list[] = $tmp;
			}
		}

		return $list;
	}

	/**
	 * Fetches the available times for the take-away pick-up and
	 * delivery as select options. Times in the past or not 
	 * available are not included by default.
	 *
	 * @param 	string 	$date     The check-in date.
	 * @param 	mixed 	$cart     The cart instance.
	 * @param 	array 	$options  A configuration array.
	 * 
	 * @return 	array 	A list of options.
	 */
	public static function takeawaytimes($date, $cart = null, array $options = array())
	{
		$config = VREFactory::getConfig();

		if (is_null($cart))
		{
			// get cart instance
			$cart = TakeAwayCart::getInstance();
		}

		// prepare availability search (time is not needed)
		$search = new VREAvailabilityTakeaway($date);

		/**
		 * Set selected service.
		 *
		 * @since 1.8.3
		 */
		$search->setDelivery($cart->getService());
		
		// find available times
		$shifts = $search->getAvailableTimes($cart);

		$times = array();

		// always show ASAP label if not specified
		$options['show_asap'] = isset($options['show_asap']) ? $options['show_asap'] : true;

		// flag used to show ASAP label only once
		$asap = false;

		// compare check-in date with current date or check if we should
		// hide the ASAP label according to the provided configuration
		if (!$options['show_asap'] || $search->get('date') != date($config->get('dateformat'), VikRestaurants::now()))
		{
			// hide ASAP label
			$asap = true;
		}

		// build times dropdown
		foreach ($shifts as $k => $shift)
		{
			// copy shift
			$times[$k] = array();

			foreach ($shift as $time)
			{
				// copy time only if not disabled
				if ($time->disable == false)
				{
					// clone time to avoid copying a referenced object
					$tmp = clone $time;

					if (!$asap)
					{
						// first option is marked as ASAP
						$tmp->text = JText::sprintf('VRTKTIMESELECTASAP', $tmp->text);

						$asap = true;
					}

					$times[$k][] = $tmp;
				}
			}

			// remove shift in case there are no available times
			if (count($times[$k]) == 0)
			{
				unset($times[$k]);
			}
		}

		return $times;
	}

	/**
	 * Returns a list containing all the number of
	 * participants that can be selected.
	 *
	 * @param 	boolean  $more  True to display the "MORE" option.
	 * 							If null, it will be displayed according
	 * 							to the current section.
	 *
	 * @return 	array    A list of options.
	 */
	public static function people($more = null)
	{
		$app    = JFactory::getApplication();
		$config = VREFactory::getConfig();

		// get min and max values allowed
		$min = $config->getUint('minimumpeople', 2);
		$max = $config->getUint('maximumpeople', 20);

		/**
		 * Extend people bounds if we are in the back-end.
		 *
		 * @since 1.8.1
		 */
		if ($app->isClient('administrator'))
		{
			// always start from one
			$min = 1;
			// multiply maximum number by 2
			$max *= 2;
		}

		$options = array();

		for ($people = $min; $people <= $max; $people++)
		{
			$options[] = JHtml::_('select.option', $people, JText::plural('VRE_N_PEOPLE', $people));
		}

		// check if we should display the large party label
		if (is_null($more) && $app->isClient('site') && $config->getBool('largepartylbl'))
		{
			// more argument not specified, we are in the front-end and the
			// large party url is configured to be displayed
			$more = true;
		}

		// display "MORE" option is specified
		if ($more)
		{
			$options[] = JHtml::_('select.option', -1, JText::_('VRLARGEPARTYLABEL'));
		}

		return $options;
	}

	/**
	 * Returns an array of week days.
	 *
	 * @param 	boolean  $short  True to use the short text.
	 *
	 * @return 	array 	 A list of days.
	 */
	public static function days($short = false)
	{
		$options = array();

		// create date
		$date = new JDate;

		// iterate week days
		for ($day = 0; $day < 7; $day++)
		{
			// use JDate to extract the day name
			$dayName = $date->dayToString($day, $short);
			// push day within the list
			$options[] = JHtml::_('select.option', $day, $dayName);
		}

		return $options;
	}

	/**
	 * Returns an array of years.
	 *
	 * @param 	integer  $start  The initial year.
	 * @param 	integer  $end    The ending year.
	 *
	 * @return 	array 	 A list of years.
	 */
	public static function years($start, $end)
	{
		$options = array();

		if ($start <= 0)
		{
			$year = (int) date('Y');

			$start = $year + $start;
			$end   = $year + $end;
		}

		// iterate years days
		for ($start; $start <= $end; $start++)
		{
			// push year within the list
			$options[] = JHtml::_('select.option', $start, $start);
		}

		return $options;
	}

	/**
	 * Returns an array of months.
	 *
	 * @param 	boolean  $short  True to use the short text.
	 *
	 * @return 	array 	 A list of months.
	 */
	public static function months($short = false)
	{
		$options = array();

		// create date
		$date = new JDate;

		// iterate months
		for ($month = 1; $month <= 12; $month++)
		{
			// use JDate to extract the month name
			$monthName = $date->monthToString($month, $short);
			// push month within the list
			$options[] = JHtml::_('select.option', $month, $monthName);
		}

		return $options;
	}

	/**
	 * Returns a list of day hours.
	 *
	 * @return 	array 	A list of hours.
	 */
	public static function hours()
	{
		$hours = array();

		for ($h = 0; $h < 24; $h++)
		{
			$hours[] = JHtml::_('select.option', $h, ($h < 10 ? '0' : '') . $h);
		}

		return $hours;
	}

	/**
	 * Returns a list of hour minutes.
	 *
	 * @param 	integer  $group  The group to use specific intervals.
	 * 							 If not specified, all the minutes will be used.
	 *
	 * @return 	array 	 A list of minutes.
	 */
	public static function minutes($group = 0)
	{
		$minutes = array();

		$config = VREFactory::getConfig();

		// fetch step amount
		switch ($group)
		{
			case 1:
				// use restaurant intervals
				$step = $config->getUint('minuteintervals');
				break;

			case 2:
				// use take-away intervals
				$step = $config->getUint('tkminint');
				break;

			default:
				// increase by the given step or one by one
				$step = $group ? $group : 1;
		}

		for ($m = 0; $m < 60; $m += $step)
		{
			$minutes[] = JHtml::_('select.option', $m, ($m < 10 ? '0' : '') . $m);
		}

		return $minutes;
	}

	/**
	 * Returns a list of rooms.
	 *
	 * @param 	mixed 	$admin  True to include also the unpublished rooms. If not
	 * 							specified, the client will be automatically detected.
	 *
	 * @return 	array 	A list of rooms.
	 */
	public static function rooms($admin = null)
	{
		$dbo = JFactory::getDbo();

		// auto-detect client
		if (is_null($admin))
		{
			$admin = JFactory::getApplication()->isClient('administrator');
		}

		// get all rooms
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('name', 'text'))
			->from($dbo->qn('#__vikrestaurants_room'))
			->order($dbo->qn('ordering') . ' ASC');

		// display only published menus if we are in the front-end
		if (!$admin)
		{
			$q->where($dbo->qn('published') . ' = 1');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObjectList();
		}

		return array();
	}

	/**
	 * Returns a list of take-away menus.
	 *
	 * @param 	mixed  $admin   True to include also the unpublished menus. If not
	 * 							specified, the client will be automatically detected.
	 *
	 * @return 	array  A list of menus.
	 */
	public static function takeawaymenus($admin = null)
	{
		$dbo = JFactory::getDbo();

		// auto-detect client
		if (is_null($admin))
		{
			$admin = JFactory::getApplication()->isClient('administrator');
		}

		// get all menus
		$q = $dbo->getQuery(true)
			->select('*')
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('title', 'text'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus'))
			->order($dbo->qn('ordering') . ' ASC');

		// display only published menus if we are in the front-end
		if (!$admin)
		{
			$q->where($dbo->qn('published') . ' = 1');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$menus = $dbo->loadObjectList();

			if (!$admin)
			{
				// translate menus if we are in the front-end
				VikRestaurants::translateTakeawayMenus($menus);

				// iterate records to update `text` too
				foreach ($menus as &$attr)
				{
					$attr->text = $attr->title;
				}
			}

			return $menus;
		}

		return array();
	}

	/**
	 * Returns a list of supported reservation codes.
	 *
	 * @param 	integer  $group  The group to which the codes belong.
	 * 							 Use '1' for restaurant, '2' for take-away, '3' for food.
	 * 
	 * @return 	array 	 A list of codes.
	 */
	public static function rescodes($group)
	{
		$dbo = JFactory::getDbo();

		// get all reservation codes
		$q = $dbo->getQuery(true)
			->select('*')
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('code', 'text'))
			->from($dbo->qn('#__vikrestaurants_res_code'))
			->where($dbo->qn('type') . ' = ' . (int) $group)
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObjectList();
		}

		return array();
	}

	/**
	 * Returns the specified reservation code.
	 *
	 * @param 	integer  $id     The code ID.
	 * @param 	integer  $group  The group to which the code belongs.
	 * 							 Use '1' for restaurant, '2' for take-away, '3' for food.
	 * @param   integer  $fk     If specified, the system will search also for an order
	 * 							 status that belong to the specified order/reservation.
	 * 
	 * @return 	mixed    The code details if exists, null otherwise.
	 */
	public static function rescode($id, $group = null, $fk = null)
	{
		if ((int) $id <= 0)
		{
			return null;
		}

		$dbo = JFactory::getDbo();

		static $cache = array();

		// check if the same reservation code has been already loaded
		if (!isset($cache[$id]))
		{
			$cache[$id] = null;

			// get reservation code
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_res_code'))
				->where($dbo->qn('id') . ' = ' . (int) $id);

			if (!is_null($group))
			{
				$q->where($dbo->qn('type') . ' = ' . (int) $group);
			}

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$obj = $dbo->loadObject();

				// create icon URI
				$obj->iconURI = $obj->icon ? VREMEDIA_SMALL_URI . $obj->icon : '';

				// cache result
				$cache[$id] = $obj;
			}
		}

		/**
		 * If specified, try to recover the details of the order
		 * status matching the requested reservation code and
		 * parent ID (order/reservation/food).
		 *
		 * @since 1.8.1
		 */
		if ($fk && $cache[$id])
		{
			$q = $dbo->getQuery(true)
				->select($dbo->qn('notes'))
				->select($dbo->qn('createdby'))
				->select($dbo->qn('createdon'))
				->from($dbo->qn('#__vikrestaurants_order_status'))
				->where(array(
					$dbo->qn('id_order') . ' = ' . (int) $fk,
					$dbo->qn('id_rescode') . ' = ' . $cache[$id]->id,
				));

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();	

			if ($dbo->getNumRows())
			{
				// Inject order status details with reservation codes.
				// Use a temporary variable without altering the cached object.
				$tmp = clone $cache[$id];

				foreach ($dbo->loadObject() as $k => $v)
				{
					if ($v)
					{
						// inject property only if not empty
						$tmp->{$k} = $v;
					}
				}

				return $tmp;
			}
		}

		return $cache[$id];
	}

	/**
	 * Returns the reservation code that uses the specified code.
	 *
	 * @param 	string   $rule   The rule to search.
	 * @param 	integer  $group  The group to which the code belongs.
	 * 							 Use '1' for restaurant, '2' for take-away, '3' for food.
	 * 
	 * @return 	mixed    The code ID if exists, false otherwise.
	 *
	 * @since 	1.8.1
	 */
	public static function rescoderule($rule, $group = null)
	{
		$dbo = JFactory::getDbo();

		// search reservation code by rule
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_res_code'))
			->where($dbo->qn('rule') . ' = ' . $dbo->q($rule))
			->order($dbo->qn('ordering') . ' ASC');

		if (!is_null($group))
		{
			$q->where($dbo->qn('type') . ' = ' . (int) $group);
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadResult();
		}

		return false;
	}

	/**
	 * Returns a list of supported reservation codes.
	 *
	 * @param 	array  $group  A list of statuses to include. Leave empty or
	 * 						   specify '*' to return all statuses.
	 * @param 	mixed  $empty  True to include an empty option. In case a 
	 * 						   string is passed, it will be used as placeholder.
	 * 
	 * @return 	array 	 A list of codes.
	 */
	public static function orderstatuses($included = array(), $empty = false)
	{
		// adjust included array
		if (!$included)
		{
			$included = '*';
		}
		else if ($included != '*')
		{
			$included = array_map('strtoupper', (array) $included);
		}

		$list = array(
			'CONFIRMED',
			'PENDING',
			'REMOVED',
			'CANCELLED',
		);

		$options = array();

		// add placeholder if requested
		if ($empty !== false)
		{
			// use the given text in case $empty is a string
			$options[] = JHtml::_('select.option', '', $empty === true ? JText::_('JOPTION_SELECT_PUBLISHED') : $empty);
		}

		foreach ($list as $status)
		{
			// add the status only if it has been included
			if ($included == '*' || in_array($status, $included))
			{
				$options[] = JHtml::_('select.option', $status, JText::_('VRRESERVATIONSTATUS' . $status));
			}
		}

		return $options;
	}

	/**
	 * Returns a list of payments suitable for the specified group.
	 *
	 * @param 	integer  $group   The payments group (0: all, 1: restaurant, 2: take-away).
	 * @param 	mixed 	 $admin   True to include also the unpublished payments. If not
	 * 							  specified, the client will be automatically detected.
	 *
	 * @return 	array
	 */
	public static function payments($group = null, $admin = null)
	{
		$dbo = JFactory::getDbo();

		// auto-detect client
		if (is_null($admin))
		{
			$admin = JFactory::getApplication()->isClient('administrator');
		}

		// get all payments
		$q = $dbo->getQuery(true)
			->select('*')
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('name', 'text'))
			->from($dbo->qn('#__vikrestaurants_gpayments'))
			->order($dbo->qn('ordering') . ' ASC');

		// display only published payments if we are in the front-end
		if (!$admin)
		{
			$q->where($dbo->qn('published') . ' = 1');
		}

		if ((int) $group > 0)
		{
			$q->where($dbo->qn('group') . ' IN (0, ' . (int) $group . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObjectList();
		}

		return array();
	}

	/**
	 * Returns a list of take-away menus attributes.
	 *
	 * @param 	mixed  $admin   True to include also the unpublished attributes. If not
	 * 							specified, the client will be automatically detected.
	 *
	 * @return 	array  A list of attributes.
	 */
	public static function takeawayattributes($admin = null)
	{
		$dbo = JFactory::getDbo();

		// auto-detect client
		if (is_null($admin))
		{
			$admin = JFactory::getApplication()->isClient('administrator');
		}

		// get all attributes
		$q = $dbo->getQuery(true)
			->select('*')
			->select($dbo->qn('id', 'value'))
			->select($dbo->qn('name', 'text'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_attribute'))
			->order($dbo->qn('ordering') . ' ASC');

		// display only published menus if we are in the front-end
		if (!$admin)
		{
			$q->where($dbo->qn('published') . ' = 1');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$attributes = $dbo->loadObjectList();

			if (!$admin)
			{
				// translate attributes if we are in the front-end
				VikRestaurants::translateTakeawayAttributes($attributes);

				// iterate records to update `text` too
				foreach ($attributes as &$attr)
				{
					$attr->text = $attr->name;
				}
			}

			return $attributes;
		}

		return array();
	}

	/**
	 * Returns a list of operators.
	 *
	 * @param 	mixed 	 $group  An optional group filter.
	 * @param 	boolean  $login  True to take only the operators that have
	 * 							 access to the front-end area.
	 *
	 * @return 	array
	 */
	public static function operators($group = null, $login = false)
	{
		$dbo = JFactory::getDbo();

		// get all operators
		$q = $dbo->getQuery(true)
			->select('*')
			->select($dbo->qn('id', 'value'))
			->select(sprintf(
				'CONCAT_WS(\' \', %s, %s) AS %s',
				$dbo->qn('firstname'),
				$dbo->qn('lastname'),
				$dbo->qn('text')
			))
			->from($dbo->qn('#__vikrestaurants_operator'))
			->order($dbo->qn('lastname') . ' ASC')
			->order($dbo->qn('firstname') . ' ASC');

		if (!is_null($group))
		{
			// filter by group (0: all, 1: restaurant, 2: take-away)
			$q->where($dbo->qn('group') . ' IN (0, ' . (int) $group . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObjectList();
		}

		return array();
	}

	/**
	 * Returns a list of tags.
	 *
	 * @param 	mixed 	 $group  An optional group filter.
	 *
	 * @return 	array
	 */
	public static function tags($group = null)
	{
		$dbo = JFactory::getDbo();

		// get all tags
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_tag'))
			->order($dbo->qn('ordering') . ' ASC');

		if (!is_null($group))
		{
			// filter by group
			$q->where($dbo->qn('group') . ' = ' . $dbo->q($group));
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadObjectList();
		}

		return array();
	}

	/**
	 * Displays the rating of a review by printing
	 * the matched starts as image or through FontAwesome.
	 *
	 * FontAwesome is NOT loaded here.
	 *
	 * @param 	float 	 $rating   The rating amount (0-5).
	 * @param 	mixed  	 $image    True to use the images, false to use FA (v4).
	 * 							   In case of a string, it will match FA version.
	 * @param 	boolean  $missing  True to display the missing stars, false to hide them.
	 *
	 * @return 	string 	 The resulting HTML.
	 */
	public static function rating($rating, $image = true, $missing = true)
	{
		$html = '';

		// display filled stars
		for ($i = 1; $i <= $rating; $i++)
		{
			if ($image === true)
			{
				$html .= '<img src="' . VREASSETS_URI . 'css/images/rating-star.png" />';
			}
			else
			{
				// look for a specific FontAwesome version
				if (version_compare((string) $image, '5', '>='))
				{
					// use FA 5
					$class = 'fas fa-star';
				}
				else
				{
					// use FA 4
					$class = 'fa fa-star';
				}

				$html .= '<i class="' . $class . '"></i>';
			}
		}
		
		// display half star
		if (round($rating) != $rating)
		{
			if ($image === true)
			{
				$html .= '<img src="' . VREASSETS_URI . 'css/images/rating-star-middle.png" />';
			}
			else
			{
				// look for a specific FontAwesome version
				if (version_compare((string) $image, '5', '>='))
				{
					// use FA 5
					$class = 'fas fa-star-half-alt';
				}
				else
				{
					// use FA 4
					$class = 'fa fa-star-half-o';
				}
				
				$html .= '<i class="' . $class . '"></i>';
			}
		}
		
		// display missing stars
		if ($missing)
		{
			for ($i = round($rating) + 1; $i <= 5; $i++)
			{
				if ($image === true)
				{
					$html .= '<img src="' . VREASSETS_URI . 'css/images/rating-star-no.png" />';
				}
				else
				{
					// look for a specific FontAwesome version
					if (version_compare((string) $image, '5', '>='))
					{
						// use FA 5
						$class = 'far fa-star';
					}
					else
					{
						// use FA 4
						$class = 'fa fa-star-o';
					}
					
					$html .= '<i class="' . $class . '"></i>';
				}
			}
		}

		return $html;
	}
}
