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
 * VikRestaurants take-away order confirmation view.
 * Displayed only after adding some food into the cart.
 *
 * @since 1.2
 */
class VikRestaurantsViewtakeawayconfirm extends JViewVRE
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
		
		$config = VREFactory::getConfig();

		// get cart instance
		$cart = TakeAwayCart::getInstance();
		
		$args = array();
		$args['date']     = $input->get('date', '', 'string');
		$args['hourmin']  = $input->get('hourmin', '', 'string');
		$args['delivery'] = $input->get('delivery', null, 'uint');

		// get payment gateways (2: take-away group)
		$payments = VikRestaurants::getAvailablePayments(2);
		
		// get custom fields (1: take-away)
		$customFields = VRCustomFields::getList($group = 1);
		// translate fields
		VRCustomFields::translate($customFields);

		// check if the system uses the coupon codes for the take-away (0)
		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_coupons'))
			->where($dbo->qn('group') . ' = 1');

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		// coupon used in case the query returned something
		$any_coupon = (bool) $dbo->getNumRows();

		// obtain all the available times for pick-up and delivery
		$times = JHtml::_('vikrestaurants.takeawaytimes', $args['date'], $cart);

		if (!$args['hourmin'] && $times)
		{
			// use first time available in case there is no selected time
			$sh = reset($times);
			$args['hourmin'] = $sh[0]->value;
		}

		// init special days manager
		$sdManager = new VRESpecialDaysManager('takeaway');
		// set checkin date
		$sdManager->setStartDate($args['date']);
		// get special days
		$sdList = $sdManager->getList();

		// flags used to check what type of service is allowed
		$delivery = $pickup = null;

		// flag used to check whether the delivery service ever changes
		$same_service = true;
		// flag used to check whether the special days use the same menus
		$same_menus = true;
		// flag used to check whether the special days accept the same areas
		$same_areas = true;

		if ($sdList)
		{
			for ($i = 0; $i < count($sdList) - 1; $i++)
			{
				// compare delivery service configuration between current
				// special day and the next one
				$same_service = $same_service && $sdList[$i]->hasSameService($sdList[$i + 1]);
				// compare the menus configuration between current
				// special day and the next one
				$same_menus = $same_menus && $sdList[$i]->menus == $sdList[$i + 1]->menus;
				// compare the accepted areas between the current
				// special days and the next one
				$same_areas = $same_areas && $sdList[$i]->deliveryAreas == $sdList[$i + 1]->deliveryAreas;
			}

			if ($same_service)
			{
				// all the special days share the same delivery service
				// configuration, extract delivery and pickup from the
				// first special day available
				$delivery = $sdList[0]->delivery;
				$pickup   = $sdList[0]->pickup;
			}
			else
			{
				// filter special days by check-in time in order
				// to figure out what's the delivery service to
				// use for the selected time
				$sdManager->setCheckinTime($args['hourmin']);

				// get first special day available
				$sd = $sdManager->getFirst();

				if ($sd)
				{
					// set up delivery/pickup service
					$delivery = $sd->delivery;
					$pickup   = $sd->pickup;
				}
			}
		}
		
		// get delivery service flag from configuration
		$service = $config->getUint('deliveryservice');

		if (is_null($delivery))
		{
			// unable to fetch delivery service from special days,
			// rely on default configuration
			$delivery = $service == 1 || $service == 2;
		}

		if (is_null($pickup))
		{
			// unable to fetch pickup service from special days,
			// rely on default configuration
			$pickup = $service == 0 || $service == 2;
		}

		if (!in_array($args['delivery'], array(0, 1), true))
		{
			// unable to fetch delivery service from special days,
			// rely on default configuration
			$args['delivery'] = $service == 1 || $service == 2;
		}

		// validate selected service
		if ($args['delivery'] == 1 && !$delivery)
		{
			// use pickup because delivery is disabled
			$args['delivery'] = 0;
		}

		if ($args['delivery'] == 0 && !$pickup)
		{
			// use delivery because pickup is disabled
			$args['delivery'] = 1;
		}

		/**
		 * Scan all the available deals to check whether they
		 * differ in service or shift.
		 *
		 * @since 1.8
		 */
		$deals = DealsHandler::getAvailableDeals(0);

		$same_deal_shift   = true;
		$same_deal_service = true;

		for ($i = 0; $i < count($deals); $i++)
		{
			// selected shifts and services must not be filtered
			$same_deal_shift   = $same_deal_shift && !$deals[$i]['shifts'];
			$same_deal_service = $same_deal_service && $deals[$i]['service'] == 2;
		}
		
		/**
		 * Check whether the maximum number of orders restriction
		 * applies to both the services or not. In case of a specific
		 * selection we should refresh the page every time the service
		 * changes, in order to recalculate the available times.
		 *
		 * @since 1.8.3
		 */
		$same_max_ord_service_restr = $config->getUint('tkordmaxser') == 2;

		// get current user details
		$user = VikRestaurants::getCustomer();

		/**
		 * An associative array containing the check-in details,
		 * such as: date, hourmin and delivery service.
		 * 
		 * @var array
		 */
		$this->args = &$args;
		
		/**
		 * The current cart instance.
		 *
		 * @var TakeAwayCart
		 */
		$this->cart = &$cart;

		/**
		 * A list of available times.
		 *
		 * @var array
		 */
		$this->times = &$times;

		/**
		 * Flag used to check whether the delivery service
		 * is enabled for the purchase.
		 *
		 * @var boolean
		 */
		$this->delivery = &$delivery;

		/**
		 * Flag used to check whether the pickup service
		 * is enabled for the purchase.
		 *
		 * @var boolean
		 */
		$this->pickup = &$pickup;

		/**
		 * Flag used to check whether it is needed to refresh the page
		 * every time the user changes something within the form.
		 *
		 * @var boolean
		 */
		$this->refreshTimeNeeded = $same_service == false || $same_menus == false || $same_areas == false || $same_deal_shift == false;

		/**
		 * Flag used to check whether it is needed to refresh the page
		 * every time the user changes type of service.
		 *
		 * @var boolean
		 */
		$this->refreshServiceNeeded = $same_deal_service == false || $same_max_ord_service_restr == false;

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
		
		// display the template
		parent::display($tpl);
	}
}
