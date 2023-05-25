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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants invoice controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerInvoice extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data  = array();
		$group = $app->input->getUint('group');
		$month = $app->input->getUint('month');
		$year  = $app->input->getUint('year');

		if (!is_null($group))
		{
			$data['group'] = $group;
		}

		if ($month)
		{
			$data['month'] = $month;
		}

		if ($year)
		{
			$data['year'] = $year;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.invoice.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=manageinvoice');

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.invoice.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=manageinvoice&cid[]=' . $cid[0]);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return 	void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			$input = JFactory::getApplication()->input;

			$url = 'index.php?option=com_vikrestaurants&task=invoice.add';

			// recover data from request
			$group = $input->getUint('group');
			$month = $input->getUint('month');
			$year  = $input->getUint('year');

			if (!is_null($group))
			{
				$url .= '&group=' . $group;
			}

			if ($month)
			{
				$url .= '&month=' . $month;
			}

			if ($year)
			{
				$url .= '&year=' . $year;
			}

			$this->setRedirect($url);
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @param 	array 	 $data  An array of invoice parameters to be used for the generation.
	 * 						   If not specified, the parameters in the request will be used.
	 *
	 * @return 	boolean
	 */
	public function save(array $data = array())
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();

		if ($data)
		{
			$args = $data;
		}
		else
		{
			$args['group'] 		= $input->get('group', 0, 'int');
			$args['overwrite'] 	= $input->get('overwrite', 0, 'uint');
			$args['notifycust'] = $input->get('notifycust', 0, 'uint');
			$args['inv_number'] = $input->get('inv_number', array(), 'string');
			$args['inv_date']   = $input->get('inv_date', 0, 'string');
			$args['legalinfo']  = $input->get('legalinfo', '', 'string');
			$args['id']         = $input->get('id', 0, 'uint');

			// settings
			$args['pageOrientation'] = $input->get('pageorientation', '', 'string');
			$args['pageFormat']      = $input->get('pageformat', '', 'string');
			$args['unit']            = $input->get('unit', '', 'string');
			$args['imageScaleRatio'] = abs($input->get('scale', 100, 'float')) / 100;

			// layout
			$args['font']        = $input->get('font', 'courier', 'string');
			$args['fontSizes']   = $input->get('fontsizes', array(), 'array');
			$args['headerTitle'] = '';
			$args['showFooter']  = $input->get('showfooter', false, 'bool');

			if ($input->getBool('showheader'))
			{
				$args['headerTitle'] = $input->get('headertitle', '', 'string');
			}

			// margins
			$args['margins'] = $input->get('margins', array(), 'array');
		}

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$invoice = JTableVRE::getInstance('invoice', 'VRETable');

		// update existing invoice
		if ($args['id'])
		{
			// try to save arguments
			if (!$invoice->save($args))
			{
				// get string error
				$error = $invoice->getError(null, true);

				// display error message
				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

				$url = 'index.php?option=com_vikrestaurants&view=manageinvoice';

				if ($invoice->id)
				{
					$url .= '&cid[]=' . $invoice->id;
				}

				// redirect to new/edit page
				$this->setRedirect($url);
					
				return false;
			}

			// display generic successful message
			$app->enqueueMessage(JText::plural('VRINVGENERATEDMSG', 1));

			if ($invoice->isNotified())
			{
				// invoice notified, display message
				$app->enqueueMessage(JText::plural('VRINVMAILSENT', 1));
			}
		}
		// invoices mass creation
		else
		{
			$dbo = JFactory::getDbo();

			$table = '#__vikrestaurants' . ($args['group'] == 1 ? '_takeaway' : '') . '_reservation';

			$month = $input->get('month', 1, 'uint');
			$year  = $input->get('year', 0, 'uint');
			$cid   = $input->get('cid', array(), 'uint');

			$start_ts = mktime(0, 0, 0, $month, 1, $year);
			$end_ts   = mktime(0, 0, 0, $month + 1, 1, $year) - 1;

			$generated = 0;
			$notified  = 0;

			// retrieve all reservation/orders
			$q = $dbo->getQuery(true);
			
			// select only the ID
			$q->select($dbo->qn('id'));
			// load the correct table
			$q->from($dbo->qn($table));

			if ($cid)
			{
				// get specified orders
				$q->where($dbo->qn('id') . ' IN (' . implode(',', $cid) . ')');
			}
			else
			{
				// get orders with checkin in the specified month
				$q->where($dbo->qn('checkin_ts') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts);
			}

			// make sure the order is CONFIRMED
			$q->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'));

			// order by ascending check-in
			$q->order($dbo->qn('checkin_ts') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// always increase invoice number when generating new records
				// or in case of any overwrites
				$args['increase'] = true;

				// generate invoices one by one
				foreach ($dbo->loadColumn() as $order_id)
				{
					// specify order ID
					$args['id_order'] = $order_id;

					// reset invoice to avoid duplicate columns
					$invoice->reset();

					if ($invoice->save($args))
					{
						// update generated count on success
						$generated++;

						if ($invoice->isNotified())
						{
							// increase notified count in case the invoice was sent to the customer
							$notified++;
						}

						// Unset invoice number after generating the first invoice.
						// In this way, all the next invoices will use the progressive
						// number stored in the database.
						unset($args['inv_number']);
					}
				}
			}

			if ($generated)
			{
				// display number of generated invoices
				$app->enqueueMessage(JText::plural('VRINVGENERATEDMSG', $generated));

				if ($notified)
				{
					// display number of notified customers
					$app->enqueueMessage(JText::plural('VRINVMAILSENT', $notified));
				}
			}
			else
			{
				// no generated invoices
				$app->enqueueMessage(JText::_('VRNOINVOICESGENERATED'), 'warning');

				// save invoice data to keep changed settings
				$invoice->saveInvoiceData($args);
			}
		}

		// check if the invoices was generated according to the current date time
		if ($invoice->inv_date)
		{
			// get invoice date of the last created invoice
			$date = getdate($invoice->inv_date);

			// update "month" and "year" to immediately access the page of the created invoices
			$input->set('month', $date['mon']);
			$input->set('year', $date['year']);
		}

		// always redirect to invoices list when generating the invoices
		$this->cancel();

		return true;
	}

	/**
	 * Generates an invoice for the specified reservations.
	 *
	 * @return 	boolean
	 */
	public function generate()
	{
		$input = JFactory::getApplication()->input;

		// create array with required attributes
		$data = array();
		$data['id']         = 0;
		$data['group']      = $input->get('group', 0, 'uint');
		$data['notifycust'] = $input->get('notifycust', 0, 'uint');

		// generate invoice
		return $this->save($data);
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'uint');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		JTableVRE::getInstance('invoice', 'VRETable')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$input = JFactory::getApplication()->input;

		$group = $input->get('group', null, 'uint');
		$year  = $input->get('year', 0, 'uint');
		$month = $input->get('month', 0, 'uint');

		$url = 'index.php?option=com_vikrestaurants&view=invoices';

		if (!is_null($group))
		{
			$url .= '&group=' . $group;
		}

		if ($year)
		{
			$url .= '&year=' . $year;
		}

		if ($month)
		{
			$url .= '&month=' . $month;
		}

		$this->setRedirect($url);
	}

	/**
	 * Downloads one or more selected invoices.
	 * In case of single selection, the invoice will be
	 * directly downloaded in PDF format. Otherwise a
	 * ZIP archive will be given.
	 *
	 * @return 	void
	 */
	public function download()
	{
		$app = JFactory::getApplication();
		$ids = $app->input->get('cid', array(), 'uint');

		// get invoice table
		$invoice = JTableVRE::getInstance('invoice', 'VRETable');

		// get path to download
		$path = $invoice->download($ids);

		if (!$path)
		{
			// retrieve fetched error message
			$error = $invoice->getError();

			if ($error)
			{
				// raise error
				$app->enqueueMessage($error, 'error');
			}
			else
			{
				// no error fetched, probably the list of IDs was empty
				$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
			}

			// back to main list
			$this->cancel();

			// do not go ahead
			return true;
		}

		// check if we have a PDF file or a ZIP archive
		if (preg_match("/\.pdf$/", $path))
		{
			$filename = basename($path);

			// download PDF file
			header("Content-disposition: attachment; filename={$filename}");
			header("Content-type: application/pdf");
			readfile($path);
		}
		else
		{
			header("Content-Type: application/zip");
			header("Content-disposition: attachment; filename=invoices.zip");
			header("Content-Length: " . filesize($path));
			readfile($path);

			// delete package once its contents have been buffered
			unlink($path);
		}

		// break process to complete download
		exit;
	}

	/**
	 * Loads via AJAX the remaining invoices.
	 *
	 * @return 	void
	 */
	public function ajaxload()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		
		$year  = $app->getUserStateFromRequest('vre.invoices.year', 'year', 0, 'uint');
		$month = $app->getUserStateFromRequest('vre.invoices.month', 'month', 1, 'uint');

		$lim0  = $input->getUint('start_limit');
		$lim   = $input->getUint('limit');
		
		$filters = array();
		$filters['group'] 	  = $input->getString('group');
		$filters['keysearch'] = $input->getString('keysearch');

		// get invoices
		$invoices = array();
		$not_all  = false;
		$max_lim  = 0;

		$start_ts = mktime(0, 0, 0, $month, 1, $year);
		$end_ts   = mktime(0, 0, 0, $month + 1, 1, $year) - 1;

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrestaurants_invoice'))
			->where($dbo->qn('inv_date') . ' BETWEEN ' . $start_ts . ' AND ' . $end_ts)
			->order(array(
				$dbo->qn('inv_date') . ' ASC',
				$dbo->qn('id_order') . ' ASC',
			));

		if (strlen($filters['group']))
		{
			$q->where($dbo->qn('group') . ' = ' . (int) $filters['group']);
		}

		if ($filters['keysearch'])
		{
			$q->andWhere(array(
				$dbo->qn('file') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('inv_number') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			), 'OR');
		}

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$invoices = $dbo->loadAssocList();

			$not_all = true;

			$dbo->setQuery('SELECT FOUND_ROWS();');
			if (($max_lim = $dbo->loadResult()) <= $lim0 + count($invoices))
			{
				$not_all = false;
			}
		}
		
		$invoices_html = array();

		$config = VREFactory::getConfig();
		
		$cont = $lim0;
		$dt_format = $config->get('dateformat') . ' ' . $config->get('timeformat');

		$invoiceLayout = new JLayoutFile('blocks.invoice');

		foreach ($invoices as $inv)
		{
			$cont++;
			
			$data = array(
				'id'     => $inv['id'],
				'number' => $inv['inv_number'],
				'file'   => $inv['file'],
			);
			
			$invoices_html[] = $invoiceLayout->render($data);
		}
		
		if (!$invoices_html)
		{
			$invoices_html[] = VREApplication::getInstance()->alert(JText::_('VRNOINVOICESONARCHIVE'));
		}
		
		echo json_encode(array(1, $cont, $not_all, $invoices_html, $max_lim));
		exit; 
	}
}
