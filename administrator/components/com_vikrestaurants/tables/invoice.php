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
 * VikRestaurants invoice table.
 *
 * @since 1.8
 */
class VRETableInvoice extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_invoice', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_order';
		$this->_requiredFields[] = 'inv_number';
		$this->_requiredFields[] = 'file';
		$this->_requiredFields[] = 'group';

		// auto-load invoices framework only when needed to
		// avoid conflicts with other plugins
		VRELoader::import('library.invoice.factory');
	}

	/**
	 * Method to bind an associative array or object to the Table instance. This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($src, $ignore = array())
	{
		$src = (array) $src;

		// save flag used to check whether the customer should be notified or not
		$this->_notifyCustomer = !empty($src['notifycust']);

		$dbo = JFactory::getDbo();

		if (empty($src['id']))
		{
			if (empty($src['id_order']) || !isset($src['group']))
			{
				// ID order is mandatory when creating an invoice
				$this->setError('Missing Order ID');

				return false;
			}

			// check if there is already an invoice for the given order
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id'))
				->from($dbo->qn($this->getTableName()))
				->where($dbo->qn('id_order') . ' = ' . (int) $src['id_order'])
				->where($dbo->qn('group') . ' = ' . (int) $src['group']);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// order exists, switch to UPDATE
				$src['id'] = (int) $dbo->loadResult();
			}
		}

		// invoice creation
		if ($src['id'] == 0)
		{
			if (empty($src['createdon']))
			{
				$src['createdon'] = VikRestaurants::now();
			}
		}
		// invoice update
		else if (!isset($src['id_order']))
		{
			// retrieve order ID of the stored invoice
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id_order'))
				->from($dbo->qn($this->getTableName()))
				->where($dbo->qn('id') . ' = ' . (int) $src['id']);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				// invoice not found, abort
				$this->setError(sprintf('Invoice [%d] not found', $src['id']));

				return false;
			}

			$src['id_order'] = (int) $dbo->loadResult();
		}

		if ($src['id'] && empty($src['overwrite']))
		{
			// do not overwrite existing record (error not needed)
			return false;
		}

		// retrieve order details
		if ($src['group'] == 0)
		{
			// restaurant reservation data
			$order = VREOrderFactory::getReservation($src['id_order']);

			// get invoice handler class
			$invoice = VREInvoiceFactory::getInstance($order, 'restaurant');
		}
		else
		{
			// take-away order data
			$order = VREOrderFactory::getOrder($src['id_order']);

			// get invoice handler class
			$invoice = VREInvoiceFactory::getInstance($order, 'takeaway');
		}

		// make sure the order exists
		if (!$order)
		{
			// order not found, abort
			$this->setError(sprintf('Order [%d] not found', $src['id_order']));

			return false;
		}

		// build invoice number in case it is an array (number + suffix)
		if (isset($src['inv_number']) && is_array($src['inv_number']))
		{
			$src['inv_number'] = implode('/', $src['inv_number']);
		}

		if (isset($src['inv_date']))
		{
			if ($src['inv_date'] == '1')
			{
				// use current datetime
				$src['inv_date'] = VikRestaurants::now();

				$src['datetype'] = 1;
			}
			else if ($src['inv_date'] == '2')
			{
				// use order checkin
				$src['inv_date'] = $order->checkin_ts;

				$src['datetype'] = 2;
			}
			else if (is_string($src['inv_date']))
			{
				// create timestamp based on given date
				$src['inv_date'] = VikRestaurants::createTimestamp($src['inv_date'], 0, 0);
			}
		}

		// update invoice params
		$invoice->setParams($src);
		// update invoice constraints
		$invoice->setConstraints($src);

		// in case the invoice number was not set, recover it
		if (!isset($src['inv_number']))
		{
			// get invoice paramaters
			$params = $invoice->getParams();

			// use current invoice number
			$src['inv_number'] = $params->number . '/' . $params->suffix;
		}

		if (isset($src['increase']))
		{
			// increase only if specified
			$increaseNumber = (bool) $src['increase'];
		}
		else
		{
			// increase invoice number only on insert
			$increaseNumber = $src['id'] == 0;
		}

		try
		{
			// generate invoice PDF and obtain path
			$path = $invoice->generate($increaseNumber);

			if (!$path)
			{
				// an error occurred while generating the invoice
				throw new Exception('Unable to generate the PDF.', 500);
			}

			// store invoice file name
			$src['file'] = basename($path);

			// in case the invoice date is empty, recover it
			if (empty($src['inv_date']))
			{
				$src['inv_date'] = $invoice->getParams()->inv_date;
			}
		}
		catch (Exception $e)
		{
			// an error occurred, set message and abort
			$this->setError($e->getMessage());

			return false;
		}

		// save invoice handler for later use
		$this->_invoiceHandler = $invoice;

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		// invoke parent to store the record
		if (!parent::store($updateNulls))
		{
			// do not proceed in case of error
			return false;
		}

		$this->_notified = false;

		// check if the customer should be notified
		if ($this->_notifyCustomer)
		{
			// create invoice absolute path
			$path = VREINVOICE . DIRECTORY_SEPARATOR . $this->file;

			// notify customer 
			$this->_notified = $this->_invoiceHandler->send($path);
		}

		return true;
	}

	/**
	 * Returns whether the customer has been notified or not.
	 *
	 * @return 	boolean
	 */
	public function isNotified()
	{
		return !empty($this->_notified);
	}

	/**
	 * Saves the invoice parameters and constraints.
	 * To be used to update the invoices configuration
	 * without generating a new one.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 *
	 * @return 	void
	 */
	public function saveInvoiceData($src)
	{
		$src = (array) $src;

		// build invoice number in case it is an array (number + suffix)
		if (isset($src['inv_number']) && is_array($src['inv_number']))
		{
			$src['inv_number'] = implode('/', $src['inv_number']);
		}

		if (isset($src['inv_date']))
		{
			if ($src['inv_date'] == '1')
			{
				$src['datetype'] = 1;
			}
			else if ($src['inv_date'] == '2')
			{
				$src['datetype'] = 2;
			}
		}

		// get invoice handler (do not care of the group)
		$invoice = VREInvoiceFactory::getInstance();

		// update invoice params
		$invoice->setParams($src);
		// update invoice constraints
		$invoice->setConstraints($src);

		// save configuration
		$invoice->save();
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed    $ids  Either the record ID or a list of records.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($ids = null)
	{
		if (!$ids)
		{
			return false;
		}

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		// get all invoice files
		$q = $dbo->getQuery(true)
			->select($dbo->qn('file'))
			->from($dbo->qn('#__vikrestaurants_invoice'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// nothing to delete
			return false;
		}

		$files = $dbo->loadColumn();

		// delete invoices from database
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_invoice'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete invoices from file system
		foreach ($files as $file)
		{
			$path = VREINVOICE . DIRECTORY_SEPARATOR . $file;

			// delete file only if exists
			if (is_file($path))
			{
				$aff = unlink($path) || $aff;
			}
		}

		return $aff;
	}

	/**
	 * Method to download one or more invoices.
	 *
	 * @param   mixed  $ids  Either the record ID or a list of records.
	 *
	 * @return  mixed  The path of the file to download (either a PDF or a ZIP).
	 * 				   Returns false in case of errors.
	 */
	public function download($ids)
	{
		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		if (!$ids)
		{
			// nothing to search
			return false;
		}

		// get all invoice files
		$q = $dbo->getQuery(true)
			->select($dbo->qn('file'))
			->from($dbo->qn('#__vikrestaurants_invoice'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// abort, nothing else to do here
			return false;
		}

		$files = $dbo->loadColumn();

		if (count($files) == 1)
		{
			$path = VREINVOICE . DIRECTORY_SEPARATOR . $files[0];

			if (!is_file($path))
			{
				// file not found, raise error
				$this->setError(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
				return false;
			}

			// only one record, return the base path of the file to download
			return $path;
		}

		// create a package to download multiple files at once
		if (!class_exists('ZipArchive'))
		{
			// ZipArchive class is mandatory to create a package
			$this->setError('The ZipArchive class is not installed on your server.');
			return false;
		}
			
		$zipname = VREINVOICE . DIRECTORY_SEPARATOR . 'invoices-' . VikRestaurants::now() . '.zip';
		
		// init package
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);

		// add files to the package
		foreach ($files as $file)
		{
			$path = VREINVOICE . DIRECTORY_SEPARATOR . $file;
			
			// make sure the file exists before adding it
			if (is_file($path))
			{
				$zip->addFile($path);
			}
		}

		// compress the package
		$zip->close();

		// return the path of the archive
		return $zipname;
	}
}
