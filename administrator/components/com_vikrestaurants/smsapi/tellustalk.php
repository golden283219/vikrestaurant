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
 * Integration between VikRestaurants and TellUsTalk provider.
 *
 * @since 1.7
 */
class VikSmsApi
{	
	private $order_info;
	private $params;
	private $log = '';

	public static function getAdminParameters()
	{
		return array(
			/**
			 * User authentication ID.
			 *
			 * @var string
			 */
			'userid' => array(
				'label'    => 'User ID',
				'type'     => 'text',
				'required' => 1,
			),
			
			/**
			 * User authentication password.
			 *
			 * @var string
			 */	
			'password' => array(
				'label'    => 'Password',
				'type'     => 'password',
				'required' => 1,
			),

			/**
			 * The default phone prefix.
			 *
			 * @var    string
			 * @since  1.8
			 */
			'prefix' => array(
				'label' 	=> 'Default Prefix//Enter the + symbol before the digits.',
				'type' 		=> 'text',
				'required' 	=> 0,
			),
		);
	}
	
	public function __construct($order, $params = array())
	{
		$this->order_info = $order;
		$this->params = !empty($params) ? $params : $this->params;
	}
	
	public function sendMessage($phone_number, $msg_text)
	{
		$phone_number = $this->sanitize($phone_number);
	
		$request = array(
			"to" => "sms:".$phone_number,
			"text" => $msg_text
		);
	
		$json = json_encode($request);

		$curl_opts = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Basic '.base64_encode($this->params['userid'].':'.$this->params['password'])
			)
        );
 
		$ch = curl_init('https://tellus-talk.appspot.com/send/v1');
		curl_setopt_array($ch, $curl_opts);
		$response = curl_exec($ch);
		
		$result = new stdClass;
		$result->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		return $result;
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
	
	public function validateResponse($response_obj)
	{
		switch ($response_obj->responseCode) {
			case 200:
				return true;
			case 400:
				$this->log.="Bad Request\n";
				break;
			case 401:
				$this->log.="Unauthorized\n";
				break;
			case 404:
				$this->log.="Not Found\n";
				break;
			case 405:
				$this->log.="Method not Allowed\n";
				break;
			case 500:
				$this->log.="Internal Server Error\n";
				break;
		}

		return false;
	}
	
	public function getLog()
	{
		return $this->log;
	}

}
