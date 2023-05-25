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
 * Event used to check the availability of the tables.
 *
 * @since 1.7
 */
class TableAvailable extends EventAPIs
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
		$input = JFactory::getApplication()->input;

		if (!$args)
		{
			// get booking arguments from request
			$args = array();
			$args['date']     = $input->getString('date');
			$args['hourmin']  = $input->getString('hourmin');
			$args['people']   = $input->getUint('people');
			$args['id_table'] = $input->getInt('id_table', 0);
		}
		else
		{
			// do not init a default value for the other arguments because
			// VikRestaurants::isRequestReservationValid() will check the
			// request integrity for us
			$args['id_table'] = isset($args['id_table']) ? (int) $args['id_table'] : 0;
		}

		// validate request
		$code = VikRestaurants::isRequestReservationValid($args);

		if ($code !== 0)
		{
			// fetch error message from code
			$error = VikRestaurants::getResponseFromReservationRequest($code);
			// register response
			$response->setContent(JText::_($error));
			
			// bad request, throw exception
			throw new Exception($response->getContent(), 400);
		}

		// from now on the result should be ok even if there are no available tables
		$response->setStatus(1);

		// prepare response object for client
		$obj = new stdClass;
		$obj->status = 0;

		// extract hour and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
		
		// get checkin timestamp
		$checkin_ts = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);

		// make sure the reservations are allowed for the selected date time
		if (!VikRestaurants::isReservationsAllowedOn($checkin_ts))
		{
			// reservation blocked for today
			$obj->message = JText::_('VRNOMORERESTODAY');
			// register response
			$response->setContent($obj->message);

			/**
			 * Let the application framework safely output the response.
			 *
			 * @since 1.8.4
			 */
			return $obj;
		}

		// init special days manager
		$sdManager = new VRESpecialDaysManager('restaurant');
		// set checkin date
		$sdManager->setStartDate($args['date']);
		// set checkin time
		$sdManager->setCheckinTime($args['hourmin']);

		// get first available special day
		$specialDay = $sdManager->getFirst();

		if ($specialDay)
		{
			// make sure we haven't reached the threshold of allowed people
			if ($specialDay->canHostPeople($args) == false)
			{
				// unable to host the requested party size
				$obj->message = JText::_('VRRESNOSINGTABLEFOUND');
				// register response
				$response->setContent($obj->message);

				/**
				 * Let the application framework safely output the response.
				 *
				 * @since 1.8.4
				 */
				return $obj;
			}

			// check if we should ignore closing days
			$ignore_cd = $specialDay->ignoreClosingDays;
		}
		else
		{
			// never ignore closing days
			$ignore_cd = false;
		}
		
		// check if we have a closing day for the selected checkin date
		if (!$ignore_cd && VikRestaurants::isClosingDay($args))
		{
			// the selected day is closed
			$obj->message = JText::_('VRSEARCHDAYCLOSED');
			// register response
			$response->setContent($obj->message);

			/**
			 * Let the application framework safely output the response.
			 *
			 * @since 1.8.4
			 */
			return $obj;
		}

		// create availability search object
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $args['people']);

		if ($args['id_table'])
		{
			// check availability for the selected table
			if ($search->isTableAvailable($args['id_table']))
			{
				$obj->status = 1;
				$obj->table  = $args['id_table'];

				/**
				 * Return the details of the table.
				 *
				 * @since 1.8
				 */
				$obj->details = $search->getTable($args['id_table']);
			}
			else
			{
				// the selected table is not available
				$obj->message = JText::_('VRTNOTAVAILABLE');
			}
		}
		else
		{
			// get all available tables
			$tables = $search->getAvailableTables();

			if (count($tables))
			{
				// register first table found
				$obj->status = 1;
				$obj->table  = $tables[0]->id;

				/**
				 * Return a list containing all the available tables.
				 *
				 * @since 1.8
				 */
				$obj->list = $tables;
			}
			else
			{
				// no tables available
				$obj->message = JText::_('VRRESNOSINGTABLEFOUND');	
			}
		}

		/**
		 * Let the application framework safely output the response.
		 *
		 * @since 1.8.4
		 */
		return $obj;
	}

	/**
	 * @override
	 * Returns the title of the event.
	 *
	 * @return 	string 	The title of the event.
	 */
	public function getTitle()
	{
		return 'Table Availability';
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
		return JLayoutHelper::render('apis.plugins.table_available', array('plugin' => $this));
	}
}
