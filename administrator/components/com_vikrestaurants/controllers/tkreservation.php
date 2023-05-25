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
 * VikRestaurants take-away order controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerTkreservation extends VREControllerAdmin
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

		// unset user state for being recovered again
		$app->setUserState('vre.tkreservation.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// recover incoming view
		$from = JFactory::getApplication()->input->get('from');

		$url = 'index.php?option=com_vikrestaurants&view=managetkreservation';

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
		$app->setUserState('vre.tkreservation.data', array());

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
		$from = JFactory::getApplication()->input->get('from');

		$url = 'index.php?option=com_vikrestaurants&view=managetkreservation&cid[]=' . $cid[0];

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

			$url = 'index.php?option=com_vikrestaurants&task=tkreservation.add';

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
		$args['delivery_service']     = $input->getUint('delivery_service', 0);
		$args['id_user'] 	          = $input->getUint('id_user', 0);
		$args['purchaser_nominative'] = $input->getString('purchaser_nominative', '');
		$args['purchaser_mail']       = $input->getString('purchaser_mail', '');
		$args['purchaser_phone']      = $input->getString('purchaser_phone', '');
		$args['purchaser_prefix']     = $input->getString('purchaser_prefix', '');
		$args['purchaser_country']    = $input->getString('purchaser_country', '');
		// always let the address is fully recovered from the custom fields
		// because the purchaser address string might not contain "address_2"
		// $args['purchaser_address']    = $input->getString('id_useraddr', '');
		$args['total_to_pay']         = $input->getFloat('total_to_pay', 0.0);
		$args['pay_charge']           = $input->getFloat('pay_charge', 0.0);
		$args['delivery_charge']      = $input->getFloat('delivery_charge', 0.0);
		$args['taxes']                = $input->getFloat('taxes', 0.0);
		$args['status']               = $input->getString('status', '');
		$args['id_payment']           = $input->getUint('id_payment', 0);
		$args['route']                = $input->get('route', array(), 'array');
		$args['notes']                = $input->getRaw('notes', '');
		$args['id']                   = $input->getInt('id', 0);

		// get restaurant custom fields
		$args['custom_f'] = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_TAKEAWAY, $match, $strict = false);

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

		// auto-fill purchaser address if specified only as custom fields
		if (empty($args['purchaser_address']) && isset($match['purchaser_address']))
		{
			$args['purchaser_address'] = $match['purchaser_address'];
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
		$from = JFactory::getApplication()->input->get('from');

		// get record table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		// try to save arguments
		if (!$order->save($args))
		{
			// get string error
			$error = $order->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managetkreservation';

			if ($order->id)
			{
				$url .= '&cid[]=' . $order->id;
			}

			if ($from)
			{
				$url .= '&from=' . $from;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// check if we should send a notification e-mail to the customer
		if ($input->getBool('notify_customer'))
		{
			// import mail factory class
			VRELoader::import('library.mail.factory');
			// instantiate mail provider
			$mail = VREMailFactory::getInstance('takeaway', 'customer', $order->id);
			// send e-mail to customer
			$mail->send();
		}

		if ($args['id'] == 0)
		{
			// redirect to cart page when creating a new reservation (do not display any messages here)
			$this->setRedirect('index.php?option=com_vikrestaurants&view=managetkrescart&cid[]=' . $order->id);
		}
		else
		{
			// display generic successful message
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

			$url = 'index.php?option=com_vikrestaurants&task=tkreservation.edit&cid[]=' . $order->id;

			if ($from)
			{
				$url .= '&from=' . $from;
			}

			// redirect to edit page
			$this->setRedirect($url);
		}

		return true;
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
		JTableVRE::getInstance('tkreservation', 'VRETable')->delete($cid);

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

		$this->setRedirect('index.php?option=com_vikrestaurants&view=' . ($from ? $from : 'tkreservations'));
	}

	/**
	 * Task used to access the BILL management page of an existing order.
	 *
	 * @return 	boolean
	 */
	public function editbill()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.tkdiscord.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));


		$this->setRedirect('index.php?option=com_vikrestaurants&view=tkdiscord&cid[]=' . $cid[0]);

		return true;
	}

	/**
	 * Task used to save the order BILL data set in the request.
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
		$args['id'] = $input->get('id', 0, 'uint');

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
		$reservation = JTableVRE::getInstance('tkreservation', 'VRETable');

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
		$order = $reservation->getBill($args['id']);

		$use_taxes = VikRestaurants::isTakeAwayTaxesUsable();

		if (!$use_taxes)
		{
			// In case of included taxes, the discount should be applied to the
			// GRAND TOTAL. For this reason, we need to re-add the taxes to the
			// total net first.
			$order->net      += $order->taxes;
			$order->finalNet += $order->taxes;
		}

		// NET_NO_DISCOUNT : TAXES_NO_DISCOUNT = NET_DISCOUNT : TAXES_DISCOUNT
		$order->taxes_no_disc = $order->finalNet > 0 ? $order->net * $order->taxes / $order->finalNet : 0;

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

		// get new taxes
		// NET_DISCOUNT : TAX_DISCOUNT = BASE_NET : BASE_TAX
		// TAX_DISCOUNT = NET_DISCOUNT * BASE_TAX / BASE_NET
		$order->taxes = $order->net > 0 ? $order->finalNet * $order->taxes_no_disc / $order->net : 0;

		/**
		 * Always round the calculated amount to 2 decimals, in order
		 * to avoid roundings when saving the amount in the database.
		 *
		 * Use PHP_ROUND_HALF_UP to avoid stealing any cents to the state!
		 * It is better to pay a bit more of VAT rather than having taxes problems...
		 *
		 * @since 1.8
		 */
		$order->taxes = round($order->taxes, 2, PHP_ROUND_HALF_UP);

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
		$args['total_to_pay'] = $order->finalNet + $order->payCharge + $order->deliveryCharge;
		$args['taxes']        = $order->taxes;

		if ($use_taxes)
		{
			// excluded taxes, add taxes again to grand total
			$args['total_to_pay'] += $order->taxes;
		}

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
				$args['tip_amount'] = $args['total_to_pay'] * $tip['amount'] / 100;
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
		$args['total_to_pay'] += $args['tip_amount'];

		// try to save arguments
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=tkdiscord&cid[]=' . $reservation->id;

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		$url = 'index.php?option=com_vikrestaurants&task=tkreservation.editbill&cid[]=' . $reservation->id;

		// redirect to edit page
		$this->setRedirect($url);

		return true;
	}

	/**
	 * AJAX End-point used to change the status code of an order.
	 *
	 * @return 	void
	 */
	public function changecodeajax()
	{
		$input = JFactory::getApplication()->input;	
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();
		
		$code = array();
		$code['group']      = 2;
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

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		// update order
		$order->save($args);

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
	 * AJAX End-point used to retrieve the management form of the selected
	 * product, which might be stored within the cart (EDIT) or going to
	 * be added for the first time (INSERT).
	 *
	 * @return 	void
	 */
	public function cartitem()
	{
		$input = JFactory::getApplication()->input;	
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		$id_product = $input->get('id_product', 0, 'uint');
		$id_item    = $input->get('id_item', 0, 'uint');

		// get product table
		$prod = JTableVRE::getInstance('tkentry', 'VRETable');

		// obtain product details
		$data = $prod->getProduct($id_product);

		if (!$data)
		{
			// raise AJAX error, unable to find selected item
			UIErrorFactory::raiseError(403, JText::_('VRTKCARTROWNOTFOUND'));
		}

		// get item table
		$item = JTableVRE::getInstance('tkresprod', 'VRETable');

		// obtain cart item details
		$item = $item->getItem($id_item);

		if (!$item)
		{
			// use default product data
			$item = new stdClass;
			$item->id                = 0;
			$item->id_product_option = $data->variations ? $data->variations[0]->id : 0;
			$item->price             = $data->price;
			$item->quantity          = 1;
			$item->notes             = '';
			$item->toppingGroupsRel  = array();

			if ($data->variations)
			{
				// increase default price by the variation price
				$item->price += $data->variations[0]->inc_price;
			}

			// iterate topping groups
			foreach ($data->groups as $group)
			{
				// check if the group has toppings, requires a single selection and
				// it is suitable for any variation or for the selected one
				if (!$group->multiple && $group->toppings && ($group->id_variation == 0 || $group->id_variation == $item->id_product_option))
				{
					// mark first topping as selected
					$item->toppingGroupsRel[$group->id] = array($group->toppings[0]->id => 1);

					// increase default price by the first available topping
					$item->price += $group->toppings[0]->rate;
				}
			}
		}

		// render layout
		$html = JLayoutHelper::render('cart.itemform', array(
			'product' => $data,
			'item'    => $item,
		));

		echo json_encode($html);
		exit;
	}

	/**
	 * AJAX end-point used to add an item to the order.
	 *
	 * @return 	void
	 */
	public function additemajax()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		$args = array();
		$args['id_res']            = $input->get('id_reservation', 0, 'uint');
		$args['id_product']        = $input->get('id_product', 0, 'uint');
		$args['id_product_option'] = $input->get('id_option', 0, 'uint');
		$args['quantity']          = $input->get('quantity', 1, 'uint');
		$args['price']             = $input->get('price', 0, 'float');
		$args['notes']             = $input->get('notes', '', 'string');
		$args['id']                = $input->get('id', 0, 'uint');

		// get groups
		$groups = $input->get('groups', array(), 'array');

		// fetch group-topping relations
		$group_topping_lookup = array();
		$units_lookup = array();

		foreach ($groups as $group)
		{
			foreach ($group['toppings'] as $topping)
			{
				// register relation between the group and the topping
				$group_topping_lookup[] = array(
					'id_group'   => (int) $group['id'],
					'id_topping' => (int) $topping,
				);

				/**
				 * Check whether the topping should use
				 * the specified number of units.
				 *
				 * @since 1.8.2
				 */
				if (isset($group['units'][$topping]))
				{
					if (!isset($units_lookup[$group['id']]))
					{
						$units_lookup[$group['id']] = array();
					}

					// register number of units
					$units_lookup[$group['id']][$topping] = $group['units'][$topping];
				}
			}
		}
		
		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// raise AJAX error, not authorised to create/edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get order product table
		$resprod = JTableVRE::getInstance('tkresprod', 'VRETable');

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		// cache order so that it is possible to detect any added products (1: take-away)
		VREOperatorLogger::getInstance()->cache($args['id_res'], 1);

		// get current price stored in database
		$old = $resprod->getPrice($args['id']);

		// try to save arguments
		if (!$resprod->save($args))
		{
			// get string error
			$error = $resprod->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		// attach/detach the selected toppings to the item
		$resprod->setAttachedToppings($group_topping_lookup, $units_lookup);

		// Update bill by the price of the added item (subtract the previous price from total).
		// Update the taxes amount too.
		$order->updateBill($resprod->id_res, $resprod->price - $old->price, $resprod->taxes - $old->taxes);
		// now get bill prices
		$bill = $order->getBill($resprod->id_res);
		
		// get saved item
		$item = $resprod->getProperties();

		// get default product to inject name too
		$def = $resprod->getDefaultItem();

		$item['name'] = $def->name . ($item['id_product_option'] ? ' - ' . $def->option_name : '');
		
		echo json_encode(array(
			'item'  => $item,
			'total' => $bill->total,
			'taxes' => $bill->taxes,
			'net'   => $bill->net,
			'bill'  => $bill,
		));
		exit;
	}

	/**
	 * AJAX end-point used to remove an item from the order.
	 *
	 * @return 	void
	 */
	public function removeitemajax()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.delete', 'com_vikrestaurants'))
		{
			// raise AJAX error, not authorised to delete records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$id_assoc = $input->get('id_assoc', 0, 'uint');
		$id_res   = $input->get('id_res', 0, 'uint');

		// get order product table
		$resprod = JTableVRE::getInstance('tkresprod', 'VRETable');

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		// cache order so that it is possible to detect any deleted products (1: take-away)
		VREOperatorLogger::getInstance()->cache($id_res, 1);

		// get product price
		$prod = $resprod->getPrice($id_assoc);

		// delete record
		if (!$resprod->delete($id_assoc))
		{
			// an error occurred while trying to delete the record
			UIErrorFactory::raiseError(403, JText::_('VRE_AJAX_GENERIC_ERROR'));
		}

		// update bill by subtracting the price of the removed product
		$order->updateBill($id_res, $prod->price * -1, $prod->taxes * -1);
		// now get bill prices
		$bill = $order->getBill($id_res);
		
		echo json_encode(array(
			'total' => $bill->total,
			'taxes' => $bill->taxes,
			'net'   => $bill->net,
		));
		exit;
	}

	/**
	 * AJAX end-point used to confirm an order.
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
			|| !$user->authorise('core.access.tkorders', 'com_vikrestaurants')
			|| count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		foreach ($ids as $id)
		{
			$args = array(
				'id'         => $id,
				'status'     => 'CONFIRMED',
				'need_notif' => 1,
			);

			// update order
			if (!$order->save($args))
			{
				// get string error
				$error = $order->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to decline an order.
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
			|| !$user->authorise('core.access.tkorders', 'com_vikrestaurants')
			|| count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		foreach ($ids as $id)
		{
			$args = array(
				'id'     => $id,
				'status' => 'REMOVED',
			);

			// update order
			if (!$order->save($args))
			{
				// get string error
				$error = $order->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to notify an order.
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
			|| !$user->authorise('core.access.tkorders', 'com_vikrestaurants')
			|| count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		VRELoader::import('library.mail.factory');

		foreach ($ids as $id)
		{
			$args = array(
				'id'         => $id,
				'need_notif' => 0,
			);

			// update order
			if (!$order->save($args))
			{
				// get string error
				$error = $order->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}

			// instantiate mail provider
			$mail = VREMailFactory::getInstance('takeaway', 'customer', $id);

			// send e-mail notification to customer
			$mail->send();
		}

		echo 1;
		exit;
	}

	/**
	 * Sends a notification SMS to the customer of the specified order.
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
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$user->authorise('core.access.tkorders', 'com_vikrestaurants'))
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
			// get order details
			$order = VREOrderFactory::getOrder($id);

			// make sure the order exists and the purchaser phone is not empty
			if ($order && $order->purchaser_phone)
			{
				// get SMS notification message (1: take-away order)
				$message = VikRestaurants::getSmsCustomerTextMessage($order, 1);

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
	 * Starts the incoming orders.
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

		$canDo = $user->authorise('core.access.tkorders', 'com_vikrestaurants')
			&& $user->authorise('core.edit', 'com_vikrestaurants');

		if (!$canDo)
		{
			// back to main list, not authorised to send SMS notifications
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}

		// restart incoming reservation
		VREFactory::getConfig()->set('tkstopuntil', -1);
		return true;
	}

	/**
	 * Stops the incoming orders.
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

		$canDo = $user->authorise('core.access.tkorders', 'com_vikrestaurants')
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

		// stop incoming reservation
		VREFactory::getConfig()->set('tkstopuntil', $until);
		return true;
	}

	/**
	 * AJAX end-point used to assign an operator to the order.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.2
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

		// get order table
		$order = JTableVRE::getInstance('tkreservation', 'VRETable');

		// update order
		if (!$order->save($args))
		{
			// get string error
			$error = $order->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to increase/decrease an availability override
	 * for the specified time slot.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.3
	 */
	public function increasetimeslotajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$args = array();
		$args['date']    = $input->get('date', '', 'string');
		$args['hourmin'] = $input->get('hourmin', '', 'string');
		$args['units']   = $input->get('units', 0, 'int');

		// check user permissions
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			// raise AJAX error, not authorised to edit records state
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get availability table
		$override = JTableVRE::getInstance('tkavail', 'VRETable');

		// update override
		if (!$override->save($args))
		{
			// get string error
			$error = $override->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		echo 1;
		exit;
	}
}
