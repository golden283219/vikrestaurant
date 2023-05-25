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
 * Integration between VikRestaurants and Clickatell.com provider.
 *
 * @since 1.5
 */
class VikSmsApi
{
	/**
	 * Order info array (currently never used).
	 *
	 * @var array
	 */
	private $order_info;

	/**
	 * The settings of the integration.
	 *
	 * @var array
	 */
	private $params;

	/**
	 * The registered logs for debug purposes.
	 *
	 * @var string
	 */
	private $log = '';

	/**
	 * Clickatell end-point for API calls.
	 *
	 * @var 	string
	 * @since 	1.1
	 */
	const BASE_URL = 'https://platform.clickatell.com';

	/**
	 * List of accepted HTTP codes.
	 * Each code must be separated by a comma.
	 *
	 * @var 	string
	 * @since 	1.1
	 */
	const ACCEPTED_CODES = '200, 201, 202';

	/**
	 * The CURL agent identifier.
	 *
	 * @var 	string
	 * @since 	1.1
	 */
	const AGENT = 'ClickatellV2/1.0';
	
	/**
	 * Defines the settings required for the integration.
	 *
	 * The supported settings are:
	 *
	 * @property 	string 	apis 	API Token.
	 * @property 	string  from    The source phone number.
	 * @property 	string  prefix 	Defualt Prefix (+ included).
	 *
	 * @return 	array 	The settings to fill.
	 */
	public static function getAdminParameters()
	{
		return array(
			/**
			 * API secret token.
			 * It replaces the old username and password.
			 *
			 * @var 	string
			 * @since 	1.7
			 */
			'apis' => array(
				'label' 	=> 'API Token',
				'type' 		=> 'password',
				'required' 	=> 1,
			),

			/**
			 * From number for 2-sync integrations.
			 *
			 * @var 	string
			 * @since 	1.7.3
			 */
			'from' => array(
				'label' 	=> 'From//Enter the FROM number only for 2-sync integrations.',
				'type' 		=> 'text',
				'required' 	=> 0,
			),

			/**
			 * The default phone prefix.
			 *
			 * @var string
			 */
			'prefix' => array(
				'label' 	=> 'Default Prefix//Enter the + symbol before the digits.',
				'type' 		=> 'text',
				'required' 	=> 0,
			),
		);
	}
	
	/**
	 * Class constructor.
	 *
	 * @param 	array 	$order 	 The order info array.
	 * @param 	array 	$params  The settings for the integration. 
	 */
	public function __construct($order, $params = array())
	{
		$this->order_info = $order;
		
		$this->params = !empty($params) ? $params : $this->params;
	}
	
	/**
	 * Send the provided message to a specific phone number
	 * using the Clickatell.com specifics.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	object  The response caught.
	 *
	 * @uses 	VikSmsApi::curl() 	Provides a cURL connection with clickatell.com.
	 */
	public function sendMessage($phone_number, $message)
	{
		if (empty($phone_number) || empty($message))
		{
			return null;
		}
		
		// sanitize phone number
		$phone_number = $this->sanitize($phone_number);
		
		$data = array();

		$data['content'] 			= $message;
		$data['to'] 				= array($phone_number);
		$data['binary']				= false;
		//$data['clientMessageId'] 	= 'uuid'; 			// maybe not required?
		//$data['userDataHeader']	= '0605040B8423F0';	// maybe not required?
		$data['validityPeriod']		= 60; // re-try to send failed messages for at most 60 minutes

		// add from option for 2-sync
		if (!empty($this->params['from']))
		{
			$data['from'] = $this->sanitize($this->params['from']);
		}

		if ($unicode_message = $this->isUnicodeContent($message))
		{
			$data['charset'] = 'UTF-8';
		}
		else
		{
			$data['charset'] = 'ASCII';
		}
		
		return $this->curl('messages', $data);
	}

	/**
	 * Evaluates the response retrieved after sending a message.
	 *
	 * @param 	object 	$response_obj 	The response to check.
	 *
	 * @return 	boolean 	True if the message has been sent, otherwise false.
	 */
	public function validateResponse($response_obj)
	{
		if (!$response_obj || !isset($response_obj->httpCode))
		{
			return false;
		}

		// parse HTTP codes list
		$codes = array_map(function($code){
			return trim($code);
		}, explode(",", static::ACCEPTED_CODES));

		// check for non-OK statuses
		if (!in_array($response_obj->httpCode, $codes)
			|| (isset($response_obj->result->error) && !empty($response_obj->result->error)))
		{
			$this->log = '<pre>' . print_r($response_obj, true) . '</pre>';

			return false;
		}

		return true;
	}
	
	/**
	 * Returns the logs registered by this class.
	 *
	 * @return 	string 	The registered logs.
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Check if the specified message contains UTF-8 characters.
	 *
	 * @param 	string 	$message 	The string to check.
	 *
	 * @return 	boolean 	True if unicode, otherwise false.
	 *
	 * @since 	1.7
	 */
	protected function isUnicodeContent($message)
	{
		if (function_exists('iconv'))
		{
			$latin = @iconv('UTF-8', 'ISO-8859-1', $message);

			if (strcmp($latin, $message))
			{
				$arr = unpack('H*hex', @iconv('UTF-8', 'UCS-2BE', $message));
				//return strtoupper($arr['hex']);
				return true;
			}
		}

		return false;
	}

	/**
	 * Abstract CURL usage.
	 *
	 * @param 	string 	$uri 	The endpoint.
	 * @param 	array 	$data 	Array of parameters.
	 *
	 * @return object 	The result of the call.
	 *
	 * @since 1.7
	 */
	protected function curl($uri, $data = array())
	{
		$data = $data ? (array) $data : $data;

		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: ' . $this->params['apis'],
		);
		
		$endpoint = static::BASE_URL . "/" . $uri;

		$curlInfo = curl_version();

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, static::AGENT . ' curl/' . $curlInfo['version'] . ' PHP/' . phpversion());

		if ($data)
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		$result = curl_exec($ch);

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$obj = new stdClass;
		$obj->httpCode 	= $httpCode;
		$obj->result 	= json_decode($result);

		return $obj;
	}

	/**
	 * Helper method used to sanitize phone numbers.
	 *
	 * @param 	string 	$phone 	The phone number to sanitize.
	 *
	 * @return 	string 	The cleansed number.
	 *
	 * @since 	1.7.3
	 */
	protected function sanitize($phone)
	{
		$phone = preg_replace("/[^0-9+]/", '', $phone);
		
		if (substr($phone, 0, 1) != '+')
		{
			if (substr($phone, 0, 2) == '00')
			{
				$phone = '+' . substr($phone, 2);
			}
			else
			{
				$phone = $this->params['prefix'] . $phone;
			}
		}

		return $phone;
	}
}
