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
 * VikRestaurants restaurant reservation search view.
 * Within this view is displayed the search results
 * of the request made, usually through the "restaurants"
 * view or with the "search" module. 
 *
 * @since 1.0
 */
class VikRestaurantsViewsearch extends JViewVRE
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
		
		// get search arguments
		$args = array();
		$args['date']    = $input->get('date', '', 'string');
		$args['hourmin'] = $input->get('hourmin', '', 'string');
		$args['people']  = $input->get('people', 0, 'uint');

		// get selected room
		$selected_room = $input->get('room', 0, 'uint');

		// it is not needed to check the integrity of the searched
		// arguments because thay have been already validated by
		// the controller before accessing this view

		/**
		 * Look for COVID19 prevention measures.
		 *
		 * @since 1.8
		 *
		 * @see COVID-19
		 */
		$people = VikRestaurants::getPeopleSafeDistance($args['people']);

		// instantiate availability search
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $people);

		// inject hours and minutes within $args
		$args['hour'] = $search->get('hour');
		$args['min']  = $search->get('min');

		// get all the tables available for the specified search arguments
		$avail = $search->getAvailableTables();

		// split standard tables from shared tables
		$standard = $shared = array();

		foreach ($avail as $table)
		{
			if (!$table->multi_res)
			{
				$standard[] = $table;
			}
			else
			{
				$shared[] = $table;
			}
		}

		// first attempt, try to search for a free STANDARD table
		$attempt = 1;
		
		if (count($standard) == 0)
		{
			// second attempt, try to search for a free SHARED table
			$attempt++;
			
			if (count($shared) == 0)
			{
				// third attempt, no available tables
				$attempt++;
			}
			
			// Elaborate time hints.
			// The method will return the first 2 available times before
			// the selected check-in time and the next 2. Such as:
			// 12:00 | 12:30 | CURRENT | 13:30 | 14:30
			// It is possible to pass a number to the function below
			// to increase/decrease the number of suggested times.
			$hints = $search->getSuggestedTimes();
		}
		else
		{
			$hints = null;
		}

		// create time object based on check-in time
		$timeslot = JHtml::_('vikrestaurants.min2time', $args['hour'] * 60 + $args['min'], $string = false);
		// include timestamp
		$timeslot->ts = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);

		// in case of table selection, we need to count all
		// the guests assigned to each shared table
		if (VikRestaurants::getReservationRequirements() == 0)
		{
			// obtain lookup with table ID (key) and
			// number of guests (value)
			$occurrences = $search->getTablesOccurrence();
		}
		else
		{
			$occurrences = array();
		}

		// get all rooms with related tables
		$rooms = $search->getRooms();

		// translate rooms in case multi-lingual is supported
		VikRestaurants::translateRooms($rooms);

		// iterate all rooms tables
		foreach ($rooms as $i => $rm)
		{
			// prepare tables attributes
			foreach ($rm->tables as $k => $table)
			{
				$table->available = 0;
				// check if the table is contained within the available list
				for ($j = 0; $j < count($avail) && $table->available == 0; $j++)
				{
					$table->available = $table->id == $avail[$j]->id ? 1 : 0;
				}

				// set table occurrence, if exists
				if (isset($occurrences[$table->id]))
				{
					$table->occurrency = $occurrences[$table->id];
				}
				else
				{
					$table->occurrency = 0;
				}

				// update table in list
				$rooms[$i]->tables[$k] = $table;
			}
		}

		// room already selected
		$step = 1;

		if ($rooms && $avail)
		{
			if (!$selected_room)
			{
				// pre-select first room available in case
				// the request doesn't contain the room ID
				$selected_room = $avail[0]->id_room;

				// room not selected
				$step = 0;
			}
		}
		
		// make sure the selected room exists
		foreach ($rooms as $rm)
		{
			if ($rm->id == $selected_room)
			{
				// room found, assign object
				$selected_room = $rm;
			}
		}

		if ($avail && is_scalar($selected_room))
		{
			// throw exception in case the room was not found
			throw new Exception('Room not found', 404);
		}
		
		$menus = array();

		// check if the customers are allowed to choose menu
		if (VikRestaurants::isMenusChoosable($args))
		{
			// Get menus available for the selected date and time.
			// Obtain only the menus that can effectively be chosen.
			$menus = VikRestaurants::getAllAvailableMenusOn($args, $choosable = true);
		}

		// translate menus in case multi-lingual is supported
		VikRestaurants::translateMenus($menus);

		/**
		 * An associative array containing the check-in details,
		 * such as: date, hourmin and people.
		 * 
		 * @var array
		 */
		$this->args = &$args;

		/**
		 * A list of tables available for the selected check-in.
		 *
		 * @var array
		 */
		$this->avail = &$avail;

		/**
		 * A list of suggested times close to the selected check-in.
		 * By default, the first 2 are before the selected time, the
		 * other ones are after the selected time.
		 *
		 * @var array|null
		 */
		$this->hints = &$hints;

		/**
		 * The time object for the selected check-in time.
		 *
		 * @var object
		 */
		$this->checkinTime = &$timeslot;

		/**
		 * The search attempt identifier.
		 * - 1: a standard table is available
		 * - 2: only shared tables are available
		 * - 3: no available tables
		 *
		 * @var integer
		 */
		$this->attempt = &$attempt;

		/**
		 * The current step of the search process.
		 * - 0: click button to display available rooms;
		 * - 1: rooms already selected, scroll down to tables.
		 *
		 * @var integer
		 */
		$this->step = &$step;

		/**
		 * The selected room object.
		 *
		 * @var object|null
		 */
		$this->selectedRoom = &$selected_room;

		/**
		 * A list of published rooms.
		 * Each room contains its own tables.
		 *
		 * @var array
		 */
		$this->rooms = &$rooms;

		/**
		 * A lookup containing the total count of guests (value)
		 * for each table (key).
		 *
		 * @var array
		 */
		$this->occurrences = &$occurrences;

		/**
		 * A list of menus that can be chosen by the customers
		 * during the booking process, if any.
		 *
		 * @var array
		 */
		$this->menus = &$menus;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}
}
