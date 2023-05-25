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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants invoice management view.
 *
 * @since 1.7
 */
class VikRestaurantsViewmanageinvoice extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';

		// set the toolbar
		$this->addToolBar($type);

		// load invoice framework
		VRELoader::import('library.invoice.factory');
		// get first available driver because we just need
		// to access the stored invoice parameters and settings
		$handler = VREInvoiceFactory::getInstance();

		// obtain invoice data
		$data = $handler->getData();

		$invoice = null;

		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_invoice'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$invoice = $dbo->loadObject();

				// split invoice number
				list($invoice->number, $invoice->suffix) = explode('/', $invoice->inv_number);
			}
		}

		if (empty($invoice))
		{
			$invoice = (object) $this->getBlankItem($data);
		}

		// add invoice data to record
		$invoice->datetype  = $data->params->datetype;
		$invoice->legalinfo = $data->params->legalinfo;
		$invoice->settings  = $data->constraints;

		// use invoice data stored in user state
		$this->injectUserStateData($invoice, 'vre.invoice.data');
		
		$this->invoice = &$invoice;
		$this->handler = &$handler;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @param 	array   $data  The stored invoices data.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem($data)
	{
		$input = JFactory::getApplication()->input;

		$date = getdate();

		// make sure the group is supported
		$group = JHtml::_('vrehtml.admin.getgroup', $input->get('group', 0, 'uint'));

		$month = $input->get('month', 0, 'uint');
		$year  = $input->get('year', 0, 'uint');

		return array(
			'id'     => 0,
			'number' => $data->params->number,
			'suffix' => $data->params->suffix,
			'month'  => $month ? $month : $date['mon'],
			'year'   => $year ? $year : $date['year'],
			'group'  => $group,
		);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string  $type  The view type ('edit' or 'new').
	 *
	 * @return 	void
	 */
	private function addToolBar($type)
	{
		// add menu title and some buttons to the page
		if ($type == 'edit')
		{
			JToolBarHelper::title(JText::_('VRMAINTITLEEDITINVOICE'), 'vikrestaurants');
		}
		else
		{
			JToolBarHelper::title(JText::_('VRMAINTITLENEWINVOICE'), 'vikrestaurants');
		}

		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('invoice.save', JText::_('VRSAVE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('invoice.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('invoice.cancel', JText::_('VRCANCEL'));
	}
}
