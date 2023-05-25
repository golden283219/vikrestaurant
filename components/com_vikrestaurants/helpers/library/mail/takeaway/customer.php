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

VRELoader::import('library.mail.template');

/**
 * Wrapper used to handle mail notifications for the
 * customers that purchase a take-away order.
 *
 * @since 1.8
 */
class VREMailTemplateTakeawayCustomer implements VREMailTemplate
{
	/**
	 * The order object.
	 *
	 * @var VREOrderTakeaway
	 */
	protected $order;

	/**
	 * The language tag to use.
	 *
	 * @var string
	 */
	protected $langtag;

	/**
	 * An optional template file to use.
	 *
	 * @var string
	 */
	protected $templateFile;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed   $order    Either the order ID or the order object.
	 * @param 	string 	$langtag  An optional language tag.
	 */
	public function __construct($order, $langtag = null)
	{
		if ($order instanceof VREOrderTakeaway)
		{
			/**
			 * Directly use the specified order.
			 *
			 * @since 1.8.2
			 */
			$this->order = $order;
		}
		else
		{
			// recover order details for the given language
			$this->order = VREOrderFactory::getOrder($order, $langtag);
		}

		if (!$langtag)
		{
			// use order lang tag in case it was not specified
			$langtag = $this->order->get('langtag', null);

			if (!$langtag)
			{
				// the order is not assigned to any lang tag, use the current one
				$langtag = JFactory::getLanguage()->getTag();
			}
		}

		// format order items prices
		$currency = VREFactory::getCurrency();

		foreach ($this->order->items as $k => $v)
		{
			// format price
			$v->formattedPrice = $currency->format($v->price);

			$this->order->items[$k] = $v;
		}

		// register language tag
		$this->langtag = $langtag;

		// load given language to translate template contents
		VikRestaurants::loadLanguage($this->langtag);
	}

	/**
	 * Returns the code of the template before 
	 * being parsed.
	 *
	 * @param 	string  An optional template file to use.
	 * 					If not specified, the one set in
	 * 					configuration will be used.
	 *
	 * @return 	void
	 */
	public function setFile($file)
	{
		// use specified template file
		$this->templateFile = $file;

		// check if a filename or a path was passed
		if ($file && !is_file($file))
		{
			// make sure we have a valid file path
			$this->templateFile = VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . $file;
		}
	}

	/**
	 * Returns the code of the template before 
	 * being parsed.
	 *
	 * @return 	string
	 */
	public function getTemplate()
	{
		// copy order details in a local
		// variable for being used directly
		// within the template file
		$order = $this->order;

		if ($this->templateFile)
		{
			// use specified template file
			$file = $this->templateFile;
		}
		else
		{
			// get template file from configuration
			$file = VREFactory::getConfig()->get('tkmailtmpl');

			// build template path
			$file = VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . $file;
		}

		// make sure the file exists
		if (!is_file($file))
		{
			// missing file, return empty string
			return '';
		}

		// start output buffering 
		ob_start();
		// include file to catch its contents
		include $file;
		// write template contents within a variable
		$content = ob_get_contents();
		// clear output buffer
		ob_end_clean();

		// free space
		unset($order);

		return $content;
	}

	/**
	 * Fetches the subject to be used in the e-mail.
	 *
	 * @return 	string
	 */
	public function getSubject()
	{
		// get restaurant name
		$fromname = VREFactory::getConfig()->getString('restname');

		// fetch subject
		$subject = JText::sprintf('VRCUSTOMEREMAILTKSUBJECT', $fromname);

		// let plugins manipulate the subject for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'customer', 'subject', $subject, $this->order);

		if ($res === false)
		{
			// a plugin prevented the e-mail sending
			return '';
		}

		return $subject;
	}

	/**
	 * Parses the HTML of the template and returns it.
	 *
	 * @return 	string
	 */
	public function getHtml()
	{
		$config   = VREFactory::getConfig();
		$currency = VREFactory::getCurrency();

		// load template HTML
		$tmpl = $this->getTemplate();

		// order status color
		switch ($this->order->status)
		{
			case 'CONFIRMED':
				$order_status_color = '#006600';
				break;

			case 'PENDING':
				$order_status_color = '#D9A300';
				break;

			case 'REMOVED':
				$order_status_color = '#B20000';
				break;

			case 'CANCELLED':
				$order_status_color = '#F01B17';
				break;

			default:
				$order_status_color = 'inherit';
		}

		// fetch payment name
		if ($this->order->payment)
		{
			// use payment name
			$payment_name = $this->order->payment->name;
		}
		else
		{
			// use "total to pay" label
			$payment_name = JText::_('VRTKORDERTOTALTOPAY');
		}

		// fetch payment notes
		$payment_notes = '';

		if ($this->order->payment)
		{
			if ($this->order->status == 'PENDING')
			{
				// show notes before purchase when waiting for the payment
				$payment_notes = $this->order->payment->notes->beforePurchase;
			}
			else if ($this->order->status == 'CONFIRMED')
			{
				// show notes after purchase when the order has been confirmed
				$payment_notes = $this->order->payment->notes->afterPurchase;	
			}
		}

		// fetch coupon string
		if ($this->order->coupon)
		{
			$coupon_str = $this->order->coupon->code;

			if ($this->order->coupon->amount > 0)
			{
				$coupon_str .= ' : ';

				if ($this->order->coupon->type == 1)
				{
					$coupon_str .= $this->order->coupon->amount . '%';
				}
				else
				{
					$coupon_str .= $currency->format($this->order->coupon->amount);
				}
			}
		}
		else
		{
			$coupon_str = '';
		}

		$vik = VREApplication::getInstance();

		// fetch order link HREF
		$order_link_href = "index.php?option=com_vikrestaurants&view=order&ordnum={$this->order->id}&ordkey={$this->order->sid}";
		$order_link_href = $vik->routeForExternalUse($order_link_href);

		// fetch cancellation link HREF
		$cancellation_link_href = $order_link_href . '#cancel';

		// fetch confirmation link HREF
		$confirmation_link_href = "index.php?option=com_vikrestaurants&task=confirmord&oid={$this->order->id}&conf_key={$this->order->conf_key}&tid=1";
		$confirmation_link_href = $vik->routeForExternalUse($confirmation_link_href);

		// fetch tracking link
		$track_order_link_href = "index.php?option=com_vikrestaurants&view=trackorder&oid={$this->order->id}&sid={$this->order->sid}";
		$track_order_link_href = $vik->routeForExternalUse($track_order_link_href);

		// fetch company logo image
		$logo_str = $config->get('companylogo');

		if ($logo_str && is_file(VREMEDIA . DIRECTORY_SEPARATOR . $logo_str))
		{
			$logo_str = '<img src="' . VREMEDIA_URI . $logo_str . '" alt="' . htmlspecialchars($config->get('restname')) . '" />';
		}
		else
		{
			$logo_str = '';
		}

		// format checkin
		$formatted_checkin = $this->order->checkin_lc3;

		// fetch service label
		$delivery_service = JText::_($this->order->delivery_service ? 'VRTKORDERDELIVERYOPTION' : 'VRTKORDERPICKUPOPTION');

		// get user ID
		$billing = $this->order->billing;
		$user    = null;

		if ($billing && $billing->jid > 0)
		{
			// get user details
			$user = JFactory::getUser($billing->jid);
		}

		// build placeholders lookup
		$placeholders = array(
			'logo'                   => $logo_str,
			'company_name'           => $config->get('restname'),
			'order_number'           => $this->order->id,
			'order_key'              => $this->order->sid,
			'order_date_time'        => $formatted_checkin,
			'order_delivery_service' => $delivery_service,
			'order_status_color'     => $order_status_color,
			'order_status'           => JText::_('VRRESERVATIONSTATUS' . $this->order->status),
			'order_payment'          => $payment_name,
			'order_payment_notes'    => $payment_notes,
			'order_total_cost'       => $currency->format($this->order->total_to_pay),
			'order_total_net'        => $currency->format($this->order->total_net),
			'order_delivery_charge'  => $currency->format($this->order->delivery_charge),
			'order_total_tip'        => $currency->format($this->order->tip_amount),
			'order_total_tax'        => $currency->format($this->order->taxes),
			'order_coupon_code'      => $coupon_str,
			'order_link'             => $order_link_href,
			'cancellation_link'      => $cancellation_link_href,
			'confirmation_link'      => $confirmation_link_href,
			'track_order_link'       => JText::sprintf('VRTRACKORDERCHECKLINK', $track_order_link_href),
			'user_name'              => $user ? $user->name : '',
			'user_username'          => $user ? $user->username : '',
			'user_email'             => $user ? $user->email : '',
		);

		// parse e-mail template placeholders
		foreach ($placeholders as $tag => $value)
		{
			$tmpl = str_replace("{{$tag}}", $value, $tmpl);
		}

		// let plugins manipulate the content for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'customer', 'content', $tmpl, $this->order);

		if ($res === false)
		{
			// a plugin prevented the e-mail sending
			return '';
		}

		return $tmpl;
	}

	/**
	 * Sends the HTML contents via e-mail.
	 *
	 * @return 	boolean
	 */
	public function send()
	{
		$config = VREFactory::getConfig();

		// get recipient from order detail
		$recipient = $this->order->get('purchaser_mail');

		if (!$recipient)
		{
			// missing recipient
			return false;
		}

		// get administrator e-mail
		$adminmail = VikRestaurants::getAdminMail();
		// get sender e-mail address
		$sendermail = VikRestaurants::getSenderMail();
		// get restaurant name
		$fromname = $config->getString('restname');
		
		// fetch subject
		$subject = $this->getSubject();
			
		// parse e-mail template
		$html = $this->getHtml();

		if (empty($subject) || empty($html))
		{
			// do not send e-mail in case the subject or
			// the content are empty
			return false;
		}
		
		// send the e-mail notification
		return VREApplication::getInstance()->sendMail(
			$sendermail,
			$fromname,
			$recipient,
			$adminmail,
			$subject,
			$html
		);
	}

	/**
	 * Checks whether the notification should be sent.
	 *
	 * @return 	boolean
	 */
	public function shouldSend()
	{
		// check if the customer is allowed to self-confirm its order
		if (VikRestaurants::canUserApproveOrder($this->order))
		{
			// self-confirmation is allowed, force the e-mail sending
			// because the confirmation link can be found only within
			// the notification e-mail sent
			return true;
		}

		// retrieve from configuration when the notification
		// should be sent to customers
		$when = VREFactory::getConfig()->getUint('tkmailcustwhen');

		if ($when == 0)
		{
			// never send to customers
			return false;
		}

		if ($when == 1 && $this->order->status != 'CONFIRMED')
		{
			// send only when CONFIRMED status
			return false;
		}

		// always send to customers
		return true;
	}
}
