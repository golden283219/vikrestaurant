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
 * Factory class used to load the correct router handler
 * according to the platform version.
 *
 * @since 1.8.3
 */
trait VRESefRouter
{
	/**
	 * Method used to find the item ID that matches the specified type.
	 *
	 * @param 	string 	 $type 	   The item type.
	 * @param 	array 	 $args 	   An associative array with the arguments to match.
	 * @param 	array    $exclude  A list of attributes that should be empty on the searched items.
	 *
	 * @return 	integer  The item ID that matches the type, "zero" otherwise.
	 *
	 * @uses 	matchItemArguments()
	 */
	protected function getProperItemID($type, array $args = array(), array $exclude = array())
	{
		$user   = JFactory::getUser();

		/**
		 * Compare the authorised view levels of the current user against
		 * the access level of the menu item for which we are searching.
		 * This will avoid displaying a login page in place of the expected one.
		 *
		 * @since 1.8.4
		 */
		$levels = $user->getAuthorisedViewLevels();

		foreach ($this->menu->getMenu() as $itemid => $item)
		{
			if (isset($item->query['option']) && isset($item->query['view'])
				&& $item->query['option'] == 'com_vikrestaurants' && $item->query['view'] == $type
				&& ($item->language == '*' || $item->language == $this->langtag)
				&& in_array($item->access, $levels)
				&& $this->matchItemArguments($item, $args, $exclude))
			{
				return $itemid;
			}
		}

		return 0;
	}

	/**
	 * Checks if the item matches all the specified arguments.
	 * The arguments must be contained within the query property.
	 *
	 * @param 	object 	 $item 	   The menu item object.
	 * @param 	array 	 $args 	   The associative array to check.
	 * @param 	array    $exclude  A list of attributes that should be empty on the searched items.
	 *
	 * @return 	boolean  True if the item matches, false otherwise.
	 */
	protected function matchItemArguments($item, $args, array $exclude = array())
	{
		if (!count($args) && !count($exclude))
		{
			// always compatible in case of empty arguments
			return true;
		}

		if (!isset($item->query))
		{
			// do not accept in case of empty query
			return false;
		}

		$query = $item->query;

		// iterate query to search for non scalar values
		foreach ($query as $k => $v)
		{
			/**
			 * Make sure the attribute specified by the menu item
			 * is not contained within the "excluded" list.
			 * 
			 * @since 1.8.5
			 */
			if (!empty($v) && in_array($k, $exclude))
			{
				return false;
			}

			if (is_array($v))
			{
				// validate array to make sure it matches the searched query
				if (!isset($args[$k]) || array_diff_assoc($args[$k], $v))
				{
					// missing match
					return false;
				}

				// unset value to avoid warnings in the next check
				unset($query[$k]);
				unset($args[$k]);
			}
		}

		// the difference between the array must return an empty array
		return !array_diff_assoc($args, $query);
	}

	/**
	 * Checks whether the router should be used.
	 *
	 * @return 	boolean
	 */
	protected function isActive()
	{
		// use router onl in case both SEF and URL Rewrite settings
		// have been enabled from the Joomla! global configuration
		if (!$this->app->get('sef') || !$this->app->get('sef_rewrite'))
		{
			return false;
		}

		// Security check to turn off the router in case of issues.
		// The router setting doesn't exist and will always return
		// TRUE by default. It is possible to insert a setting in
		// the database with value equals to 0 to disable the usage
		// of the router provided by VikRestaurants.
		if (VREFactory::getConfig()->getBool('router', true) === false)
		{
			return false;
		}

		return true;
	}
}
