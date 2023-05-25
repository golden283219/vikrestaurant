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
 * VikRestaurants reservation controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerReservation extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data = array();

		// use the checkin date, if specified
		$checkin_date = $app->input->getString('date', '');

		if ($checkin_date)
		{
			$data['date'] = $checkin_date;
		}

		// use the checkin time, if specified
		$checkin_time = $app->input->getString('hourmin', '');

		if ($checkin_time)
		{
			$data['hourmin'] = $checkin_time;
		}

		// use the table, if specified
		$id_table = $app->input->getUint('idt', 0);

		if ($id_table)
		{
			$data['id_table'] = $id_table;
		}

		// use the number of participants, if specified
		$people = $app->input->getUint('people', 0);

		if ($people)
		{
			$data['people'] = $people;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.reservation.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// recover incoming view
		$from = $app->input->get('from');

		$url = 'index.php?option=com_vikrestaurants&view=managereservation';

		if ($from)
		{
			$url .= '&from=' . $from;
		}

		$this->setRedirect($url);

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.reservation.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		// recover incoming view
		$from = $app->input->get('from');

		$url = 'index.php?option=com_vikrestaurants&view=managereservation&cid[]=' . $cid[0];

		if ($from)
		{
			$url .= '&from=' . $from;
		}

		$this->setRedirect($url);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->cancel();
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return 	void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			// recover incoming view
			$from = JFactory::getApplication()->input->get('from');

			$url = 'index.php?option=com_vikrestaurants&task=reservation.add';

			if ($from)
			{
				$url .= '&from=' . $from;
			}

			$this->setRedirect($url);
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['date']                 = $input->getString('date', '');
		$args['hourmin']              = $input->getString('hourmin', '');
		$args['id_table']             = $input->getUint('id_table', 0);
		$args['people']               = $input->getUint('people', 0);
		$args['id_user'] 	          = $input->getUint('id_user', 0);
		$args['purchaser_nominative'] = $input->getString('purchaser_nominative', '');
		$args['purchaser_mail']       = $input->getString('purchaser_mail', '');
		$args['purchaser_phone']      = $input->getString('purchaser_phone', '');
		$args['purchaser_prefix']     = $input->getString('purchaser_prefix', '');
		$args['purchaser_country']    = $input->getString('purchaser_country', '');
		$args['deposit']              = $input->getFloat('deposit', 0.0);
		$args['bill_value']           = $input->getFloat('bill_value', 0.0);
		$args['bill_closed']          = $input->getUint('bill_closed', 0);
		$args['status']               = $input->getString('status', '');
		$args['id_payment']           = $input->getUint('id_payment', 0);
		$args['notes']                = $input->getRaw('notes', '');
		$args['stay_time']            = $input->getUint('stay_time', 0);
		$args['id']                   = $input->getInt('id', 0);

		// get restaurant custom fields
		$args['custom_f'] = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_RESTAURANT, $match, $strict = false);

		// auto-fill purchaser nominative if specified only as custom fields
		if (empty($args['purchaser_nominative']) && !empty($match['purchaser_nominative']))
		{
			$args['purchaser_nominative'] = $match['purchaser_nominative'];
		}

		// auto-fill purchaser e-mail if specified only as custom fields
		if (empty($args['purchaser_mail']) && !empty($match['purchaser_mail']))
		{
			$args['purchaser_mail'] = $match['purchaser_mail'];
		}

		// auto-fill purchaser phone if specified only as custom fields
		if (empty($args['purchaser_phone']) && !empty($match['purchaser_phone']))
		{
			$args['purchaser_phone'] = $match['purchaser_phone'];
		}

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// recover incoming view
		$from = $input->get('from');

		// get record table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// try to save arguments
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managereservation';

			if ($reservation->id)
			{
				$url .= '&cid[]=' . $reservation->id;
			}

			if ($from)
			{
				$url .= '&from=' . $from;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// update reservation menus
		$resmenu = JTableVRE::getInstance('resmenu', 'VRETable');

		$menu_assoc    = $input->get('menu_assoc', array(), 'array');
		$menu_quantity = $input->get('quantity', array(), 'array');

		foreach ($menu_assoc as $id_menu => $id)
		{
			// get related quantity
			$quantity = isset($menu_quantity[$id_menu]) ? $menu_quantity[$id_menu] : 0;

			if ($quantity)
			{
				$menu = array(
					'id'             => $id,
					'id_menu'        => $id_menu,
					'id_reservation' => $reservation->id,
					'quantity'       => $quantity,
				);

				// insert/update the reservation menu
				$resmenu->save($menu);
			}
			else if ($id)
			{
				// no quantity for this menu, delete it
				$resmenu->delete($id);
			}
		}

		// check if we should send a notification e-mail to the customer
		if ($input->getBool('notify_customer'))
		{
			// import mail factory class
			VRELoader::import('library.mail.factory');
			// instantiate mail provider
			$mail = VREMailFactory::getInstance('restaurant', 'customer', $reservation->id);
			// send e-mail to customer
			$mail->send();
		}

		$url = 'index.php?option=com_vikrestaurants&task=reservation.edit&cid[]=' . $reservation->id;

		if ($from)
		{
			$url .= '&from=' . $from;
		}

		// redirect to edit page
		$this->setRedirect($url);

		return true;
	}

	/**
	 * Task used to save a reservation closure.
	 *
	 * @param 	boolean  $ajax  True if the request has been made via AJAX.
	 *
	 * @return 	boolean
	 */
	public function saveclosure($ajax = false)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();
		
		$args = array();
		$args['id'] = $input->get('id', 0, 'uint');

		if ($input->getBool('reopen'))
		{
			// permanently delete closure in case "RE-OPEN" checkbox was checked
			$input->set('cid', array($args['id']));
			$this->delete();
			return false;
		}

		// load closure data from request
		$args['date']      = $input->get('date', '', 'string');
		$args['hourmin']   = $input->get('hourmin', '', 'string');
		$args['hour']	   = $input->get('hour', '', 'string');
		$args['min']	   = $input->get('min', '', 'string');
		$args['id_table']  = $input->get('id_table', 0, 'uint');
		$args['notes']     = $input->get('notes', '', 'raw');
		$args['stay_time'] = $input->get('stay_time', 0, 'uint');

		if (empty($args['id_table']))
		{
			// try to retrieve table from a different variable
			$args['id_table'] = $input->get('idt', 0, 'uint');

			if (empty($args['id_table']))
			{
				if ($ajax)
				{
					// raise error
					UIErrorFactory::raiseError(400, 'Missing table ID');
				}
				else
				{
					// display error message
					$app->enqueueMessage('Missing table ID', 'error');
					
					$this->cancel();

					return false;
				}
			}
		}

		if (empty($args['stay_time']))
		{
			// use default amount if time of stay was not specified
			$args['stay_time'] = VikRestaurants::getAverageTimeStay();
		}

		// get table details
		$q = $dbo->getQuery(true)
			->select($dbo->qn('max_capacity'))
			->from($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id') . ' = ' . $args['id_table']);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			if ($ajax)
			{
				// raise error
				UIErrorFactory::raiseError(404, 'Table ID [' . $args['id_table'] . '] not found');
			}
			else
			{
				// display error message
				$app->enqueueMessage('Table ID [' . $args['id_table'] . '] not found', 'error');
				
				$this->cancel();

				return false;
			}
		}

		// Always use the maximum capacity supported by the table.
		// This avoids to receive other reservations in case the
		// table is shared
		$args['people'] = (int) $dbo->loadResult();

		$args['closure']              = 1;
		$args['status']               = 'CONFIRMED';
		$args['purchaser_nominative'] = 'CLOSURE';

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			if ($ajax)
			{
				// raise error
				UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}
			else
			{
				// back to main list, not authorised to create/edit records
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$this->cancel();

				return false;
			}
		}

		// get record table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// try to save arguments
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			$error = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error);

			if ($ajax)
			{
				// raise error
				UIErrorFactory::raiseError(500, $error);
			}
			else
			{
				// display error message
				$app->enqueueMessage($error, 'error');

				$this->cancel();
					
				return false;
			}
		}

		if ($ajax)
		{
			echo $reservation->id;
			exit;
		}

		$this->cancel();

		return true;
	}

	/**
	 * AJAX end-point used to save a reservation closure.
	 *
	 * @return 	void
	 */
	public function saveclosureajax()
	{
		$this->saveclosure(true);
	}

	/**
	 * Task used to switch table for the given reservation.
	 *
	 * @return 	boolean
	 */
	public function changetable()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		$args = array();
		$args['id_table'] = $input->get('newid', 0, 'uint');
		$args['id']       = $input->get('id_order', 0, 'uint');

		// check user permissions (do not allow creation of new reservations here)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$args['id'])
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get search arguments from request
		$date    = $input->get('date', '', 'string');
		$hourmin = $input->get('hourmin', '', 'string');
		$table   = $args['id_table'];

		// recover number of people from reservation details
		$q = $dbo->getQuery(true)
			->select($dbo->qn('people'))
			->from($dbo->qn('#__vikrestaurants_reservation'))
			->where($dbo->qn('id') . ' = ' . $args['id']);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			throw new Exception('Unable to find the reservation [' . $args['id'] . ']', 404);
		}

		$people = (int) $dbo->loadResult();

		// create search parameters
		$search = new VREAvailabilitySearch($date, $hourmin, $people);

		// check if the specified table is available
		if ($search->isTableAvailable($table, $args['id']))
		{
			// get record table
			$reservation = JTableVRE::getInstance('reservation', 'VRETable');

			// update reservation
			if ($reservation->save($args))
			{
				$app->enqueueMessage(JText::_('VRMAPTABLECHANGEDSUCCESS'));
			}
			else
			{
				// get string error
				$error = $reservation->getError(null, true);

				// display error message
				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');
			}
		}
		else
		{
			// table already occupied
			$app->enqueueMessage(JText::_('VRMAPTABLENOTCHANGED'), 'error');
		}

		$this->cancel();

		return true;
	}

	/**
	 * AJAX end-point used to switch table for the given reservation.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.3
	 */
	public function changetableajax()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		$args = array();
		$args['id_table'] = $input->get('id_table', 0, 'uint');
		$args['id']       = $input->get('id_order', 0, 'uint');

		// check user permissions (do not allow creation of new reservations here)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$args['id'])
		{
			// not authorised to create/edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// do not search for tables availability
		
		// get record table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// update reservation
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			$error = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error);

			// an error occurred while trying to save the reservation
			UIErrorFactory::raiseError(500, $error);
		}
		
		echo 1;
		exit;
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'uint');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		JTableVRE::getInstance('reservation', 'VRETable')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		// recover incoming view
		$from = JFactory::getApplication()->input->get('from', null);

		$this->setRedirect('index.php?option=com_vikrestaurants&view=' . ($from ? $from : 'reservations'));
	}

	/**
	 * Task used to access the BILL management page of an existing reservation.
	 *
	 * @return 	boolean
	 */
	public function editbill()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.resbill.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$url = 'index.php?option=com_vikrestaurants&view=managebill&cid[]=' . $cid[0];

		// recover incoming view
		$from = $app->input->get('from');
		
		if ($from)
		{
			$url .= '&from=' . $from;
		}

		$this->setRedirect($url);

		return true;
	}

	/**
	 * Task used to save the reservation BILL data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclosebill()
	{
		if ($this->savebill())
		{
			$this->cancel();
		}
	}

	/**
	 * Task used to save the reservation BILL data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the BILL that has been saved.
	 *
	 * @return 	boolean
	 */
	public function savebill()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['bill_value']  = $input->get('bill_value', 0, 'float');
		$args['bill_closed'] = $input->get('bill_closed', 0, 'uint');
		$args['deposit'] 	 = $input->get('deposit', 0, 'float');
		$args['tot_paid'] 	 = $input->get('tot_paid', 0, 'float');
		$args['id']          = $input->get('id', 0, 'uint');

		// discount
		$discount = array();
		$discount['method']    = $input->get('method', 0, 'uint');
		$discount['id_coupon'] = $input->get('id_coupon', 0, 'uint');
		$discount['amount']    = $input->get('amount', 0.0, 'float');
		$discount['percentot'] = $input->get('percentot', 0, 'uint');

		// tip
		$tip = array();
		$tip['method']    = $input->get('tip_method', 0, 'uint');
		$tip['amount']    = $input->get('tip_amount', 0, 'float');
		$tip['percentot'] = $input->get('tip_percentot', 0, 'uint');

		// check user permissions
		if (!$user->authorise('core.edit', 'com_vikrestaurants') || !$args['id'])
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// unset any amount in case one of the following methods was selected:
		// 0: no selection
		// 3: remove coupon
		// 6: remove discount
		if ($discount['method'] == 0 || $discount['method'] == 3 || $discount['method'] == 6)
		{
			$discount['amount']    = 0;
			$discount['percentot'] = 2;
		}

		// returns an object containing the order total details
		$order = $reservation->getBill($args['id'], $args['bill_value']);

		// update discount value in case a method was selected
		if ($discount['method'] != 0)
		{
			// remove discount amount from order net (previous discount is ignored)
			if ($discount['percentot'] == 1)
			{
				// percentage discount
				$order->finalNet = $order->net - $order->net * $discount['amount'] / 100;
			}
			else
			{
				// fixed discount
				$order->finalNet = $order->net - $discount['amount'];
			}

			/**
			 * Always round the calculated amount to 2 decimals, in order
			 * to avoid roundings when saving the amount in the database.
			 *
			 * Use PHP_ROUND_HALF_DOWN to let the discount will be rounded up
			 * instead of the final cost.
			 *
			 * @since 1.8
			 */
			$order->finalNet = round($order->finalNet, 2, PHP_ROUND_HALF_DOWN);
		}

		// make sure the discount is not lower than 0
		$order->finalNet = max(array(0, $order->finalNet));

		// check if we should alter the coupon code
		if ($discount['method'] == 1 || $discount['method'] == 2 || $discount['method'] == 3)
		{
			// clear coupon
			$args['coupon_str'] = '';

			if ($discount['method'] == 1 || $discount['method'] == 2)
			{
				// get coupon table
				$couponTable = JTableVRE::getInstance('coupon', 'VRETable');

				// add/replace coupon code
				$coupon = $couponTable->redeem($discount['id_coupon']);
				
				if ($coupon)
				{
					// overwrite coupon discount amount with the specified one
					$coupon->percentot = $discount['percentot'];
					$coupon->value     = $discount['amount'];

					// set new coupon code
					$args['coupon_str'] = $coupon;
				}
			}
		}

		// update values
		$args['discount_val'] = $order->net - $order->finalNet;
		$args['bill_value']   = $order->finalNet + $order->payCharge;

		// include current TIP amount
		$args['tip_amount'] = $order->tip;

		// apply/update tip
		if ($tip['method'] == 1 || $tip['method'] == 2)
		{
			// use given amount
			$args['tip_amount'] = $tip['amount'];

			if ($tip['percentot'] == 1)
			{
				// percentage amount, calculate tip on the total bill
				$args['tip_amount'] = $args['bill_value'] * $tip['amount'] / 100;
			}

			/**
			 * Always round the calculated amount to 2 decimals, in order
			 * to avoid roundings when saving the amount in the database.
			 *
			 * Use PHP_ROUND_HALF_DOWN to avoid receiving a tip higher than
			 * the expected amount.
			 *
			 * @since 1.8
			 */
			$args['tip_amount'] = round($args['tip_amount'], 2, PHP_ROUND_HALF_DOWN);
		}
		// remove tip
		else if ($tip['method'] == 3)
		{
			$args['tip_amount'] = 0;
		}

		// increase bill value by the TIP amount
		$args['bill_value'] += $args['tip_amount'];

		// try to save arguments
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managebill&cid[]=' . $reservation->id;

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		$url = 'index.php?option=com_vikrestaurants&task=reservation.editbill&cid[]=' . $reservation->id;

		// recover incoming view
		$from = $input->get('from');
		
		if ($from)
		{
			$url .= '&from=' . $from;
		}

		// redirect to edit page
		$this->setRedirect($url);

		return true;
	}

	/**
	 * Changes the "bill closed" parameter of the selected records.
	 *
	 * @return 	boolean
	 */
	public function changebill()
	{
		$app   = JFactory::getApplication();
		$cid   = $app->input->get('cid', array(), 'uint');
		$state = $app->input->get('state', 0, 'int');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// change state of selected records
		$table = JTableVRE::getInstance('reservation', 'VRETable');

		$table->setColumnAlias('published', 'bill_closed');
		$table->publish($cid, $state);

		// back to records list
		$this->cancel();

		return true;
	}

	/**
	 * AJAX end-point used to add an item to the reservation.
	 *
	 * @return 	void
	 */
	public function additemajax()
	{
		$input = JFactory::getApplication()->input;
		$user  = JFactory::getUser();

		$args = array();
		$args['id_reservation']    = $input->get('id_reservation', 0, 'uint');
		$args['id_product']        = $input->get('id_product', 0, 'uint');
		$args['id_product_option'] = $input->get('id_option', 0, 'uint');
		$args['quantity']          = $input->get('quantity', 1, 'uint');
		$args['price']             = $input->get('price', 0, 'float');
		$args['notes']             = $input->get('notes', '', 'string');
		$args['id']                = $input->get('id', 0, 'uint');
		
		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// raise AJAX error, not authorised to create/edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation product table
		$resprod = JTableVRE::getInstance('resprod', 'VRETable');

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// cache order so that it is possible to detect any added products (0: restaurant)
		VREOperatorLogger::getInstance()->cache($args['id_reservation'], 0);

		// get current price stored in database
		$old_price = $resprod->getPrice($args['id']);

		// try to save arguments
		if (!$resprod->save($args))
		{
			// get string error
			$error = $resprod->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		// update bill by the price of the added item (subtract the previous price from total)
		$reservation->updateBill($resprod->id_reservation, $resprod->price - $old_price);
		
		// get saved item
		$item = $resprod->getProperties();
		
		echo json_encode(array('item' => $item, 'total' => $reservation->bill_value));
		exit;
	}

	/**
	 * AJAX end-point used to remove an item from the reservation.
	 *
	 * @return 	void
	 */
	public function removeitemajax()
	{
		$input = JFactory::getApplication()->input;
		$user  = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.delete', 'com_vikrestaurants'))
		{
			// raise AJAX error, not authorised to delete records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$id_assoc = $input->get('id_assoc', 0, 'uint');
		$id_res   = $input->get('id_res', 0, 'uint');

		// get reservation product table
		$resprod = JTableVRE::getInstance('resprod', 'VRETable');

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// cache order so that it is possible to detect any deleted products (0: restaurant)
		VREOperatorLogger::getInstance()->cache($id_res, 0);

		// get product price
		$price = $resprod->getPrice($id_assoc);

		// delete record
		if (!$resprod->delete($id_assoc))
		{
			// an error occurred while trying to delete the record
			UIErrorFactory::raiseError(500, JText::_('VRE_AJAX_GENERIC_ERROR'));
		}

		// update bill by subtracting the price of the removed product
		$reservation->updateBill($id_res, $price * -1);
		
		echo json_encode($reservation->bill_value);
		exit;
	}

	/**
	 * AJAX end-point used to change the status code of a reservation.
	 *
	 * @return 	void
	 */
	public function changecodeajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();
		
		$code = array();
		$code['group']      = 1;
		$code['id_order']   = $input->get('id', 0, 'uint');
		$code['id_rescode'] = $input->get('id_code', 0, 'uint');
		$code['notes'] 		= $input->get('notes', '', 'string');
		$code['id']         = 0;

		if (empty($notes))
		{
			// use NULL to avoid overwriting the notes
			$notes = null;
		}

		// check user permissions (abort in case the order ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$code['id_order'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$args = array();
		$args['id']      = $code['id_order'];
		$args['rescode'] = $code['id_rescode'];

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// update reservation
		$reservation->save($args);

		if ($code['id_rescode'])
		{
			// get record table
			$rescodeorder = JTableVRE::getInstance('rescodeorder', 'VRETable');

			// try to save arguments
			$rescodeorder->save($code);
		}
		
		// get reservation codes details
		$rescode = JHtml::_('vikrestaurants.rescode', $code['id_rescode'], $code['group']);

		echo json_encode($rescode);
		exit;
	}

	/**
	 * AJAX end-point used to change the reservation notes.
	 *
	 * @return 	void
	 */
	public function savenotesajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$args = array();
		$args['id']    = $input->get('id', 0, 'uint');
		$args['notes'] = $input->get('notes', '', 'string');

		// check user permissions (abort in case the order ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$args['id'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// update reservation
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to assign an operator to the reservation.
	 *
	 * @return 	void
	 */
	public function assignoperatorajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$args = array();
		$args['id']          = $input->get('id', 0, 'uint');
		$args['id_operator'] = $input->get('id_operator', 0, 'uint');

		// check user permissions (abort in case the order ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$args['id'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// update reservation
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point to obtain a JSON list of available tables for
	 * the specified search arguments.
	 *
	 * @return 	void
	 */
	public function availabletablesajax()
	{
		$input = JFactory::getApplication()->input;

		$args = array();
		$args['date']     = $input->get('date', '', 'string');
		$args['hourmin']  = $input->get('hourmin', '0:0', 'string');
		$args['people']   = $input->get('people', 2, 'uint');
		$args['staytime'] = $input->get('staytime', null, 'uint');
		$args['id_res']   = $input->get('id_res', 0, 'uint');

		// Prepare search arguments.
		// Do not use ADMIN permissions to properly display the tables as unavailable
		// when they are unpublished or in case they belong to a closed room.
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $args['people'], $admin = false);

		/**
		 * Force usage of the specified time of stay.
		 *
		 * @since 1.8.2
		 */
		$search->setStayTime($args['staytime']);

		// get free tables (exclude current reservation ID)
		$tables = $search->getAvailableTables($args['id_res']);

		$list = array();

		foreach ($tables as $t)
		{
			$list[] = $t->id;
		}

		echo json_encode($list);
		exit;
	}

	/**
	 * AJAX end-point used to confirm a reservation.
	 *
	 * @return 	void
	 */
	public function confirmajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$ids = $input->get('cid', array(), 'uint');

		// filter empty IDs to avoid inserting them
		$ids = array_filter($ids);

		// check user permissions (abort in case the order ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants')
			|| !$user->authorise('core.access.reservations', 'com_vikrestaurants')
			|| count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		foreach ($ids as $id)
		{
			$args = array(
				'id'         => $id,
				'status'     => 'CONFIRMED',
				'need_notif' => 1,
			);

			// update reservation
			if (!$reservation->save($args))
			{
				// get string error
				$error = $reservation->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to decline a reservation.
	 *
	 * @return 	void
	 */
	public function refuseajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$ids = $input->get('cid', array(), 'uint');

		// filter empty IDs to avoid inserting them
		$ids = array_filter($ids);

		// check user permissions (abort in case the order ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants')
			|| !$user->authorise('core.access.reservations', 'com_vikrestaurants')
			|| count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		foreach ($ids as $id)
		{
			$args = array(
				'id'     => $id,
				'status' => 'REMOVED',
			);

			// update reservation
			if (!$reservation->save($args))
			{
				// get string error
				$error = $reservation->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to notify a reservation.
	 *
	 * @return 	void
	 */
	public function notifyajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$ids = $input->get('cid', array(), 'uint');

		// filter empty IDs to avoid inserting them
		$ids = array_filter($ids);

		// check user permissions (abort in case the order ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants')
			|| !$user->authorise('core.access.reservations', 'com_vikrestaurants')
			|| count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		VRELoader::import('library.mail.factory');

		foreach ($ids as $id)
		{
			$args = array(
				'id'         => $id,
				'need_notif' => 0,
			);

			// update reservation
			if (!$reservation->save($args))
			{
				// get string error
				$error = $reservation->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}

			// instantiate mail provider
			$mail = VREMailFactory::getInstance('restaurant', 'customer', $id);

			// send e-mail notification to customer
			$mail->send();
		}

		echo 1;
		exit;
	}

	/**
	 * Sends a notification SMS to the customer of the specified reservation.
	 *
	 * @return 	void
	 */
	public function sendsms()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$cid = $input->get('cid', array(), 'uint');

		// check user permissions
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$user->authorise('core.access.reservations', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to send SMS notifications
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		try
		{
			// get current SMS instance
			$smsapi = VREApplication::getInstance()->getSmsInstance();
		}
		catch (Exception $e)
		{
			// back to main list, SMS API not configured
			$app->enqueueMessage(JText::_('VRSMSESTIMATEERR1'), 'error');
			$this->cancel();

			return false;
		}

		$notified = 0;
		$errors   = array();

		foreach ($cid as $id)
		{
			// get reservation details
			$order = VREOrderFactory::getReservation($id);

			// make sure the order exists and the purchaser phone is not empty
			if ($order && $order->purchaser_phone)
			{
				// get SMS notification message (0: restaurant reservation)
				$message = VikRestaurants::getSmsCustomerTextMessage($order, 0);

				// send message
				$response = $smsapi->sendMessage($order->purchaser_phone, $message);

				// validate response
				if ($smsapi->validateResponse($response))
				{
					// successful notification
					$notified++;
				}
				else
				{
					// unable to send the notification, register error message
					$errors[] = $smsapi->getLog();
				}
			}
		}

		if ($notified)
		{
			// successful message
			$app->enqueueMessage(JText::plural('VRCUSTOMERSMSSENT', $notified));
		}
		else
		{
			// no notifications sent
			$app->enqueueMessage(JText::plural('VRCUSTOMERSMSSENT', $notified), 'warning');
		}

		// display any returned errors
		if ($errors)
		{
			// do not display duplicate or empty errors
			$errors = array_unique(array_filter($errors));

			foreach ($errors as $err)
			{
				$app->enqueueMessage($err, 'error');
			}
		}

		// back to main list
		$this->cancel();
	}

	/**
	 * Starts the incoming reservations.
	 *
	 * @return 	void
	 */
	public function startincoming()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();

		// already set redirect to dashboard
		$this->setRedirect('index.php?option=com_vikrestaurants');

		$canDo = $user->authorise('core.access.reservations', 'com_vikrestaurants')
			&& $user->authorise('core.edit', 'com_vikrestaurants');

		if (!$canDo)
		{
			// back to main list, not authorised to send SMS notifications
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		// restart incoming reservation
		VREFactory::getConfig()->set('stopuntil', -1);
		return true;
	}

	/**
	 * Stops the incoming reservations.
	 *
	 * @return 	void
	 */
	public function stopincoming()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();

		// already set redirect to dashboard
		$this->setRedirect('index.php?option=com_vikrestaurants');

		$canDo = $user->authorise('core.access.reservations', 'com_vikrestaurants')
			&& $user->authorise('core.edit', 'com_vikrestaurants');

		if (!$canDo)
		{
			// back to main list, not authorised to send SMS notifications
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		// fetch limit
		$date = getdate(VikRestaurants::now());
		$until = mktime(0, 0, 0, $date['mon'], $date['mday'] + 1, $date['year']);

		// restart incoming reservation
		VREFactory::getConfig()->set('stopuntil', $until);
		return true;
	}
}
