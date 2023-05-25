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
 * VikRestaurants restaurant reservation management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagereservation extends JViewVRE
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
		
		$reservation = array();
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_reservation'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$reservation = $dbo->loadObject();
				
				$reservation->custom_f = json_decode($reservation->custom_f, true);
				
				$reservation->date    = date(VREFactory::getConfig()->get('dateformat'), $reservation->checkin_ts);
				$reservation->hourmin = date('H:i', $reservation->checkin_ts);

				if ($reservation->closure)
				{
					// use CLOSURE management layout
					$this->setLayout('closure');
				}
			}
		}

		if (empty($reservation))
		{
			$reservation = (object) $this->getBlankItem();
		}

		// use reservation data stored in user state
		$this->injectUserStateData($reservation, 'vre.reservation.data');
		
		// get rooms and tables
		$rooms = array();

		$q = $dbo->getQuery(true)
			->select('t.*')
			->select($dbo->qn('r.name', 'room_name'))
			->from($dbo->qn('#__vikrestaurants_room', 'r'))
			->from($dbo->qn('#__vikrestaurants_table', 't'))
			->where($dbo->qn('r.id') . ' = ' . $dbo->qn('t.id_room'))
			->order($dbo->qn('r.ordering') . ' ASC')
			->order($dbo->qn('t.name') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $r)
			{
				if (!isset($rooms[$r->id_room]))
				{
					$room = new stdClass;
					$room->id     = $r->id;
					$room->name   = $r->room_name;
					$room->tables = array();

					$rooms[$r->id_room] = $room;
				}

				$rooms[$r->id_room]->tables[] = $r;
			}
		}
		
		// get custom fields

		$cfields = VRCustomFields::getList(0, VRCustomFields::FILTER_EXCLUDE_REQUIRED_CHECKBOX);
		
		// get all menus

		$all_menus = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('m.id'))
			->select($dbo->qn('m.name'))
			->from($dbo->qn('#__vikrestaurants_menus', 'm'))
			->where($dbo->qn('m.choosable') . ' = 1')
			->order($dbo->qn('m.ordering') . ' ASC');

		if ($reservation->id)
		{
			$q->select($dbo->qn('a.id', 'id_assoc'));
			$q->select($dbo->qn('a.quantity'));

			$join = array();
			$join[] = $dbo->qn('m.id') . ' = ' . $dbo->qn('a.id_menu');
			$join[] = $dbo->qn('a.id_reservation') . ' = ' . $reservation->id;

			$q->leftjoin($dbo->qn('#__vikrestaurants_res_menus_assoc', 'a') . ' ON ' . implode(' AND ', $join));
		}
		else
		{
			$q->select('0 AS ' . $dbo->qn('id_assoc'));
			$q->select('0 AS ' . $dbo->qn('quantity'));
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$all_menus = $dbo->loadObjectList();
		}

		// retrieve customer details

		if ($reservation->id_user > 0)
		{
			$customer = VikRestaurants::getCustomer($reservation->id_user);
		}
		else
		{
			$customer = null;
		}
		
		$this->reservation  = &$reservation;
		$this->rooms        = &$rooms;
		$this->customFields = &$cfields;
		$this->allMenus     = &$all_menus;
		$this->customer     = &$customer;
		$this->returnTask   = $input->get('from', null);

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

		$time = VikRestaurants::getClosestTime($date, true);
		$time = $input->getString('hourmin', $time);

		return array(
			'id'                   => 0,
			'date'                 => $date,
			'hourmin'              => $time,
			'id_table'             => $input->getUint('idt', 0),
			'id_payment'           => 0,
			'people'               => $input->getUint('people', 2),
			'bill_closed'          => 0,
			'bill_value'           => 0,
			'deposit'              => 0, 
			'purchaser_nominative' => '',
			'purchaser_mail'       => '',
			'purchaser_phone'      => '',
			'purchaser_prefix'     => '',
			'purchaser_country'    => '',
			'status'               => 'CONFIRMED',
			'notes'                => '',
			'id_user'              => 0,
			'stay_time'            => 0, // do not force time of stay
			'closure'              => 0,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITRESERVATION'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWRESERVATION'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if (!$this->reservation->closure)
		{
			if ($user->authorise('core.edit', 'com_vikrestaurants')
				|| $user->authorise('core.create', 'com_vikrestaurants'))
			{
				JToolbarHelper::apply('reservation.save', JText::_('VRSAVE'));
				JToolbarHelper::save('reservation.saveclose', JText::_('VRSAVEANDCLOSE'));
			}

			if ($user->authorise('core.edit', 'com_vikrestaurants')
				&& $user->authorise('core.create', 'com_vikrestaurants'))
			{
				JToolbarHelper::save2new('reservation.savenew', JText::_('VRSAVEANDNEW'));
			}
		}
		else
		{
			// save CLOSURE
			if ($user->authorise('core.edit', 'com_vikrestaurants'))
			{
				JToolbarHelper::apply('reservation.saveclosure', JText::_('VRSAVE'));
			}
		}
		
		JToolbarHelper::cancel('reservation.cancel', JText::_('VRCANCEL'));
	}
}
