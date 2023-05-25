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

VRELoader::import('library.deals.rule');

/**
 * Used to handle the deals stored in the database.
 *
 * @since  	1.6
 * @since 	1.7  Renamed from VRDealsHandler
 */
class DealsHandler
{
	/**
	 * Get all the available deals between the selected date.
	 * Target products and Gift products are not included.
	 * @usedby 	DealsHandler::getAvailableFullDeals()
	 *
	 * @param 	mixed    $cart 	Either the take-away cart instance or the check-in date.
	 * @param 	integer  $type 	The value of the type to filter deals. 
	 * 							Use 0 to skip type filtering.
	 *
	 * @return 	array 	 The list containing all the deals found.
	 */
	public static function getAvailableDeals($cart, $type = 0)
	{
		if (is_scalar($cart) || !$cart)
		{
			// use specified timestamp
			$ts = (int) $cart;

			// recover cart instance
			$cart = TakeAwayCart::getInstance();
		}
		else
		{
			// obtain check-in date from cart
			$ts = $cart->getCheckinTimestamp();
		}
		
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('deal.*')
			->select($dbo->qn('day.id_weekday'))
			->from($dbo->qn('#__vikrestaurants_takeaway_deal', 'deal'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_deal_day_assoc', 'day') . ' ON ' . $dbo->qn('deal.id') . ' = ' . $dbo->qn('day.id_deal'))
			->where($dbo->qn('deal.published') . ' = 1');

		if ((int) $ts > 0)
		{
			$q->andWhere(array(
				$dbo->qn('deal.start_ts') . ' = -1',
				$ts . ' BETWEEN ' . $dbo->qn('deal.start_ts') . ' AND (' . $dbo->qn('deal.end_ts') . ' + 86399)',
			), 'OR');
		}

		if ($type > 0)
		{
			$q->where($dbo->qn('deal.type') . ' = ' . (int) $type);
		}

		$q->order($dbo->qn('deal.ordering') . ' ASC');
		$q->order($dbo->qn('day.id_weekday') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() > 0)
		{
			$arr = array();

			foreach ($dbo->loadAssocList() as $deal)
			{
				if (!isset($arr[$deal['id']]))
				{
					// JSON decode working shifts
					$deal['shifts'] = (array) json_decode($deal['shifts']);

					$deal['days_filter'] = array();

					$arr[$deal['id']] = $deal;
				}

				if ($deal['id_weekday'] !== null)
				{
					$arr[$deal['id']]['days_filter'][] = $deal['id_weekday'];
				}
			}
			
			$deals = array();

			foreach ($arr as $a)
			{
				// check if the deal is compatible
				$a['active'] = (int) static::isDealCompatible($a, $cart);

				// register deal in case it is active for the selected
				// date or in case the date wasn't specified
				if ($ts <= 0 || $a['active'])
				{
					$deals[] = $a;
				}
			}
			
			return $deals;
		}
		
		return array();	
	}
	
	/**
	 * Get all the available deals between the selected date.
	 * Target products and Gift products are included.
	 *
	 * @param 	mixed    $cart 	Either the take-away cart instance or the check-in date.
	 * @param 	integer  $type 	The value of the type to filter deals. 
	 * 							Use 0 to skip type filtering.
	 *
	 * @return 	array 	 The list containing all the deals found.
	 *
	 * @uses 	getAvailableDeals()
	 */
	public static function getAvailableFullDeals($ts, $type = -1)
	{
		// get available deals
		$deals = self::getAvailableDeals($ts, $type);
		
		if (!count($deals))
		{
			return array();
		}
		
		$dbo = JFactory::getDbo();
		
		// iterate deals to recover products
		foreach ($deals as $k => $deal)
		{
			// recover targets
			$q = $dbo->getQuery(true);

			$q->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_deal_product_assoc'))
				->where($dbo->qn('id_deal') . ' = ' . $deal['id']);

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows() > 0)
			{
				$deals[$k]['products'] = $dbo->loadAssocList();
			}
			else
			{
				$deals[$k]['products'] = array();
			}

			// recover gifts
			$q = $dbo->getQuery(true);

			$q->select('g.*');
			$q->select($dbo->qn('p.name', 'product_name'));
			$q->select($dbo->qn('p.price', 'product_price'));
			$q->select($dbo->qn('p.ready'));
			$q->select($dbo->qn('p.id_takeaway_menu'));
			$q->select($dbo->qn('o.name', 'option_name'));
			$q->select($dbo->qn('o.inc_price', 'option_price'));

			$q->from($dbo->qn('#__vikrestaurants_takeaway_deal_free_assoc', 'g'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'p') . ' ON ' . $dbo->qn('g.id_product') . ' = ' . $dbo->qn('p.id'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('g.id_option') . ' = ' . $dbo->qn('o.id'));

			$q->where($dbo->qn('g.id_deal') . ' = ' . $deal['id']);

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows() > 0)
			{
				$deals[$k]['gifts'] = $dbo->loadAssocList();
			}
			else
			{
				$deals[$k]['gifts'] = array();
			}
		}
		
		return $deals;
	}

	/**
	 * Checks whether the specified deal is available for the
	 * requested check-in date, time and service.
	 *
	 * @param 	array 		  $deal  The deal to check.
	 * @param 	TakeAwayCart  $cart  The cart instance.
	 *
	 * @return 	boolean 	  True if compatible, false otherwise.
	 *
	 * @since 	1.8
	 */
	protected static function isDealCompatible($deal, $cart)
	{
		// get week day
		$w = date('w', $cart->getCheckinTimestamp());

		// make sure the day of the week is supported
		if ($deal['days_filter'] && !in_array($w, $deal['days_filter']))
		{
			// week day not supported
			return false;
		}

		// make sure the selected time is supported
		if ($deal['shifts'])
		{
			// convert check-in time in minutes
			$hm = JHtml::_('vikrestaurants.time2min', $cart->getCheckinTime(true));

			$ok = false;

			// iterate working shifts
			for ($i = 0; $i < count($deal['shifts']) && !$ok; $i++)
			{
				// get working shift details
				$shift = JHtml::_('vikrestaurants.timeofshift', $deal['shifts'][$i]);

				// make sure the working shift exists and contains the selected time
				if ($shift && $shift->from <= $hm && $hm <= $shift->to)
				{
					$ok = true;
				}
			}

			if (!$ok)
			{
				// no compatible shifts
				return false;
			}
		}

		// check if the deal should be applied for a specific service
		if ($deal['service'] != 2 && $deal['service'] != $cart->getService())
		{
			// service not compatible
			return false;
		}

		return true;
	}

	/**
	 * This method sort the deals by pushing all the unactive items at the bottom.
	 *
	 * @param 	array 	$deals 	The list containing the deals to sort.
	 *
	 * @return 	array 	The deals sorted.
	 */
	public static function reOrderActiveDeals(array $deals)
	{
		if (count($deals) <= 1)
		{
			return $deals;
		}

		usort($deals, function($a, $b)
		{
			return $b['active'] - $a['active'];
		});
			
		return $deals;
	}
	
	/**
	 * Return the index of the deal which matches the specified parameters. 
	 * 
	 * @param 	array 	 $matches  The associative array containing all the keys to match.
	 * @param 	array 	 $deals    The array containing all the available deals. 
	 * 							   The deals should be retrieved with the method getAvailableFullDeals.
	 *
	 * @return 	integer  The index of the deal found, otherwise false.
	 * 
	 * @see 	getAvailableFullDeals()
	 */
	public static function isProductInDeals(array $matches, array $deals)
	{
		$keys_matches = array_keys($matches);

		if (!count($keys_matches))
		{
			return false;
		}
		
		foreach ($deals as $index => $deal)
		{
			foreach ($deal['products'] as $prod)
			{
				$found = true;
				
				for ($i = 0; $i < count($keys_matches) && $found; $i++)
				{
					$found = $found && (
						$matches[$keys_matches[$i]] == $prod[$keys_matches[$i]]
						// ignore id_option match when deal product doesn't specify it
						// all the options of the entry will be taken
						|| ($keys_matches[$i] == 'id_option' && $prod['id_option'] <= 0)  
					);
				}

				if ($found)
				{
					return $index;
				}
			}
		}
		
		return false;
	}

	/**
	 * Helper method used to extend the paths in which the rules
	 * should be found.
	 *
	 * @param 	mixed 	$path  The path to include (optional).
	 *
	 * @return 	array   A list of supported directories.
	 *
	 * @since 	1.8
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
		}

		// return list of included paths
		return $paths;
	}

	/**
	 * Returns a list of supported deals.
	 *
	 * @return 	array
	 *
	 * @since 	1.8
	 */
	public static function getSupportedDeals()
	{
		static $drivers = null;

		// fetch drivers only once
		if (is_null($drivers))
		{
			$drivers = array();

			// configuration array for deals
			$config = array();

			/**
			 * This event can be used to support custom deals.
			 * It is enough to include the directory containing
			 * the new rules. Only the files that inherits the
			 * DealRule class will be taken.
			 *
			 * Example:
			 * // register custom deal(s)
			 * DealsHandler::addIncludePath($path);
			 * // assign plugin configuration to deal
			 * $config['customdeal'] = $this->params;
			 *
			 * @param 	array  &$config  It is possible to inject the configuration for
			 * 							 a specific deal. The parameters have to be assigned
			 * 							 by using the deal file name.
			 *
			 * @return 	void
			 *
			 * @since 	1.8
			 */
			VREFactory::getEventDispatcher()->trigger('onLoadSupportedDeals', array(&$config));

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
					$class = 'DealRule' . ucfirst($filename);

					// make sure the class exists
					if (class_exists($class))
					{
						// fetch configuration params
						$params = isset($config[$filename]) ? $config[$filename] : array();

						// instantiate class
						$driver = new $class($params);

						// use driver only whether it is a valid instance
						if ($driver instanceof DealRule)
						{
							// map drivers by ID in order to override existing rules
							$drivers[$driver->getID()] = $driver;
						}
					}
				}
			}

			// sort by driver ID
			ksort($drivers, SORT_NUMERIC);
		}

		return $drivers;
	}

	/**
	 * Preflight checks before checking for some deals.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public static function beforeApply(&$cart)
	{
		// get all supported drivers
		$drivers = self::getSupportedDeals();

		foreach ($drivers as &$driver)
		{
			// execute rule preflight
			$driver->preflight($cart);
		}
	}

	/**
	 * Applies the specified deal to the cart.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 * @param 	array 		  $deal   The deal to apply.
	 *
	 * @return 	boolean 	  True if applied, false otherwise.
	 *
	 * @since 	1.8
	 */
	public static function apply(&$cart, $deal)
	{
		// get all supported drivers
		$drivers = self::getSupportedDeals();

		// make sure the deal is supported
		if (!isset($drivers[$deal['type']]))
		{
			// deal type not supported
			return false;
		}

		// apply deal
		return $drivers[$deal['type']]->apply($cart, $deal);
	}
}
