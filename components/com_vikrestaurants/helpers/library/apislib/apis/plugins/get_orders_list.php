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
 * Event used to return a list of restaurant reservation
 * and take-away order. It is possible to specify a
 * threshold in order to obtain only the reservations
 * with ID higher than the specified amounts.
 *
 * @since 1.7
 */
class GetOrdersList extends EventAPIs
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
			$args['last_id'] = $input->get('last_id', array(), 'uint');
		}

		if (empty($args['last_id']) || !is_array($args['last_id']) || count($args['last_id']) != 2)
		{
			// unset threshold
			$args['last_id'] = array(0, 0);
		}

		// prepare client response

		$response->setStatus(1);

		$obj = new stdClass;
		$obj->status = 1;
		$obj->orders = array();

		$dbo = JFactory::getDbo();

		// get restaurant reservations

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array(
				'id',
				'purchaser_nominative',
				'purchaser_mail',
				'created_on',
				'checkin_ts',
			)))
			->select('0 AS ' . $dbo->qn('group'))
			->from($dbo->qn('#__vikrestaurants_reservation'))
			->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'))
			->order($dbo->qn('id') . ' DESC');

		if ($args['last_id'][0])
		{
			// don't get orders already processed
			$q->where($dbo->qn('id') . ' > ' . $args['last_id'][0]);
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$obj->orders = $dbo->loadObjectList();

			// register response
			$response->appendContent('Restaurant reservations fetched: #' . count($obj->orders) . "\n");
		}

		// get takeaway orders

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array(
				'id',
				'purchaser_nominative',
				'purchaser_mail',
				'created_on',
				'checkin_ts',
			)))
			->select('1 AS ' . $dbo->qn('group'))
			->from($dbo->qn('#__vikrestaurants_takeaway_reservation'))
			->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'))
			->order($dbo->qn('id') . ' DESC');

		if ($args['last_id'][1])
		{
			// don't get orders already processed
			$q->where($dbo->qn('id') . ' > ' . $args['last_id'][1]);
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// load take-away orders
			$tmp = $dbo->loadObjectList();

			// register response
			$response->appendContent('Take-Away orders fetched: #' . count($tmp) . "\n");

			// merge reservations and orders
			$obj->orders = array_merge($obj->orders, $tmp);
		}

		// sort orders by creation date DESC
		usort($obj->orders, function($a, $b)
		{
			return $b->created_on - $a->created_on;
		});

		/**
		 * Let the application framework safely output the response.
		 *
		 * @since 1.8.4
		 */
		return $obj;
	}

	/**
	 * @override
	 * Returns the description of the plugin.
	 *
	 * @return 	string
	 */
	public function getDescription()
	{
		/**
		 * Read the description HTML from a layout.
		 *
		 * @since 1.8
		 */
		return JLayoutHelper::render('apis.plugins.get_orders_list', array('plugin' => $this));
	}
}
