<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_map
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Take-Away Map module.
 *
 * @since 1.1.2
 */
class VikRestaurantsTakeAwayMapHelper
{
	/**
	 * Returns an array of supported locations.
	 * 
	 * @return  array
	 */
	public static function getLocations()
	{
		// load list of take-away addresses
		$locations = VikRestaurants::getTakeAwayOriginAddresses($assoc = true);

		// take only the published locations that have both the lat and lng specified
		$locations = array_filter($locations, function($l)
		{
			return !is_null($l->latitude) && !is_null($l->longitude) && $l->published;
		});

		$root = JUri::root();

		// prepare location attributes
		foreach ($locations as &$l)
		{
			// cast coordinates to floating point
			$l->latitude  = (float) $l->latitude;
			$l->longitude = (float) $l->longitude;

			// replace new lines with <br> tags
			$l->description = preg_replace("/\R+/", '', nl2br($l->description));

			if (!empty($l->image) && strpos($l->image, $root) !== 0)
			{
				// make image URL safe
				$l->image = $root . ltrim($l->image, '/');
			}
		}

		return $locations;
	}
}
