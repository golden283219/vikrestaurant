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
 * Event used to obtain a list of reservations and orders
 * that haven't been downloaded yet.
 *
 * @since 1.8.4
 */
class PullOrders extends EventAPIs
{
	/**
	 * The custom action that the event have to perform.
	 * This method should not contain any exit or die function, 
	 * otherwise the event won't be stopped properly.
	 *
	 * All the information to return, should be echoed instead.
	 *
	 * @param 	array 		  $args 	 The provided arguments for the event.
	 * @param 	ResponseAPIs  $response  The response object for admin.
	 *
	 * @return 	mixed         The response to output or the error message (ErrorAPIs).
	 */
	protected function doAction(array $args, ResponseAPIs &$response)
	{
		if (!$args)
		{
			$input = JFactory::getApplication()->input;
			
			// no payload found, recover arguments from request
			$args = array();
			$args['reset'] = $input->getBool('reset', false);
		}

		// wrap arguments in a registry
		$args = new JRegistry($args);

		$eventArgs = array();
		$eventArgs['last_id'] = array();

		// check whether a reset was asked
		if (!$args->get('reset'))
		{
			// nope, get latest pulled IDs
			$eventArgs['last_id'] = $this->get('last_id', array());
		}

		// get current framework instance
		$apis = FrameworkAPIs::getInstance();
		
		try
		{
			/**
			 * Trigger GetOrdersList plugin to retrieve the orders and
			 * reservations according to the fetched thresholds.
			 *
			 * @see GetOrdersList
			 */
			$json = $apis->dispatch('get_orders_list', $eventArgs);
		}
		catch (Exception $e)
		{
			// register response here
			$response->setContent($e->getMessage());
			// propagate exception
			throw $e;
		}

		// prepare client response

		$response->setStatus(1);

		// decode response
		$result = json_decode($json);

		// make sure the plugin fetched at least an order/reservation
		if ($result->status && $result->orders)
		{
			$last_id = $this->get('last_id', array(0, 0));

			foreach ($result->orders as $order)
			{
				// index 0: restaurant, index 1: take-away
				$index = (int) $order->group;

				// take highest ID of the current group
				$last_id[$index] = max(array($last_id[$index], $order->id));
			}

			// save latest IDs within the event configuration
			$this->set('last_id', $last_id);
		}

		// return only the list of orders
		return $result->orders;
	}

	/**
	 * @override
	 * Returns the description of the plugin.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		// read the description HTML from a layout
		return JLayoutHelper::render('apis.plugins.pull_orders', array('plugin' => $this));
	}
}
