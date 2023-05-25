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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants quick reservation (module) controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerQuickres extends VREControllerAdmin
{
	/**
	 * AJAX end-point used to retrieve a list of tables
	 * available for the searched arguments.
	 *
	 * @return 	void
	 */
	public function findtable()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		$params = $this->getModuleParams();

		// get session lifetime specified in module configuration
		$session_lifetime = $params->get('session_lifetime', 15) * 60;
		
		// retrieve from session the last booking made
		$user_session = (int) $app->getUserState('vre.quickres.session', 0);

		// make sure the difference between the current time and the last booking time
		// if greater than the lifetime threshold
		if (!empty($user_session) && time() - $session_lifetime < $user_session)
		{
			// too many attempts
			$error = JText::sprintf('VRQRMOD_SPAMATTEMPT', ceil(($session_lifetime - (time() - $user_session)) / 60));
			// raise error
			UIErrorFactory::raiseError(403, $error);
		}

		// get search arguments
		$args = array();
		$args['date']    = $input->get('date', '', 'string');
		$args['hourmin'] = $input->get('hourmin', '', 'string');
		$args['people']  = $input->get('people', 0, 'uint');

		/**
		 * Flag used to check whether the customer already agreed
		 * that all the customers belong to the same family.
		 *
		 * @var   boolean
		 * @since 1.8
		 *
		 * @see   COVID-19
		 */
		$app->setUserState('vre.search.family', $input->getBool('family', false));

		// validate request
		$code = VikRestaurants::isRequestReservationValid($args);

		if ($code !== 0)
		{
			// fetch error message from code
			$error = VikRestaurants::getResponseFromReservationRequest($code);

			// raise error
			UIErrorFactory::raiseError(400, JText::_($error));
		}

		// extract hour and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
		
		// get checkin timestamp
		$ts = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);
		
		// make sure the reservations are allowed for the selected date time
		if (!VikRestaurants::isReservationsAllowedOn($ts))
		{
			// reservations blocked for today
			UIErrorFactory::raiseError(403, JText::_('VRNOMORERESTODAY'));
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
				UIErrorFactory::raiseError(403, JText::_('VRNOMORERESTODAY'));
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
			// the restaurant is closed for the selected date
			UIErrorFactory::raiseError(403, JText::_('VRSEARCHDAYCLOSED'));
		}
		
		/**
		 * Remove all the reservations that haven't been confirmed
		 * within the specified range of time (15 minutes by default).
		 *
		 * In this way, we can free the tables that were occupied
		 * before showing the availability to this customer.
		 */
		VikRestaurants::removeRestaurantReservationsOutOfTime();

		/**
		 * Look for COVID19 prevention measures.
		 *
		 * @see COVID-19
		 */
		$people = VikRestaurants::getPeopleSafeDistance($args['people']);

		// instantiate availability search
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $people);

		// get all the tables available for the specified search arguments
		$tables = $search->getAvailableTables();

		if (!$tables)
		{
			// No available tables, elaborate time hints.
			// The method will return the first 2 available times before
			// the selected check-in time and the next 2. Such as:
			// 12:00 | 12:30 | CURRENT | 13:30 | 14:30
			// It is possible to pass a number to the function below
			// to increase/decrease the number of suggested times.
			$hints = $search->getSuggestedTimes();

			// make sure we have at least a valid hint
			if (!array_filter($hints))
			{
				// no available table for the selected date and time
				UIErrorFactory::raiseError(404, JText::_('VRRESNOSINGTABLEFOUND'));
			}

			// return hints list
			echo json_encode(array(-1, $hints));
			exit;
		}

		// build summary text
		$date_str = JText::sprintf(
			'VRQRMOD_DATETIMESTR',
			date(VikRestaurants::getDateFormat(), $ts),
			date(VikRestaurants::getTimeFormat(), $ts),
			$args['people']
		);

		// find all available rooms
		$rooms = array();

		foreach ($tables as $t)
		{
			if (!isset($rooms[$t->id_room]))
			{
				$rm = new stdClass;
				$rm->id   = $t->id_room;
				$rm->name = $t->room_name;
				// assign first available table too
				$rm->tid = $t->id;

				$rooms[$t->id_room] = $rm;
			}
		}

		// translate rooms
		VikRestaurants::translateRooms($rooms);

		$table = array(
			'rid' => $tables[0]->id_room,
			'tid' => $tables[0]->id,
		);
		
		$app->setUserState('vre.quickres.reservation', array(
			'args'  => $args,
			'rooms' => $rooms,
			'table' => $table,
		));

		// do not preserve keys in JSON
		$rooms = array_values($rooms);

		if (count($rooms) == 1)
		{
			// auto-select the only one available
			$rooms[0]->str = JText::sprintf('VRQRMOD_ROOMSELSTR', $rooms[0]->name); 
		}
		
		echo json_encode(array(1, $date_str, $rooms));
		exit;
	}

	/**
	 * AJAX end-point used to pick a room.
	 *
	 * @return 	void
	 */
	public function selectroom()
	{
		$app = JFactory::getApplication();

		// retrieve last search
		$search = $app->getUserState('vre.quickres.reservation', null);
		
		if (empty($search))
		{
			// raise error, search not started yet
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get room ID
		$id_room = $app->input->getInt('id_room');

		if (!isset($search['rooms'][$id_room]))
		{
			// missing selection
			echo json_encode(array(0));
			exit;
		}

		$room = $search['rooms'][$id_room];

		$search['table']['rid'] = $room->id;
		$search['table']['tid'] = $room->tid;

		$app->setUserState('vre.quickres.reservation', $search);

		echo json_encode(array(1, JText::sprintf('VRQRMOD_ROOMSELSTR', $room->name)));
		exit;
	}

	/**
	 * AJAX end-point used to save a reservation.
	 *
	 * @return 	void
	 */
	public function save()
	{
		$app    = JFactory::getApplication();
		$dbo    = JFactory::getDbo();
		$user   = JFactory::getUser();
		$config = VREFactory::getConfig();

		// retrieve last search
		$search = $app->getUserState('vre.quickres.reservation', null);
		
		if (empty($search))
		{
			// raise error, search not started yet
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get module parameters
		$params = $this->getModuleParams();

		$vik = VREApplication::getInstance();

		/**
		 * Validate ReCaptcha before saving the reservation.
		 * The ReCaptcha must be enabled globally and from the
		 * configuration of the module.
		 *
		 * @since 1.8.2
		 */
		if ($vik->isGlobalCaptcha() && $params->get('recaptcha') && !$vik->reCaptcha('check'))
		{
			// invalid captcha
			UIErrorFactory::raiseError(400, JText::_('PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL'));
		}

		// prepare event dispatcher
		$dispatcher = VREFactory::getEventDispatcher();

		// always load tables from the back-end
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');

		// recover searched arguments
		$args = $search['args'];
		// recover selected table
		$args['table'] = $search['table']['tid'];

		// validate request
		$code = VikRestaurants::isRequestReservationValid($args);

		if ($code !== 0)
		{
			// fetch error message from code
			$error = VikRestaurants::getResponseFromReservationRequest($code);

			// raise error, invalid request
			UIErrorFactory::raiseError(400, JText::_($error));
		}

		$data = array();

		try
		{
			// get restaurant custom fields
			$data['custom_f'] = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_RESTAURANT, $match);
		}
		catch (Exception $e)
		{
			// invalid custom fields
			UIErrorFactory::raiseError(400, $e->getMessage());
		}

		// inject purchaser details fetched with the custom fields
		$data = array_merge($data, $match);

		// extract hour and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
		
		// get checkin timestamp
		$data['checkin_ts'] = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);
		
		// set number of people
		$data['people'] = $args['people'];

		// make sure the reservations are allowed for the selected date time
		if (!VikRestaurants::isReservationsAllowedOn($data['checkin_ts']))
		{
			// reservation blocked for today
			UIErrorFactory::raiseError(404, JText::_('VRNOMORERESTODAY'));
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
				UIErrorFactory::raiseError(404, JText::_('VRNOMORERESTODAY'));
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
			UIErrorFactory::raiseError(404, JText::_('VRSEARCHDAYCLOSED'));
		}
		
		/**
		 * Look for COVID19 prevention measures.
		 *
		 * @since 1.8
		 *
		 * @see COVID-19
		 */
		$people = VikRestaurants::getPeopleSafeDistance($args['people'], $app->getUserState('vre.search.family', false));

		// create availability search object
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $people);

		// make sure the table is available
		$available = $search->isTableAvailable($args['table'], null, $cluster);

		if (!$available)
		{
			// get details of selected table
			$tmp = $search->getTable($args['table']);

			// unset selected table
			$args['table'] = null;

			// get all available tables
			$tables = $search->getAvailableTables();

			/**
			 * The table is no more available. In case the selection
			 * of the table is not allowed, we should automatically search
			 * for a different available table, so that the customer
			 * won't have to restart with the booking process.
			 *
			 * @since 1.8.3  Rely on the module configuration instead of the
			 *               default reservation requirements setting.
			 */
			if ($params->get('chooseroom') == 1)
			{
				// rooms selection allowed, search for a table available
				// for the room selected during the booking process
				$id_room = $tmp ? $tmp->id_room : 0;

				for ($i = 0; $i < count($tables) && !$args['table']; $i++)
				{
					if ($tables[$i]->id_room == $id_room)
					{
						// table found
						$args['table'] = $tables[$i]->id;
					}
				}
			}
			else
			{
				// no table/rooms selection, search for the first available table
				if (count($tables))
				{
					// get first available table
					$args['table'] = $tables[0]->id;
				}
			}

			if ($args['table'])
			{
				// make sure again the table is available and
				// retrieve a cluster of assigned tables, if any
				$available = $search->isTableAvailable($args['table'], null, $cluster);

				if (!$available)
				{
					// something went wrong, unset table
					$args['table'] = null;
				}
			}
			
			// make sure the table found is now available
			if (!$args['table'])
			{
				// no available tables for the selected date and time
				UIErrorFactory::raiseError(404, JText::_('VRERRTABNOLONGAV'));
			}
		}

		// register table ID in reservation data
		$data['id_table'] = $args['table'];

		// get default status from configuration, which will
		// be used only in case the reservation doesn't require
		// a deposit and the system doesn't support any payments
		$data['status'] = $config->get('defstatus');

		// set current language
		$data['langtag'] = JFactory::getLanguage()->getTag();

		// save user data
		if (!$user->guest)
		{
			// lookup used to inject reservation data within customer data
			$lookup = array(
				'billing_name'         => 'purchaser_nominative',
				'billing_mail'         => 'purchaser_mail',
				'billing_phone'        => 'purchaser_phone',
				'billing_phone_prefix' => 'purchaser_prefix',
				'country_code'         => 'purchaser_country',
				'fields'               => 'custom_f',
			);

			$customer_data = array(
				'id'  => 0,
				'jid' => $user->id,
			);

			foreach ($lookup as $ck => $dk)
			{
				// make sure the related value is set
				if (!empty($data[$dk]))
				{
					$customer_data[$ck] = $data[$dk];
				}
			}

			// get customer
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn('#__vikrestaurants_users'))
				->where($dbo->qn('jid') . ' = ' . $user->id);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// customer already exists, do update
				$customer_data['id'] = (int) $dbo->loadResult();
			}

			// get customer table
			$customer = JTableVRE::getInstance('customer', 'VRETable');

			// insert/update customer
			if ($customer->save($customer_data))
			{
				// assign reservation to saved customer
				$data['id_user'] = $customer->id;
			}
		}

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// save reservation
		if (!$reservation->save($data))
		{
			// an error occurred while saving the reservation
			UIErrorFactory::raiseError(500, JText::_('VRINSERTRESERVATIONERROR'));
		}

		// obtain reservation properties for later use
		$resData = (object) $reservation->getProperties();

		/**
		 * Check if the table has been chosen in combination
		 * with a cluster of tables.
		 *
		 * @since 1.8
		 */
		if ($cluster)
		{
			// iterate additional tables
			foreach ($cluster as $id_table)
			{
				$tmp = array(
					// reset ID for insert
					'id' => 0,
					// keep the same SID
					'sid' => $reservation->sid,
					// keep the same confirmation key
					'conf_key' => $reservation->conf_key,
					// use a different table
					'id_table' => $id_table,
					// link record to parent reservation
					'id_parent' => $resData->id,
				);

				// save reservation for another table
				$reservation->save($tmp);
			}
		}

		VRELoader::import('library.mail.factory');

		// get notification e-mail for customer
		$customerMail = VREMailFactory::getInstance('restaurant', 'customer', $resData->id);

		// check if the customer should receive the notification e-mail
		if ($customerMail->shouldSend())
		{
			// send e-mail notification
			$customerMail->send();
		}
		
		// get notification e-mail for admin and operators
		$adminMail = VREMailFactory::getInstance('restaurant', 'admin', $resData->id);

		// check if the admin/operator should receive the notification e-mail
		if ($adminMail->shouldSend())
		{
			// send e-mail notification
			$adminMail->send();
		}
		
		// send SMS notification in case the reservation was confirmed
		if ($resData->status == 'CONFIRMED')
		{
			// dispatch SMS (0: restaurant reservation)
			VikRestaurants::sendSmsAction($resData->purchaser_phone, $resData->id, 0);
		}

		// fetch fields summary
		$custom_fields_summary = array(
			$resData->purchaser_nominative,
			$resData->purchaser_mail,
			$resData->purchaser_phone,
		);

		$custom_fields_summary = implode(' ', array_filter($custom_fields_summary));

		/**
		 * Use Itemid provided by quick reservation module while routing
		 * the order summary URL.
		 *
		 * @since 1.7.4
		 */
		$url = JRoute::_(sprintf(
			'index.php?option=com_vikrestaurants&view=reservation&ordnum=%s&ordkey=%s&Itemid=%d',
			$resData->id,
			$resData->sid,
			(int) $params->get('itemid')
		), false);
		
		// unset reservation details
		$app->setUserState('vre.quickres.reservation', null);
		// register last booking session
		$app->setUserState('vre.quickres.session', time());
		
		echo json_encode(array(1, $custom_fields_summary, $url));
		exit;
	}

	/**
	 * Returns the parameters of Quick Reservation module
	 * published on the currect Item ID.
	 *
	 * @return 	JRegistry  The module parameters.
	 *
	 * @since 	1.8.2
	 */
	protected function getModuleParams()
	{
		jimport('joomla.application.module.helper');
		$module = JModuleHelper::getModule('mod_vikrestaurants_quickres');

		return new JRegistry(json_decode($module->params));
	}
}
