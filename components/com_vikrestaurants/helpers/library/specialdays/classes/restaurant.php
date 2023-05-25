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
 * Wrapper class to handle the properties of a special day
 * for the restaurant group.
 *
 * @since 1.8
 */
class VRESpecialDayRestaurant extends VRESpecialDay
{
	/**
	 * The deposit amount to leave.
	 *
	 * @var float
	 */
	protected $deposit;

	/**
	 * Flag used to check whether the deposit have
	 * to be left for each person in the party.
	 *
	 * @var boolean
	 */
	protected $depositPerPerson;

	/**
	 * The minimum number of people needed to ask
	 * for the deposit.
	 *
	 * @var integer
	 */
	protected $depositGuests;

	/**
	 * The total number of people allowed per interval.
	 *
	 * @var null|integer
	 */
	protected $allowedPeople;

	/**
	 * Flag used to check whether the customers are allowed
	 * to pick the menus during the booking process.
	 *
	 * @var boolean
	 */
	protected $chooseMenu;

	/**
	 * Flag used to check whether the customers are allowed
	 * to pick different menus.
	 *
	 * @var boolean
	 */
	protected $choiceFreedom;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed  $args  Either an array or an object containing
	 * 						  the properties of the special day record.
	 */
	public function __construct($args)
	{
		// let parent initialize the object first
		parent::__construct($args);

		$args = (object) $args;

		// get deposit cost
		$this->deposit = abs((float) $args->depositcost);

		// check deposit per person
		$this->depositPerPerson = (bool) $args->perpersoncost;

		// get minimum number of guests for deposit
		$this->depositGuests = $args->askdeposit;

		// get allowed people
		$this->allowedPeople = $args->peopleallowed >= 0 ? (int) $args->peopleallowed : null;

		// check if menus are choosable
		$this->chooseMenu = (bool) $args->choosemenu;

		// check if menus are choosable
		$this->choiceFreedom = (bool) $args->freechoose;
	}

	/**
	 * Returnes the total deposit to leave depending on
	 * the number of people in the party.
	 *
	 * @param 	integer  $people  The party size.
	 *
	 * @return 	float 	 The total amount.
	 */
	public function getTotalDeposit($people)
	{
		if ($this->depositGuests == 0 || $people < $this->depositGuests)
		{
			// never apply deposit in case it is disabled or in
			// case the number of people is lower than the specified amount
			return 0;
		}

		if ($this->depositPerPerson === false)
		{
			// use 1 in case the deposit is a fixed amount
			$people = 1;
		}

		return $this->deposit * max(array(1, abs((int) $people)));
	}

	/**
	 * Checks whether the specified number of people
	 * is still accepted according to the allowed
	 * people threshold and the current number of guests.
	 *
	 * @param 	array 	 $args  An associative array containing the checkin
	 * 							details: date, hourmin, people.
	 *
	 * @return 	boolean  True if allowed, false otherwise.
	 */
	public function canHostPeople($args)
	{
		if (is_null($this->allowedPeople))
		{
			// never block in case of no threshold
			return true;
		}

		// init availability search
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $args['people']);

		// get tables occurrences
		$tables = $search->getTablesOccurrence();

		// calculate total number of guests (include requested arguments)
		$guests = array_sum(array_values($tables)) + $args['people'];

		// make sure the number of guests doesn't exceed the threshold
		return $this->allowedPeople >= $guests;
	}

	/**
	 * @override
	 * Returns a list of menus assigned to the special day.
	 *
	 * @param 	boolean  $objects  True to return the database objects.
	 *
	 * @return 	array
	 *
	 * @since 	1.8.2
	 */
	public function getMenus($objects = false)
	{
		// obtain menus from parent
		$menus = parent::getMenus();

		if ($objects)
		{
			// retrieve menu details from database
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_menus'))
				->where($dbo->qn('published') . ' = 1')
				->order($dbo->qn('ordering') . ' ASC');

			if ($menus)
			{
				// search for assigned menus
				$q->where($dbo->qn('id') . ' IN (' . implode(',', $menus) . ')');
			}

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$menus = $dbo->loadObjectList();
			}
			else
			{
				$menus = array();
			}
		}

		return $menus;
	}
}
