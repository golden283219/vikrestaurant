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
 * VikRestaurants operator reservation management view.
 *
 * @since 1.8
 */
class VikRestaurantsViewopmanageres extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		
		////// LOGIN //////
		
		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin() && $operator->isRestaurantAllowed();
		
		if (!$access)
		{
			$itemid = $input->get('Itemid', 0, 'uint');

			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}
		
		////// MANAGEMENT //////
		
		$ids = $input->getUint('cid', array());
		
		if (count($ids) > 1)
		{
			$q = $dbo->getQuery(true);

			$q->select($dbo->qn('r.id'));
			$q->select($dbo->qn('r.sid'));
			$q->select($dbo->qn('r.purchaser_nominative'));
			$q->select($dbo->qn('r.purchaser_mail'));
			$q->select($dbo->qn('r.purchaser_phone'));
			$q->select($dbo->qn('r.checkin_ts'));
			$q->select($dbo->qn('r.people'));
			$q->select($dbo->qn('r.rescode'));
			$q->select($dbo->qn('t.name', 'tname'));

			$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'));

			$q->where($dbo->qn('r.id') . ' IN (' . implode(',', $ids) . ')');

			if ($operator->get('rooms'))
			{
				// rooms are already separated by a comma
				$q->where($dbo->qn('t.id_room') . ' IN (' . $operator->get('rooms') . ')');
			}

			// check if the operator can see all the reservations
			if (!$operator->canSeeAll())
			{
				// check if the operator can self-assign reservations
				if ($operator->canAssign())
				{
					// retrieve reservation if already assigned to this operator
					// or whether it is free of assignment
					$q->andWhere($dbo->qn('r.id_operator') . ' IN (0, ' . (int) $operator->get('id') . ')');
				}
				else
				{
					// retrieve the reservation only if assigned to the operator
					$q->where($dbo->qn('r.id_operator') . ' = ' . (int) $operator->get('id'));
				}
			}

			$q->order($dbo->qn('id') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$rows = $dbo->loadAssocList();
			}
			else
			{
				$rows = array();
			}

			$this->rows = &$rows;

			// display list of reservations
			$this->setLayout('list');
		}
		else
		{
			$reservation = array();
			
			if ($ids)
			{
				$q = $dbo->getQuery(true)
					->select('*')
					->from($dbo->qn('#__vikrestaurants_reservation'))
					->where($dbo->qn('id') . ' = ' . $ids[0]);

				// check if the operator can see all the reservations
				if (!$operator->canSeeAll())
				{
					// check if the operator can self-assign reservations
					if ($operator->canAssign())
					{
						// retrieve reservation if already assigned to this operator
						// or whether it is free of assignment
						$q->andWhere($dbo->qn('id_operator') . ' IN (0, ' . (int) $operator->get('id') . ')');
					}
					else
					{
						// retrieve the reservation only if assigned to the operator
						$q->where($dbo->qn('id_operator') . ' = ' . (int) $operator->get('id'));
					}
				}

				$dbo->setQuery($q, 0, 1);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$reservation = $dbo->loadObject();

					// make sure the operator can access the table
					if (!$operator->canAccessTable($reservation->id_table))
					{
						throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
					}
					
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

			if ($reservation->stay_time == 0)
			{
				$reservation->stay_time = VREFactory::getConfig()->get('averagetimestay');
			}
			
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

			if ($operator->get('rooms'))
			{
				// rooms are already separated by a comma
				$q->where($dbo->qn('t.id_room') . ' IN (' . $operator->get('rooms') . ')');
			}
			
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

			$cfields = VRCustomFields::getList(0, VRCustomFields::FILTER_EXCLUDE_REQUIRED_CHECKBOX | VRCustomFields::FILTER_EXCLUDE_SEPARATOR);

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
			$this->customer     = &$customer;
		}

		$this->operator = &$operator;

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

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
		$date = $input->getString('date', null);
		$time = $input->getString('hourmin', null);

		/**
		 * Find closest date and time.
		 *
		 * @since 1.7.4
		 */
		if ($date === null || $time === false)
		{
			$time = VikRestaurants::getClosestTime($date, $next = true);

			// re-convert date timestamp to string
			$date = date(VREFactory::getConfig()->get('dateformat'), $date);

			if (!$time)
			{
				$time = '0:0';
			}
		}

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
			'id_operator'          => 0,
			'stay_time'            => 0, // do not force time of stay
			'closure'              => 0,
			'custom_f'             => array(),
		);
	}
}
