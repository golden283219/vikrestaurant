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
 * The APIs error representation.
 *
 * @since  1.7
 */
class ErrorAPIs implements JsonSerializable
{
	/**
	 * The identifier code of the error.
	 *
	 * @var integer
	 */
	public $errcode;

	/**
	 * The text description of the error.
	 *
	 * @var string
	 */
	public $error;

	/**
	 * Class constructor.
	 * 
	 * @param 	integer 	$errcode 	The code identifier.
	 * @param 	string 		$error 		The text description.
	 */
	public function __construct($errcode, $error)
	{
		$this->errcode = $errcode;
		$this->error   = $error;
	}

	/**
	 * Return this object encoded in JSON.
	 *
	 * @return 	string 	This object in JSON.
	 */
	public function toJSON()
	{
		return json_encode($this);
	}

	/**
	 * Creates a standard object, containing all the supported properties,
	 * to be used when this class is passed to "json_encode()".
	 *
	 * @return  object
	 *
	 * @since 	1.8.4
	 *
	 * @see     JsonSerializable
	 */
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}

	/**
	 * Raise the specified error and stop the flow if needed.
	 *
	 * @param 	integer 	$errcode 	The code identifier.
	 * @param 	string 		$error 		The text description.
	 * @param 	boolean 	$exit 		True to stop the execution, otherwise false.
	 *
	 * @return 	mixed 		The error raised when exit is not needed, otherwise the error will be echoed in JSON.
	 */
	public static function raise($errcode, $error, $exit = true)
	{
		$err = new ErrorAPIs($errcode, $error);

		if ($exit)
		{
			echo $err->toJSON();
			exit;
		}

		return $err;
	}
}
