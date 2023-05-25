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
 * Integration between VikRestaurants and SmsHosting provider.
 *
 * @since 1.3
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
	 * SmsHosting end-point for API calls.
	 *
	 * @var string
	 */
	const BASE_URI = 'https://api.smshosting.it/rest/api';
	
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
			'apikey' => array(
				'label'    => 'API Key',
				'type'     => 'text',
				'required' => 1,
			),
			
			'apisecret' => array(
				'label'    => 'API Secret',
				'type'     => 'password',
				'required' => 1,
			),
			
			'sender' => array(
				'label'    => 'Sender Name//Max 11 characters',
				'type'     => 'text',
				'required' => 0,
			),
			
			'prefix' => array(
				'label'    => 'Default Prefix//Enter the + symbol before the digits.',
				'type'     => 'text',
				'required' => 0,
			),
			
			'sandbox' => array(
				'label'   => 'Sandbox',
				'type'    => 'select',
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
	 * @param 	mixed 	$order 	 The order info array.
	 * @param 	array 	$params  The settings for the integration. 
	 */
	public function __construct($order, $params = array())
	{
		$this->order_info = $order;
		
		$this->params = !empty($params) ? $params : $this->params;
	}
	
	/**
	 * Sends the provided message to a specific phone number
	 * using the SmsHosting specifics.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	object  The response caught.
	 *
	 * @uses 	_send() Provides a cURL connection with smshosting.it.
	 */
	public function sendMessage($phone_number, $msg_text, $when = NULL)
	{
		if (empty($phone_number) || empty($msg_text))
		{
			return null;
		}
		
		return $this->_send('/sms/send', $phone_number, $msg_text, $when);
	}
	
	/**
	 * Tries to estimate the remaining balance of an SmsHosting account.
	 *
	 * @param 	string 	$phone_number 	The destination phone number.
	 * @param 	string 	$message 		The plain message to send.
	 *
	 * @return 	object  The response caught.
	 *
	 * @uses 	_send() Provides a cURL connection with smshosting.it.
	 */
	public function estimate($phone_number, $msg_text)
	{
		return $this->_send('/sms/estimate', $phone_number, $msg_text);
	}

	private function _send($dir_uri, $phone_number, $msg_text, $when = NULL)
	{
		$this->log = '';
		
		$unicode = $this->containsUnicode($msg_text);
		
		if (strlen($this->params['sender']) > 11) {
			$start = 0;
			if (substr($this->params['sender'], 0, strlen($this->params['prefix'])) == $this->params['prefix']) {
				$start = strlen($this->params['prefix']);
			}
			$this->params['sender'] = trim(substr($this->params['sender'], $start, 11));
		}
		
		$phone_number = $this->sanitize($phone_number);
		
		$post = array(
			'to' => urlencode($phone_number),
			'from' => urlencode($this->params['sender']),
			'group' => urlencode(NULL),
			'text' => urlencode($msg_text),
			'date' => urlencode($when),
			'transactionId' => urlencode(NULL),
			'sandbox' => urlencode( $this->params['sandbox'] ),
			'statusCallback' => urlencode(NULL),
			'type' => $unicode ? 'unicode' : 'text'
		);
		
		if ($this->params['sandbox']) {
			$this->log .= '<pre>'.print_r($this->params, true)."</pre>\n\n";
			$this->log .= '<pre>'.print_r($post, true)."</pre>\n\n";
		}
		
		$complete_uri = self::BASE_URI.$dir_uri;
		
		$array_result = $this->sendPost( $complete_uri, $post );
		
		if ($array_result['from_smsh']) {
			return $this->parseResponse( $array_result );
		} else {
			return false;
		}
	} 
	
	private function sendPost($complete_uri, $data)
	{
		$post = '';
		foreach ($data as $k => $v) {
			$post .= "&$k=$v";
		}
		
		$array_result = array();
		
		// If available, use CURL
		if (function_exists('curl_version')) {
			
			$to_smsh = curl_init( $complete_uri );
			curl_setopt($to_smsh, CURLOPT_POST, true);
			curl_setopt($to_smsh, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($to_smsh, CURLOPT_USERPWD, $this->params['apikey'] . ":" . $this->params['apisecret']);
			curl_setopt($to_smsh, CURLOPT_POSTFIELDS, $post);
			
			$array_result['from_smsh'] = curl_exec($to_smsh);
			
			$array_result['smsh_response_status'] = curl_getinfo($to_smsh, CURLINFO_HTTP_CODE);
			
			curl_close($to_smsh);
			
		} else if (ini_get( 'allow_url_fopen')) {
			// No CURL available so try the awesome file_get_contents
			
			$opts = array(
				'http' => array(
					'method' => 'POST',
					'ignore_errors' => true,
					'header' => "Authorization: Basic ".base64_encode($this->params['apikey'] . ":" . $this->params['apisecret']) . "\r\nContent-type: application/x-www-form-urlencoded",
					'content' => $post 
				) 
			);
			$context = stream_context_create($opts);
			$array_result['from_smsh'] = file_get_contents($complete_uri, false, $context);
			
			list($version, $status_code, $msg) = explode(' ', $http_response_header[0], 3);
			
			$array_result['smsh_response_status'] = $status_code;
			
		} else {
			// No way of sending a HTTP post
			$array_result['from_smsh'] = false; 
		}

		return $array_result;
	}

	private function parseResponse($arr)
	{	
		$response = json_decode($arr['from_smsh']);
		
		$response_obj;
		
		if (is_array($response)) {
			$response_obj = new stdClass;
			$response_obj->response = $response; 
		} else {
			$response_obj = $response;	 
		}
		
		
		if ($arr['smsh_response_status'] == 200) {
			$response_obj->errorCode = 0;
		}
		
		$this->log .= '<pre>'.print_r($response_obj, true)."</pre>\n\n";
		
		if ($response_obj) {
			return $response_obj;
		} 
		
		return false;
	}
	
	public function validateResponse($response_obj)
	{
		return ($response_obj === NULL || $response_obj->errorCode == 0);
	}
	
	///// UTILS /////
	
	public function getLog()
	{
		return $this->log;
	}
	
	private function containsUnicode($msg_text)
	{
		return max(array_map('ord', str_split($msg_text))) > 127;
	}
	
	/**
	 * Helper method used to sanitize phone numbers.
	 *
	 * @param 	string 	$phone 	The phone number to sanitize.
	 *
	 * @return 	string 	The cleansed number.
	 *
	 * @since 	1.8
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
