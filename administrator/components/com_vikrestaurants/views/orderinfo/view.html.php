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
 * VikRestaurants reservation summary view.
 *
 * @since 1.0
 */
class VikRestaurantsVieworderinfo extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$input = JFactory::getApplication()->input;

		// force blank component layout
		$input->set('tmpl', 'component');
		
		$id = $input->get('id', 0, 'uint');

		// check the status of the reservation first
		VikRestaurants::removeRestaurantReservationsOutOfTime($id);
		
		// get order details
		$order = VREOrderFactory::getReservation($id, JFactory::getLanguage()->getTag());

		if (!$order)
		{
			throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
		}
		
		$this->order = &$order;

		// create search instance
		$search = new VREAvailabilitySearch(
			$order->checkin_ts,
			date('H:i', $order->checkin_ts)
		);

		// set up time of stay
		$search->setStayTime($order->stay_time);

		// take only the ID of the tables
		$occupancy = array_keys($search->getTablesOccurrence());

		// load existing tables
		$existing = array_map(function($t)
		{
			return $t->id;
		}, $order->tables);

		// subtract the tables set to the reservation in order
		// to allow their selection
		$this->occupiedTables = array_values(array_diff($occupancy, $existing));

		// display the template
		parent::display($tpl);
	}
}
