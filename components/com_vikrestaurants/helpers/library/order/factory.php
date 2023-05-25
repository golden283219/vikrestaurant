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
 * Factory class used to retrieve the details of either the restaurant
 * reservations or the take-away orders.
 *
 * @since 1.8
 */
abstract class VREOrderFactory
{
	/**
	 * A list of cached orders.
	 *
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * Returns the restaurant reservation instance.
	 *
	 * @param 	integer  $id       The reservation ID.
	 * @param 	mixed    $langtag  The language tag. If null, the default one will be used.
	 * @param 	array 	 $options  An array of options to be passed to the order instance.
	 *
	 * @return 	mixed    The reservation instance.
	 */
	public static function getReservation($id, $langtag = null, array $options = array())
	{
		return static::get('restaurant', $id, $langtag, $options);
	}

	/**
	 * Returns the take-away order instance.
	 *
	 * @param 	integer  $id       The order ID.
	 * @param 	mixed    $langtag  The language tag. If null, the default one will be used.
	 * @param 	array 	 $options  An array of options to be passed to the order instance.
	 *
	 * @return 	mixed    The take-away order instance.
	 */
	public static function getOrder($id, $langtag = null, array $options = array())
	{
		return static::get('takeaway', $id, $langtag, $options);
	}

	/**
	 * Unset cache every time the reservation/order details change.
	 *
	 * @param 	string   $group  The group ('restaurant' or 'takeaway').
	 * @param 	integer  $id     The order ID.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.2
	 */
	public static function changed($group, $id)
	{
		if (isset(static::$cache[$group][$id]))
		{
			unset(static::$cache[$group][$id]);
		}
	}

	/**
	 * Returns the reservation/order instance.
	 *
	 * @param 	string   $group    The group ('restaurant' or 'takeaway').
	 * @param 	integer  $id       The order ID.
	 * @param 	mixed    $langtag  The language tag. If null, the default one will be used.
	 * @param 	array 	 $options  An array of options to be passed to the order instance.
	 *
	 * @return 	mixed    The order instance.
	 *
	 * @throws 	Exception
	 */
	protected static function get($group, $id, $langtag = null, array $options = array())
	{
		$key = $langtag ? $langtag : 'auto';

		// make sure the group is set in the cache pool
		if (!isset(static::$cache[$group]))
		{
			static::$cache[$group] = array();
		}

		// load handler class
		if (!VRELoader::import('library.order.classes.' . $group))
		{
			throw new Exception(sprintf('Order driver [%s] not found', $group), 404);
		}

		// create class name
		$classname = 'VREOrder' . ucfirst($group);

		// make sure the class handler exists
		if (!class_exists($classname))
		{
			throw new Exception(sprintf('Order class [%s] does not exist', $classname), 404);
		}

		// Check if the instance already exists in the cache pool.
		// Skip cache in case the configuration contains the "ignore_cache" attribute.
		if (!isset(static::$cache[$group][$id][$key]) || !empty($options['ignore_cache']))
		{
			// create a space for the given order to support multiple languages
			if (!isset(static::$cache[$group][$id]))
			{
				static::$cache[$group][$id] = array();
			}

			// retrieve the order for the given language
			$obj = new $classname($id, $langtag, $options);

			if (!empty($options['ignore_cache']))
			{
				// return the object before caching it
				return $obj;
			}

			// retrieve the order for the given language
			static::$cache[$group][$id][$key] = $obj;
		}

		return static::$cache[$group][$id][$key];
	}
}
