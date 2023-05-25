<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_quickres
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Quick Reservation module.
 *
 * @since 1.3
 */
class VikRestaurantsQuickResHelper
{
	/**
	 * Get the list of special days for the restaurant.
	 *
	 * @return 	array 	The special days.
	 *
	 * @deprecated 1.5 without replacement
	 */
	public static function getSpecialDays()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('*')
			->from($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('group') . ' = 1');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return array();			
		}

		return $dbo->loadAssocList();
	}

	/**
	 * Get the list of custom fields for the restaurant.
	 *
	 * @return 	array 	The custom fields.
	 */
	public static function getCustomFields()
	{
		/**
		 * Recover custom fields by using the
		 * native helper class.
		 *
		 * @since 1.4
		 */
		$fields = VRCustomFields::getList(0, VRCustomFields::FILTER_EXCLUDE_SEPARATOR);
		
		// translate fields
		VRCustomFields::translate($fields);

		return $fields;
	}

	/**
	 * Get the stored fields (if any) of the logged user.
	 *
	 * @return 	array 	The user fields.
	 */
	public static function getUserFields()
	{
		/**
		 * Recover customer details by using
		 * the native helper method.
		 */
		$user = VikRestaurants::getCustomer();

		if (!$user)
		{
			// customer doesn't exist
			return array();
		}

		// obtain custom fields
		return $user->fields->restaurant;
	}
}
