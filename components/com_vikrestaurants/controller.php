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

// import joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of VikRestaurants component.
 *
 * @since 1.0
 */
class VikRestaurantsController extends JControllerVRE
{
	/**
	 * Display task.
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$input = JFactory::getApplication()->input;

		$view = strtolower($input->get('view', ''));

		// check if we should invoke a method before displaying the view
		if (method_exists($this, $view))
		{
			// invoke method
			$this->{$view}();
		}

		// forbid access to disabled areas
		switch ($view)
		{
			case 'restaurants':
			case 'search':
			case 'confirmres':
				if (!VikRestaurants::isRestaurantEnabled())
				{
					throw new Exception(JText::_('VRRESTAURANTDISABLED'), 403);
				}
				break;

			case 'takeaway':
			case 'takeawayconfirm':
			case 'takeawayitem':
				if (!VikRestaurants::isTakeawayEnabled())
				{
					throw new Exception(JText::_('VRTAKEAWAYDISABLED'), 403);
				}
				break;
		}

		parent::display();
	}
	
	/**
	 * Validate the request made before checking
	 * for any available tables.
	 *
	 * @return 	void
	 */
	public function search()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		// always reset selected menus when
		// accessing the search view
		$session = JFactory::getSession();
		$session->set('vrmenus', null, 'vrcart');

		$args = array();
		$args['date']    = $input->getString('date'); 
		$args['hourmin'] = $input->getString('hourmin');
		$args['people']  = $input->getUint('people');

		$itemid = $input->get('Itemid', null, 'uint');

		// fetch error URL
		$error_url = 'index.php?option=com_vikrestaurants&view=restaurants';

		if ($args)
		{
			$error_url .= '&' . http_build_query($args);
		}

		if ($itemid)
		{
			$error_url .= '&Itemid=' . $itemid;
		}

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

			$app->enqueueMessage(JText::_($error), 'error');
			$app->redirect(JRoute::_($error_url, false));
			exit;
		}

		// extract hour and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
		
		// get checkin timestamp
		$ts = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);
		
		// make sure the reservations are allowed for the selected date time
		if (!VikRestaurants::isReservationsAllowedOn($ts))
		{
			// reservation blocked for today
			$app->enqueueMessage(JText::_('VRNOMORERESTODAY'), 'error');
			$app->redirect(JRoute::_($error_url, false));
			exit;
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
				$app->enqueueMessage(JText::_('VRNOMORERESTODAY'), 'error');
				$app->redirect(JRoute::_($error_url, false));
				exit;
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
			$app->enqueueMessage(JText::_('VRSEARCHDAYCLOSED'), 'error');
			$app->redirect(JRoute::_($error_url, false));
			exit;
		}
		
		/**
		 * Remove all the reservations that haven't been confirmed
		 * within the specified range of time (15 minutes by default).
		 *
		 * In this way, we can free the tables that were occupied
		 * before showing the availability to this customer.
		 * 
		 * @since 1.8
		 */
		VikRestaurants::removeRestaurantReservationsOutOfTime();
	}
	
	/**
	 * Performs additional checks before letting the 
	 * customers access the confirmation page.
	 *
	 * @return 	void
	 */
	public function confirmres()
	{	
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$session = JFactory::getSession();
		
		$args = array();
		$args['date']    = $input->getString('date'); 
		$args['hourmin'] = $input->getString('hourmin');
		$args['people']  = $input->getUint('people');
		$args['table']   = $input->getUint('table');
		// get list of selected menus, if requested
		$args['menus'] = $input->get('menus', array(), 'array');

		// recover family flag from user state
		$family = $app->getUserState('vre.search.family', false);

		$itemid = $input->get('Itemid', null, 'uint');

		// fetch error URL
		$error_url = 'index.php?option=com_vikrestaurants&date=' . $args['date'] . '&hourmin=' . $args['hourmin'] . '&people=' . $args['people'];

		if ($itemid)
		{
			$error_url .= '&Itemid=' . $itemid;
		}
		
		// validate request
		$code = VikRestaurants::isRequestReservationValid($args);

		if ($code !== 0)
		{
			// fetch error message from code
			$error = VikRestaurants::getResponseFromReservationRequest($code);

			$app->enqueueMessage(JText::_($error), 'error');
			// back to restaurants view in case of malformed request
			$app->redirect(JRoute::_($error_url . '&view=restaurants', false));
			exit;
		}
		
		// validate selected menus
		$session_menus = $session->get('vrmenus', null, 'vrcart');
		
		// make sure the menus haven't been yet validated
		if (empty($session_menus) && VikRestaurants::isMenusChoosable($args))
		{
			// validate menus
			if (!VikRestaurants::validateSelectedMenus($args))
			{
				$app->enqueueMessage(JText::_('VRSEARCHMENUSNOTVALID'), 'error');
				// back to search view in case of missing menus selection
				$app->redirect(JRoute::_($error_url . '&view=search' . ($family ? '&family=1' : ''), false));
				exit;
			}

			// valid menus, register in session
			$session->set('vrmenus', $args['menus'], 'vrcart');
		}

		// get submitted coupon key, if any
		$coupon = $input->get('couponkey', null, 'string');
		// get previous coupon code
		$prev = $session->get('vr_coupon_data', null);

		if (!$coupon)
		{
			// coupon not submitted, validate previous one (if set)
			$coupon = $prev;
		}

		// check if the coupon code have been submitted
		if ($coupon)
		{
			// validate coupon
			$coupon = VikRestaurants::validateCoupon($coupon, $args['people']);

			if ($coupon)
			{
				// coupon found, save it in session
				$session->set('vr_coupon_data', $coupon);

				// display successful message if the coupon changed
				if (!$prev || $prev->code != $coupon->code)
				{
					$app->enqueueMessage(JText::_('VRCOUPONFOUND'));
				}
			}
			else
			{
				// invalid coupon, unset it
				$session->set('vr_coupon_data', null);
				// display failure message
				$app->enqueueMessage(JText::_('VRCOUPONNOTVALID'), 'error');
			}
		}
	}
	
	/**
	 * Task used to save a restaurant reservation.
	 *
	 * @return 	void
	 */
	public function saveorder()
	{	
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$dbo     = JFactory::getDbo();
		$user    = JFactory::getUser();
		$session = JFactory::getSession();
		$config  = VREFactory::getConfig();

		// prepare event dispatcher
		$dispatcher = VREFactory::getEventDispatcher();

		// always load tables from the back-end
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');

		// first of all, validate search arguments
		$args = array();
		$args['date']    = $input->getString('date'); 
		$args['hourmin'] = $input->getString('hourmin');
		$args['people']  = $input->getUint('people');
		$args['table']   = $input->getUint('table');

		// get selected menus from session, if any
		$args['menus'] = $session->get('vrmenus', null, 'vrcart');

		// recover family flag from user state
		$family = $app->getUserState('vre.search.family', false);

		$itemid = $input->get('Itemid', null, 'uint');

		// fetch error URL
		$error_url = 'index.php?option=com_vikrestaurants&date=' . $args['date'] . '&hourmin=' . $args['hourmin'] . '&people=' . $args['people'];

		if ($itemid)
		{
			$error_url .= '&Itemid=' . $itemid;
		}

		/**
		 * Validate session token before to proceed.
		 *
		 * @since 1.8
		 */
		if (!JSession::checkToken())
		{
			// invalid token, back to confirm page
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$app->redirect(JRoute::_($error_url . '&view=confirmres&table=' . $args['table'], false));
			exit;
		}

		$vik = VREApplication::getInstance();

		/**
		 * Validate ReCaptcha before processing the reservation request.
		 * The ReCaptcha is never asked to registered customers.
		 *
		 * @since 1.8.2
		 */
		if ($user->guest && $vik->isGlobalCaptcha() && !$vik->reCaptcha('check'))
		{
			// invalid captcha
			$app->enqueueMessage(JText::_('PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL'), 'error');
			$app->redirect(JRoute::_($error_url . '&view=confirmres&table=' . $args['table'], false));
			exit;
		}
		
		// validate request
		$code = VikRestaurants::isRequestReservationValid($args);

		if ($code !== 0)
		{
			// fetch error message from code
			$error = VikRestaurants::getResponseFromReservationRequest($code);

			$app->enqueueMessage(JText::_($error), 'error');
			// back to restaurants view in case of malformed request
			$app->redirect(JRoute::_($error_url . '&view=restaurants', false));
			exit;
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
			$app->enqueueMessage($e->getMessage(), 'error');
			// back to confirmation view
			$app->redirect(JRoute::_($error_url . '&view=confirmres&table=' . $args['table'], false));
			exit;
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
			$app->enqueueMessage(JText::_('VRNOMORERESTODAY'), 'error');
			// back to first step
			$app->redirect(JRoute::_($error_url . '&view=restaurant', false));
			exit;
		}

		// check if the customers are allowed to choose menus
		$choose_menu = VikRestaurants::isMenusChoosable($args);
		// make sure the customers completed the menus selection
		if ($choose_menu && !VikRestaurants::validateSelectedMenus($args))
		{
			// invalid menus
			$app->enqueueMessage(JText::_('VRSEARCHMENUSNOTVALID'), 'error');
			// back to search view in case of missing menus selection
			$app->redirect(JRoute::_($error_url . '&view=search' . ($family ? '&family=1' : ''), false));
			exit;
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
				$app->enqueueMessage(JText::_('VRNOMORERESTODAY'), 'error');
				// back to first step
				$app->redirect(JRoute::_($error_url . '&view=restaurant', false));
				exit;
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
			$app->enqueueMessage(JText::_('VRSEARCHDAYCLOSED'), 'error');
			// back to first step
			$app->redirect(JRoute::_($error_url . '&view=restaurant', false));
			exit;
		}
		
		/**
		 * Look for COVID19 prevention measures.
		 *
		 * @since 1.8
		 *
		 * @see COVID-19
		 */
		$people = VikRestaurants::getPeopleSafeDistance($args['people'], $family);

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
			 */
			if ($config->getUint('reservationreq') == 1)
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
			else if ($config->getUint('reservationreq') == 2)
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
				$app->enqueueMessage(JText::_('VRERRTABNOLONGAV'), 'error');
				// back to search view
				$app->redirect(JRoute::_($error_url . '&view=search' . ($family ? '&family=1' : ''), false));
				exit;
			}
		}

		// register table ID in reservation data
		$data['id_table'] = $args['table'];

		// get total deposit to leave
		$data['deposit']  = VikRestaurants::getTotalDeposit($args);
		$data['tot_paid'] = 0;

		// get default status from configuration, which will
		// be used only in case the reservation doesn't require
		// a deposit and the system doesn't support any payments
		$data['status'] = $config->get('defstatus');

		// check if the customer should leave a deposit and make
		// sure the system owns at least a published payment
		if ($data['deposit'] > 0 && Vikrestaurants::hasPayment($group = 1))
		{
			// get selected payment
			$data['id_payment'] = $input->getUint('vrpaymentradio');

			// make sure the selected payment exists
			$payment = VikRestaurants::hasPayment($group = 1, $data['id_payment']);

			if (!$payment)
			{
				// the selected payment does not exist
				$app->enqueueMessage(JText::_('VRERRINVPAYMENT'), 'error');
				// back to confirmation view
				$app->redirect(JRoute::_($error_url . '&view=confirmres&table=' . $args['table'], false));
				exit;
			}

			// auto-confirm reservations according to the configuration of
			// the payment, otherwise force PENDING status to let the
			// customers be able to start a transaction
			$data['status'] = $payment->setconfirmed ? 'CONFIRMED' : 'PENDING';

			if ($payment->charge != 0)
			{
				// apply payment charge to total deposit
				if ($payment->percentot == 1)
				{
					// percentage charge based on total deposit
					$data['pay_charge'] = $data['deposit'] * (float) $payment->charge / 100;
				}
				else
				{
					// fixed amount
					$data['pay_charge'] = (float) $payment->charge;
				}

				/**
				 * Always round the calculated charge to 2 decimals, in order
				 * to avoid roundings when saving the amount in the database.
				 *
				 * @since 1.8
				 */
				$data['pay_charge'] = round($data['pay_charge'], 2, PHP_ROUND_HALF_UP);

				// increase/decrease total deposit by the payment charge
				$data['deposit'] += $data['pay_charge'];
			}
		}

		// validate coupon only once we are sure that the
		// reservation is ready to be saved, as GIFT coupons
		// have to be permanently removed from the database
		$coupon = $session->get('vr_coupon_data', null);
		
		if (!empty($coupon))
		{	
			// get coupon table
			$couponTable = JTableVRE::getInstance('coupon', 'VRETable');

			// redeem coupon code
			$data['coupon_str'] = $couponTable->redeem($coupon->code);
			
			// unset coupon code from session
			$session->set('vr_coupon_data', null);
		}

		/**
		 * Try to calculate the bill value according to
		 * the selected menus.
		 *
		 * @since 1.8
		 */
		if ($args['menus'] && $choose_menu)
		{
			$data['bill_value'] = 0;

			$menu_ids = array_map('intval', array_keys($args['menus']));

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('id', 'cost')))
				->from($dbo->qn('#__vikrestaurants_menus'))
				->where($dbo->qn('id') . ' IN (' . implode(',', $menu_ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				foreach ($dbo->loadObjectList() as $m)
				{
					// Take the cost of the menu and multiply it by the
					// number of times that it has been selected. Then
					// sum the result to the bill value.
					$data['bill_value'] += $m->cost * $args['menus'][$m->id];
				}
			}
		}

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
			$app->enqueueMessage(JText::_('VRINSERTRESERVATIONERROR'), 'error');
			// back to the search view
			$app->redirect(JRoute::_($error_url . '&view=search' . ($family ? '&family=1' : ''), false));
			exit;
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

		// assign the selected menus to the reservation, if any		
		if ($choose_menu)
		{
			// get reservations menus table
			$resmenu = JTableVRE::getInstance('resmenu', 'VRETable');

			foreach ($args['menus'] as $id_menu => $quantity)
			{
				$menu = array(
					'id'             => 0,
					'id_menu'        => $id_menu,
					'id_reservation' => $resData->id,
					'quantity'       => $quantity,
				);

				// insert the reservation menu
				$resmenu->save($menu);
				$resmenu->reset();
			}
		}
		
		// unset menus from session
		$session->set('vrmenus', null, 'vrcart');

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

		// fetch redirect URL
		$url = sprintf(
			'index.php?option=com_vikrestaurants&view=reservation&ordnum=%d&ordkey=%s%s',
			$resData->id,
			$resData->sid,
			$itemid ? '&Itemid=' . $itemid : ''
		);
		
		// redirect to order summary view
		$app->redirect(JRoute::_($url, false));
	}
	
	/**
	 * End-point used by the payment gateways as notification URL.
	 * This is task validates the transaction details returned by
	 * the bank.
	 *
	 * Only for RESTAURANT reservations.
	 *
	 * @return 	void
	 */
	public function notifypayment()
	{
		$dispatcher = VREFactory::getEventDispatcher();

		$input = JFactory::getApplication()->input;
			
		$oid = $input->getUint('ordnum');
		$sid = $input->getAlnum('ordkey');
		
		$dbo = JFactory::getDbo();

		// Get reservation details (filter by ID and SID).
		// In case the reservation doesn't exist, an
		// exception will be thrown.
		$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));

		/**
		 * Added support for online bill payment.
		 * Temporarily revert status to PENDING to allow
		 * payments in case the bill is closed and the
		 * remaining balance is higher than 0.
		 *
		 * @since 1.8.1
		 */
		if ($reservation->bill_closed && $reservation->bill_value - $reservation->tot_paid)
		{
			$reservation->status = 'PENDING';
		}

		/**
		 * This event is triggered every time a payment tries
		 * to validate a transaction made.
		 *
		 * DOES NOT trigger in case the reservation doesn't exist.
		 *
		 * @param 	mixed 	&$reservation  The details of the restaurant reservation.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.1
		 */
		$dispatcher->trigger('onReceivePaymentNotification', array(&$reservation));

		/**
		 * Allow the payment for REMOVED reservations because they
		 * have been probably paid while they were PENDING.
		 * 
		 * @since 1.8
		 */
		$accepted = array(
			'PENDING',
			'REMOVED',
		);
		
		// make sure the reservation can be paid
		if (!in_array($reservation->status, $accepted))
		{
			// status not allowed
			throw new Exception('The current status of the reservation does not allow any payments.', 403);
		}

		// get payment details (unpublished payments are supported)
		$payment = VikRestaurants::hasPayment($group = 1, $reservation->id_payment, $strict = false);

		// make sure the payment exists
		if (!$payment)
		{
			throw new Exception('The selected payment does not exist.', 404);
		}

		$vik = VREApplication::getInstance();

		$config = VREFactory::getConfig();
			
		// fetch transaction data	
		$paymentData = array();

		/**
		 * The payment URLs are correctly routed for external usage.
		 *
		 * @since 1.8
		 */
		$return_url = $vik->routeForExternalUse("index.php?option=com_vikrestaurants&view=reservation&ordnum={$oid}&ordkey={$sid}", false);
		$error_url  = $vik->routeForExternalUse("index.php?option=com_vikrestaurants&view=reservation&ordnum={$oid}&ordkey={$sid}", false);
		
		/**
		 * Include the Notification URL in both the PLAIN and ROUTED formats.
		 *
		 * @since 1.8.1
		 */
		$notify_url = "index.php?option=com_vikrestaurants&task=notifypayment&ordnum={$oid}&ordkey={$sid}";

		/**
		 * Calculate total amount to pay.
		 *
		 * @since 1.8.1
		 */
		if ($reservation->bill_closed && $reservation->bill_value)
		{
			// pay remaining balance after ordering
			$total_to_pay = $reservation->bill_value;
		}
		else
		{
			// leave a deposit online
			$total_to_pay = $reservation->deposit;
		}

		// subtract amount already paid
		$total_to_pay = max(array(0, $total_to_pay - $reservation->tot_paid));

		$paymentData['type']                 = 'restaurant.validate';
		$paymentData['oid']                  = $reservation->id;
		$paymentData['sid']                  = $reservation->sid;
		$paymentData['tid']                  = 0;
		$paymentData['transaction_name']     = JText::sprintf('VRRESTRANSACTIONNAME', $config->get('restname'));
		$paymentData['transaction_currency'] = $config->get('currencyname');
		$paymentData['currency_symb']        = $config->get('currencysymb');
		$paymentData['tax']                  = 0;
		$paymentData['return_url']           = $return_url;
		$paymentData['error_url']            = $error_url;
		$paymentData['notify_url']           = $vik->routeForExternalUse($notify_url, false);
		$paymentData['notify_url_plain']     = JUri::root() . $notify_url;
		$paymentData['total_to_pay']         = $total_to_pay;
		$paymentData['total_net_price']      = $total_to_pay;
		$paymentData['total_tax']            = 0;
		$paymentData['payment_info']         = $payment;
		$paymentData['details'] = array(
			'purchaser_nominative' => $reservation->purchaser_nominative,
			'purchaser_mail'       => $reservation->purchaser_mail,
			'purchaser_phone'      => $reservation->purchaser_phone,
		);

		/**
		 * Trigger event to manipulate the payment details.
		 *
		 * @param 	array 	&$order   The transaction details.
		 * @param 	mixed 	&$params  The payment configuration as array or JSON.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.1
		 */
		$dispatcher->trigger('onInitPaymentTransaction', array(&$paymentData, &$payment->params));
			
		/**
		 * Instantiate the payment using the platform handler.
		 *
		 * @since 1.8
		 */
		$obj = $vik->getPaymentInstance($payment->file, $paymentData, $payment->params);
		
		// validate payment transaction
		$result = $obj->validatePayment();

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$table = JTableVRE::getInstance('reservation', 'VRETable');
		
		// check for a successful result
		if ($result['verified'])
		{
			if (!empty($result['tot_paid']))
			{
				// increase total amount paid
				$reservation->tot_paid += (float) $result['tot_paid'];
			}

			// update reservation status
			$reservation->status = 'CONFIRMED';
			
			// prepare save data
			$data = array(
				'id'       => $reservation->id,
				'status'   => $reservation->status,
				'tot_paid' => $reservation->tot_paid,
			);

			/**
			 * Auto-set "Leave" reservation code when the customer
			 * pays the remaining balance after closing the bill.
			 *
			 * @since 1.8.1
			 */
			if ($reservation->bill_closed)
			{
				// get reservation code used to "leave" the table
				$id_code = JHtml::_('vikrestaurants.rescoderule', 'leave', 1);

				// make sure the code exists
				if ($id_code)
				{
					// set reservation code
					$data['rescode'] = $id_code;

					$code = array();
					$code['group']      = 1;
					$code['id_order']   = $reservation->id;
					$code['id_rescode'] = $id_code;
					$code['id']         = 0;

					// get record table
					$rescodeorder = JTableVRE::getInstance('rescodeorder', 'VRETable');

					// try to save arguments
					$rescodeorder->save($code);
				}
			}

			// update reservation status
			$table->save($data);

			// get current language tag
			$langtag = JFactory::getLanguage()->getTag();
			
			VRELoader::import('library.mail.factory');

			// get notification e-mail for customer
			$customerMail = VREMailFactory::getInstance('restaurant', 'customer', $reservation->id);

			// check if the customer should receive the notification e-mail
			if ($customerMail->shouldSend())
			{
				// send e-mail notification
				$customerMail->send();
			}
			
			// get notification e-mail for admin and operators
			$adminMail = VREMailFactory::getInstance('restaurant', 'admin', $reservation->id);

			// check if the admin/operator should receive the notification e-mail
			if ($adminMail->shouldSend())
			{
				// send e-mail notification
				$adminMail->send();
			}
			
			// send SMS notification after receiving the payment
			// dispatch SMS (0: restaurant reservation)
			VikRestaurants::sendSmsAction($reservation->purchaser_phone, $reservation->id, 0);

			/**
			 * Trigger event after the validation of a successful transaction.
			 *
			 * @param 	array 	$order  The transaction details.
			 * @param 	array 	$args   The response array.
			 *
			 * @return 	void
			 *
			 * @since 	1.8.1
			 */
			$dispatcher->trigger('onSuccessPaymentTransaction', array($paymentData, $result));

			// restore previous language tag
			VikRestaurants::loadLanguage($langtag);
		}
		else
		{
			// check if the payment registered any logs
			if (!empty($result['log']))
			{
				$text = array(
					'Reservation #' . $reservation->id . '-' . $reservation->sid . ' (Restaurant)',
					$result['log'],
				);

				// send error logs to administrator(s)
				VikRestaurants::sendAdminMailPaymentFailed($text);

				// get current date and time
				$timeformat = preg_replace("/:i/", ':i:s', $config->get('timeformat'));
				$now = date($config->get('dateformat') . ' ' . $timeformat, VikRestaurants::now());

				// build log string
				$log  = str_repeat('-', strlen($now) + 4) . "\n";
				$log .= "| $now |\n";
				$log .= str_repeat('-', strlen($now) + 4) . "\n";
				$log .= "\n" . $result['log'];

				if (!empty($reservation->payment_log))
				{
					// prepend previous logs
					$log = $reservation->payment_log . "\n\n" . $log;
				}

				// prepare save data
				$data = array(
					'id'          => $reservation->id,
					'payment_log' => $log,
				);

				// update reservation logs
				$table->save($data);
			}

			/**
			 * Trigger event after the validation of a failed transaction.
			 *
			 * @param 	array 	$order  The transaction details.
			 * @param 	array 	$args   The response array.
			 *
			 * @return 	void
			 *
			 * @since 	1.8.1
			 */
			$dispatcher->trigger('onFailPaymentTransaction', array($paymentData, $result));
		}

		// check whether the payment instance supports a method
		// to be executed after the validation
		if (method_exists($obj, 'afterValidation'))
		{
			$obj->afterValidation($result['verified'] ? 1 : 0);
		}
	}

	/**
	 * Completes the cancellation of the specified
	 * restaurant reservation.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public function cancel_reservation()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		
		$oid = $input->getUint('oid');
		$sid = $input->getAlnum('sid');

		// get cancellation reason, if specified
		$reason = trim($input->getString('reason'));

		$config = VREFactory::getConfig();

		// get reason setting
		$canc_reason = $config->getUint('cancreason');

		// build return URL
		$url = "index.php?option=com_vikrestaurants&view=reservation&ordnum=$oid&ordkey=$sid";

		$itemid = $input->get('Itemid', null, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}

		if ((strlen($reason) > 0 && strlen($reason) < 32)
			|| (strlen($reason) == 0 && $canc_reason == 2))
		{
			// invalid reason
			$app->redirect(JRoute::_($url . '#cancel', false));
			exit;
		}
		
		if (!VikRestaurants::isCancellationEnabled())
		{
			// cancellation disabled
			$app->enqueueMessage(JText::_('VRORDERCANCDISABLEDERROR'), 'error');
			$app->redirect(JRoute::_($url, false));
			exit;
		}

		// Get reservation details.
		// In case the reservation doesn't exist, an exception
		// will be thrown.
		$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));
		
		if (!VikRestaurants::canUserCancelOrder($reservation))
		{
			// currently unable to cancel the reservation
			$error = JText::sprintf('VRORDERCANCEXPIREDERROR', $config->getUint('canctime'));

			$app->enqueueMessage($error, 'error');
			$app->redirect(JRoute::_($url, false));
			exit;
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservationTable = JTableVRE::getInstance('reservation', 'VRETable');

		$data = array(
			'id'     => $reservation->id,
			'status' => 'CANCELLED',
		);

		// cancel reservation
		$reservationTable->save($data);
		
		VRELoader::import('library.mail.factory');

		// get customer e-mail template
		$customerMail = VREMailFactory::getInstance('restaurant', 'customer', $reservation->id);
		// always send notification in case of cancellation
		$customerMail->send();
		
		// include reason for cancellation e-mail
		$options = array(
			'cancellation_reason' => $reason
		);

		// get cancellation e-mail for administrator
		$cancMail = VREMailFactory::getInstance('restaurant', 'cancellation', $reservation->id, null, $options);
		// always send notification in case of cancellation
		$cancMail->send();
		
		$app->redirect(JRoute::_($url, false));
	}

	/**
	 * Completes the cancellation of the specified
	 * take-away order.
	 *
	 * @return 	void
	 *
	 * @since 	1.3
	 */
	public function cancel_order()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		if ($input->get('type') === 0)
		{
			// Backward compatibility to support templates that
			// uses old view overrides. Forward the customer to
			// the task used to cancel a reservation.
			$this->cancel_reservation();
			return;
		}
		
		$oid = $input->getUint('oid');
		$sid = $input->getAlnum('sid');

		// get cancellation reason, if specified
		$reason = trim($input->getString('reason'));

		$config = VREFactory::getConfig();

		// get reason setting
		$canc_reason = $config->getUint('tkcancreason');

		// build return URL
		$url = "index.php?option=com_vikrestaurants&view=order&ordnum=$oid&ordkey=$sid";

		$itemid = $input->get('Itemid', null, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}

		if ((strlen($reason) > 0 && strlen($reason) < 32)
			|| (strlen($reason) == 0 && $canc_reason == 2))
		{
			// invalid reason
			$app->redirect(JRoute::_($url . '#cancel', false));
			exit;
		}
		
		if (!VikRestaurants::isTakeAwayCancellationEnabled())
		{
			// cancellation disabled
			$app->enqueueMessage(JText::_('VRORDERCANCDISABLEDERROR'), 'error');
			$app->redirect(JRoute::_($url, false));
			exit;
		}

		// Get order details.
		// In case the order doesn't exist, an exception
		// will be thrown.
		$order = VREOrderFactory::getOrder($oid, null, array('sid' => $sid));
		
		if (!VikRestaurants::canUserCancelOrder($order))
		{
			// make sure the order status is valid
			if ($order->status == 'CONFIRMED')
			{
				// currently unable to cancel the order
				$error = JText::sprintf('VRORDERCANCEXPIREDERROR', $config->getUint('tkcanctime'));
			}

			$app->enqueueMessage($error, 'error');
			$app->redirect(JRoute::_($url, false));
			exit;
		}

		// get order table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$orderTable = JTableVRE::getInstance('tkreservation', 'VRETable');

		$data = array(
			'id'     => $order->id,
			'status' => 'CANCELLED',
		);

		// cancel order
		$orderTable->save($data);
		
		VRELoader::import('library.mail.factory');

		// get customer e-mail template
		$customerMail = VREMailFactory::getInstance('takeaway', 'customer', $order->id);
		// always send notification in case of cancellation
		$customerMail->send();
		
		// include reason for cancellation e-mail
		$options = array(
			'cancellation_reason' => $reason
		);

		// get cancellation e-mail for administrator
		$cancMail = VREMailFactory::getInstance('takeaway', 'cancellation', $order->id, null, $options);
		// always send notification in case of cancellation
		$cancMail->send();
		
		$app->redirect(JRoute::_($url, false));
	}

	/**
	 * Performs additional checks before letting the 
	 * customers access the take-away order 
	 * confirmation page.
	 *
	 * @return 	void
	 *
	 * @since 	1.2
	 */
	public function takeawayconfirm()
	{	
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$session = JFactory::getSession();
		$config  = VREFactory::getConfig();

		$itemid = $input->get('Itemid', null, 'uint');

		// fetch error URL
		$error_url = 'index.php?option=com_vikrestaurants&view=takeaway';

		if ($itemid)
		{
			$error_url .= '&Itemid=' . $itemid;
		}

		// load cart and deals helpers
		VikRestaurants::loadCartLibrary();
		VikRestaurants::loadDealsLibrary();

		// get cart instance
		$cart = TakeAwayCart::getInstance();
		
		$args = array();
		$args['date']     = $input->get('date', '', 'string');
		$args['hourmin']  = $input->get('hourmin', '', 'string');
		$args['delivery'] = $input->get('delivery', null, 'uint');

		$reset_deals = false;

		if (empty($args['hourmin']))
		{
			// get check-in time, only if set
			$time = $cart->getCheckinTime();

			if ($time)
			{
				// update time from filters
				$args['hourmin'] = $time;
				// inject time in INPUT for being used later
				$input->set('hourmin', $args['hourmin']);
			}
		}
		else
		{
			$reset_deals = true;
		}
		
		// check if we should update the check-in date
		if (VikRestaurants::isTakeAwayDateAllowed() && $args['date'])
		{
			// update only if date is set and the check-in can be changed
			$cart->setCheckinTimestamp(VikRestaurants::createTimestamp($args['date'], 0, 0));

			$reset_deals = true;
		}
		else
		{
			// otherwise retrieve stored check-in
			$args['date'] = date($config->get('dateformat'), $cart->getCheckinTimestamp());
			// inject date in INPUT for being used later
			$input->set('date', $args['date']);
		}

		// validate the time against the available ones,
		// because the selected time might be not available
		// and the next one could be on a different shift
		if (!VikRestaurants::validateTakeAwayTime($args['hourmin'], $args['date']))
		{
			// inject time in INPUT for being used later
			$input->set('hourmin', $args['hourmin']);

			// invalid time, reset deals
			$reset_deals = true;
		}

		// refresh cart time
		$cart->setCheckinTime($args['hourmin']);

		// get service previously selected
		$prev_service = $cart->getService();

		if (is_null($args['delivery']))
		{
			// keep current service, if any
			$args['delivery'] = $prev_service;
		}

		// update delivery service
		$cart->setService($args['delivery']);

		if ($prev_service != $cart->getService())
		{
			// refresh deals in case the service has changed
			$reset_deals = true;
		}

		// inject service in INPUT for being used later
		$input->set('delivery', $cart->getService());

		if ($reset_deals)
		{
			// re-check for deals when the date or time change
			VikRestaurants::resetDealsInCart($cart, $args['hourmin']);
			VikRestaurants::checkForDeals($cart);
		}
		
		// update cart
		$cart->store();

		// make sure the orders are allowed for the selected date time
		if (!VikRestaurants::isTakeAwayReservationsAllowedOn($cart->getCheckinTimestamp()))
		{
			// orders have been stopped
			$app->enqueueMessage(JText::_('VRTKMENUNOTAVAILABLE3'), 'error');
			$app->redirect(JRoute::_($error_url, false));
			exit;
		}

		/**
		 * Use an helper method to calculate the minimum cost 
		 * needed to proceed with the purchase.
		 *
		 * @since 1.8.3
		 */
		$mincost = Vikrestaurants::getTakeAwayMinimumCostPerOrder();

		// make sure the total cost of the cart reached the minimum threshold
		if ($cart->getTotalCost() < $mincost)
		{
			// format minimum cost
			$cost = VREFactory::getCurrency()->format($mincost);

			// continue shopping to reach the minimum cost
			$app->enqueueMessage(JText::sprintf('VRTAKEAWAYMINIMUMCOST', $cost), 'error');
			$app->redirect(JRoute::_($error_url, false));
			exit;
		}

		// get submitted coupon key, if any
		$coupon = $input->get('couponkey', null, 'string');
		// get previous coupon code
		$prev = $session->get('vr_coupon_data', null);

		if (!$coupon)
		{
			// coupon not submitted, validate previous one (if set)
			$coupon = $prev;
		}

		// check if the coupon code have been submitted
		if ($coupon)
		{
			// validate coupon
			$coupon = VikRestaurants::validateTakeawayCoupon($coupon, $cart);

			// coupon code deal type
			$deal_type = 5;

			// search for a coupon already registered within the cart
			$index = $cart->deals()->indexOfType($deal_type);

			if ($index != -1)
			{
				// remove coupon discount from cart
				$cart->deals()->removeAt($index);
			}

			if ($coupon)
			{
				// coupon found, save it in session
				$session->set('vr_coupon_data', $coupon);

				// display successful message if the coupon changed
				if (!$prev || $prev->code != $coupon->code)
				{
					$app->enqueueMessage(JText::_('VRCOUPONFOUND'));
				}

				// create coupon discount
				$discount = new TakeAwayDiscount(
					$coupon->code,
					$coupon->value,
					$coupon->percentot,
					$quantity = 1,
					$deal_type
				);

				// insert discount within the cart as "coupon"
				$cart->deals()->insert($discount);
			}
			else
			{
				// invalid coupon, unset it
				$session->set('vr_coupon_data', null);
				// display failure message
				$app->enqueueMessage(JText::_('VRCOUPONNOTVALID'), 'error');
			}

			// update cart
			$cart->store();
		}

		/**
		 * Remove all the take-away orders that haven't been confirmed
		 * within the specified range of time (15 minutes by default).
		 *
		 * In this way, we can free the slots that were occupied
		 * before showing the availability to this customer.
		 * 
		 * @since 1.8
		 */
		VikRestaurants::removeTakeAwayOrdersOutOfTime();
	}
	
	/**
	 * Task used to save a take-away order.
	 *
	 * @return 	void
	 *
	 * @since 	1.2
	 */
	function savetakeawayorder()
	{
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$dbo     = JFactory::getDbo();
		$user    = JFactory::getUser();
		$session = JFactory::getSession();
		$config  = VREFactory::getConfig();

		// prepare event dispatcher
		$dispatcher = VREFactory::getEventDispatcher();

		// always load tables from the back-end
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		
		// get cart instance
		VikRestaurants::loadCartLibrary();
		$cart = TakeAwayCart::getInstance();

		// retrieve search arguments
		$args = array();
		$args['date']     = date($config->get('dateformat'), $cart->getCheckinTimestamp());
		$args['hourmin']  = $input->getString('hourmin');
		$args['delivery'] = $input->getString('delivery');

		$itemid = $input->get('Itemid', null, 'uint');

		// fetch error URL
		$error_url = 'index.php?option=com_vikrestaurants';

		if ($itemid)
		{
			$error_url .= '&Itemid=' . $itemid;
		}

		/**
		 * Validate session token before to proceed.
		 *
		 * @since 1.8
		 */
		if (!JSession::checkToken())
		{
			// invalid token, back to confirm page
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
			exit;
		}

		$vik = VREApplication::getInstance();

		/**
		 * Validate ReCaptcha before processing the take-away order.
		 * The ReCaptcha is never asked to registered customers.
		 *
		 * @since 1.8.2
		 */
		if ($user->guest && $vik->isGlobalCaptcha() && !$vik->reCaptcha('check'))
		{
			// invalid captcha
			$app->enqueueMessage(JText::_('PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL'), 'error');
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
			exit;
		}

		// validate request
		$code = VikRestaurants::isRequestTakeAwayOrderValid($args);

		if ($code !== 0)
		{
			// fetch error message from code
			$error = VikRestaurants::getResponseFromTakeAwayOrderRequest($code);

			$app->enqueueMessage(JText::_($error), 'error');
			// back to confirm view in case of malformed request (do not keep searched arguments)
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm', false));
			exit;
		}

		$data = array();

		try
		{
			/**
			 * Set delivery service within custom fields manager
			 * so that it is possible to ignore the validation of those
			 * fields that depend on a specific service only.
			 *
			 * @since 1.8
			 */
			VRCustomFields::$deliveryService = (bool) $args['delivery'];

			// get restaurant custom fields
			$data['custom_f'] = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_TAKEAWAY, $match);
		}
		catch (Exception $e)
		{
			// invalid custom fields
			$app->enqueueMessage($e->getMessage(), 'error');
			// back to confirmation view
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
			exit;
		}

		// inject purchaser details fetched with the custom fields
		$data = array_merge($data, $match);

		// extract hour and minutes
		list($args['hour'], $args['min']) = explode(':', $args['hourmin']);
		
		// get checkin timestamp
		$data['checkin_ts'] = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);
		
		// set delivery service
		$data['delivery_service'] = $args['delivery'];

		// make sure the reservations are allowed for the selected date time
		if (!VikRestaurants::isTakeAwayReservationsAllowedOn($data['checkin_ts']))
		{
			// reservation blocked for today
			$app->enqueueMessage(JText::_('VRTKMENUNOTAVAILABLE3'), 'error');
			// back to confirm page (do not take searched arguments)
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm', false));
			exit;
		}

		// check stock availability
		if (!VikRestaurants::checkCartStockAvailability($cart))
		{
			// Some products are no more available...
			// Update cart and go back to confirmation page.
			$cart->store();
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
			exit;
		}

		// validate selected address against supported areas in case of delivery
		if ($args['delivery'])
		{
			// get delivery info from session
			$delivery_info = $session->get('delivery_address', null, 'vre');

			if (VikRestaurants::hasDeliveryAreas() && ($delivery_info === null || empty($delivery_info->status)))
			{
				// invalid or missing address
				$app->enqueueMessage(JText::_('VRTKDELIVERYLOCNOTFOUND'), 'error');
				$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
				exit;
			}
		}
		
		if (empty($delivery_info))
		{
			// fill an empty delivery object
			$delivery_info = new stdClass;
			
			$delivery_info->area = new stdClass;
			$delivery_info->area->charge  = 0;
			$delivery_info->area->minCost = 0;

			$delivery_info->address = array();
			$delivery_info->address['fullAddress']    = '';
			$delivery_info->address['country_2_code'] = '';
			$delivery_info->address['country']        = '';
			$delivery_info->address['state']          = '';
			$delivery_info->address['city']           = '';
			$delivery_info->address['zip']            = '';
			$delivery_info->address['street']         = array(
				'name'   => '',
				'number' => '',
			);
		}

		if (empty($data['purchaser_address']))
		{
			// copy full address within order data (if not set)
			$data['purchaser_address'] = $delivery_info->address['fullAddress'];
		}

		/**
		 * Use an helper method to calculate the minimum cost 
		 * needed to proceed with the purchase.
		 *
		 * @since 1.8.3
		 */
		$mincost = Vikrestaurants::getTakeAwayMinimumCostPerOrder($delivery_info->area->minCost, $args);
		
		// make sure the total cost of the cart reached the minimum threshold
		if ($cart->getTotalCost() < $mincost)
		{
			// format minimum cost
			$cost = VREFactory::getCurrency()->format($mincost);

			// continue shopping to reach the minimum cost
			$app->enqueueMessage(JText::sprintf('VRTAKEAWAYMINIMUMCOST', $cost), 'error');
			$app->redirect(JRoute::_($error_url . '&view=takeaway', false));
			exit;
		}

		// reset preparation timestamp
		$data['preparation_ts'] = null;

		// prepare availability search
		$search = new VREAvailabilityTakeaway($args['date'], $args['hourmin']);
		// check if the selected time slot is still available
		$avail = $search->isTimeAvailable($cart, $data['preparation_ts']);

		if (!$avail)
		{
			// check if we have a closing day for the selected checkin date
			if (VikRestaurants::isClosingDay($args))
			{
				// the selected day is closed
				$app->enqueueMessage(JText::_('VRSEARCHDAYCLOSED'), 'error');	
			}
			else
			{
				// the selected time is no more available
				$app->enqueueMessage(JText::_('VRTKNOTIMEAVERR'), 'error');
			}

			// back to confirmation step (do not keep searched args)
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm', false));
			exit;
		}

		// get taxes configuration
		$use_taxes = $config->getUint('tkusetaxes');
		$tax_ratio = $config->getFloat('tktaxesratio');

		// calculate totals
		$total_to_pay = $cart->getTotalCost();
		$grand_total  = $cart->getRealTotalCost($use_taxes);
		$total_net    = $cart->getRealTotalNet($use_taxes);
		$taxes        = $cart->getRealTotalTaxes($use_taxes);
		$discount_val = $cart->getTotalDiscount();
		
		$deliverycost = 0;

		if ($args['delivery'] == 1)
		{
			/**
			 * Validate free delivery by using the apposite helper method.
			 *
			 * @since 1.8.3
			 */
			if (!VikRestaurants::isTakeAwayFreeDeliveryService($cart))
			{
				// calculate delivery cost based on total net and delivery area found
				$deliverycost = VikRestaurants::getTakeAwayDeliveryServiceAddPrice($total_net, $delivery_info->area);
			}
		}
		else
		{
			// calculate pickup cost based on total net
			$deliverycost = VikRestaurants::getTakeAwayPickupAddPrice($total_net);
		}

		/**
		 * Add taxes to delivery cost.
		 *
		 * @since 1.7.4
		 */
		if ($deliverycost > 0)
		{
			if ($use_taxes == 0)
			{
				// included
				$_tax         = round($deliverycost - ($deliverycost * 100 / (100 + $tax_ratio)), 2, PHP_ROUND_HALF_UP);
				$deliverycost = round($deliverycost - $_tax, 2, PHP_ROUND_HALF_DOWN);
			}
			else
			{
				// excluded
				$_tax = round($deliverycost * $tax_ratio / 100, 2, PHP_ROUND_HALF_UP);
			}

			// increase grand total
			$grand_total += $deliverycost + $_tax;

			// increase taxes
			$taxes += $_tax;
		}
		else if ($deliverycost < 0)
		{
			// get proportional taxes
			$taxes = $taxes * ($grand_total - abs($deliverycost)) / $grand_total; 

			// subtract delivery cost from grand total
			$grand_total -= abs($deliverycost);

			/**
			 * Keep the discount under the delivery charge in order
			 * to have the same behavior between the front-end and the
			 * back-end.
			 *
			 * @since 1.8
			 */
			// mark delivery cost as discount
			// $discount_val += abs($deliverycost);
			// unset delivery cost
			// $deliverycost = 0;
		}

		// inject costs within the order data
		$data['total_to_pay']    = round($grand_total, 2);
		$data['taxes']           = round($taxes, 2);
		$data['delivery_charge'] = $deliverycost;
		$data['discount_val']    = $discount_val;

		/**
		 * Add gratuity to grand total
		 *
		 * @since 1.7.4
		 */
		$data['tip_amount'] = abs(round($input->getFloat('gratuity', 0), 2));

		$data['total_to_pay'] += $data['tip_amount'];

		// get default status from configuration, which will
		// be used only in case the reservation doesn't require
		// a cost and the system doesn't support any payments
		$data['status'] = $config->get('tkdefstatus');

		// check if the customer has something to pay and make
		// sure the system owns at least a published payment
		if ($data['total_to_pay'] > 0 && Vikrestaurants::hasPayment($group = 2))
		{
			// get selected payment
			$data['id_payment'] = $input->getUint('vrpaymentradio');

			// make sure the selected payment exists
			$payment = VikRestaurants::hasPayment($group = 2, $data['id_payment']);

			if ($payment && $payment->enablecost != 0)
			{
				// validate payment cost requirements
				if ($payment->enablecost > 0 && $payment->enablecost > $data['total_to_pay'])
				{
					// the selected payment is not valid (minimum cost not exceeded)
					$payment = null;
				}
				else if ($payment->enablecost < 0 && abs($payment->enablecost) < $data['total_to_pay'])
				{
					// the selected payment is not valid (maximum cost exceeded)
					$payment = null;
				}
			}

			if (!$payment)
			{
				// the selected payment does not exist
				$app->enqueueMessage(JText::_('VRERRINVPAYMENT'), 'error');
				// back to confirmation view
				$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
				exit;
			}

			// auto-confirm order according to the configuration of
			// the payment, otherwise force PENDING status to let the
			// customers be able to start a transaction
			$data['status'] = $payment->setconfirmed ? 'CONFIRMED' : 'PENDING';

			if ($payment->charge != 0)
			{
				// apply payment charge to grand total
				if ($payment->percentot == 1)
				{
					// percentage charge based on total to pay
					$data['pay_charge'] = $data['total_to_pay'] * (float) $payment->charge / 100;
				}
				else
				{
					// fixed amount
					$data['pay_charge'] = (float) $payment->charge;
				}

				/**
				 * Always round the calculated charge to 2 decimals, in order
				 * to avoid roundings when saving the amount in the database.
				 *
				 * @since 1.8
				 */
				$data['pay_charge'] = round($data['pay_charge'], 2, PHP_ROUND_HALF_UP);

				// increase/decrease grand total by the payment charge
				$data['total_to_pay'] += $data['pay_charge'];
			}
		}
		
		// validate coupon only once we are sure that the
		// order is ready to be saved, as GIFT coupons
		// have to be permanently removed from the database
		$coupon = $session->get('vr_coupon_data', null);
		
		if (!empty($coupon))
		{	
			// get coupon table
			$couponTable = JTableVRE::getInstance('coupon', 'VRETable');

			// redeem coupon code
			$data['coupon_str'] = $couponTable->redeem($coupon->code);
			
			// unset coupon code from session
			$session->set('vr_coupon_data', null);
		}

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
				'tkfields'             => 'custom_f',
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
			else
			{
				// set up billing address only on insert
				$customer_data['billing_state']   = $delivery_info->address['state'];
				$customer_data['billing_city']    = $delivery_info->address['city'];
				$customer_data['billing_address'] = trim(implode(' ', array_values($delivery_info->address['street'])));
				$customer_data['billing_zip']     = $delivery_info->address['zip'];
			}

			// get customer table
			$customer = JTableVRE::getInstance('customer', 'VRETable');

			// insert/update customer
			if ($customer->save($customer_data))
			{
				// assign reservation to saved customer
				$data['id_user'] = $customer->id;

				// check if we have a full address for delivery
				if (strlen($delivery_info->address['fullAddress']))
				{
					// get customer data
					$user_data = VikRestaurants::getCustomer($customer->id);

					$found = false;

					// iterate all user locations
					foreach ($user_data->locations as $addr)
					{
						/**
						 * Check if the address already exists by comparing the single components
						 * of the full address.
						 *
						 * @since 1.7.4
						 */
						if (!$found && VikRestaurants::compareAddresses((array) $addr, $delivery_info->address))
						{
							/**
							 * Register ID found.
							 *
							 * @since 1.8.3
							 */
							$found = (int) $addr->id;
						}
					}

					// get delivery location table
					$userLocation = JTableVRE::getInstance('userlocation', 'VRETable');

					if ($found)
					{
						// prepare delivery location data for UPDATE
						$location = array(
							'id' => $found,
						);
					}
					else
					{
						// prepare delivery location data for INSERT
						$location = array(
							'id_user'   => $customer->id,
							'country'   => $data['purchaser_country'],
							'state'     => $delivery_info->address['state'],
							'city'      => $delivery_info->address['city'],
							'address'   => trim(implode(' ', array_values($delivery_info->address['street']))),
							'zip'       => $delivery_info->address['zip'],
							'latitude'  => $delivery_info->latitude,
							'longitude' => $delivery_info->longitude,
						);
					}

					/**
					 * Register delivery notes within location record.
					 *
					 * @since 1.8.3
					 */
					if (!empty($data['delivery_notes']))
					{
						$location['note'] = $data['delivery_notes'];
					}

					/**
					 * Save only in case there's something to bind in addition
					 * to the ID (UPDATE). Needed to apply the delivery notes
					 * to locations that have been already saved.
					 *
					 * @since 1.8.3
					 */
					if (count($location) >= 2)
					{
						// save location
						$userLocation->save($location);
					}
				}
			}
		}

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		// save order
		if (!$order->save($data))
		{
			// an error occurred while saving the order
			$app->enqueueMessage(JText::_('VRINSERTRESERVATIONERROR'), 'error');
			// back to the confirmation view
			$app->redirect(JRoute::_($error_url . '&view=takeawayconfirm&hourmin=' . $args['hourmin'] . '&delivery=' . $args['delivery'], false));
			exit;
		}

		// get order items table
		$orderItem = JTableVRE::getInstance('tkresprod', 'VRETable');
		// get item group topping table
		$itemTopping = JTableVRE::getInstance('tkresprodtopping', 'VRETable');
		
		// save ordered products
		foreach ($cart->getItemsList() as $item)
		{
			// prepare product data
			$product = array(
				'id'         => 0,
				'id_product' => $item->getItemID(),
				'id_res'     => $order->id,
				'quantity'   => $item->getQuantity(),
				'price'      => $item->getTotalCost() / $item->getQuantity(),
				'taxes'      => $item->getTaxes($use_taxes),
				'notes'      => $item->getAdditionalNotes(),
			);

			// get variation ID
			$var_id = (int) $item->getVariationID();

			if ($var_id > 0)
			{
				$product['id_product_option'] = $var_id;
			}
			else
			{
				$product['id_product_option'] = 0;
			}

			// save order item
			$orderItem->save($product);
			
			// iterate item topping groups
			foreach ($item->getToppingsGroupsList() as $group)
			{
				// iterate item group toppings
				foreach ($group->getToppingsList() as $topping)
				{
					// prepare topping data
					$toppingData = array(
						'id'         => 0,
						'id_assoc'   => $orderItem->id,
						'id_group'   => $group->getGroupID(),
						'id_topping' => $topping->getToppingID(),
						'units'      => $topping->getUnits(),
					);

					// save topping
					$itemTopping->save($toppingData);
					$itemTopping->reset();
				}
			}

			$orderItem->reset();
		}
		
		// flush the cart
		$cart->emptyCart()->store();

		// unset delivery address from session
		$session->clear('delivery_address', 'vre');
		
		VRELoader::import('library.mail.factory');

		// get notification e-mail for customer
		$customerMail = VREMailFactory::getInstance('takeaway', 'customer', $order->id);

		// check if the customer should receive the notification e-mail
		if ($customerMail->shouldSend())
		{
			// send e-mail notification
			$customerMail->send();
		}
		
		// get notification e-mail for admin and operators
		$adminMail = VREMailFactory::getInstance('takeaway', 'admin', $order->id);

		// check if the admin/operator should receive the notification e-mail
		if ($adminMail->shouldSend())
		{
			// send e-mail notification
			$adminMail->send();
		}

		// inform the administrator(s) about any products
		// that are close to be out of stock
		if ($order->status == 'CONFIRMED')
		{
			// get notification e-mail for admin
			$stockMail = VREMailFactory::getInstance('takeaway', 'stock');

			// check if the admin should receive the notification e-mail
			if ($stockMail->shouldSend())
			{
				// send e-mail notification
				$stockMail->send();
			}
		}
		
		// send SMS notification in case the order was confirmed
		if ($order->status == 'CONFIRMED')
		{
			// dispatch SMS (1: take-away order)
			VikRestaurants::sendSmsAction($order->purchaser_phone, $order->id, 1);
		}

		// fetch redirect URL
		$url = sprintf(
			'index.php?option=com_vikrestaurants&view=order&ordnum=%d&ordkey=%s%s',
			$order->id,
			$order->sid,
			$itemid ? '&Itemid=' . $itemid : ''
		);
		
		// redirect to order summary view
		$app->redirect(JRoute::_($url, false));
	}

	/**
	 * End-point used by the payment gateways as notification URL.
	 * This is task validates the transaction details returned by
	 * the bank.
	 *
	 * Only for TAKE-AWAY orders.
	 *
	 * @return 	void
	 *
	 * @since 	1.2
	 */
	function notifytkpayment()
	{
		$dispatcher = VREFactory::getEventDispatcher();

		$input = JFactory::getApplication()->input;
			
		$oid = $input->getUint('ordnum');
		$sid = $input->getAlnum('ordkey');
		
		$dbo = JFactory::getDbo();

		// Get order details (filter by ID and SID).
		// In case the order doesn't exist, an
		// exception will be thrown.
		$order = VREOrderFactory::getOrder($oid, null, array('sid' => $sid));

		/**
		 * This event is triggered every time a payment tries
		 * to validate a transaction made.
		 *
		 * DOES NOT trigger in case the order doesn't exist.
		 *
		 * @param 	mixed 	&$order  The details of the take-away order.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.1
		 */
		$dispatcher->trigger('onReceivePaymentNotification', array(&$order));

		/**
		 * Allow the payment for REMOVED orders because they
		 * have been probably paid while they were PENDING.
		 * 
		 * @since 1.8
		 */
		$accepted = array(
			'PENDING',
			'REMOVED',
		);
		
		// make sure the order can be paid
		if (!in_array($order->status, $accepted))
		{
			// status not allowed
			throw new Exception('The current status of the order does not allow any payments.', 403);
		}

		// get payment details (unpublished payments are supported)
		$payment = VikRestaurants::hasPayment($group = 2, $order->id_payment, $strict = false);

		// make sure the payment exists
		if (!$payment)
		{
			throw new Exception('The selected payment does not exist.', 404);
		}

		$vik = VREApplication::getInstance();

		$config = VREFactory::getConfig();
			
		// fetch transaction data	
		$paymentData = array();

		/**
		 * The payment URLs are correctly routed for external usage.
		 *
		 * @since 1.8
		 */
		$return_url = $vik->routeForExternalUse("index.php?option=com_vikrestaurants&view=order&ordnum={$oid}&ordkey={$sid}", false);
		$error_url  = $vik->routeForExternalUse("index.php?option=com_vikrestaurants&view=order&ordnum={$oid}&ordkey={$sid}", false);
		
		/**
		 * Include the Notification URL in both the PLAIN and ROUTED formats.
		 *
		 * @since 1.8.1
		 */
		$notify_url = "index.php?option=com_vikrestaurants&task=notifytkpayment&ordnum={$oid}&ordkey={$sid}";

		$paymentData['type']                 = 'takeaway.validate';
		$paymentData['oid']                  = $order->id;
		$paymentData['sid']                  = $order->sid;
		$paymentData['tid']                  = 1;
		$paymentData['transaction_name']     = JText::sprintf('VRTRANSACTIONNAME', $config->get('restname'));
		$paymentData['transaction_currency'] = $config->get('currencyname');
		$paymentData['currency_symb']        = $config->get('currencysymb');
		$paymentData['tax']                  = 0;
		$paymentData['return_url']           = $return_url;
		$paymentData['error_url']            = $error_url;
		$paymentData['notify_url']           = $vik->routeForExternalUse($notify_url, false);
		$paymentData['notify_url_plain']     = JUri::root() . $notify_url;
		$paymentData['total_to_pay']         = $order->total_to_pay;
		$paymentData['total_net_price']      = $order->total_to_pay;
		$paymentData['total_tax']            = 0;
		$paymentData['payment_info']         = $payment;
		$paymentData['details'] = array(
			'purchaser_nominative' => $order->purchaser_nominative,
			'purchaser_mail'       => $order->purchaser_mail,
			'purchaser_phone'      => $order->purchaser_phone,
		);

		/**
		 * Trigger event to manipulate the payment details.
		 *
		 * @param 	array 	&$order   The transaction details.
		 * @param 	mixed 	&$params  The payment configuration as array or JSON.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.1
		 */
		$dispatcher->trigger('onInitPaymentTransaction', array(&$paymentData, &$payment->params));

		/**
		 * Instantiate the payment using the platform handler.
		 *
		 * @since 1.8
		 */
		$obj = $vik->getPaymentInstance($payment->file, $paymentData, $payment->params);
		
		// validate payment transaction
		$result = $obj->validatePayment();

		// get order table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$table = JTableVRE::getInstance('tkreservation', 'VRETable');

		// check for a successful result
		if ($result['verified'])
		{
			if (!empty($result['tot_paid']))
			{
				// increase total amount paid
				$order->tot_paid += (float) $result['tot_paid'];
			}

			// update order status
			$order->status = 'CONFIRMED';
			
			// prepare save data
			$data = array(
				'id'       => $order->id,
				'status'   => $order->status,
				'tot_paid' => $order->tot_paid,
			);

			// update order status
			$table->save($data);

			// get current language tag
			$langtag = JFactory::getLanguage()->getTag();
			
			VRELoader::import('library.mail.factory');

			// get notification e-mail for customer
			$customerMail = VREMailFactory::getInstance('takeaway', 'customer', $order->id);

			// check if the customer should receive the notification e-mail
			if ($customerMail->shouldSend())
			{
				// send e-mail notification
				$customerMail->send();
			}
			
			// get notification e-mail for admin and operators
			$adminMail = VREMailFactory::getInstance('takeaway', 'admin', $order->id);

			// check if the admin/operator should receive the notification e-mail
			if ($adminMail->shouldSend())
			{
				// send e-mail notification
				$adminMail->send();
			}

			// inform the administrator(s) about any products
			// that are close to be out of stock
			// get notification e-mail for admin
			$stockMail = VREMailFactory::getInstance('takeaway', 'stock');

			// check if the admin should receive the notification e-mail
			if ($stockMail->shouldSend())
			{
				// send e-mail notification
				$stockMail->send();
			}
			
			// send SMS notification after receiving the payment
			// dispatch SMS (1: take-away orders)
			VikRestaurants::sendSmsAction($order->purchaser_phone, $order->id, 1);

			/**
			 * Trigger event after the validation of a successful transaction.
			 *
			 * @param 	array 	$order  The transaction details.
			 * @param 	array 	$args   The response array.
			 *
			 * @return 	void
			 *
			 * @since 	1.8.1
			 */
			$dispatcher->trigger('onSuccessPaymentTransaction', array($paymentData, $result));

			// restore previous language tag
			VikRestaurants::loadLanguage($langtag);
		}
		else
		{
			// check if the payment registered any logs
			if (!empty($result['log']))
			{
				$text = array(
					'Order #' . $order->id . '-' . $order->sid . ' (Take-Away)',
					$result['log'],
				);

				// send error logs to administrator(s)
				VikRestaurants::sendAdminMailPaymentFailed($text);

				// get current date and time
				$timeformat = preg_replace("/:i/", ':i:s', $config->get('timeformat'));
				$now = date($config->get('dateformat') . ' ' . $timeformat, VikRestaurants::now());

				// build log string
				$log  = str_repeat('-', strlen($now) + 4) . "\n";
				$log .= "| $now |\n";
				$log .= str_repeat('-', strlen($now) + 4) . "\n";
				$log .= "\n" . $result['log'];

				if (!empty($order->payment_log))
				{
					// prepend previous logs
					$log = $order->payment_log . "\n\n" . $log;
				}

				// prepare save data
				$data = array(
					'id'          => $order->id,
					'payment_log' => $log,
				);

				// update order logs
				$table->save($data);
			}

			/**
			 * Trigger event after the validation of a failed transaction.
			 *
			 * @param 	array 	$order  The transaction details.
			 * @param 	array 	$args   The response array.
			 *
			 * @return 	void
			 *
			 * @since 	1.8.1
			 */
			$dispatcher->trigger('onFailPaymentTransaction', array($paymentData, $result));
		}

		// check whether the payment instance supports a method
		// to be executed after the validation
		if (method_exists($obj, 'afterValidation'))
		{
			$obj->afterValidation($result['verified'] ? 1 : 0);
		}
	}

	function registeruser() {
		$this->create_new_user(VikRestaurants::isRegistrationEnabled());
	}

	function tkregisteruser() {
		$this->create_new_user(VikRestaurants::isTakeAwayRegistrationEnabled());
	}
	
	private function create_new_user($enabled)
	{		
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		$return_url = base64_decode($input->getBase64('return'));

		if (empty($return_url))
		{
			$return_url = 'index.php';
		}
		else
		{
			$return_url = JRoute::_($return_url, false);
		}
		
		if (!$enabled)
		{
			$app->enqueueMessage(JText::_('VRREGISTRATIONFAILED1'), 'error');
			$app->redirect($return_url);
			exit;
		}

		if (!JSession::checkToken())
		{
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$app->redirect($return_url);
			exit;
		}

		$vik = VREApplication::getInstance();

		/**
		 * Validate ReCaptcha while registering a new account.
		 *
		 * @since 1.7.4
		 */
		if ($vik->isCaptcha() && !$vik->reCaptcha('check'))
		{
			// invalid captcha
			$app->enqueueMessage(JText::_('PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL'), 'error');
			$app->redirect($return_url);
			exit;
		}
		
		$args = array();
		$args['firstname']    = $input->getString('fname', '');
		$args['lastname']     = $input->getString('lname', '');
		$args['email']        = $input->getString('email', '');
		$args['confemail']    = $input->getString('confemail', null);
		$args['username']     = $input->getString('username', null);
		$args['password']     = $input->getString('password', '');
		$args['confpassword'] = $input->getString('confpassword', null);
		
		if (!VikRestaurants::checkUserArguments($args))
		{
			$app->enqueueMessage(JText::_('VRREGISTRATIONFAILED2'), 'error');
			$app->redirect($return_url);
			exit;
		}
		
		$userid = VikRestaurants::createNewJoomlaUser($args);

		if (!$userid || $userid == 'useractivate' || $userid == 'adminactivate')
		{
			//$app->enqueueMessage(JText::_('VRREGISTRATIONFAILED3'), 'error');
			// use native com_users messages
			$app->redirect($return_url);
			exit;
		}
		
		// AUTO LOG IN
		$credentials = array(
			'username' => $args['username'],
			'password' => $args['password'],
			'remember' => true,
		);
		
		$app->login($credentials);
		$currentUser = JFactory::getUser();
		$currentUser->setLastVisit(VikRestaurants::now());
		$currentUser->set('guest', 0);
		// END LOG IN
		
		$app->redirect($return_url);
	}

	/**
	 * AJAX end-point to obtain a list of available working
	 * shifts for the given date and group (1: restaurant, 2: take-away).
	 *
	 * @return 	void
	 *
	 * @since 	1.5
	 */
	public function get_working_shifts()
	{
		$input = JFactory::getApplication()->input;
		
		$date 	= $input->get('date', '', 'string');
		$group  = $input->get('group', 1, 'uint');
		
		$shifts = JHtml::_('vikrestaurants.times', $group, $date);

		$html = '';
		
		foreach ($shifts as $optgroup => $options)
		{
			if ($optgroup)
			{
				$html .= '<optgroup label="' . $optgroup . '">';
			}

			foreach ($options as $opt)
			{
				$html .= '<option value="' . $opt->value . '">' . $opt->text . '</option>';
			}

			if ($optgroup)
			{
				$html .= '</optgroup>';
			}
		}
		
		echo json_encode(array(1, $html));
		exit;
	}

	/**
	 * AJAX end-point to access the details form of a
	 * product that is going to be added into the cart.
	 *
	 * @return 	void
	 *
	 * @since 	1.6
	 */
	public function tkadditem()
	{
		$input = JFactory::getApplication()->input;

		// add support for both view and task
		$input->set('view', 'tkadditem');

		// get JSON response in case of blank layout
		if ($input->get('tmpl') == 'component')
		{
			// start output buffer
			ob_start();
			try
			{
				// display view
				parent::display();
			}
			catch (Exception $e)
			{
				// clear output buffer
				ob_end_clean();
				// raise error
				UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
			}

			// obtain view HTML from buffer
			$html = ob_get_contents();
			// clear output buffer
			ob_end_clean();

			// encode HTML in JSON to avoid encoding issues
			echo json_encode($html);
			exit;
		}
	}
	
	/**
	 * Task used to confirm the requested reservation/order.
	 *
	 * @return 	void
	 *
	 * @since 	1.3
	 */
	function confirmord()
	{
		$input = JFactory::getApplication()->input;

		$id       = $input->getUint('oid');
		$conf_key = $input->getAlnum('conf_key');
		$group    = $input->getUint('tid');
		
		if (empty($conf_key))
		{
			// missing confirmation key
			echo '<div class="vr-confirmpage order-error">' . JText::_('VRCONFORDNOROWS') . '</div>';
			return;
		}

		try
		{
			if ($group == 0)
			{
				// check if the reservation has expired
				VikRestaurants::removeRestaurantReservationsOutOfTime($id);

				// get restaurant reservation details
				$order = VREOrderFactory::getReservation($id, null, array('conf_key' => $conf_key));
			}
			else
			{
				// check if the order has expired
				VikRestaurants::removeTakeAwayOrdersOutOfTime($id);

				// get take-away order details
				$order = VREOrderFactory::getOrder($id, null, array('conf_key' => $conf_key));
			}
		}
		catch (Exception $e)
		{
			echo '<div class="vr-confirmpage order-error">' . JText::_('VRCONFORDNOROWS') . '</div>';
			return;
			// order not found
		}
		
		// make sure the order can be approved
		if ($order->status != 'PENDING')
		{
			if ($order->status == 'CONFIRMED')
			{
				// the order was already approved
				echo '<div class="vr-confirmpage order-notice">' . JText::_('VRCONFORDISCONFIRMED') . '</div>';
			}
			else
			{
				// the order cannot be approved anymore
				echo '<div class="vr-confirmpage order-error">' . JText::_('VRCONFORDISREMOVED') . '</div>';
			}
			return;
		}
		
		// get order table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$orderTable = JTableVRE::getInstance($group == 0 ? 'reservation' : 'tkreservation', 'VRETable');

		$data = array(
			'id'     => $order->id,
			'status' => 'CONFIRMED',
		);

		// approve order
		$orderTable->save($data);
		
		VRELoader::import('library.mail.factory');

		// get customer e-mail template
		$customerMail = VREMailFactory::getInstance($group == 0 ? 'restaurant' : 'takeaway', 'customer', $order->id);
			
		// make sure the customer should receive the e-mail
		if ($customerMail->shouldSend())
		{
			$customerMail->send();
		}

		// get admin e-mail template
		$adminMail = VREMailFactory::getInstance($group == 0 ? 'restaurant' : 'takeaway', 'admin', $order->id);
			
		// make sure the administrator should receive the e-mail
		if ($adminMail->shouldSend())
		{
			$adminMail->send();
		}
		
		echo '<div class="vr-confirmpage order-good">' . JText::_('VRCONFORDCOMPLETED') . '</div>';
	}

	/**
	 * Task used to approve the requested review.
	 *
	 * @return 	void
	 *
	 * @since 	1.6
	 */
	function approve_review()
	{
		$input = JFactory::getApplication()->input;

		$id       = $input->getUint('id');
		$conf_key = $input->getAlnum('conf_key');
		
		if (empty($conf_key))
		{
			echo '<div class="vr-confirmpage order-error">' . JText::_('VRCONFREVIEWNOROWS') . '</div>';
			return;
		}

		// initialize review handler
		$handler = new ReviewsHandler();

		try
		{
			// obtain the product review
			$review = $handler->takeaway()->getReview($id, array('conf_key' => $conf_key));
		}
		catch (Exception $e)
		{
			// review not found
			echo '<div class="vr-confirmpage order-error">' . JText::_('VRCONFREVIEWNOROWS') . '</div>';
			return;
		}
		
		if ($review->published)
		{
			// review already approved
			echo '<div class="vr-confirmpage order-notice">' . JText::_('VRCONFREVIEWISCONFIRMED') . '</div>';
			return;
		}
		
		// get review table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reviewTable = JTableVRE::getInstance('review', 'VRETable');

		$data = array(
			'id'        => $review->id,
			'published' => 1,
		);

		// approve review
		$reviewTable->save($data);
		
		echo '<div class="vr-confirmpage order-good">' . JText::_('VRCONFREVIEWCOMPLETED') . '</div>';
	}

	/**
	 * Task used to perform the user log out.
	 *
	 * @return 	void
	 */
	function userlogout()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->guest)
		{
			// complete log out in case the user is not a guest
			$app->logout($user->id);
		}

		// get return URL from request
		$return_url = $app->input->get('return', null, 'string');

		if (is_null($return_url))
		{
			// get return view
			$view   = $app->input->get('return_view', 'allorders');
			$itemid = $app->input->get('Itemid', null, 'uint');

			// build return URL
			$return_url = JRoute::_('index.php?option=com_vikrestaurants&view=' . $view . ($itemid ? '&Itemid=' . $itemid : ''), false);
		}
		else
		{
			// decode return URL
			$return_url = base64_decode($return_url);
		}

		$app->redirect($return_url);
	}

	/**
	 * Task used to register a product review left by
	 * a customer.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	function submit_review()
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$dbo    = JFactory::getDbo();
		$user   = JFactory::getUser();
		$config = VREFactory::getConfig();

		// build return URL
		$url =  'index.php?option=com_vikrestaurants&view=revslist';

		// append item ID
		$itemid = $input->get('Itemid', null, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}
		
		// append request filters
		foreach ($input->get('request', array(), 'array') as $k => $v)
		{
			if (!empty($k))
			{
				$url .= '&' . $k . '=' . $v;
			}
		}
		
		// fetch review data
		$args = array();
		$args['title']               = $input->getString('review_title');
		$args['comment']             = $input->getString('review_comment');
		$args['rating']              = $input->getUint('review_rating', 1);
		$args['id_takeaway_product'] = $input->getUint('id_tk_prod');
		$args['published']           = VikRestaurants::isReviewsAutoPublished();
		$args['langtag']             = JFactory::getLanguage()->getTag();

		if ($user->guest)
		{
			// get user details from request
			$args['jid']   = 0;
			$args['name']  = $input->getString('review_user_name');
			$args['email'] = $input->getString('review_user_mail');
		}
		else
		{
			// use account name and e-mail
			$args['jid']   = $user->id;
			$args['name']  = $user->name;
			$args['email'] = $user->email;
		}

		// bind data before starting checking the request,
		// so that we can recover the filled details
		$app->setUserState('vre.review.data', $args);

		/**
		 * Prevent direct access to this task.
		 * Submit is allowed only if the form
		 * to leave a review is visited.
		 *
		 * @since 1.8
		 */
		if (!JSession::checkToken())
		{
			// invalid session token
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}

		$vik = VREApplication::getInstance();

		/**
		 * Added support for ReCaptcha validation.
		 * The ReCaptha is displayed only if it has
		 * been globally configured.
		 *
		 * @since 1.8
		 */
		if ($vik->isGlobalCaptcha() && !$vik->reCaptcha('check'))
		{
			// invalid captcha
			$app->enqueueMessage(JText::_('PLG_RECAPTCHA_ERROR_INCORRECT_CAPTCHA_SOL'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}

		// make sure the user is allowed to leave a reviews for this product
		if (!VikRestaurants::canLeaveTakeAwayReview($args['id_takeaway_product']))
		{
			// user cannot leave a review for this element
			$app->enqueueMessage(JText::_('VRPOSTREVIEWAUTHERR'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}
		
		// if comment is required, make sure it is not empty
		if ($config->getBool('revcommentreq') && empty($args['comment']))
		{
			// missing required comment
			$app->enqueueMessage(JText::_('VRPOSTREVIEWFILLERR'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}

		// make sure, if specified, the specified comment is long enough
		if (strlen($args['comment']) > 0 && strlen($args['comment']) < $config->getUint('revminlength'))
		{
			// not enough characters wrote
			$app->enqueueMessage(JText::_('VRPOSTREVIEWFILLERR'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}

		// validate user data
		if (empty($args['name']) || !VikRestaurants::validateUserEmail($args['email']))
		{
			// invalid name or e-mail
			$app->enqueueMessage(JText::_('VRPOSTREVIEWFILLERR'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}

		// strip the characters that exceed the maximum limit
		$args['comment'] = mb_substr($args['comment'], 0, $config->getUint('revmaxlength'), 'UTF-8');

		// check if this is a verified purchaser
		$args['verified'] = (int) VikRestaurants::isVerifiedTakeAwayReview($args['id_takeaway_product']);

		// always load tables from the back-end
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		// get review table
		$review = JTableVRE::getInstance('review', 'VRETable');

		// try to save the review
		if (!$review->save($args))
		{
			// an error occurred
			$app->enqueueMessage(JText::_('VRPOSTREVIEWFILLERR'), 'error');
			$app->redirect(JRoute::_($url . '&submit_rev=1', false));
			exit;
		}

		// clear user state on success
		$app->setUserState('vre.review.data', array());
		
		if ($args['published'])
		{
			// review approved
			$app->enqueueMessage(JText::_('VRPOSTREVIEWCREATEDCONF'));
		}
		else
		{
			// waiting for approval
			$app->enqueueMessage(JText::_('VRPOSTREVIEWCREATEDPEND'));
		}

		VRELoader::import('library.mail.factory');

		// get notification e-mail for admin
		$reviewMail = VREMailFactory::getInstance('takeaway', 'review', $review->id);

		// check if the administrator should receive the notification e-mail
		if ($reviewMail->shouldSend())
		{
			// send e-mail notification
			$reviewMail->send();
		}
		
		$app->redirect(JRoute::_($url, false));
	}

	/**
	 * Returns the details of the delivery area that contains
	 * the specified coordinates/ZIP code.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	function get_location_delivery_info()
	{
		$input   = JFactory::getApplication()->input;
		$session = JFactory::getSession();

		// load arguments from request
		$lat  = $input->getFloat('lat');
		$lng  = $input->getFloat('lng');
		$zip  = $input->getString('zip');
		$city = $input->getString('city');
		$addr = $input->get('address', array(), 'array');

		$area = null;

		// make sure we have some delivery areas
		if ($has = VikRestaurants::hasDeliveryAreas())
		{
			// find the matching area
			$area = VikRestaurants::getDeliveryAreaFromCoordinates($lat, $lng, $zip, $city);
		}

		$response = new stdClass;
		$response->status = 0;

		if ($area === null && $has)
		{
			// address not accepted for delivery
			$response->error = JText::_('VRTKDELIVERYLOCNOTFOUND');
			$session->clear('delivery_address', 'vre');	
		}
		else
		{
			$currency = VREFactory::getCurrency();

			$response->status    = 1;
			$response->latitude	 = $lat;
			$response->longitude = $lng;
			$response->zip       = $zip;
			$response->address   = $addr;

			$response->area = new stdClass;

			if ($has)
			{
				$response->area->name         = $area['name'];
				$response->area->charge       = (float) $area['charge'];
				$response->area->chargeLabel  = ($area['charge'] > 0 ? '+ ' : '') . $currency->format($area['charge']);
				$response->area->minCost      = (float) $area['min_cost'];
				$response->area->minCostLabel = $currency->format($area['min_cost']);
			}
			else
			{
				$response->area->name         = '';
				$response->area->charge       = 0.0;
				$response->area->chargeLabel  = $currency->format(0);
				$response->area->minCost      = 0.0;
				$response->area->minCostLabel = $currency->format(0);
			}

			// fill full charge label
			$base_charge = VikRestaurants::getTakeAwayDeliveryServiceAddPrice();
			$percent_tot = VikRestaurants::getTakeAwayDeliveryServicePercentOrTotal();

			if ($percent_tot == 1)
			{
				$response->area->fullChargeLabel = $base_charge . '%' . ($response->area->charge != 0 ? ' ' . $response->area->chargeLabel : '');
			}
			else
			{
				$base_charge += $response->area->charge;
				$response->area->fullChargeLabel = ($base_charge > 0 ? '+ ' : '') . $currency->format($base_charge);
			}

			// register details in session
			$session->set('delivery_address', $response, 'vre');
		}

		echo json_encode($response);
		exit;
	}

	/**
	 * ##########################
	 * #     APIs End-Point     #
	 * ##########################
	 * 
	 * This function is the end-point to dispatch events requested from external connections.
	 * It is required to specify all the following values:
	 *
	 * @param 	string 	username 	The username for login.
	 * @param 	string 	password 	The password for login.
	 * @param 	string 	event 		The name of the event to dispatch.
	 * 
	 * It is also possible to pre-send certain arguments to dispatch within the event:
	 *
	 * @param 	array 	args 		The arguments of the event (optional).
	 *								All the specified values are cleansed with string filtering.
	 *
	 * @return 	string 				In case of error it is returned a JSON string with the code (errcode) 
	 * 								and the message of the error (error).
	 *								In case of success the result may vary on the event dispatched.
	 *
	 * @since 	1.6
	 */
	function apis()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// instantiate APIs Framework
		// leave constructor empty to select default plugins folder: 
		// .../helpers/library/apislib/apis/plugins/
		$apis = VREFactory::getApis();

		// check if APIs are allowed, otherwise disable all
		if (!$apis->isEnabled())
		{
			/**
			 * Use a HTTP response in place of the JSON one.
			 *
			 * @since 1.8.4
			 */
			UIErrorFactory::raiseError(403, 'API Framework is disabled');
		}

		// flush stored APIs logs
		VikRestaurants::flushApiLogs();

		// get credentials
		$username = $input->getString('username');
		$password = $input->getString('password');

		// get event to dispatch
		$event = $input->get('event');

		/**
		 * Try to retrieve the plugin arguments from JSON body.
		 *
		 * @since 1.8.3
		 */
		$args = $input->json->getArray();

		if (!$args)
		{
			// arguments not found, try to retrieve them from the request
			$args = $input->get('args', array(), 'string');
		}

		// create a Login for this user
		$login = new LoginAPIs($username, $password, $input->server->get('REMOTE_ADDR'));

		// do login
		if (!$apis->connect($login))
		{
			// user is not authorized to login
			$apis->output($apis->getError());

			// terminate the request
			$app->close();
		}

		// user correctly logged in, dispatch the event
		$result = $apis->trigger($event, $args);

		// always disconnect the user
		$apis->disconnect();

		if (!$result)
		{
			// event error thrown
			$apis->output($apis->getError());
		}

		// terminate the request
		$app->close();
	}
}
