<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_event
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Event module.
 *
 * @since 1.3.1
 */
class VikRestaurantsEventHelper
{
	/**
	 * Return the special day selected from the customer.
	 *
	 * @param 	JRegistry 	$param 	The configuration object.
	 *
	 * @return 	array 		The special day.
	 */
	public static function getSpecialDay($params)
	{	
		$dbo = JFactory::getDbo();
		
		$id = $params->get('special_day');

		$q = $dbo->getQuery(true);

		$q->select('*')
			->from($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('id') . ' = ' . (int) $id);
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadAssoc();
		}
		
		return array();
	}
}
