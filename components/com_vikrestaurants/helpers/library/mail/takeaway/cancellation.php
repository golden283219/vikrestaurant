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
 * Wrapper used to handle mail notifications to be sent
 * to the administrators every time a customer makes
 * an order cancellation.
 *
 * @since 1.8
 */
class VREMailTemplateTakeawayCancellation implements VREMailTemplate
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
	 * @param 	array 	$options  A configuration array.
	 */
	public function __construct($order, $langtag = null, array $options = array())
	{
		if (!$langtag)
		{
			// always use default language in case it is not specified
			$langtag = VikRestaurants::getDefaultLanguage();
		}

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

		// register language tag
		$this->langtag = $langtag;

		// inject cancellation reason within order object if provided
		if (isset($options['cancellation_reason']))
		{
			$this->order->cancellation_reason = $options['cancellation_reason'];
		}

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
			$file = VREFactory::getConfig()->get('tkcancmailtmpl');

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
		$subject = JText::sprintf('VRORDERCANCELLEDSUBJECT', $fromname);

		// let plugins manipulate the subject for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'cancellation', 'subject', $subject, $this->order);

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

		// fetch order link HREF
		$order_link_href = 'index.php?option=com_vikrestaurants&view=tkreservations&ids[]=' . $this->order->id;
		$order_link_href = VREApplication::getInstance()->adminUrl($order_link_href);

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

		// build placeholders lookup
		$placeholders = array(
			'logo'                 => $logo_str,
			'company_name'         => $config->get('restname'),
			'order_number'         => $this->order->id,
			'order_key'            => $this->order->sid,
			'order_date_time'      => $formatted_checkin,
			'order_total_cost'     => $currency->format($this->order->total_to_pay),
			'order_link'           => $order_link_href,
			'cancellation_content' => JText::_('VRORDERCANCELLEDCONTENT'),
			'cancellation_reason'  => $this->order->cancellation_reason,
		);

		// parse e-mail template placeholders
		foreach ($placeholders as $tag => $value)
		{
			$tmpl = str_replace("{{$tag}}", $value, $tmpl);
		}

		// let plugins manipulate the content for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'cancellation', 'content', $tmpl, $this->order);

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

		// get administrators e-mail
		$adminmails = VikRestaurants::getAdminMailList();
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

		// init application
		$vik = VREApplication::getInstance();

		$sent = false;

		// iterate all administrators that should be notified
		foreach ($adminmails as $recipient)
		{
			// send the e-mail notification
			$sent = $vik->sendMail($sendermail, $fromname, $recipient, $recipient, $subject, $html) || $sent;
		}

		// iterate all the operators that should be notified (2 = take-away)
		foreach (VikRestaurants::getNotificationOperatorsMails(2) as $recipient)
		{
			// send the e-mail notification
			$sent = $vik->sendMail($sendermail, $fromname, $recipient, $recipient, $subject, $html) || $sent;
		}
		
		return $sent;
	}

	/**
	 * Checks whether the notification should be sent.
	 *
	 * @return 	boolean
	 */
	public function shouldSend()
	{
		// always send cancellation e-mails
		return true;
	}
}
