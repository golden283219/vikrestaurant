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
 * Class used to generate the invoices of the restaurant reservations.
 *
 * @since 	1.6
 */
class VREInvoiceRestaurant extends VREInvoice
{
	/**
	 * @override
	 * Returns the destination path of the invoice.
	 *
	 * @return 	string 	The invoice path.
	 */
	protected function getInvoicePath()
	{
		$parts = array(
			VREINVOICE,
			$this->order->id . '-' . $this->order->sid . '.pdf',
		);

		return implode(DIRECTORY_SEPARATOR, $parts);
	}

	/**
	 * @override
	 * Returns the page template that will be used to 
	 * generate the invoice.
	 *
	 * @return 	string 	The base HTML.
	 */
	protected function getPageTemplate()
	{
		$data = array(
			'order' => $this->order,
		);

		if (JFactory::getApplication()->isClient('administrator'))
		{
			$base = VREBASE . DIRECTORY_SEPARATOR . 'layouts';
		}
		else
		{
			$base = null;
		}

		return JLayoutHelper::render('templates.invoice.restaurant', $data, $base);
	}

	/**
	 * @override
	 * Parses the given template to replace the placeholders
	 * with the values contained in the order details.
	 *
	 * @param 	string 	The template to parse.
	 *
	 * @return 	mixed 	The invoice page or an array of pages.
	 */
	protected function parseTemplate($tmpl)
	{
		// let parent starts template parsing
		$tmpl = parent::parseTemplate($tmpl);

		// get config
		$config   = VREFactory::getConfig();
		$currency = VREFactory::getCurrency();

		// get invoice params
		$params = $this->getParams();

		/**
		 * INVOICE DATE
		 */

		if (empty($params->inv_date))
		{
			if ($params->datetype == 1)
			{
				// use current datetime
				$invoice_date = VikRestaurants::now();
			}
			else
			{
				// use reservation checkin
				$invoice_date = $this->order->checkin_ts;
			}

			// update invoice date for being recovered after generating the invoice
			$this->setParams(array('inv_date' => $invoice_date));
		}
		else
		{
			// use specified date
			$invoice_date = $params->inv_date;
		}

		// inject invoice date in template
		$tmpl = str_replace('{invoice_date}', date($config->get('dateformat'), $invoice_date), $tmpl);

		/**
		 * CUSTOMER INFORMATION
		 */

		$customer_info = '';

		// iterate order custom fields
		foreach ($this->order->fields as $label => $value)
		{
			if (!empty($value))
			{
				// print custom field (label: value)
				$customer_info .= JText::_($label) . ': ' . $value . "<br/>\n";
			}
		}

		// inject customer information in template
		$tmpl = str_replace('{customer_info}', $customer_info, $tmpl);

		/**
		 * BILLING DETAILS
		 */

		$billing_info = '';

		if ($this->order->billing)
		{
			$parts = array();

			// VAT and company name
			$company_info = '';

			if (!empty($this->order->billing->company))
			{
				$company_info .= $this->order->billing->company . ' ';
			}

			if (!empty($this->order->billing->vatnum))
			{
				$company_info .= $this->order->billing->vatnum;
			}

			if ($company_info)
			{
				$parts[] = $company_info;
			}
			
			// City information
			$city_info = '';

			if (!empty($this->order->billing->billing_state))
			{
				$city_info .= $this->order->billing->billing_state . ', ';
			}

			if (!empty($this->order->billing->billing_city))
			{
				$city_info .= $this->order->billing->billing_city . ' ';
			}

			if (!empty($this->order->billing->billing_zip))
			{
				$city_info .= $this->order->billing->billing_zip;
			}

			if ($city_info)
			{
				$parts[] = $city_info;
			}
			
			// Address information
			$address_info = '';

			if (!empty($this->order->billing->billing_address))
			{
				$address_info .= $this->order->billing->billing_address;
			}

			if (!empty($this->order->billing->billing_address_2))
			{
				$address_info .= ", " . $this->order->billing->billing_address_2;
			}

			if ($address_info)
			{
				$parts[] = $address_info;
			}

			// build details
			$billing_info = implode("<br />\n", array_filter($parts));
		}

		// inject billing details in template
		$tmpl = str_replace('{billing_info}', $billing_info, $tmpl);
		
		/**
		 * INVOICE TOTAL
		 */

		$tax_ratio = $config->getFloat('taxesratio', 0);
		$use_taxes = $config->getUint('usetaxes', 0);

		$gratuity = $this->order->tip_amount;

		$grand_total = $this->order->bill_value - $gratuity;

		if ($use_taxes == 0)
		{
			// included
			$net = $grand_total * 100.0 / ($tax_ratio + 100.0);
		}
		else
		{
			$net = $grand_total;
			
			// excluded
			$grand_total *= 1 + $tax_ratio / 100.0;
		}

		$taxes    = $grand_total - $net;
		$discount = $this->order->discount_val;

		// do not increase NET by DISCOUNT as it is already
		// added while parsing the document
		// $net += $discount;

		$grand_total += $gratuity;

		$tmpl = str_replace('{invoice_totalnet}'   , $currency->format($net + $discount), $tmpl);
		$tmpl = str_replace('{invoice_discountval}', $currency->format($discount)       , $tmpl);
		$tmpl = str_replace('{invoice_totaltip}'   , $currency->format($gratuity)       , $tmpl);
		$tmpl = str_replace('{invoice_totaltax}'   , $currency->format($taxes)          , $tmpl);
		$tmpl = str_replace('{invoice_grandtotal}' , $currency->format($grand_total)    , $tmpl);
		
		return $tmpl;
	}

	/**
	 * @override
	 * Returns the e-mail address of the user that should
	 * receive the invoice via mail.
	 *
	 * @return 	string 	The customer e-mail.
	 */
	protected function getRecipient()
	{
		return $this->order->purchaser_mail;
	}
}
