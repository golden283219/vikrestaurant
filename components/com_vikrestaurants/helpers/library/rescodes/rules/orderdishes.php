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
 * Class used to send a notification to start the dishes ordering when this
 * reservation code rule is invoked.
 *
 * This rule should be invoked when the customer arrives at the restaurant
 * or when they sit at the table.
 *
 * @since 1.8.1
 */
class ResCodesRuleOrderdishes extends ResCodesRule
{
	/**
	 * The first name of the customer.
	 *
	 * @var string
	 */
	protected $customerName;

	/**
	 * The URL to reach to start ordering the dishes.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * @override
	 * Checks whether the specified group is supported
	 * by the rule. Available only for restaurant.
	 *
	 * @param 	string 	 $group  The group to check.
	 *
	 * @return 	boolean  True if supported, false otherwise.
	 */
	public function isSupported($group)
	{
		return !strcasecmp($group, 'restaurant');
	}

	/**
	 * Executes the rule.
	 *
	 * @param 	mixed  $order  The order details object.
	 *
	 * @return 	void
	 */
	public function execute($order)
	{
		// get current language tag
		$langtag = JFactory::getLanguage()->getTag();

		// load front-end language according to the tag assigned to the reservation
		VikRestaurants::loadLanguage($order->langtag ? $order->langtag : $langtag);

		// extract first name from nominative
		$this->customerName = $order->purchaser_nominative;

		if ($this->customerName)
		{
			$chunks = preg_split("/\s+/", $this->customerName);
			// assume the customer specified its name first
			$this->customerName = array_shift($chunks);
		}
		else
		{
			// use default "Customer"
			$this->customerName = JText::_('VRORDERCUSTOMER');
		}

		// fetch URL
		$this->url = 'index.php?option=com_vikrestaurants&view=orderdishes&ordnum=' . $order->id . '&ordkey=' . $order->sid;
		$this->url = VREApplication::getInstance()->routeForExternalUse($this->url, false);

		// send e-mail notification
		$this->sendMail($order);
		// send SMS notification
		$this->sendSMS($order);

		// fetch current language client
		$client = JFactory::getApplication()->isClient('administrator') ? JPATH_ADMINISTRATOR : JPATH_SITE;

		// reload previous language
		VikRestaurants::loadLanguage($langtag, $client);
	}

	/**
	 * Sends a SMS notification to the phone number
	 * specified by the customer during the purchase.
	 *
	 * @param 	mixed    $order  The order details object.
	 *
	 * @param 	boolean  True on success, false otherwise.
	 */
	protected function sendSMS($order)
	{
		if (!$order->purchaser_phone)
		{
			// missing phone number
			return false;
		}

		try
		{
			// get current SMS instance
			$smsapi = VREApplication::getInstance()->getSmsInstance();

			// prepare message
			$text = JText::sprintf('VRE_ORDERDISHES_SMS_NOTIFICATION', $this->customerName, $this->url);

			// try to send a notification to the specified phone number
			$smsapi->sendMessage($order->purchaser_phone, $text);
		}
		catch (Exception $e)
		{
			// SMS framework not supported
			return false;
		}

		return true;
	}

	/**
	 * Sends an e-mail notification to the address
	 * specified by the customer during the purchase.
	 *
	 * @param 	mixed    $order  The order details object.
	 *
	 * @param 	boolean  True on success, false otherwise.
	 */
	protected function sendMail($order)
	{
		if (!$order->purchaser_mail)
		{
			// missing phone number
			return false;
		}

		// get sender e-mail address
		$sendermail = VikRestaurants::getSenderMail();
		// get restaurant name
		$fromname = VREFactory::getConfig()->getString('restname');

		// prepare subject
		$subject = JText::sprintf('VRE_ORDERDISHES_EMAIL_NOTIFICATION_SUBJECT');
		// prepare message
		$text = JText::sprintf('VRE_ORDERDISHES_EMAIL_NOTIFICATION', $this->customerName, $fromname, $this->url);

		// try to send the e-mail to the specified address
		VREApplication::getInstance()->sendMail($sendermail, $fromname, $order->purchaser_mail, $sendermail, $subject, $text, $attachments = null, $is_html = false);

		return true;
	}
}
