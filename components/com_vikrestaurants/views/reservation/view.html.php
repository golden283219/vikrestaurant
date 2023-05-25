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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants restaurant reservation summary view.
 * In case the request doesn't provide the ORDER NUMBER
 * and the ORDER KEY, a form to search a reservation
 * will be displayed.
 *
 * @since 1.8
 */
class VikRestaurantsViewreservation extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$dbo    = JFactory::getDbo();
		$config = VREFactory::getConfig();
		
		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');
		
		$reservation = null;
		$paymentData = null;
		
		// make sure the ORDER NUMBER and ORDER KEY have been submitted
		if (!empty($oid) && !empty($sid))
		{
			// check if the reservation has expired
			VikRestaurants::removeRestaurantReservationsOutOfTime($oid);

			try
			{
				// get reservation details (filter by ID and SID)
				$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));
			}
			catch (Exception $e)
			{
				// reservation not found
			}

			if ($reservation)
			{
				// check if a payment is required
				if ($reservation->id_payment > 0)
				{
					// get payment details (1: restaurant, $strict: obtain unpublished too)
					$payment = VikRestaurants::hasPayment($group = 1, $reservation->id_payment, $strict = false);

					if (!$payment)
					{
						// payment not found, raise error
						throw new Exception(sprintf('Payment [%d] not found', $reservation->id_payment), 404);
					}

					$paymentData = array();

					$vik = VREApplication::getInstance();

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
				
					$paymentData['type']                 = 'restaurant.create';
					$paymentData['oid']                  = $reservation->id;
					$paymentData['sid']                  = $reservation->sid;
					$paymentData['tid']                  = 0;
					$paymentData['transaction_name']     = JText::sprintf('VRTRANSACTIONNAME', $config->get('restname'));
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
					VREFactory::getEventDispatcher()->trigger('onInitPaymentTransaction', array(&$paymentData, &$paymentData['payment_info']->params));
				}
			}
			else
			{
				// raise error, reservation not found
				$app->enqueueMessage(JText::_('VRORDERRESERVATIONERROR'), 'error');
			}		
		}
		
		if (!$reservation)
		{
			// use "track" layout in case the reservation
			// was not found or in case the order number
			// and the order key was not submitted
			$this->setLayout('track');
		}

		/**
		 * An object containing the details of the specified
		 * restaurant reservation.
		 * 
		 * @var VREOrderRestaurant|null
		 */
		$this->reservation = &$reservation;

		/**
		 * An associative array containing the payment
		 * details, if any.
		 * 
		 * @var array|null
		 */
		$this->payment = &$paymentData;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}

	/**
	 * Checks whether the payment (if needed) matches
	 * the specified position. In that case, the payment
	 * form/notes will be echoed.
	 *
	 * @param 	string 	$position  The position in which to print the payment.
	 *
	 * @return 	string 	The HTML to display.
	 */
	protected function displayPayment($position)
	{
		if (!$this->payment)
		{
			// nothing to display
			return '';
		}

		$position = 'vr-payment-position-' . $position;

		// get payment position
		$tmp = $this->payment['payment_info']->position;

		if (!$tmp)
		{
			// use bottom by default
			$tmp = 'vr-payment-position-bottom';
		}

		// compare payment position
		if ($tmp != $position)
		{
			// position doesn't match
			return '';
		}

		$reservation = clone $this->reservation;

		/**
		 * Added support for online bill payment.
		 * Temporarily revert status to PENDING to allow
		 * payments in case the bill is closed and the
		 * remaining balance is higher than 0.
		 *
		 * @since 1.8.1
		 */
		if ($reservation->bill_closed && $this->payment['total_to_pay'])
		{
			$reservation->status = 'PENDING';
		}

		// build display data
		$data = array(
			'data'  => $this->payment,
			'order' => $reservation,
		);

		// return payment layout
		return JLayoutHelper::render('blocks.payment', $data);
	}
}
