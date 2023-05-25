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
 * VikRestaurants maps view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmaps extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		/**
		 * Loads dedicated CSS file.
		 *
		 * @since 1.7.4
		 */
		VREApplication::getInstance()->addStyleSheet(VREASSETS_URI . 'css/oversight.css');

		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();
		$config = VREFactory::getConfig();
		
		// set the toolbar
		$this->addToolBar();

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

		// use local time
		$now = VikRestaurants::now();

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
		
		$this->rooms 					 = &$rooms;
		$this->tables 					 = &$allRoomTables;
		$this->selectedRoomId 			 = &$id_room;
		$this->roomHeight 				 = &$roomHeight;
		$this->filters 					 = &$filters;
		$this->reservationTableOnDate 	 = &$rows;
		$this->allSharedTablesOccurrency = &$allSharedTablesOccurrency;
		$this->currentReservations 		 = &$current_res;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWMAPS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::custom('map.edit', 'edit', 'edit', JText::_('VREDIT'), false);
		}
	}
}
