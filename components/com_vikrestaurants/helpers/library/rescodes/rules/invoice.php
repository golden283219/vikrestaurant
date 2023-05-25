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
 * Class used to apply the "Invoice" rule type.
 *
 * @since 1.8
 */
class ResCodesRuleInvoice extends ResCodesRule
{
	/**
	 * Checks whether the specified group is supported
	 * by the rule. Children classes can override this
	 * method to drop the support for a specific group.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return !strcasecmp($group, 'restaurant')
			|| !strcasecmp($group, 'takeaway');
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
		// get total amount
		if ($order instanceof VREOrderRestaurant)
		{
			$total = $order->bill_value;
		}
		else
		{
			$total = $order->total_to_pay;
		}

		// generate invoice only in case the total amount is higher than 0
		if ($total > 0)
		{
			// prepare invoice data
			$data = array();
			$data['id']         = 0;
			$data['id_order']   = $order->id;
			$data['group']      = $order instanceof VREOrderRestaurant ? 0 : 1;
			$data['notifycust'] = $order->purchaser_mail ? 1 : 0;

			if (JFactory::getApplication()->isClient('site'))
			{
				// load back-end language to properly generate the invoice
				VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);
			}

			// get record table
			$invoice = JTableVRE::getInstance('invoice', 'VRETable');

			// try to generate the invoice
			$invoice->save($data);
		}

		// close bill in case of restaurant reservation
		if ($order instanceof VREOrderRestaurant)
		{
			$data = array(
				'id'          => $order->id,
				'bill_closed' => 1,
			);

			// get reservation table instance
			$table = JTableVRE::getInstance('reservation', 'VRETable');

			// update time of stay
			$table->save($data);
		}
	}
}
