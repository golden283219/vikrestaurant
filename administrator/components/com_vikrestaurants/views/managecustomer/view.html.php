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
 * VikRestaurants customer management view.
 *
 * @since 1.3
 */
class VikRestaurantsViewmanagecustomer extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$input 	= JFactory::getApplication()->input;
		$dbo 	= JFactory::getDbo();
		
		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$customer = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_users'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$customer = $dbo->loadObject();

				// decode custom fields
				$customer->fields   = (array) json_decode($customer->fields, true);
				$customer->tkfields = (array) json_decode($customer->tkfields, true);

				// get delivery locations
				$customer->locations = array();

				$q = $dbo->getQuery(true)
					->select('*')
					->from($dbo->qn('#__vikrestaurants_user_delivery'))
					->where($dbo->qn('id_user') . ' = ' . $customer->id)
					->order($dbo->qn('ordering') . ' ASC');
	
				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					// include full address string too
					foreach ($dbo->loadObjectList() as $l)
					{
						$l->fullString = VikRestaurants::deliveryAddressToStr($l);

						$customer->locations[] = $l;
					}
				}
			}
		}

		if (empty($customer))
		{
			$customer = (object) $this->getBlankItem();
		}

		$customer->username = '';
		$customer->usermail = $customer->billing_mail;

		// use customer data stored in user state
		$this->injectUserStateData($customer, 'vre.customer.data');

		// get custom fields
		$custom_fields = array(
			// restaurants
			array(),
			// take-away
			array(),
		);

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_custfields'))
			->where(array(
				$dbo->qn('type') . ' <> ' . $dbo->q('checkbox'),
				$dbo->qn('required') . ' = 0',
			), 'OR')
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $field)
			{
				$custom_fields[$field->group][] = $field;
			}
		}
		
		// get selected user name
		$juser = null;

		if ($customer->jid > 0)
		{
			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('id', 'name')))
				->from($dbo->qn('#__users'))
				->where($dbo->qn('id') . ' = ' . $customer->jid);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$juser = $dbo->loadObject();
			}
		}
		
		$this->customer 	= &$customer;
		$this->juser 		= &$juser;
		$this->customFields = &$custom_fields;

		$this->isTmpl = $input->get('tmpl') === 'component';

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem()
	{	
		return array(
			'id'                => 0,
			'jid'               => '',
			'billing_name'      => '',
			'billing_mail'      => '',
			'billing_phone'     => '',
			'country_code'      => VRCustomFields::getDefaultCountryCode(),
			'billing_state'     => '',
			'billing_city'      => '', 
			'billing_address'   => '',
			'billing_address_2' => '',
			'billing_zip'       => '',
			'company'           => '',
			'vatnum'            => '',
			'ssn'               => '',
			'fields'            => array(),
			'tkfields'          => array(),
			'notes'             => '',
			'image'             => '',
			'locations'         => array(),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITCUSTOMER'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWCUSTOMER'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('customer.save', JText::_('VRSAVE'));
			JToolbarHelper::save('customer.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('customer.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('customer.cancel', JText::_('VRCANCEL'));
	}
}
