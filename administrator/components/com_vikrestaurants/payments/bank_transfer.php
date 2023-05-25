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
 * The Bank Transfer (or Pay upon Arrival) payment gateway is used to skip the payment method process 
 * when you have other gateways published. This gateway means you will receive the payment by cash 
 * when the customers will come to your company.
 *
 * @since 1.0
 */
class VREPaymentMethodBankTransfer
{
	/**
	 * The order information needed to complete the payment process.
	 *
	 * @var array
	 */
	private $order;
	
	/**
	 * Return the fields that should be filled in from the details of the payment.
	 * No fields to return for this payment gateway.
	 *
	 * @return 	array 	The fields array.
	 */
	public static function getAdminParameters()
	{
		return array();
	}
	
	/**
	 * Class constructor.
	 *
	 * @param 	array 	$order 	 The order info array.
	 * @param 	array 	$params  The payment configuration. These fields are the 
	 * 							 same of the getAdminParameters() function.
	 */
	public function __construct($order, $params = array())
	{
		$this->order = $order;
	}
	
	/**
	 * This method is invoked every time a user visits the page of a
	 * reservation with PENDING Status.
	 *
	 * This method should not do anything.
	 *
	 * @return 	void
	 */
	public function showPayment()
	{	
		/**
		 * Payment notes are shown with the PRE PURCHASE NOTES parameter.
		 *
		 * @since 1.7
		 */

		return true;
	}
	
	/**
	 * Validate the transaction details sent from the bank. 
	 * This method is invoked by the system every time the Notify URL 
	 * is visited (the one used in the showPayment() method).
	 *
	 * This method should be NEVER called.
	 *
	 * @return 	array 	The array result, which MUST contain the "verified" key (1 or 0).
	 */
	public function validatePayment()
	{
		$array_result = array();

		/**
		 * Prevent self-confirmation when manually reaching 
		 * the end-point used to validate a payment.
		 *
		 * @since 1.8.1
		 */
		$array_result['verified'] = 0;
		
		return $array_result;
	}
}
