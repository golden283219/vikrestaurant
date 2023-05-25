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
 * Integration between VikRestaurants and CMTelecom provider.
 *
 * @since 1.7
 */
class VikSmsApi {
	
	private $order_info;
	private $params;
	private $log = '';
	private $BASE_URI = 'https://gw.cmtelecom.com/v1.0/message';
	private $devMachine = false;
	
	public static function getAdminParameters()
	{
		return array(
			'producttoken' => array(
				'label'    => 'Product Token',
				'type'     => 'password',
				'required' => 1,
			),
			'sender' => array(
				'label'    => 'Sender Name//Maximum 11 alpha or 16 numeric characters',
				'type'     => 'text',
				'required' => 0,
			),
			'prefix' => array(
				'label'    => 'Default Prefix//Enter the + symbol before the digits.',
				'type'     => 'text',
				'required' => 0,
			),
			'minimumNumberOfMessageParts' => array(
				'label'    => 'Minimum number of message parts//Used when sending multipart or concatenated SMS messages',
				'type'     => 'text',
				'default'  => 1,
				'required' => 0,
			),
			'maximumNumberOfMessageParts' => array(
				'label'    => 'Maximum number of message parts//Used when sending multipart or concatenated SMS messages',
				'type'     => 'text',
				'default'  => 4,
				'required' => 0,
			),
		);
	}
	
	public function __construct($order, $params = array())
	{
		$this->order_info = $order;
		$this->params = !empty($params) ? $params : $this->params;
	}

	public function sendMessage($phone_number, $msg_text, $when = NULL)
	{
		if (empty($phone_number) || empty($msg_text)) return;

		$phone_number = $this->sanitize($phone_number);

		return $this->_send($phone_number, $msg_text);
	}
	
	public function getLog()
	{
		return $this->log;
	}
	
	///// CMTELECOM /////
	
	private function _send($destination, $message)
	{
		$this->log = '';
		
		$jsonArray = array(
			'messages' => array(
				'authentication' => array(
					'producttoken' => $this->params['producttoken']
				),
				'msg' => array(
					array(
						'from' => $this->params['sender'],
						'to' => array(
							array(
								'number' => $destination
							)
						),
						'minimumNumberOfMessageParts' => (isset($this->params['minimumNumberOfMessageParts']) && !empty($this->params['minimumNumberOfMessageParts']) ? $this->params['minimumNumberOfMessageParts'] : 1),
						'maximumNumberOfMessageParts' => (isset($this->params['maximumNumberOfMessageParts']) && !empty($this->params['maximumNumberOfMessageParts']) ? $this->params['maximumNumberOfMessageParts'] : 1),
						'customGrouping3' => 'E4J',
						'body' => array(
							'type' => 'AUTO',
							'content' => $message
						)
					)
				)
			)
		);
				
		$jsonString = json_encode($jsonArray);
		$result = $this->_doPost($jsonString);
		
		return $result;
	}
	
	public function validateResponse($responsObj)
	{
		if (!$responsObj) {
			return false;
		}

		$responsObj->errorCode = $responsObj->code;
		if ($responsObj->code == 200) {
			$responsObj->errorCode = 0;
			// success
			return true;
		} else if (isset($responsObj->data->details)) {
			// failure
			$this->log = $responsObj->data->details;
		}

		return false;
	}
	
	private function _doPost($jsonString)
	{
		$ch = curl_init();
		
		if ($this->devMachine) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		curl_setopt($ch, CURLOPT_URL, $this->BASE_URI);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($jsonString))
		);
		$result = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($curl_errno = curl_errno($ch)) {
			$this->log = "Something went wrong with the request: (".$curl_errno.") ".curl_error($ch);
			return false;
		}

		$response_obj = new stdClass;
		$response_obj->data = json_decode($result); 
		$response_obj->code = $httpcode;

		return $response_obj;
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

		/**
		 * Trim leading zero on French local format.
		 *
		 * @since 1.8.2
		 */
		return preg_replace("/^+330/", '+33', $phone);
	}
}
