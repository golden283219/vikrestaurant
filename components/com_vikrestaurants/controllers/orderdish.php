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
VRELoader::import('library.dishes.cart');

/**
 * VikRestaurants dishes ordering controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerOrderdish extends VREControllerAdmin
{
	/**
	 * AJAX end-point used to access the creation page of a new record.
	 *
	 * @return 	void
	 */
	public function add()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		$id_item = $input->get('id', 0, 'uint');
		$index   = $input->get('index', -1, 'int');

		try
		{
			// first of all, check reservation permissions
			$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));

			if ($index == -1)
			{
				// create new item instance
				$item = new VREDishesItem($id_item);
			}
			else
			{
				// get current cart instance
				$cart = VREDishesCart::getInstance($reservation->id);

				// get item from cart
				$item = $cart->getItemAt($index);

				if (!$item)
				{
					// item not found
					throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
				}
			}

			// make sure the item is still writable
			if (!$item->isWritable())
			{
				throw new Exception(JText::_('VRTKCARTDISHCANTEDIT'), 403);
			}
		}
		catch (Exception $e)
		{
			// catch exception and raise error safely
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// prepare display data
		$data = array(
			'index'       => $index,
			'item'        => $item,
			'reservation' => $reservation,
		);

		// render layout of the form used to insert/update a dish
		$html = JLayoutHelper::render('orderdish.popup', $data);

		// safely encode HTML
		echo json_encode(array($html));
		exit;
	}

	/**
	 * AJAX end-point used to insert/update a cart item.
	 *
	 * @return 	void
	 */
	public function addcart()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		$id_item = $input->get('id', 0, 'uint');
		$index   = $input->get('index', -1, 'int');

		try
		{
			// first of all, check reservation permissions
			$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));

			// make sure the user can actually order the food
			if (!VikRestaurants::canUserOrderFood($reservation, $errmsg))
			{
				// not allowed to order further dishes
				throw new Exception($errmsg ? $errmsg : 'Error', 403);
			}

			// get current cart instance
			$cart = VREDishesCart::getInstance($reservation->id);

			if ($index == -1)
			{
				// create new item instance
				$item = new VREDishesItem($id_item);
			}
			else
			{
				// get item from cart
				$item = $cart->getItemAt($index);

				if (!$item)
				{
					// item not found
					throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
				}
			}

			// make sure the item is still writable
			if (!$item->isWritable())
			{
				throw new Exception(JText::_('VRTKCARTDISHCANTEDIT'), 403);
			}
		}
		catch (Exception $e)
		{
			// catch exception and raise error safely
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// set item notes
		$item->setAdditionalNotes($input->getString('notes', ''));
		// set variation
		$item->setVariation($input->getUint('id_product_option', 0));

		// set item quantity
		$quantity = $input->getUint('quantity', 0);
		$item->setQuantity($quantity);

		if ($index == -1)
		{
			// try to look for an equal item already stored in the cart
			$index = $cart->indexOf($item);

			if ($index == -1)
			{
				// push item within the cart
				$cart->addItem($item);
			}
			else
			{
				// item found, get it from cart
				$item = $cart->getItemAt($index);

				// increase quantity
				$item->add($quantity);
			}
		}

		// save cart
		$cart->store();

		// build response data
		$response = new stdClass;
		$response->total    = $cart->getTotalCost();
		$response->cartHTML = JLayoutHelper::render('orderdish.cart', [
			'cart'        => $cart,
			'reservation' => $reservation,
		]);

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX end-point used to delete a cart item.
	 *
	 * @return 	void
	 */
	public function removecart()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		$index   = $input->get('index', 0, 'uint');

		try
		{
			// first of all, check reservation permissions
			$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));

			// get current cart instance
			$cart = VREDishesCart::getInstance($reservation->id);

			// get item from cart
			$item = $cart->getItemAt($index);

			if (!$item)
			{
				// item not found
				throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
			}

			// make sure the item is still writable
			if (!$item->isWritable() || $reservation->bill_closed)
			{
				throw new Exception(JText::_('VRTKCARTDISHCANTEDIT'), 403);
			}
		}
		catch (Exception $e)
		{
			// catch exception and raise error safely
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// permanently remove item from cart
		$cart->removeItemAt($index, $item->getQuantity());

		// save cart
		$cart->store();

		// build response data
		$response = new stdClass;
		$response->total    = $cart->getTotalCost();
		$response->cartHTML = JLayoutHelper::render('orderdish.cart', [
			'cart'        => $cart,
			'reservation' => $reservation,
		]);

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX end-point used to transmit to the kitchen
	 * all the pending dishes.
	 *
	 * @return 	void
	 */
	public function transmit()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		try
		{
			// first of all, check reservation permissions
			$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));
		}
		catch (Exception $e)
		{
			// catch exception and raise error safely
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// get current cart instance
		$cart = VREDishesCart::getInstance($reservation->id);

		// transmit dishes
		$cart->transmit();

		// save cart
		$cart->store();

		// build response data
		$response = new stdClass;
		$response->total    = $cart->getTotalCost();
		$response->cartHTML = JLayoutHelper::render('orderdish.cart', [
			'cart'        => $cart,
			'reservation' => $reservation,
		]);

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX end-point used to close the bill of a reservation.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.1
	 */
	public function closebill()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		try
		{
			// first of all, check reservation permissions
			$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));
		}
		catch (Exception $e)
		{
			// catch exception and raise error safely
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// get current cart instance
		$cart = VREDishesCart::getInstance($reservation->id);

		if (!$reservation->bill_closed)
		{
			// iterate items list
			foreach ($cart->getItemsList() as $item)
			{
				// check if we have a volatile dish
				if ($item->getRecordID() == 0)
				{
					// delete item before closing the bill
					$cart->removeItem($item, $item->getQuantity());
				}
			}

			// save cart
			$cart->store();

			// get reservation code able to close the bill
			$id_code = JHtml::_('vikrestaurants.rescoderule', 'closebill', 1);

			// make sure the code exists
			if ($id_code)
			{
				$args = array();
				$args['id']      = $reservation->id;
				$args['rescode'] = $id_code;

				// get reservation table
				JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
				$reservationTable = JTableVRE::getInstance('reservation', 'VRETable');

				// update reservation
				$reservationTable->save($args);

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

		// build response data
		$response = new stdClass;
		$response->total    = $cart->getTotalCost();
		$response->cartHTML = JLayoutHelper::render('orderdish.cart', [
			'cart'        => $cart,
			'reservation' => $reservation,
		]);

		echo json_encode($response);
		exit;
	}

	/**
	 * Task used to select a payment method in order to 
	 * complete the payment after closing the bill.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.1
	 */
	public function paynow()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		$itemid = $input->get('Itemid', null, 'uint');
		$itemid = $itemid ? '&Itemid=' . $itemid : '';

		// first of all, check reservation permissions
		$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));

		// in case the reservation doesn't exist, an exception will be thrown

		if (!$reservation->bill_closed)
		{
			// bill not yet closed
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=orderdishes&ordnum=' . $oid . '&ordkey=' . $sid . $itemid, false));
			exit;
		}

		// make sure the selected payment exists
		$payment = VikRestaurants::hasPayment($group = 1, $input->getUint('id_payment'));

		if (!$payment)
		{
			// the selected payment does not exist
			$app->enqueueMessage(JText::_('VRERRINVPAYMENT'), 'error');
			// back to order dishes view
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=orderdishes&ordnum=' . $oid . '&ordkey=' . $sid . $itemid, false));
			exit;
		}

		// decrease bill by any existing tip amount
		$reservation->bill_value -= $reservation->tip_amount;

		// update gratuity with the specified amount
		$reservation->tip_amount = abs($input->getFloat('gratuity', 0));
		// increase bill total
		$reservation->bill_value += $reservation->tip_amount;

		if ($input->getBool('ceiltip'))
		{
			// round up bill total
			$ceil = ceil($reservation->bill_value);

			// add difference to tip amount
			$reservation->tip_amount += $ceil - $reservation->bill_value;

			// update total
			$reservation->bill_value = $ceil;
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$table = JTableVRE::getInstance('reservation', 'VRETable');

		$data = array(
			'id'         => $reservation->id,
			'id_payment' => $payment->id,
			'tip_amount' => $reservation->tip_amount,
			'bill_value' => $reservation->bill_value,
		);

		if (!$table->save($data))
		{
			// get string error
			$error = $table->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');
			// back to order dishes view
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=orderdishes&ordnum=' . $oid . '&ordkey=' . $sid . $itemid, false));
			exit;
		}

		// go to reservation summary page
		$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=reservation&ordnum=' . $oid . '&ordkey=' . $sid . $itemid, false));
	}
}
