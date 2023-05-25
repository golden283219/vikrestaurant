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
 * Class used to apply the "Cooking" rule type for dishes.
 *
 * @since 1.8
 */
class ResCodesRuleCooking extends ResCodesRule
{
	/**
	 * @override
	 * Checks whether the specified group is supported
	 * by the rule. Available only for take-away.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return !strcasecmp($group, 'food');
	}

	/**
	 * Executes the rule.
	 *
	 * @param 	mixed  $food  Either the food details object or its PK.
	 *
	 * @return 	void
	 */
	public function execute($food)
	{
		if (is_object($food))
		{
			// extract ID from food object
			$food = $food->id;
		}
		else if (is_array($food))
		{
			// extract ID from foor array
			$food = $food['id'];
		}

		$data = array(
			'id'     => $food,
			'status' => 'cooking',
		);

		// get reservation table instance
		$table = JTableVRE::getInstance('resprod', 'VRETable');

		// update order
		$table->save($data);
	}
}
