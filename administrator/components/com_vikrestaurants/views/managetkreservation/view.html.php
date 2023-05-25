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
 * VikRestaurants take-away order management view.
 *
 * @since 1.2
 */
class VikRestaurantsViewmanagetkreservation extends JViewVRE
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
		
		$ids  = $input->getUint('cid', array());
		$type = $ids ? 'edit' : 'new';
		
		$order = array();
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_reservation'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$order = $dbo->loadObject();
				
				$order->custom_f = json_decode($order->custom_f, true);
				
				$order->date    = date(VREFactory::getConfig()->get('dateformat'), $order->checkin_ts);
				$order->hourmin = date('H:i', $order->checkin_ts);

				if ($order->route)
				{
					$order->route = json_decode($order->route);
				}
				else
				{
					$order->route = null;
				}
			}
		}

		if (empty($order))
		{
			$order = (object) $this->getBlankItem();
		}

		// use order data stored in user state
		$this->injectUserStateData($order, 'vre.tkreservation.data');

		// get custom fields

		$cfields = VRCustomFields::getList(1, VRCustomFields::FILTER_EXCLUDE_REQUIRED_CHECKBOX);
		
		// retrieve customer details

		if ($order->id_user > 0)
		{
			$customer = VikRestaurants::getCustomer($order->id_user);
		}
		else
		{
			$customer = null;
		}

		// get maximum delivery area charge

		$q = $dbo->getQuery(true)
			->select('MAX(' . $dbo->qn('charge') . ') AS ' . $dbo->qn('max'))
			->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
			->where($dbo->qn('published') . ' = 1');

		$dbo->setQuery($q);
		$dbo->execute();

		$max_delivery_charge = (int) $dbo->loadResult();

		$this->order 	         = &$order;
		$this->customFields      = &$cfields;
		$this->customer          = &$customer;
		$this->maxDeliveryCharge = &$max_delivery_charge;
		$this->returnTask        = $input->get('from', null);

		// set the toolbar
		$this->addToolBar($type);

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
		$input = JFactory::getApplication()->input;

		// create default date and time
		$date = date(VREFactory::getConfig()->get('dateformat'), VikRestaurants::now());
		$date = $input->getString('date', $date);

		$time = VikRestaurants::getClosestTime($date, true, 2);
		$time = $input->getString('hourmin', $time);

		return array(
			'id'                   => 0,
			'date'                 => $date,
			'hourmin'              => $time,
			'status'               => 'CONFIRMED',
			'total_to_pay'         => 0.0,
			'delivery_service'     => 1,
			'id_user'              => 0,
			'id_payment'           => 0,
			'purchaser_nominative' => '',
			'purchaser_mail'       => '',
			'purchaser_phone'      => '',
			'purchaser_prefix'     => '',
			'purchaser_country'    => '',
			'purchaser_address'    => '',
			'notes'                => '',
			'taxes'                => 0.0,
			'delivery_charge'      => 0.0,
			'pay_charge'           => 0.0,
			'route'                => null,
			'custom_f'             => array(),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITTKRES'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWTKRES'), 'vikrestaurants');
		}

		$user = JFactory::getUser();

		// update existing reservation
		if ($this->order->id)
		{
			if ($user->authorise('core.edit', 'com_vikrestaurants'))
			{
				JToolbarHelper::apply('tkreservation.save', JText::_('VRSAVE'));
				JToolbarHelper::save('tkreservation.saveclose', JText::_('VRSAVEANDCLOSE'));
			}

			if ($user->authorise('core.edit', 'com_vikrestaurants')
				&& $user->authorise('core.create', 'com_vikrestaurants'))
			{
				JToolbarHelper::save2new('tkreservation.savenew', JText::_('VRSAVEANDNEW'));
			}
		}
		// insert new reservation
		else
		{
			if ($user->authorise('core.create', 'com_vikrestaurants'))
			{
				// save reservation and go to CART page
				JToolbarHelper::apply('tkreservation.save', JText::_('VRMANAGEMENU23'));
			}
		}
		
		JToolbarHelper::cancel('tkreservation.cancel', JText::_('VRCANCEL'));
	}
}
