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

VRELoader::import('pdf.tcpdf.tcpdf');
VRELoader::import('pdf.constraints');

/**
 * Abstract class used to implement the common functions
 * that will be invoked to generate and send invoices.
 *
 * @since 	1.8
 */
abstract class VREInvoice
{
	/**
	 * The order details.
	 *
	 * @var object
	 */
	protected $order;

	/**
	 * The invoice arguments (e.g. increment number or legal info).
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * The invoice properties (e.g. page margins or units).
	 *
	 * @var object
	 */
	protected $constraints;

	/**
	 * Class constructor.
	 *
	 * @param 	array 	The order details.
	 *
	 * @uses 	getParams()
	 */
	public function __construct($order)
	{
		$this->order = $order;

		// init params
		$this->getParams();
	}

	/**
	 * Returns an array containing the invoice arguments.
	 *
	 * @return 	array 	The invoice arguments.
	 */
	public function getParams()
	{
		if (!$this->params)
		{
			$data = VREFactory::getConfig()->getObject('invoiceobj', null);

			if (!isset($data->params) || !is_object($data->params) || !get_object_vars($data->params))
			{
				// create parameters for the first time
				$this->params = new stdClass;
				$this->params->number    = 1;
				$this->params->suffix    = date('Y');
				$this->params->datetype  = 1; // 1: today, 2: booking checkin
				$this->params->inv_date  = null;
				$this->params->legalinfo = '';

				$this->constraints = null;
			}
			else
			{
				// use stored parameters
				$this->params = $data->params;

				// unset invoice date if it was stored in the database
				$this->params->inv_date  = null;
			}

			// check if the constraints was set in the stored JSON
			if (!isset($data->constraints) || !is_object($data->constraints))
			{
				// no constraints, use empty array to load default settings
				$data->constraints = array();
			}

			// create new constraints instance with stored data
			$this->constraints = new VikRestaurantsConstraintsPDF($data->constraints);
		}
		
		return $this->params;
	}

	/**
	 * Overwrites the invoice parameters.
	 *
	 * @param 	object  $params  The parameters to set.
	 *
	 * @return 	self    This object to support chaining.
	 */
	public function setParams($params)
	{
		$params = (object) $params;

		// get params first to have always the default properties
		$this->getParams();

		foreach ($params as $k => $v)
		{
			if (property_exists($this->params, $k))
			{
				$this->params->{$k} = $v;
			}
		}

		if (!empty($params->inv_number))
		{
			list($this->params->number, $this->params->suffix) = explode('/', $params->inv_number);
		}

		return $this;
	}
	
	/**
	 * Returns an object containing the invoice properties.
	 *
	 * @return 	object 	The invoice properties.
	 *
	 * @uses 	getParams()
	 */
	public function getConstraints()
	{
		if (!$this->constraints)
		{
			// load constraints
			$this->getParams();
		}

		return $this->constraints;
	}

	/**
	 * Overwrites the invoice constraints.
	 *
	 * @param 	object  $settings  The constraints to set.
	 *
	 * @return 	self    This object to support chaining.
	 */
	public function setConstraints($settings)
	{
		// get constraints first to have always the default settings
		$this->getConstraints();

		// DO NOT cast the settings to array/object because
		// the VikRestaurantsConstraintsPDF might be passed.
		// In that case, an iterator should be returned.

		foreach ($settings as $k => $v)
		{
			$this->constraints->{$k} = $v;
		}

		return $this;
	}

	/**
	 * Returns an object containing the invoice parameters and constraints.
	 *
	 * @param 	boolean  $array  True to return the data as array.
	 *
	 * @return 	object
	 */
	public function getData($array = false)
	{
		if (!$array)
		{
			$data = new stdClass;
			$data->params      = $this->params;
			$data->constraints = $this->constraints;
		}
		else
		{
			$data = array(
				'params'      => (array) $this->params,
				'constraints' => $this->constraints->toArray(),
			);
		}

		return $data;
	}

	/**
	 * Method used to save the current parameters and settings.
	 *
	 * @return 	self  This object to support chaining.
	 *
	 * @uses 	getData()
	 */
	public function save()
	{
		// update invoice data
		VREFactory::getConfig()->set('invoiceobj', $this->getData());

		return $this;
	}

	/**
	 * Increase the invoice number after a successful generation.
	 *
	 * @return 	void
	 *
	 * @uses 	save()
	 */
	protected function increaseNumber()
	{
		// increase number by one
		$this->params->number++;
		// store parameters
		$this->save();
	}

	/**
	 * Generate the invoices related to the specified order.
	 *
	 * @param 	boolean  $increase  True to increase the invoice number by one step after generation.
	 *
	 * @return 	mixed 	 The invoice path on success, otherwise false.
	 *
	 * @uses 	getInvoicePath()
	 * @uses 	getPageTemplate()
	 * @uses 	parseTemplate()
	 * @uses 	increaseNumber()
	 */
	public function generate($increase = true)
	{
		if (!$this->order)
		{
			return false;
		}

		// get invoice path
		$path = $this->getInvoicePath();
		// get constraints
		$constraints = $this->getConstraints();

		if (is_file($path))
		{
			// unlink pdf if already exists
			@unlink($path);
		}

		if (!empty($constraints->font))
		{
			// use specified font
			$font = $constraints->font;
		}
		else
		{
			// use DejavuSans font by default for UTF-8 compliance
			$font = 'dejavusans';
		}

		// check if the selected font is supported
		if (!$this->isFontSupported($font))
		{
			 // fallback to Courier default font
			 $font = 'courier';  
		}
		
		$pdf = new TCPDF($constraints->pageOrientation, $constraints->unit, $constraints->pageFormat, true, 'UTF-8', false);

		// get title from constraints
		$title = !empty($constraints->headerTitle) ? $constraints->headerTitle : null;

		if ($title)
		{
			// set page title
			$pdf->SetTitle($title);

			// show header
			$pdf->SetHeaderData('', 'auto', $title, '');

			// set header font
			$pdf->setHeaderFont(array($font, '', $constraints->fontSizes->header));

			// set header margin
			$pdf->SetHeaderMargin((int) $constraints->margins->header);
		}
		else
		{
			// nothing to display in header, hide it
			$pdf->SetPrintHeader(false);
		}	

		// default monospaced font
		// $pdf->SetDefaultMonospacedFont('courier');

		// margins
		$pdf->SetMargins($constraints->margins->left, $constraints->margins->top, $constraints->margins->right);

		$pdf->SetAutoPageBreak(true, $constraints->margins->bottom);
		$pdf->setImageScale($constraints->imageScaleRatio);
		$pdf->SetFont($font, '', $constraints->fontSizes->body);

		// check if we should display the footer
		if (!empty($constraints->showFooter))
		{
			// show footer
			$pdf->SetPrintFooter(true);

			// set footer font
			$pdf->setFooterFont(array($font, '', $constraints->fontSizes->footer));

			// set footer margin
			$pdf->SetFooterMargin($constraints->margins->footer);
		}
		else
		{
			// hide footer otherwise
			$pdf->SetPrintFooter(false);
		}

		// get invoice template
		$tmpl = $this->getPageTemplate($this->order);

		// parse template
		$pages = $this->parseTemplate($tmpl);

		if (!is_array($pages))
		{
			$pages = array($pages);
		}

		// add pages
		foreach ($pages as $page)
		{
			$pdf->addPage();
			$pdf->writeHTML($page, true, false, true, false, '');
		}
		
		// write file
		$pdf->Output($path, 'F');

		// check if the file has been created
		if (!is_file($path))
		{
			return false;
		}

		if ($increase)
		{
			// increase invoice number in case we are generating progressively
			$this->increaseNumber();
		}

		return $path;
	}

	/**
	 * Sends the invoice via e-mail to the customer.
	 *
	 * @param 	string 	 $path 	The invoice path, which will be 
	 * 							included as attachment within the e-mail.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 *
	 * @uses 	getRecipient()
	 */
	public function send($path)
	{
		$to = $this->getRecipient();

		if (!$to)
		{
			return false;
		}

		$admin_mail_list = VikRestaurants::getAdminMailList();
		$sendermail      = VikRestaurants::getSenderMail();
		$fromname        = VikRestaurants::getRestaurantName();
		
		// fetch mail subject
		$subject = JText::sprintf('VRINVMAILSUBJECT', $fromname, $this->order->id . '-' . $this->order->sid);
		// fetch mail content
		$content = JText::sprintf('VRINVMAILCONTENT', $fromname, $this->order->id . '-' . $this->order->sid);

		// add separators to content
		$content = str_repeat('#', 40) . "\n\n" . $content . "\n\n" . str_repeat('#', 40) . "\n\n";
		
		$vik = VREApplication::getInstance();

		return $vik->sendMail($sendermail, $fromname, $to, $admin_mail_list[0], $subject, $content, array($path), $isHtml = false);
	}

	/**
	 * Checks if the specified font is supported.
	 *
	 * @param 	string   $font  The font family name.
	 *
	 * @return 	boolean  True if the font is supported, false otherwise.
	 */
	public function isFontSupported($font)
	{
		$font = strtolower($font);

		// font system supported by default
		switch ($font)
		{
			case 'courier':
			case 'helvetica':
				return true;
		}

		// create font driver path under TCPDF "fonts" folder
		$path = implode(DIRECTORY_SEPARATOR, array(VREHELPERS, 'pdf', 'tcpdf', 'fonts', $font . '.php'));

		// check if a the font is installed
		return is_file($path);
	}

	/**
	 * Parses the given template to replace the placeholders
	 * with the values contained in the order details.
	 *
	 * @param 	string 	$tmpl  The template to parse.
	 *
	 * @return 	mixed 	The invoice page or an array of pages.
	 */
	protected function parseTemplate($tmpl)
	{
		$config = VREFactory::getConfig();

		$logo_name = $config->get('companylogo');

		// company logo
		if ($logo_name)
		{ 
			$logo_str = '<img src="' . VREMEDIA_URI . $logo_name . '" />';
		}
		else
		{
			$logo_str = '';
		}

		$tmpl = str_replace('{company_logo}', $logo_str, $tmpl);
		
		// company info
		$tmpl = str_replace('{company_info}', nl2br($this->params->legalinfo), $tmpl);
		
		// invoice details
		$suffix = '';

		if (!empty($this->params->suffix))
		{
			$suffix = '/' . $this->params->suffix;
		}

		$tmpl = str_replace('{invoice_number}', $this->params->number, $tmpl);
		$tmpl = str_replace('{invoice_suffix}', $suffix 			 , $tmpl);

		return $tmpl;
	}

	/**
	 * Returns the destination path of the invoice.
	 *
	 * @return 	string 	The invoice path.
	 */
	abstract protected function getInvoicePath();

	/**
	 * Returns the page template that will be used to 
	 * generate the invoice.
	 *
	 * @return 	string 	The base HTML.
	 */
	abstract protected function getPageTemplate();

	/**
	 * Returns the e-mail address of the user that should
	 * receive the invoice via mail.
	 *
	 * @return 	string 	The customer e-mail.
	 */
	abstract protected function getRecipient();
}
