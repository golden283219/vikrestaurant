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
 * VikRestaurants oversight view.
 * Gives access to the private area of the
 * operators in the front-end.
 *
 * @since 1.6
 */
class VikRestaurantsViewoversight extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return void
	 */
	function display($tpl = null)
	{	
		////// LOGIN //////
		
		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin();
		
		/**
		 * The operator details if exists, false otherwise.
		 *
		 * @var array|boolean
		 */
		$this->operator = $operator ? $operator->getProperties() : false;

		/**
		 * Flag used to check whether the operator
		 * can access this area.
		 *
		 * @var boolean
		 */
		$this->ACCESS = &$access;

		/**
		 * The operator user instance if exists, false otherwise.
		 *
		 * @var VREOperatorUser|boolean
		 */
		$this->user = $operator;
		
		////// MANAGEMENT //////
		
		if ($access)
		{
			// retrieve selected group from request
			$group = JFactory::getApplication()->input->getUint('group', 1);

			// validate requested group
			if ($group == 1 && !$operator->isRestaurantAllowed())
			{
				// restaurant selected but not allowed, revert to take-away
				$group = 2;
			}

			// validate requested group
			if ($group == 2 && !$operator->isTakeawayAllowed())
			{
				// take-away selected but not allowed, revert to restaurant
				$group = 1;
			}

			if ($group == 1)
			{
				$this->loadRestaurantContents();
				$tpl = 'restaurant';

				/**
				 * Loads dedicated CSS file.
				 *
				 * @since 1.7.4
				 */
				VREApplication::getInstance()->addStyleSheet(VREASSETS_URI . 'css/oversight.css');
			}
			else
			{
				$this->loadTakeawayContents();
				$tpl = 'takeaway';
			}
		}

		// prepare page content
		VikRestaurants::prepareContent($this);

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

		// display the template
		parent::display($tpl);
	}

	/**
	 * Loads the contents for the dashboard of the restaurant.
	 *
	 * @return 	void
	 */
	private function loadRestaurantContents()
	{
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();
		$config = VREFactory::getConfig();

		$id_room = $app->getUserStateFromRequest('vre.map.selectedroom', 'selectedroom', 0, 'uint');

		$_df = $app->getUserStateFromRequest('vre.map.datefilter', 'datefilter', '', 'string');
		$_hm = $app->getUserStateFromRequest('vre.map.hourmin', 'hourmin', '', 'string');
		$_pl = $app->getUserStateFromRequest('vre.map.people', 'people', 1, 'uint');
		
		$_df_ts = VikRestaurants::createTimestamp($_df, 0, 0);

		if (strlen($_df) == 0 || $_df_ts == -1)
		{
			$_df_ts = VikRestaurants::now();
		}

		$_df = date($config->get('dateformat'), $_df_ts);
		
		$_hm_exp = explode(':', $_hm);

		$args = array(
			'date'    => $_df,
			'hourmin' => $_hm,
		);

		if (count($_hm_exp) != 2 || !VikRestaurants::isHourBetweenShifts($args, 1))
		{
			$_hm = VikRestaurants::getFirstAvailableHour();
			$_hm_exp = explode(':', $_hm);
		}
		
		if (VikRestaurants::getMinimumPeople() > $_pl || VikRestaurants::getMaximumPeople() < $_pl)
		{
			$_pl = max(array(2, VikRestaurants::getMinimumPeople())); // 2 or higher
		}
		
		$filters = array(
			'date' 		=> $_df,
			'hourmin' 	=> $_hm,
			'people' 	=> $_pl,
			'hour' 		=> $_hm_exp[0],
			'min' 		=> $_hm_exp[1],
		);

		/**
		 * Find closest time for current day.
		 * Only if the time wasn't submitted through the form.
		 *
		 * @since 1.7.4
		 */
		if (JDate::getInstance()->format(VikRestaurants::getDateFormat()) == $filters['date']
			&& VikRestaurants::isTimePast($filters)
			&& !$input->getBool('formsubmitted'))
		{
			// same day, try to fetch the closest time
			$tmp = VikRestaurants::getClosestTime();

			if ($tmp)
			{
				// new hours and minutes, update $filters
				$_hm_exp = explode(':', $tmp);

				$filters['hourmin'] = $tmp;
				$filters['hour'] 	= $_hm_exp[0];
				$filters['min']  	= $_hm_exp[1];
			}
		}
		
		$rooms = array();
		
		$res_ts = VikRestaurants::createTimestamp($filters['date'], $filters['hour'], $filters['min']);
		
		$q = "SELECT `rm`.*, (
			SELECT COUNT(1)
			FROM `#__vikrestaurants_room_closure` AS `rc`
		 	WHERE `rc`.`id_room`=`rm`.`id` AND `rc`.`start_ts` <= $res_ts AND $res_ts < `rc`.`end_ts` LIMIT 1
		) AS `isClosed` 
		FROM `#__vikrestaurants_room` AS `rm`
		ORDER BY `rm`.`ordering`";

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$rooms = $dbo->loadObjectList();

			if ($this->user->get('rooms'))
			{
				$tmp = explode(',', $this->user->get('rooms'));

				// make sure the selected room is supported
				if (!in_array($id_room, $tmp))
				{
					// unset room
					$id_room = 0;
				}

				// take only the rooms supported by the operator
				$rooms = array_values(array_filter($rooms, function($room) use ($tmp)
				{
					return in_array($room->id, $tmp);
				}));
			}

			foreach ($rooms as &$room)
			{
				$room->graphics_properties = json_decode($room->graphics_properties);
			}

			/**
			 * Always use the first room available.
			 *
			 * @since 1.7.4
			 */
			if ($id_room <= 0)
			{
				$id_room = $rooms[0]->id;
			}
		}
		
		$allRoomTables = array();
		
		if ($id_room > 0)
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_table'))
				->where($dbo->qn('id_room') . ' = ' . $id_room);

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$allRoomTables = $dbo->loadObjectList();

				foreach ($allRoomTables as &$table)
				{
					$table->design_data = json_decode($table->design_data);
				}
			}
		}
		
		// Create availability search.
		// Do not use ADMIN permissions to properly display the tables as unavailable
		// when they are unpublished or in case they belong to a closed room.
		// Even if the tables are unavailable, they are still bookable through the MAPS view.
		$search = new VREAvailabilitySearch($filters['date'], $filters['hourmin'], $filters['people'], $admin = false);

		// get all available tables
		$rows = $search->getAvailableTables();
		
		// calculates tables occurrence
		$allSharedTablesOccurrency = $search->getTablesOccurrence();

		// get current reservations
		$current_res = $search->getReservations();

		$operator = $this->user;

		/**
		 * Take only the reservations that can be managed by the operator.
		 *
		 * @since 1.8
		 */
		$current_res = array_values(array_filter($current_res, function($res) use ($operator)
		{
			if ($operator->canSeeAll())
			{
				// operator can access all the reservations
				return true;
			}

			// take reservation if already assigned to this operator
			// or whether it is free of assignment
			if ($operator->canAssign())
			{
				// see reservation if not 
				return in_array($res->id_operator, array(0, $operator->get('id')));
			}

			// only take reservations assigned to this operator
			return $res->id_operator == $operator->get('id');
		}));

		// use local time
		$now = VikRestaurants::now();

		$vik = VREApplication::getInstance();

		foreach ($current_res as &$res)
		{
			if (empty($res->stay_time))
			{
				$res->stay_time = $config->getUint('averagetimestay');
			}

			$res->checkin_date = date($config->get('dateformat'), $res->checkin_ts);
			$res->checkin_time = date($config->get('timeformat'), $res->checkin_ts);
			$res->checkin 	   = $res->checkin_date . ' ' . $res->checkin_time;

			if ($res->checkin_ts <= $now && $now < $res->checkin_ts + (int) $res->stay_time * 60)
			{
				$res->time_left = $res->checkin_ts + $res->stay_time * 60 - $now;
			}

			// strip HTML from notes
			$res->notes = strip_tags($res->notes);
		}
		
		// get reservation codes
		$all_res_codes = JHtml::_('vikrestaurants.rescodes', 1);
		
		$this->rooms 						= &$rooms;
		$this->tables 						= &$allRoomTables;
		$this->selectedRoomId 				= &$id_room;
		$this->filters 						= &$filters;
		$this->reservationTableOnDate 		= &$rows;
		$this->allSharedTablesOccurrency 	= &$allSharedTablesOccurrency;
		$this->currentReservations 			= &$current_res;
		$this->allResCodes 					= &$all_res_codes;
		$this->timeOk 						= true;
	}

	/**
	 * Loads the contents for the dashboard of the take-away.
	 *
	 * @return 	void
	 */
	private function loadTakeawayContents()
	{
		// nothing to load
	}
}
