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

VRELoader::import('library.invoice.invoice');

/**
 * Invoices factory class.
 *
 * @since 	1.8
 */
class VREInvoiceFactory
{
	/**
	 * A list of instances.
	 *
	 * @var array
	 */
	protected static $classes = array();

	/**
	 * Returns a new instance of this object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param 	array 	$order 	The order details.
	 * @param 	string 	$group 	The invoices group.
	 *
	 * @return 	self 	A new instance of this object.
	 */
	public static function getInstance($order = null, $group = null)
	{
		if (empty($group))
		{
			// get all supported drivers
			$drivers = glob(VRELIB . DIRECTORY_SEPARATOR . 'invoice' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . '*.php');

			// extract first one available
			$group = array_shift($drivers);

			// get base name and remove .php file extension
			$group = preg_replace("/\.php$/i", '', basename($group));
		}

		if (!isset(static::$classes[$group]))
		{
			if (!VRELoader::import('library.invoice.classes.' . $group))
			{
				throw new Exception('Invoice group [' . $group . '] not supported', 404);
			}

			$classname = 'VREInvoice' . ucfirst($group);

			if (!class_exists($classname))
			{
				throw new Exception('Invoice handler [' . $classname . '] not found', 404);
			}

			static::$classes[$group] = $classname;
		}

		// get cached classname
		$classname = static::$classes[$group];

		// instantiate new object
		$obj = new $classname($order);

		if (!$obj instanceof VREInvoice)
		{
			throw new Exception('The invoice handler [' . $classname . '] is not a valid instance', 500);
		}

		return $obj;
	}
}
