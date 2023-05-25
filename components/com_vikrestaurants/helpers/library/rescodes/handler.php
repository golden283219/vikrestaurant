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

VRELoader::import('library.rescodes.rule');

/**
 * Used to handle the rules of the reservation codes.
 *
 * @since 1.8
 */
class ResCodesHandler
{
	/**
	 * A list of cached rules.
	 *
	 * @var array
	 */
	protected static $rules = null;

	/**
	 * Helper method used to extend the paths in which the rules
	 * should be found.
	 *
	 * @param 	mixed 	$path  The path to include (optional).
	 *
	 * @return 	array   A list of supported directories.
	 */
	public static function addIncludePath($path = null)
	{
		static $paths = array();
		
		if (!$paths)
		{
			// add standard folder
			$paths[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rules';
		}

		// include path if specified
		if ($path && is_dir($path))
		{
			$paths[] = $path;

			// reset rules as some of them need to be reloaded
			static::$rules = null;
		}

		// return list of included paths
		return $paths;
	}

	/**
	 * Returns a list of supported rules.
	 *
	 * @param 	mixed 	$group  Optionally filter the rules by group (restaurant or takeaway).
	 *
	 * @return 	array   A list of supported rules.
	 */
	public static function getSupportedRules($group = null)
	{
		// fetch drivers only once
		if (is_null(static::$rules))
		{
			static::$rules = array();

			// configuration array for rules
			$config = array();

			/**
			 * This event can be used to support custom rules.
			 * It is enough to include the directory containing
			 * the new rules. Only the files that inherits the
			 * ResCodesRule class will be taken.
			 *
			 * Example:
			 * // register custom rule(s)
			 * ResCodesHandler::addIncludePath($path);
			 * // assign plugin configuration to rule (customrule is the filename)
			 * $config['customrule'] = $this->params;
			 *
			 * @param 	array  &$config  It is possible to inject the configuration for
			 * 							 a specific rule. The parameters have to be assigned
			 * 							 by using the rule file name.
			 *
			 * @return 	void
			 *
			 * @since 	1.8
			 */
			VREFactory::getEventDispatcher()->trigger('onLoadReservationCodesRules', array(&$config));

			// iterate loaded paths
			foreach (static::addIncludePath() as $path)
			{
				// get all drivers within the specified folder
				$files = glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

				// iterate files found
				foreach ($files as $file)
				{
					// require file only once
					require_once $file;

					// fetch class name from file name
					$filename = preg_replace("/\.php$/i", '', basename($file));
					$class = 'ResCodesRule' . ucfirst($filename);

					// make sure the class exists
					if (class_exists($class))
					{
						// fetch configuration params
						$params = isset($config[$filename]) ? $config[$filename] : array();

						// instantiate class
						$driver = new $class($params);

						// use driver only whether it is a valid instance
						if ($driver instanceof ResCodesRule)
						{
							// map drivers by key
							static::$rules[$driver->getID()] = $driver;
						}
					}
				}
			}

			// sort rules by ascending name (keep keys)
			uasort(static::$rules, function($a, $b)
			{
				return strcasecmp($a->getName(), $b->getName());
			});
		}

		// check if we have a group
		if (is_null($group))
		{
			// no group filtering
			return static::$rules;
		}

		// filter the rules by group
		return array_filter(static::$rules, function($driver) use($group)
		{
			// make sure the driver supports this group
			return $driver->isSupported($group);
		});
	}

	/**
	 * Returns an instance of the requested rule.
	 *
	 * @param 	string 	$rule   The rule name.
	 * @param 	mixed 	$group  Optionally check whether the rule supports the group.
	 *
	 * @return 	ResCodesRule
	 *
	 * @throws 	RuntimeException
	 */
	public static function getRule($rule, $group = null)
	{
		// get all supported rules
		foreach (static::getSupportedRules() as $driver)
		{
			// compare rule with the specified one
			if ($driver->getID() == $rule && (is_null($group) || $driver->isSupported($group)))
			{
				// rule found
				return $driver;
			}
		}

		// rule not found, throw exception
		throw new RuntimeException(sprintf('Reservations codes [%s] rule not found', $rule), 404);
	}

	/**
	 * Triggers the rule of the specified reservation code.
	 *
	 * @param 	integer  $id_code   The reservation code ID.
	 * @param 	integer  $id_order  The reservation/order ID.
	 * @param 	string 	 $group 	The requested group (restaurant or takeaway).
	 *
	 * @return 	boolean  True if the code owns a rule, false otherwise.
	 *
	 * @throws 	RuntimeException
	 */
	public static function trigger($id_code, $id_order, $group)
	{
		if (!$id_code || !$id_order || !$group)
		{
			// invalid code, order or group
			throw new RuntimeException('Cannot perform code rule due to an invalid request', 400);
		}

		// retrieve reservation code rule
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);
		
		$q->select($dbo->qn('rule'));
		$q->from($dbo->qn('#__vikrestaurants_res_code'));
		$q->where($dbo->qn('id') . ' = ' . (int) $id_code);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// reservation code not found
			throw new RuntimeException(sprintf('Code [%d] not found', $id_code), 404);
		}

		$rule = $dbo->loadResult();

		if (empty($rule))
		{
			// rule not suported, do not go ahead
			return false;
		}

		if ($group == 'restaurant')
		{
			// load restaurant reservation
			$order = VREOrderFactory::getReservation($id_order);
		}
		else if ($group == 'takeaway')
		{
			// load take-away order
			$order = VREOrderFactory::getOrder($id_order);
		}
		else
		{
			// use specified argument
			$order = $id_order;
		}

		// get rule driver
		$driver = static::getRule($rule, $group);

		// execute the driver
		$driver->execute($order);

		return true;
	}
}
