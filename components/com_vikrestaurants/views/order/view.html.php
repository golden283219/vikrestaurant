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
 * VikRestaurants take-away order summary view.
 * In case the request doesn't provide the ORDER NUMBER
 * and the ORDER KEY, a form to search an order
 * will be displayed.
 *
 * @since 1.8
 */
class VikRestaurantsVieworder extends JViewVRE
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
		
		$order       = null;
		$paymentData = null;

		// make sure the ORDER NUMBER and ORDER KEY have been submitted
		if (!empty($oid) && !empty($sid))
		{
			// check if the order has expired
			VikRestaurants::removeTakeAwayOrdersOutOfTime($oid);

			try
			{
				// get order details (filter by ID and SID)
				$order = VREOrderFactory::getOrder($oid, null, array('sid' => $sid));
			}
			catch (Exception $e)
			{
				// order not found
			}

			if ($order)
			{
				// check if a payment is required
				if ($order->id_payment > 0)
				{
					// get payment details (2: take-away, $strict: obtain unpublished too)
					$payment = VikRestaurants::hasPayment($group = 2, $order->id_payment, $strict = false);

					if (!$payment)
					{
						// payment not found, raise error
						throw new Exception(sprintf('Payment [%d] not found', $order->id_payment), 404);
					}

					$paymentData = array();

					$vik = VREApplication::getInstance();

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
				
					$paymentData['type']                 = 'takeaway.create';
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
					VREFactory::getEventDispatcher()->trigger('onInitPaymentTransaction', array(&$paymentData, &$paymentData['payment_info']->params));
				}
			}
			else
			{
				// raise error, order not found
				$app->enqueueMessage(JText::_('VRORDERRESERVATIONERROR'), 'error');
			}		
		}
		
		if (!$order)
		{
			// use "track" layout in case the order
			// was not found or in case the order number
			// and the order key was not submitted
			$this->setLayout('track');
		}

		/**
		 * An object containing the details of the specified
		 * take-away order.
		 * 
		 * @var VREOrderTakeaway|null
		 */
		$this->order = &$order;

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

		// build display data
		$data = array(
			'data'  => $this->payment,
			'order' => $this->order,
		);

		// return payment layout
		return JLayoutHelper::render('blocks.payment', $data);
	}
}
