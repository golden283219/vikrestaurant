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
 * Class used to apply the "Close Bill" rule type for restaurant reservations.
 *
 * @since 1.8
 */
class ResCodesRuleCloseBill extends ResCodesRule
{
	/**
	 * @override
	 * Checks whether the specified group is supported
	 * by the rule. Available only for restaurant.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return !strcasecmp($group, 'restaurant');
	}

	/**
	 * Executes the rule.
	 *
	 * @param 	mixed  $order  The order details object.
	 *
	 * @return 	void
	 */
	public function execute($order)
	{
		// prepare save data
		$data = array(
			'id'          => $order->id,
			'bill_closed' => 1,
		);

		// get reservation table instance
		$table = JTableVRE::getInstance('reservation', 'VRETable');

		// close bill
		$table->save($data);
	}
}
