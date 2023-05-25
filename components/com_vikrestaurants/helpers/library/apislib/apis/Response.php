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
 * The APIs response wrapper.
 *
 * @since  1.7
 */
class ResponseAPIs
{
	/**
	 * The status of the response. True for success, false on failure.
	 *
	 * @var boolean
	 */
	private $status = false;

	/**
	 * The text description of the response.
	 *
	 * @var string
	 */
	private $content = "";

	/**
	 * The initial timestamp in seconds of the creation of this object.
	 *
	 * @var integer
	 */
	private $startTime = 0;

	/**
	 * Class constructor.
	 * 
	 * @param 	boolean		$status 	True for success response, otherwise false.
	 * @param 	string 	 	$content 	The text description of the response.
	 *
	 * @uses 	setStatus() 	Set the status of the response.
	 * @uses 	setContent() 	Set the content of the response.
	 */
	public function __construct($status = false, $content = '')
	{
		$this->setStatus($status)->setContent($content);

		$this->startTime = microtime(true);
	}

	/**
	 * Set the status of the response.
	 * @usedby 	ResponseAPIs::__construct()
	 *
	 * @param 	boolean       $status 	True for success response, otherwise false.
	 *
	 * @return 	ResponseAPIs  This object to support chaining.
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Return true if the status of the response is success, otherwise false.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	public function isVerified()
	{
		return $this->status == true;
	}

	/**
	 * Return true if the status of the response is failure, otherwise false.
	 *
	 * @return 	boolean  True on failure, otherwise false.
	 */
	public function isError()
	{
		return $this->status == false;
	}

	/**
	 * Set the text description of the response.
	 * @usedby 	ResponseAPIs::__construct()
	 * @usedby 	ResponseAPIs::clearContent()
	 *
	 * @param 	mixed 	      $content 	The content of the response.
	 *
	 * @return 	ResponseAPIs  This object to support chaining.
	 */
	public function setContent($content)
	{
		/**
		 * Add support for non-string values.
		 *
		 * @since 1.8
		 */
		if (!is_scalar($content))
		{
			// stringify non-scalar value
			$content = print_r($content, true);
		}

		$this->content = (string) $content;

		return $this;
	}

	/**
	 * Append some text to the existing description of the response.
	 *
	 * @param 	string 	      $content  The content of the response.
	 *
	 * @return 	ResponseAPIs  This object to support chaining.
	 *
	 * @uses 	setContent()
	 */
	public function appendContent($content)
	{
		// keep current contents
		$tmp = $this->content;

		// set contents with default method
		$this->setContent($content);

		// prepend previous contents
		$this->content = $tmp . $this->content;

		return $this;
	}

	/**
	 * Prepend some text to the existing description of the response.
	 *
	 * @param 	string 	      $content 	The content of the response.
	 *
	 * @return 	ResponseAPIs  This object to support chaining.
	 *
	 * @uses 	setContent()
	 */
	public function prependContent($content)
	{
		// keep current contents
		$tmp = $this->content;

		// set contents with default method
		$this->setContent($content);

		// append previous contents
		$this->content .= $tmp;

		return $this;
	}

	/**
	 * Clear the text description of the response.
	 *
	 * @return 	ResponseAPIs  This object to support chaining.
	 *
	 * @uses 	setContent()
	 */
	public function clearContent()
	{
		return $this->setContent('');
	}

	/**
	 * Get the text description of the response.
	 *
	 * @return 	string 	The text description of the response.
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Get the initial timestamp of the response.
	 * The initial time is recorded during the creation of the response.
	 *
	 * @return 	integer  The initial timestamp in seconds.
	 */
	public function createdOn()
	{
		return $this->startTime;
	}

	/**
	 * Get the elapsed time between the current time and the initial time.
	 *
	 * @return 	integer  The elapsed time in seconds.
	 */
	public function getElapsedTime()
	{
		return microtime() - $this->startTime;
	}
}
