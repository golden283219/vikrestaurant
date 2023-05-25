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
 * when trying to book some take-away meals.
 *
 * @since 1.8
 */
class VREAvailabilityTakeaway extends JObject
{
	/**
	 * A pool of day-orders.
	 *
	 * @var array
	 */
	protected static $_orders = array();

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
	 * Flag used to check whether the customer is looking
	 * for a delivery order (true) or a pickup one (false).
	 *
	 * @var   boolean
	 * @since 1.8.3
	 */
	protected $delivery = null;

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
	 * @param 	mixed 	 $date     Either a date string or a UNIX timestamp.
	 * @param 	mixed 	 $time     Either a hourmin string (e.g. 20:30) or an array
	 * 							   containing the hour and the minutes.
	 * @param 	mixed 	 $service  True for delivery service, false for pickup.
	 *
	 * @uses 	setDate()
	 * @uses 	setTime()
	 * @uses 	setDelivery()
	 */
	public function __construct($date, $time = null, $service = null)
	{
		// set arguments
		$this->setDate($date);
		
		if ($time)
		{
			$this->setTime($time);
		}

		if (!is_null($service))
		{
			$this->setDelivery($service);
		}
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
	 * Flags the delivery service with the specified status.
	 *
	 * @param 	boolean  $service  True for delivery service, false for pickup.
	 *
	 * @return 	self 	 This object to support chaining.
	 *
	 * @since 	1.8.3
	 */
	public function setDelivery($service = true)
	{
		$this->delivery = (bool) $service;

		return $this;
	}

	/**
	 * Checks whether the current service was set to
	 * delivery or pickup.
	 *
	 * In case of missing service, the default one will be
	 * taken from the cart instance.
	 *
	 * @return 	boolean  True for delivery, false for pickup.
	 *
	 * @since 	1.8.3
	 */
	public function isDelivery()
	{
		if (is_null($this->delivery))
		{
			// check service set within the cart instance
			$this->delivery = TakeAwayCart::getInstance()->getService() == 1;
		}

		return $this->delivery;
	}

	/**
	 * Returns a list of supported times for the
	 * specified date. Times that are out of 
	 * availability will be marked as disabled.
	 *
	 * @return 	array  A list of working shifts.
	 */
	public function getTimes()
	{
		// get all day times
		$options = array(
			// do not ignore closing days
			'closure' => true,
			// pass delivery service to avoid caching the times
			// fetched for a different type of service
			'delivery' => $this->isDelivery(),
		);
		
		$times = JHtml::_('vikrestaurants.times', 2, $this->get('date'), $options);

		$config = VREFactory::getConfig();

		// check whether the date selection is allowed
		$is_date_allowed = $config->getBool('tkallowdate');
		// in case the date selection is disabled, check whether pre-orders are enabled
		$is_live_orders = $is_date_allowed ? false : $config->getBool('tkwhenopen');
		// check if we should allow only pre-orders
		$is_pre_orders = $config->getBool('tkpreorder');
		
		if ($is_live_orders || $is_pre_orders)
		{
			$tmp = array();

			$now = VikRestaurants::now();

			// calculate minutes of current time
			$hm = getdate($now);
			$hm = (int) $hm['hours'] * 60 + (int) $hm['minutes'];

			foreach ($times as $k => $list)
			{
				// get first and last times of this shift
				$first = reset($list);
				$last  = end($list);

				// extract hours and minutes from times
				$fhm = explode(':', $first->value);
				$lhm = explode(':', $last->value);

				// In case of live orders, we need to take only the
				// shifts that include the current date and time.
				if ($is_live_orders)
				{
					// calculate minutes
					$fhm = (int) $fhm[0] * 60 + (int) $fhm[1];
					$lhm = (int) $lhm[0] * 60 + (int) $lhm[1];

					// make sure the current time is between the shift delimiters
					if ($fhm <= $hm && $hm <= $lhm)
					{
						// copy shift in temporary list
						$tmp[$k] = $list;
					}
				}
				// In case of pre-orders, we need to take only the shifts
				// that DO NOT include the current date and time.
				else if ($is_pre_orders)
				{
					// create shift timestamp
					$fhm = VikRestaurants::createTimestamp($this->get('date'), $fhm[0], $fhm[1]);

					// make sure the current time is not prior then the shift initial time
					if ($fhm > $now)
					{
						// copy shift in temporary list
						$tmp[$k] = $list;
					}
				}
			}

			// replace times list
			$times = $tmp;
		}

		// get orders for the given date
		$orders = $this->getOrders();

		$dispatcher = VREFactory::getEventDispatcher();

		// get maximum number of orders that can be accepted for each time slot
		$max_orders_per_interval = $config->getUint('tkordperint');
		$service_restr           = $config->getUint('tkordmaxser');

		// iterate working shifts
		foreach ($times as &$shift)
		{
			// iterate intervals
			foreach ($shift as $k => $time)
			{
				// reset items count
				$shift[$k]->count = 0;

				// count the total number of orders booked at this time slot
				$shift[$k]->ordersCount = 0;

				// iterate orders
				foreach ($orders as $o)
				{
					// compare order time with shift time
					if ($o->checkinTime == $time->value)
					{
						// occupate slot recursively (if needed)
						$this->_occupySlot($shift, $k, $o);

						// increase orders count in case according to the service restrictions
						// 0: pickup only
						// 1: delivery only
						// 2: pickup or delivery
						if ($service_restr == 2
							|| ($service_restr == 1 && $o->delivery_service == 1)
							|| ($service_restr == 0 && $o->delivery_service == 0))
						{
							$shift[$k]->ordersCount++;
						}
					}
				}

				// prepare event arguments
				$args = array($max_orders_per_interval, $shift, $time, $this);

				/**
				 * Look for an override that may increase/decrease the
				 * default maximum number of accepted orders per slot.
				 *
				 * @since 1.8.3
				 */
				$_max = $max_orders_per_interval + $this->getOverride($time);

				/**
				 * Plugins can use this hook to change at runtime the maximum number
				 * of orders per slot. Only the value returned by the plugin with
				 * highest priority will be used.
				 *
				 * It is possible to use $slot->ordersCount to retrieve the total
				 * number of orders that were placed for this time.
				 *
				 * @param 	integer  $max     The default amount.
				 * @param 	array    $times   The current working shift and all the related timeslots.
				 * @param 	object 	 $slot    The current timeslot.
				 * @param 	self     $search  The availability search instance.
				 *
				 * @return 	integer  The maximum amount to use.
				 *
				 * @since 	1.8.3
				 */
				$return = $dispatcher->numbers('onCalculateMaxOrdersPerInterval', $args);

				// check whether the plugins returned something
				if ($return)
				{
					// use the first returned value
					$_max = (int) $return[0];
				}

				// register maximum amount within the time slot
				$shift[$k]->maxOrders = $_max;

				/**
				 * Make sure the current time slot doesn't own the
				 * maximum number of supported orders per interval.
				 * Otherwise, turn it off.
				 *
				 * @since 1.8.2
				 */
				if ($time->ordersCount >= $_max)
				{
					/**
					 * Disable slot only in case the restriction applies to
					 * the selected service.
					 *
					 * @since 1.8.3
					 */
					if ($service_restr == 2
						|| ($service_restr == 1 && $this->isDelivery() === true)
						|| ($service_restr == 0 && $this->isDelivery() === false))
					{
						// maximum number of orders exceeded, disable time slot
						$shift[$k]->disable = true;
					}
				}
			}
		}

		/**
		 * Plugins can use this hook to manipulate the available timeslots
		 * at runtime, in order to enhance the default restrictions and the
		 * functionalities.
		 *
		 * The timeslots can be enabled/disabled by toggling the related
		 * `disable` property.
		 *
		 * @param 	array  $times   An array of working shifts and the releated timeslots.
		 * @param 	self   $search  The availability search instance.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.3
		 */
		$dispatcher->trigger('onPrepareTakeAwayTimes', array(&$times, $this));

		return $times;
	}

	/**
	 * Returns a list of supported times for the specified
	 * date and able to suit the products in the cart. 
	 *
	 * Times that are out of availability will be marked
	 * as disabled.
	 *
	 * @param 	mixed  $cart  The cart instance.
	 *
	 * @return 	array  A list of working shifts.
	 */
	public function getAvailableTimes($cart = null)
	{
		if (is_null($cart))
		{
			// get cart instance if not specified
			$cart = TakeAwayCart::getInstance();
		}

		// get supported times
		$times = $this->getTimes();

		// create temporary order based on cart items
		$order = new stdClass;
		$order->total = $cart->getPreparationItemsQuantity();

		$config = VREFactory::getConfig();

		// get maximum number of slots for which we can go backward
		$backmins = $config->getUint('tkmealsbackslots');
		// get time slots intervals
		$minint = $config->getUint('tkminint');
		// get number of slots to book in advance
		$asap = $config->getUint('asapafter');

		// get current time
		$now = VikRestaurants::now();
		// find current time slot
		$now -= $now % ($minint * 60);

		// get soonest time (add ASAP setting to current time slot)
		$now = strtotime('+' . ($asap * $minint) . ' minutes', $now);

		// get minimum check-in date
		$minDate = $config->getUint('tkmindate');

		if ($minDate)
		{
			// increase current date by the specified number of days
			$tmp = strtotime('+' . $minDate . ' days 00:00:00', VikRestaurants::now());

			// take the highest date between MIN and ASAP
			$now = max(array($now, $tmp));
		}

		// get maximum check-in date
		$maxDate = $config->getUint('tkmaxdate');

		if ($maxDate)
		{
			// increase current date by the specified number of days
			$maxDate = strtotime('+' . $maxDate . ' days 23:59:59', VikRestaurants::now());
		}

		// iterate working shifts
		foreach ($times as &$shift)
		{
			// iterate intervals
			foreach ($shift as $k => &$time)
			{
				// go ahead only in case the time is not disabled
				if ($time->disable == false)
				{
					// convert current time slot in minutes
					list($h, $m) = explode(':', $time->value);

					// get time slot timestamp
					$ts = VikRestaurants::createTimestamp($this->get('date'), $h, $m);

					// make sure the time is not in the past and
					// doesn't exceed the maximum date (if set)
					if ($ts >= $now && (!$maxDate || $ts <= $maxDate))
					{
						// make sure we have something to prepare
						if ($order->total > 0)
						{
							if ($backmins > 0)
							{
								// recalculate preparation time threshold
								$order->preparation_ts = strtotime('-' . ($backmins * $minint) . ' minutes', $ts);

								// re-format preparation time
								$order->preparationTime = (int) date('H', $order->preparation_ts) . ':' . (int) date('i', $order->preparation_ts);
							}
							else
							{
								// ignore preparation time
								$order->preparation_ts  = null;
								$order->preparationTime = null;
							}

							// test whether this time slot is suitable for the ordered product
							$slot = $this->_checkSlotAvailability($shift, $k, $order);

							if (!$slot)
							{
								// disable time, not enough slots to take the ordered meals
								$time->disable = true;
							}
							else
							{
								// convert current time slot in minutes
								list($h2, $m2) = explode(':', $slot->value);

								// get time slot timestamp
								$slot_ts = VikRestaurants::createTimestamp($this->get('date'), $h2, $m2);

								if ($slot_ts < $now)
								{
									// disable time, not enough time to prepare the meals
									$time->disable = true;
								}
							}
						}
					}
					else
					{
						// time in the past, disable it
						$time->disable = true;
					}
				}
			}
		}

		return $times;
	}

	/**
	 * Checks whether the searched date time is suitable
	 * for the products that are currently in the cart. 
	 *
	 * @param 	mixed    $cart       The cart instance.
	 * @param 	mixed 	 &$prepTime  The preparation timestamp in case the order
	 * 								 should be prepared in a different time slot.
	 *
	 * @return 	boolean  True if available, false otherwise.
	 */
	public function isTimeAvailable($cart = null, &$prepTime = null)
	{
		// get available times
		$shifts = $this->getAvailableTimes($cart);

		$config = VREFactory::getConfig();

		// get maximum number of slots for which we can go backward
		$backmins = $config->getUint('tkmealsbackslots');
		// get time slots intervals
		$minint = $config->getUint('tkminint');

		// get time slot timestamp
		$ts = VikRestaurants::createTimestamp($this->get('date'), $this->get('hour'), $this->get('min'));

		// create temporary order based on cart items
		$order = new stdClass;
		$order->total = $cart->getPreparationItemsQuantity();

		if ($backmins > 0)
		{
			// recalculate preparation time threshold
			$order->preparation_ts = strtotime('-' . ($backmins * $minint) . ' minutes', $ts);

			// re-format preparation time
			$order->preparationTime = (int) date('H', $order->preparation_ts) . ':' . (int) date('i', $order->preparation_ts);
		}
		else
		{
			// ignore preparation time
			$order->preparation_ts  = null;
			$order->preparationTime = null;
		}

		// iterate all working shifts found
		foreach ($shifts as $shift)
		{
			// iterate all shift times
			foreach ($shift as $k => $time)
			{
				// extract hour and minutes from time value
				list($h, $m) = explode(':', $time->value);

				// compare searched time with current one
				if ((int) $h == $this->get('hour') && (int) $m == $this->get('min'))
				{
					if ($order->total == 0)
					{
						// no items to prepare, the time is still ok even if full
						return true;
					}

					// time found, make sure it is not disabled
					if ($time->disable)
					{
						// time not suitable
						return false;
					}

					// occupy time slot to understand whether the order
					// should be prepared on a different time slot
					$slot = $this->_occupySlot($shift, $k, $order);

					// check if we found a different time slot
					if ($slot && $slot->value !== $time->value)
					{
						// extract hour and minutes from preparation time value
						list($h, $m) = explode(':', $slot->value);
						// create preparation timestamp
						$prepTime = VikRestaurants::createTimestamp($this->get('date'), $h, $m);
					}

					// time is available
					return true;
				}
			}
		}

		// time not available
		return false;
	}

	/**
	 * Tries to check whether the specified time slot is available for
	 * the purchase of the selected orders.
	 *
	 * @param 	array 	 $times  The list of time slots.
	 * @param 	integer  $index  The current time index.
	 * @param 	object 	 $order  The order to handle.
	 *
	 * @return 	mixed    In case the slot can suit the ordered products,
	 * 					 the last occupied slot will be returned.
	 * 					 False otherwise.
	 */
	protected function _checkSlotAvailability($shift, $index, $order)
	{
		$times = array();

		// clone times in order to avoid updating the original array
		foreach ($shift as $time)
		{
			$times[] = clone $time;
		}

		// clone order to avoid updating the original object
		$tmp = clone $order;

		// try to occupy the slot and the previous ones as long as possible
		return $this->_occupySlot($times, $index, $tmp);
	}

	/**
	 * Helper function used to place the items of a order
	 * recursively within the specified time slot and the
	 * previous ones.
	 *
	 * @param 	array 	 &$times  The list of time slots.
	 * @param 	integer  $index   The current time index.
	 * @param 	object 	 $order   The order to handle.
	 *
	 * @return 	mixed    In case the slot can suit the ordered products,
	 * 					 the last occupied slot will be returned.
	 * 					 False otherwise.
	 */
	protected function _occupySlot(&$times, $index, $order)
	{
		// get maximum number of preparation meals per interval
		$max = VREFactory::getConfig()->getUint('mealsperint');

		$dispatcher = VREFactory::getEventDispatcher();

		/**
		 * Plugins can use this hook to change at runtime the maximum number
		 * of preparation meals per slot. Only the value returned by the plugin
		 * with highest priority will be used.
		 *
		 * It is possible to use $slot->count to retrieve the total number
		 * of preparation items that were scheduled for this slot.
		 *
		 * @param 	integer  $max     The default amount.
		 * @param 	array    $times   The current working shift and all the related timeslots.
		 * @param 	object 	 $slot    The current timeslot.
		 * @param 	self     $search  The availability search instance.
		 *
		 * @return 	integer  The maximum amount to use.
		 *
		 * @since 	1.8.3
		 */
		$return = $dispatcher->numbers('onCalculateMaxPreparationMeals', array($max, $times, $times[$index], $this));

		// check whether the plugins returned something
		if ($return)
		{
			// use the first value
			$max = (int) $return[0];
		}

		// register maximum amount within the time slot
		$times[$index]->maxItems = $max;

		// get remaining slots
		$remaining = $max - $times[$index]->count;

		$ok = false;

		// make sure there are enough slots to insert the order meals
		if ($remaining >= $order->total)
		{
			// there is enough space, add total meals
			$times[$index]->count += $order->total;

			// return booked time
			$ok = $times[$index];
		}
		else
		{
			// not enough space, occupy remaining slots
			$times[$index]->count += $remaining;

			// take meals to add backward
			$order->total -= $remaining;

			// Make sure the previous time slot exists.
			// Go ahead in case the order doesn't allow backward inserts.
			if (isset($times[$index - 1]) && $order->preparationTime !== null)
			{
				// convert previous time slot in minutes
				list($h, $m) = explode(':', $times[$index - 1]->value);
				$slot_hm = $h * 60 + $m;

				// convert order preparation time threshold in minutes
				list($h, $m) = explode(':', $order->preparationTime);
				$prep_hm = $h * 60 + $m;

				// make sure the previous time slot doesn't exceed the 
				// backward preparation threshold of the order
				if ($prep_hm <= $slot_hm)
				{
					// add recursively the remaining slots backward
					$ok = $this->_occupySlot($times, $index - 1, $order);
				}
			}
		}

		if ($times[$index]->count == $max)
		{
			// mark option as disabled, the slot
			// already owns the maximum number of
			// preparation meals
			$times[$index]->disable = true;
		}

		return $ok;
	}

	/**
	 * Returns a list of orders made on the specified day.
	 *
	 * @param 	mixed 	$date  Either a timestamp or a date.
	 *
	 * @return 	array   A list of orders.
	 */
	public function getOrders($date = null)
	{
		if (is_null($date))
		{
			$date = $this->get('date');
		}
		else if (preg_match("/^\d+$/", $date))
		{
			// we have a UNIX timestamp, convert it to a formatted date
			$date = date(VREFactory::getConfig()->get('dateformat'), $date);
		}

		// check if the orders for the selected date were already cached
		if (isset(static::$_orders[$date]))
		{
			// returned cached orders
			return static::$_orders[$date];
		}

		// cache empty list
		static::$_orders[$date] = array();

		// fetch day delimiters
		$start_ts = VikRestaurants::createTimestamp($date, 0, 0);
		$end_ts   = VikRestaurants::createTimestamp($date, 23, 59);

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		// load checkin timestamp and preparation timestamp
		$q->select($dbo->qn('o.id'));
		$q->select($dbo->qn('o.checkin_ts'));
		$q->select($dbo->qn('o.preparation_ts'));
		$q->select($dbo->qn('o.delivery_service'));
		// count total number of items
		$q->select('SUM(' . $dbo->qn('i.quantity') . ') AS ' . $dbo->qn('total'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'o'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc', 'i') . ' ON ' . $dbo->qn('i.id_res') . ' = ' . $dbo->qn('o.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'p') . ' ON ' . $dbo->qn('i.id_product') . ' = ' . $dbo->qn('p.id'));
		
		// take only the reservations with an accepted status
		$q->where($dbo->qn('o.status') . ' IN (' . implode(',', array_map(array($dbo, 'q'), $this->statuses)) . ')');
		// take only the products that require a preparation
		$q->where($dbo->qn('p.ready') . ' = 0');

		// apply date filter
		$q->andWhere(array(
			// take only the reservations for the selected date
			$dbo->qn('o.checkin_ts') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts,
			// in case of anticipated preparation (e.g. checkin @ 00:15, preparation @ 23:45),
			// we should check whether the preparation time (IF SET) is part of the selected date
			'(' . 
				$dbo->qn('o.preparation_ts') . ' IS NOT NULL' .
				' AND ' . 
				$dbo->qn('o.preparation_ts') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts . 
			')'
		), 'OR');

		// group by order
		$q->group($dbo->qn('o.id'));

		$q->order($dbo->qn('o.id') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// cache resulting orders
			foreach ($dbo->loadObjectList() as $order)
			{
				// format check-in time
				$order->checkinTime = (int) date('H', $order->checkin_ts) . ':' . (int) date('i', $order->checkin_ts);

				if ($order->preparation_ts)
				{
					// format preparation time
					$order->preparationTime = (int) date('H', $order->preparation_ts) . ':' . (int) date('i', $order->preparation_ts);
				}
				else
				{
					// no preparation time
					$order->preparationTime = null;
				}

				static::$_orders[$date][] = $order;
			}
		}

		return static::$_orders[$date];
	}

	/**
	 * Looks for an availability override for the specified time slot.
	 * In case of an existing override, the returned amount will be used
	 * to increase/decrease the default maximum orders threshold.
	 *
	 * @param 	object 	 $time  The time slot to look for.
	 *
	 * @return 	integer  The override amount.
	 *
	 * @since 	1.8.3
	 */
	protected function getOverride($time)
	{
		static $overrides = array();

		// load overrides for the selected date if not yet retrieved
		if (!isset($overrides[$this->date]))
		{
			$overrides[$this->date] = array();

			$dbo = JFactory::getDbo();

			// create date range
			$start = VikRestaurants::createTimestamp($this->date,  0,  0);
			$end   = VikRestaurants::createTimestamp($this->date, 23, 59);

			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_avail_override'))
				->where($dbo->qn('ts') . ' BETWEEN ' . $start . ' AND ' . $end)
				->order($dbo->qn('ts') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// iterate results
				foreach ($dbo->loadObjectList() as $override)
				{
					// extract time from timestamp
					$h = (int) date('H', $override->ts);
					$m = (int) date('i', $override->ts);

					// create a key compatible with the value of the slots
					$key = $h . ':' . $m;

					// create override if not yet specified
					if (!isset($overrides[$this->date][$key]))
					{
						$overrides[$this->date][$key] = 0;
					}

					// register override within the list
					$overrides[$this->date][$key] += $override->units;
				}
			}
		}

		// check whether the specified time slot owns an override
		if (isset($overrides[$this->date][$time->value]))
		{
			// override found, return it
			return $overrides[$this->date][$time->value];
		}

		// no override
		return 0;
	}
}
