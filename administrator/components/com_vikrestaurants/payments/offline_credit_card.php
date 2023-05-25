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
 * The Offline Credit Card payment gateway (seamless) is not a real method of payment. 
 * This gateway collects the credit card details of your customers and then send them via e-mail to the administrator, 
 * so that it is able to make the transaction with a virtual pos.
 *
 * After the form submission the status of the order will be changed to CONFIRMED.
 * If you want to leave the status to PENDING (to change it manually) it is needed to change the default status 
 * from the parameters of your gateway.
 *
 * For PCI compliance, the system encrypts the details of the credit card and store them partially in the database.
 * The remaining details are sent to the e-mail of the administrator.
 *
 * @since 1.0
 */
class VREPaymentMethodOfflineCreditCard
{
	/**
	 * The esit of the transaction.
	 *
	 * @var boolean
	 */
	private $validation = false;
	
	/**
	 * The order information needed to complete the payment process.
	 *
	 * @var array
	 */
	private $order;

	/**
	 * The payment configuration.
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Contains logs of errors fetched in private methods.
	 *
	 * @var   string
	 * @since 1.8
	 */
	private $errorLog = '';
	
	/**
	 * Return the fields that should be filled in from the details of the payment.
	 *
	 * @return 	array 	The fields array.
	 */
	public static function getAdminParameters()
	{
		return array(
			/**
			 * The status that will be used after submitting the
			 * credit card details (auto-confirm or leave pending).
			 *
			 * @var select
			 *
			 * @since 1.7
			 */
			'newstatus' => array(
				'type'    => 'select', 
				'label'   => JText::_('VRE_PAYMENT_OFFCC_NEWSTATUS'),
				'help'    => JText::_('VRE_PAYMENT_OFFCC_NEWSTATUS_HELP'),
				'options' => array(
					'CONFIRMED' => JText::_('VRRESERVATIONSTATUSCONFIRMED'),
					'PENDING'   => JText::_('VRRESERVATIONSTATUSPENDING'),
				),
			),

			/**
			 * Choose whether the page should always be forced to use
			 * a secure connection through HTTPS.
			 *
			 * @var select
			 *
			 * @since 1.7
			 */
			'usessl' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_PAYMENT_OFFCC_USESSL'),
				'options' => array(
					1 => JText::_('JYES'),
					0 => JText::_('JNO'),
				),
			),

			/**
			 * A list of accepted credit card brands.
			 *
			 * @var select
			 *
			 * @since 1.7
			 */
			'brands' => array(
				'type'     => 'select',
				'label'    => JText::_('VRE_PAYMENT_OFFCC_BRANDS'),
				'help'     => JText::_('VRE_PAYMENT_OFFCC_BRANDS_HELP'),
				'multiple' => 1,
				'options'  => array(
					'visa'       => 'Visa',
					'mastercard' => 'Master Card',
					'amex'       => 'American Express',
					'diners'     => 'Diners Club',
					'discover'   => 'Discover',
					'jcb'        => 'JCB',
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

		VikRestaurants::loadBankingLibrary(array('creditcard'));

		if ($this->params['brands'])
		{
			$this->params['brands'] = (array) $this->params['brands'];	
		}
		else
		{
			// get all credit card brands
			$this->params['brands'] = CreditCard::getAllBrands();
		}	
	}
	
	/**
	 * This method is invoked every time a user visits the page of a reservation with PENDING Status.
	 * Display the form to collect the details of a given credit card.
	 *
	 * @return 	void
	 *
	 * @uses 	hasCreditCard() 	Make sure the reservation has no CC details.
	 */
	public function showPayment()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		if ($this->params['usessl'])
		{
			// change scheme from URLs
			$this->order['notify_url'] = preg_replace("/^http:/", 'https:', $this->order['notify_url']);
			$this->order['return_url'] = preg_replace("/^http:/", 'https:', $this->order['return_url']);
			$this->order['error_url']  = preg_replace("/^http:/", 'https:', $this->order['error_url']);

			$uri = JUri::getInstance();

			if (strtolower($uri->getScheme()) != 'https')
			{
				// Forward to HTTPS
				$uri->setScheme('https');
				$app->redirect((string) $uri, 301);
			}
		}

		if ($this->hasCreditCard())
		{
			// The customer already submitted its credit card details.
			// If we are here, the administrator needs to manually 
			// validate the credit card before confirming the order.
			return false;
		}

		// load resources
		JHtml::_('vrehtml.assets.fontawesome');

		$vik = VREApplication::getInstance();

		$vik->addStyleSheet(VREADMIN_URI . 'payments/off-cc/resources/off-cc.css');
		$vik->addScript(VREADMIN_URI . 'payments/off-cc/resources/off-cc.js');


		// instantiate layout file to allow the customization
		// of the form generated by this payment
		$layout = new JLayoutFile('payments.offcc');

		// build display data
		$data = array(
			'data'   => $this->order,
			'params' => $this->params,
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
	 *
	 * @uses 	registerCreditCard() 	Register the CC details (partially) in the database.
	 * @uses 	notifyAdmin()	 		Send the remaining CC details via e-mail to the admin.
	 */
	public function validatePayment()
	{
		$result = array();
		$result['verified'] = 0;
		$result['tot_paid'] = 0.0;
		$result['log'] = '';

		$app   = JFactory::getApplication();
		$input = $app->input;
		
		// post data (only data in POST method)
		$request = array();
		$request['cardholder'] = $input->post->getString('cardholder');
		$request['cardnumber'] = $input->post->get('cardnumber');
		$request['expdate']    = $input->post->get('expdate');
		$request['cvc']        = $input->post->get('cvc');
		// end post data

		foreach ($request as $k => $v)
		{
			if (empty($v))
			{
				// exit and no log for invalid data
				return $result;
			}
		}
		
		/**
		 * Added support for mmYYYY format.
		 *
		 * @since 1.8
		 */
		if (!preg_match("/^(?:(?:\d{4,4})|(?:\d{6,6}))$/", $request['expdate']))
		{
			// Expiry date must have at least 4 characters to represent mmYY format
			// or up to 6 characters for the full mmYYYY format.
			// Do not register logs for invalid data.
			return $result;
		}

		$now = getdate();

		// get month number
		$month = intval(substr($request['expdate'], 0, 2));

		// get year number
		if (strlen($request['expdate']) == 4)
		{
			// mm/YY format, prepend the first 2 digits of the current year
			$year = intval(substr($now['year'], 0, 2) . substr($request['expdate'], 2, 2));
		}
		else
		{
			// mm/YYYY format, just use the given year
			$year = intval(substr($request['expdate'], 2, 4));
		}

		// create credit card instance
		$card = CreditCard::getBrand($request['cardnumber'], $request['cvc'], $month, $year, $request['cardholder']);

		if ( 
			// impossible to identify credit card brand
			!($card instanceof CreditCard)
			// impossible to charge the credit card
			|| !$card->isChargeable()
			// the brand of the credit card is not accepted
			|| !in_array($card->getBrandAlias(), $this->params['brands'])
		) {
			// exit and no log for invalid data
			return $result;
		}

		// register credit card in order information
		if ($this->registerCreditCard($card))
		{
			// notify administrator via e-mail
			$this->notifyAdmin($card);
		}
		else
		{
			if (!empty($this->errorLog))
			{
				// use fetched error log
				$result['log'] = $this->errorLog;
			}
			else
			{
				// use generic message
				$result['log'] = 'An error occurred while saving the credit card details';
			}

			return $result;
		}

		// credit card information received
		
		$this->validation = true;

		if ($this->params['newstatus'] == 'CONFIRMED')
		{
			// auto-confirm reservation
			$result['verified'] = 1;	
		}
		
		return $result;
	}
	
	/**
	 * This function is called after the payment has been validated for redirect actions.
	 * When this method is called, the class is invoked after the validatePayment() function.
	 *
	 * @param 	boolean  $result  The result of the transaction.
	 *
	 * @return 	void
	 */
	public function afterValidation($result = false)
	{
		$app = JFactory::getApplication();
		
		/**
		 * Override result argument with the validation
		 * value calculated previously.
		 *
		 * @see validatePayment()
		 */
		if ($this->validation)
		{
			// order automatically confirmed
			$app->enqueueMessage(JText::_('VROFFCCPAYMENTRECEIVED'));

			if (!$result)
			{
				// waiting for manual approval
				$app->enqueueMessage(JText::_('VROFFCCWAITAPPROVE'));
			}
		}
		else
		{
			$app->enqueueMessage(JText::_('VRPAYNOTVERIFIED'), 'error');
		}
		
		$app->redirect($this->order['return_url']);
	}

	///////////
	// UTILS //
	///////////

	/**
	 * Checks if the reservation already owns some credit card details.
	 *
	 * @return 	boolean  True if already specified, false otherwise.
	 */
	private function hasCreditCard()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('cc_details'));

		if ($this->order['tid'] == 0)
		{
			$q->from($dbo->qn('#__vikrestaurants_reservation'));
		}
		else
		{
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation'));
		}
			
		$q->where($dbo->qn('id') . ' = ' . (int) $this->order['oid']);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		return $dbo->getNumRows() && strlen($dbo->loadResult());
	}

	/**
	 * Encrypts the partial details of the credit card and registers them
	 * within the database record.
	 *
	 * @param 	CreditCard 	$card 	The credit card details.
	 *
	 * @return 	boolean 	True on success, false otherwise.
	 */
	private function registerCreditCard(CreditCard $card)
	{
		if ($card === null)
		{
			return false;
		}

		VikRestaurants::loadCryptLibrary();

		$dbo = JFactory::getDbo();

		// build object
		$obj = new stdClass;

		$obj->brand = new stdClass;
		$obj->brand->label = JText::_('VRCCBRAND');
		$obj->brand->value = $card->getBrandName();
		$obj->brand->alias = $card->getBrandAlias();

		$obj->cardHolder = new stdClass;
		$obj->cardHolder->label = JText::_('VRCCNAME');
		$obj->cardHolder->value = $card->getCardholderName();

		$obj->cardNumber = new stdClass;
		$obj->cardNumber->label = JText::_('VRCCNUMBER');
		$obj->cardNumber->value = $card->getMaskedCardNumber();
		// get only short masked card number
		$obj->cardNumber->value = $obj->cardNumber->value[0];

		$obj->expiryDate = new stdClass;
		$obj->expiryDate->label = JText::_('VREXPIRINGDATE');
		$obj->expiryDate->value = $card->getExpiryDate();

		$obj->cvc = new stdClass;
		$obj->cvc->label = JText::_('VRCVV');
		$obj->cvc->value = $card->getCvc();

		// JSON encode
		$json = json_encode($obj);

		/**
		 * Since the encryption is made using mcrypt package, an exception
		 * could be thrown as the server might not have it installed.
		 * 			
		 * We need to wrap the code below within a try/catch and take
		 * the plain string without encrypting it. This was just an 
		 * additional security layer that doesn't alter the compliance
		 * with PCI/DSS rules.
		 *
		 * @since 1.8
		 */
		try
		{
			// mask secure key
			$cipher = SecureCipher::getInstance();

			$data = $cipher->safeEncodingEncryption($json);
		}
		catch (Exception $e)
		{
			// This server doesn't support current encryption algorithm.
			// Use plain TEXT, encoded in Base64.
			$data = base64_encode($json);
		}

		// fetch table name
		$tableName = $this->order['tid'] == 0 ? 'reservation' : 'tkreservation';

		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		// get table instance
		$table = JTableVRE::getInstance($tableName, 'VRETable');

		// prepare data to save
		$args = array(
			'id'         => $this->order['oid'],
			'cc_details' => $data,

			/**
			 * Force PENDING status in order to automatically
			 * extend the time for which the order will be locked.
			 *
			 * @since 1.8
			 */
			'status'     => 'PENDING',
		);

		// register credit card details in database
		$saved = $table->save($args);

		if (!$saved)
		{
			// register table error
			$this->errorLog = $table->getError(null, true);
		}
		
		return $saved;
	}

	/**
	 * Notifies the administratot via e-mail with the remaining details
	 * of the credit card.
	 *
	 * @param 	CreditCard 	The credit card details.
	 *
	 * @return 	void
	 */
	private function notifyAdmin(CreditCard $card)
	{
		$tag     = JFactory::getLanguage()->getTag();
		$def_tag = VikRestaurants::getDefaultLanguage('admin');

		// load default language
		if ($def_tag != $tag)
		{
			VikRestaurants::loadLanguage($def_tag);
		}
	
		$config = VREFactory::getConfig();

		// get administrators e-mail
		$adminmails = VikRestaurants::getAdminMailList();
		// get sender e-mail address
		$sendermail = VikRestaurants::getSenderMail();
		// get restaurant name
		$fromname = $config->getString('restname');

		$vik = VREApplication::getInstance();


		// get information to send
		$masked_card_number = $card->getMaskedCardNumber();

		// fetch admin link
		$admin_link = 'index.php?option=com_vikrestaurants&view=' . ($this->order['tid'] == 0 ? '' : 'tk') . 'reservations&ids[]=' . $this->order['oid'];
		$admin_link = $vik->adminUrl($admin_link);
		$admin_link = '<a href="' . $admin_link . '">' . $admin_link . '</a>';

		// build subject
		$subject = JText::_($this->order['tid'] == 0 ? 'VROFFCCMAILSUBJECTRS' : 'VROFFCCMAILSUBJECTTK');
	
		// build message
		$html = JText::sprintf('VROFFCCMAILCONTENT', $this->order['oid'], $masked_card_number[1], $admin_link);
		
		foreach ($adminmails as $recipient)
		{
			// send the e-mail notification
			$vik->sendMail($sendermail, $fromname, $recipient, $recipient, $subject, $html);
		}

		// reload customer language
		if ($def_tag != $tag)
		{
			VikRestaurants::loadLanguage($tag);
		}
	}
}
