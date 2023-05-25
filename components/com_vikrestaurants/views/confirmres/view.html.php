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
 * VikRestaurants restaurant reservation confirmation view.
 * Displayed only once the search results have been confirmed.
 * We are now able to see the selected table and, if supported,
 * the menus.
 *
 * @since 1.0
 */
class VikRestaurantsViewconfirmres extends JViewVRE
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
		
		$args = array();
		$args['date']    = $input->getString('date');
		$args['hourmin'] = $input->getString('hourmin');
		$args['people']  = $input->getUint('people');
		$args['table']   = $input->getUint('table', 0);

		// instantiate availability search
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $args['people']);

		// inject hours and minutes within $args
		$args['hour'] = $search->get('hour');
		$args['min']  = $search->get('min');

		// create time object based on check-in time
		$checkin = JHtml::_('vikrestaurants.min2time', $args['hour'] * 60 + $args['min'], $string = false);
		// include timestamp
		$checkin->ts = VikRestaurants::createTimestamp($args['date'], $args['hour'], $args['min']);

		// get selected table
		$table = $search->getTable($args['table']);

		if (!$table)
		{
			// make sure the table exists
			throw new Exception(sprintf('Invalid [%d] table', $args['table']), 500);
		}

		$table->room = new stdClass;
		$table->room->id   = $table->id_room;
		$table->room->name = $table->room_name;

		// translate room in case multi-lingual is supported
		VikRestaurants::translateRooms($table->room);

		// get payment gateways (1: restaurant group)
		$payments = VikRestaurants::getAvailablePayments(1);
		
		// get custom fields (0: restaurant)
		$customFields = VRCustomFields::getList($group = 0);
		// translate fields
		VRCustomFields::translate($customFields);

		// check if the system uses the coupon codes for the restaurant (0)
		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_coupons'))
			->where($dbo->qn('group') . ' = 0');

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		// coupon used in case the query returned something
		$any_coupon = (bool) $dbo->getNumRows();
		
		// get current customer details, if any
		$user = VikRestaurants::getCustomer();

		// get total deposit to leave
		$deposit = VikRestaurants::getTotalDeposit($args);

		/**
		 * An associative array containing the check-in details,
		 * such as: date, hourmin, people and table.
		 * 
		 * @var array
		 */
		$this->args = &$args;

		/**
		 * The time object for the selected check-in time.
		 *
		 * @var object
		 */
		$this->checkinTime = &$checkin;

		/**
		 * An object containined the details of the table
		 * that have been selected in the previous step.
		 *
		 * @var object
		 */
		$this->table = &$table;
		
		/**
		 * An array of custom fields to use for collecting
		 * the billing details of the customer.
		 *
		 * @var array
		 */
		$this->customFields = &$customFields;

		/**
		 * A list of payments available for the purchase.
		 *
		 * @var array
		 */
		$this->payments = &$payments;

		/**
		 * Flag used to check whether the restaurant
		 * section of the websites uses the coupons.
		 *
		 * @var boolean
		 */
		$this->anyCoupon = &$any_coupon;

		/**
		 * The billing details of the logged-in user.
		 *
		 * @var object|null
		 */
		$this->user = &$user;

		/**
		 * The total deposit to leave to confirm the
		 * restaurant reservation.
		 *
		 * @var float
		 */
		$this->totalDeposit = &$deposit;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}	
}
