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
 * The PayPal payment gateway (hosted) prints the standard orange PayPal button to start the transaction.
 * The payment will come on PayPal website and, only after the transaction, the customers will be 
 * redirected to the order page on your website.
 *
 * @since 1.0
 */
class VREPaymentMethodPaypal
{	
	/**
	 * The order information needed to complete the payment process.
	 *
	 * @var array
	 */
	private $order;

	/**
	 * The payment configuration.
	 *
	 * @var   array
	 * @since 1.8.1
	 */
	private $params;

	/**
	 * The URL that will be used for the payment.
	 *
	 * @var   string
	 * @since 1.8.1
	 */
	private $payURL;
	
	/**
	 * Returns the fields that should be filled in from the details of the payment.
	 *
	 * @return 	array 	The fields array.
	 */
	public static function getAdminParameters()
	{
		return array(
			/**
			 * The PayPal logo image.
			 *
			 * @var custom
			 */
			'logo' => array(
				'type'  => 'custom', 
				'label' => '', 
				'html'  => '<img src="https://www.paypalobjects.com/webstatic/i/ex_ce2/logo/logo_paypal_106x29.png"/>',
			),

			/**
			 * The PayPal e-mail account.
			 *
			 * @var text
			 */
			'account' => array(
				'type'     => 'text', 
				'label'    => JText::_('VRE_PAYMENT_PAYPAL_ACCOUNT'),
				'help'     => JText::_('VRE_PAYMENT_PAYPAL_ACCOUNT_HELP'),
				'required' => 1,
			),

			/**
			 * The PayPal environment that will be used (sandbox or production).
			 *
			 * @var select
			 */
			'sandbox' => array(
				'type'    => 'select', 
				'label'   => JText::_('VRE_PAYMENT_PAYPAL_SANDBOX'),
				'help'    => JText::_('VRE_PAYMENT_PAYPAL_SANDBOX_HELP'), 
				'options' => array(
					1 => JText::_('JYES'),
					0 => JText::_('JNO'),
				),
			),

			/**
			 * Flag used to check whether TLS 1.2 protocol should be always
			 * used, in order to estabilish only safe connections.
			 *
			 * @var select
			 *
			 * @since 1.7.5
			 */
			'safemode' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_PAYMENT_PAYPAL_SSL'),
				'help'    => JText::_('VRE_PAYMENT_PAYPAL_SSL_HELP'),
				'options' => array(
					1 => JText::_('JYES'),
					0 => JText::_('JNO'),
				),
			),

			/**
			 * The image URL that will be used to display the standard
			 * "Pay Now" button provided by PayPal.
			 *
			 * @var text
			 *
			 * @since 1.7.5
			 */
			'image' => array(
				'type'    => 'text',
				'label'   => JText::_('VRE_PAYMENT_PAYPAL_IMAGE'),
				'help'    => JText::_('VRE_PAYMENT_PAYPAL_IMAGE_HELP'),
				'default' => 'https://www.paypal.com/en_GB/i/btn/btn_paynow_SM.gif',
			),

			/**
			 * Flag used to check whether the form should be automatically
			 * submitted after the page loads.
			 *
			 * @var select
			 *
			 * @since 1.8.1
			 */
			'autosubmit' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_PAYMENT_PAYPAL_AUTO_SUBMIT'),
				'help'    => JText::_('VRE_PAYMENT_PAYPAL_AUTO_SUBMIT_HELP'),
				'default' => 0,
				'options' => array(
					1 => JText::_('JYES'),
					0 => JText::_('JNO'),
				),
			),
		);
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
		$this->order  = $order;
		$this->params = $params;
		
		if (empty($this->params['image']))
		{
			// always use default image in case the URL is missing
			$adminParams = static::getAdminParameters();
			$this->params['image'] = $adminParams['image']['default'];
		}

		if ($this->params['sandbox'] == 1)
		{
			// test environment
			$this->payURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
		else
		{
			// live environment
			$this->payURL = 'https://www.paypal.com/cgi-bin/webscr';
		}
	}
	
	/**
	 * This method is invoked every time a user visits the page of a reservation with PENDING Status.
	 * Display the PayPal paynow button to begin a transaction.
	 *
	 * @return 	void
	 */
	public function showPayment()
	{
		/**
		 * Safely append the status within the query string.
		 *
		 * @since 1.8.1
		 */
		$uri = JUri::getInstance($this->order['return_url']);
		$uri->setVar('status', 1);

		// prepare form data
		$formData = array(
			'business'      => $this->params['account'],
			'cmd'           => '_xclick',
			'amount'        => number_format($this->order['total_net_price'], 2, '.', ''),
			'item_name'     => $this->order['transaction_name'],
			'quantity'      => 1,
			'tax'           => number_format($this->order['total_tax'], 2, '.', ''),
			'shipping'      => 0.00,
			'currency_code' => $this->order['transaction_currency'],
			'no_shipping'   => 1,
			'rm'            => 2,
			'notify_url'    => $this->order['notify_url_plain'],
			'return'        => (string) $uri,
		);

		/**
		 * Try to auto-populate the billing details of the
		 * registered customer.
		 *
		 * @since 1.8.1
		 *
		 * @link  https://developer.paypal.com/docs/paypal-payments-standard/integration-guide/Appx-websitestandard-htmlvariables/#auto-fill-paypal-checkout-page-variables
		 */
		$customer = VikRestaurants::getCustomer();

		// make sure the customer exists
		if ($customer)
		{
			// extract first name and last name
			$parts = preg_split("/\s+/", $customer->billing_name);

			$formData['last_name']  = array_pop($parts);
			$formData['first_name'] = implode(' ', $parts);

			// set up billing address
			$formData['country']  = $customer->country_code;
			$formData['city']     = $customer->billing_city;
			$formData['zip']      = $customer->billing_zip;
			$formData['address1'] = $customer->billing_address;
			$formData['address2'] = $customer->billing_address_2;

			// fill phone
			$formData['night_phone_b'] = $customer->billing_phone;
			// fill e-mail
			$formData['email'] = $customer->billing_mail;
		}

		/**
		 * Instantiate layout file to allow the customization
		 * of the form generated by this payment.
		 *
		 * @since 1.8.1
		 */
		$layout = new JLayoutFile('payments.paypal');

		// build display data
		$data = array(
			'data'   => $this->order,
			'params' => $this->params,
			'form'   => $formData,
			'payurl' => $this->payURL,
			'paid'   => JFactory::getApplication()->input->getBool('status'),
		);
		
		// display layout
		echo $layout->render($data);
		
		return true;
	}
	
	/**
	 * Validate the transaction details sent from the bank. 
	 * This method is invoked by the system every time the Notify URL 
	 * is visited (the one used in the showPayment() method). 
	 *
	 * @return 	array 	The array result, which MUST contain the "verified" key (1 or 0).
	 */
	public function validatePayment()
	{
		$array_result = array();
		$array_result['verified'] = 0;
		$array_result['tot_paid'] = 0.0;
		$array_result['log'] = '';
		
		//cURL Method HTTP1.1 October 2013
		$raw_post_data 	= file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		
		$myPost = array();
		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode('=', $keyval);
			if (count($keyval) == 2)
			{
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}

		// check if the form has been spoofed
		$against = array(
			'business' 	  => $this->params['account'],
			'mc_gross' 	  => number_format($this->order['total_net_price'], 2, '.', ''),
			'mc_currency' => $this->order['transaction_currency'],
			'tax'		  => number_format($this->order['total_tax'], 2, '.', ''),
		);

		/**
		 * If the account name contains the merchant code instead
		 * of the e-mail related to the account, the spoofing check will fail
		 * as the merchant code is always converted into the account e-mail.
		 *
		 * For example, if we specify 835383648, PayPal will return the related
		 * account: dev@e4j.com
		 * Then, 2 different values will be compared:
		 * "835383648" ($this->params['account']) against "dev@e4j.com" ($myPost['business'])
		 */

		// inject the original values within the payment data
		foreach ($against as $k => $v)
		{
			if (isset($myPost[$k]))
			{
				$myPost[$k] = $v;
			}
		}
		//

		$req = 'cmd=_notify-validate';
		if (function_exists('get_magic_quotes_gpc'))
		{
			$get_magic_quotes_exists = true;
		}

		foreach ($myPost as $key => $value)
		{
			if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1)
			{
				$value = urlencode(stripslashes($value));
			}
			else
			{
				$value = urlencode($value);
			}

			$req .= "&$key=$value";
			$array_result['log'] .= "&$key=$value\n";
		}
		
		if (!function_exists('curl_init'))
		{
			$array_result['log'] = "FATAL ERROR: cURL is not installed on the server\n\n" . $array_result['log'];

			return $array_result;
		}
		
		$ch = curl_init($this->payURL);

		if ($ch == false)
		{
			$array_result['log'] = "Curl error: " . curl_error($ch) . "\n\n" . $array_result['log'];

			return $array_result;
		}
		
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		
		/**
		 * Turn on TLS 1.2 protocol in case of safe mode or sandbox enabled.
		 *
		 * @since 1.7.5
		 */
		if (defined('CURLOPT_SSLVERSION') && ($this->params['sandbox'] || $this->params['safemode']))
		{
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		}

		/**
		 * Specify a user-agent to prevent a forbidden error that could
		 * occur with certain configurations.
		 * 
		 * @since 1.8.5
		 */
		curl_setopt($ch, CURLOPT_USERAGENT, sprintf('VikRestaurants/%s e4j/PayPal', VIKRESTAURANTS_SOFTWARE_VERSION));

		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		
		// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and copy it in the same folder as this php file
		// This is mandatory for some environments.
		// $cert = dirname(__FILE__) . "/cacert.pem";
		// curl_setopt($ch, CURLOPT_CAINFO, $cert);
		
		$res = curl_exec($ch);

		if (curl_errno($ch) != 0)
		{
			$array_result['log'] .= date('[Y-m-d H:i e] ') . " Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL;

			curl_close($ch);

			return $array_result;
		}
		else
		{
			$array_result['log'] .= date('[Y-m-d H:i e]') . " HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL;
			$array_result['log'] .= date('[Y-m-d H:i e]') . " HTTP response of validation request: $res" . PHP_EOL;
			
			curl_close($ch);
		}
		
		if (strcmp(trim($res), 'VERIFIED') == 0)
		{
			$array_result['tot_paid'] = $_POST['mc_gross'];
			$array_result['verified'] = 1;
			$array_result['log'] = '';
		}
		else if (strcmp(trim($res), 'INVALID') == 0)
		{
			$array_result['log'] .= date('[Y-m-d H:i e]'). " Invalid IPN: $req\n\nResponse: [$res]" . PHP_EOL;
		}
		else
		{
			$array_result['log'] .= date('[Y-m-d H:i e]'). " Unknown Error: $req\n\nResponse: [$res]" . PHP_EOL;
		}
		
		//END cURL Method HTTP1.1 October 2013
		
		return $array_result;
	}
}
