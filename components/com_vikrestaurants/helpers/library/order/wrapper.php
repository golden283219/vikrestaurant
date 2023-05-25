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
 * Abstract order class wrapper.
 *
 * @since 1.8
 */
abstract class VREOrderWrapper extends JObject implements JsonSerializable
{
	/**
	 * Class constructor.
	 *
	 * @param 	integer  $id       The reservation ID.
	 * @param 	mixed    $langtag  The language tag. If null, the default one will be used.
	 * @param 	array 	 $options  An array of options to be passed to the order instance.
	 */
	public function __construct($id, $langtag = null, array $options = array())
	{
		if (!isset($options['translate']))
		{
			// in case of no translation rule, translate only if multi-lingual is enabled
			$options['translate'] = VikRestaurants::isMultilanguage();
		}

		// load order
		$order = $this->load($id, $langtag, $options);

		// construct object
		parent::__construct($order);

		/**
		 * When "preload" option is passed, we need to prevent the
		 * lazy loading of all the secondary information.
		 *
		 * @since 1.8.4
		 */
		if (!empty($options['preload']))
		{
			// retrieve all the information with lazy load
			$this->billing;
			$this->author;
			$this->invoice;
			$this->history;
		}

		// check if we should translate the order
		if ($options['translate'])
		{
			$this->translate($langtag);
		}
	}

	/**
	 * Magic method used to access the internal properties.
	 *
	 * @param 	string  $name  The property name.
	 *
	 * @return 	mixed   The property value if exists, null otherwise.
	 */
	public function __get($name)
	{
		// build method name to look for
		$method = 'get' . ucfirst($name);

		// check if we should use an apposite method
		if (method_exists($this, $method) && is_callable(array($this, $method)))
		{
			// get stored data, if any
			$data = $this->get($name);

			if (is_null($data))
			{
				// no stored details, load them
				$data = $this->{$method}();

				// cache details
				$this->set($name, $data);
			}

			// immediately return the data
			return $data;
		}

		return $this->get($name);
	}

	/**
	 * Magic method used to check whether an internal property exists.
	 *
	 * @param 	string   $name  The property name.
	 *
	 * @return 	boolean  True if the property is set, false otherwise.
	 */
	public function __isset($name)
	{
		return $this->get($name, null) !== null;
	}

	/**
	 * Creates a standard object, containing all the supported properties,
	 * to be used when this class is passed to "json_encode()".
	 *
	 * @return  object
	 *
	 * @see     JsonSerializable
	 */
	public function jsonSerialize()
	{
		$lookup = array(
			'billing',
			'author',
			'invoice',
			'history',
		);

		// lazy load additional details before encoding them
		foreach ($lookup as $name)
		{
			$this->__get($name);
		}

		return $this;
	}

	/**
	 * Returns the order/reservation object.
	 *
	 * @param 	integer  $id       The reservation ID.
	 * @param 	mixed    $langtag  The language tag. If null, the default one will be used.
	 * @param 	array 	 $options  An array of options to be passed to the order instance.
	 *
	 * @return 	mixed    The array/object to load.
	 *
	 * @throws 	Exception
	 */
	abstract protected function load($id, $langtag = null, array $options = array());

	/**
	 * Translates the internal properties.
	 *
	 * @param 	mixed    $langtag  The language tag. If null, the default one will be used.
	 *
	 * @return 	void
	 */
	abstract protected function translate($langtag = null);

	/**
	 * Returns the billing details of the user that made the order.
	 *
	 * @return 	object
	 */
	abstract protected function getBilling();

	/**
	 * Returns the billing details of the user that made the order.
	 *
	 * @return 	object
	 */
	abstract protected function getAuthor();

	/**
	 * Returns the invoice details of the order.
	 *
	 * @return 	mixed   The invoice object is exists, false otherwise.
	 */
	abstract protected function getInvoice();

	/**
	 * Returns the history of the status codes set for the order.
	 *
	 * @return 	array
	 */
	abstract protected function getHistory();
}
