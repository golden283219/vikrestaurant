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
jimport('joomla.html.pagination');

/**
 * VikRestaurants customer info summary view.
 *
 * @since 1.3
 */
class VikRestaurantsViewcustomerinfo extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		// force blank component template
		$input->set('tmpl', 'component');
		
		$id = $input->get('id', 0, 'uint');

		// check if the location fieldset should be shown
		$locations = (bool) $input->get('locations', 1, 'uint');
		
		// load customer data
		$customer = VikRestaurants::getCustomer($id);

		if (!$customer)
		{
			throw new Exception('Customer not found', 404);
		}

		$this->customer     = &$customer;
		$this->hasLocations = $locations && $customer->locations;

		$langtag = JFactory::getLanguage()->getTag();

		// get restaurant reservations made by the customer
		$rs_start = $this->getListLimitStart(array(), $id, 'restaurant');
		$rs_limit = 10;
		$rs_nav   = '';
		$rs_count = 0;
		$rs_total = 0;

		$reservations = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS ' . $dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_reservation'))
			->where($dbo->qn('id_user') . ' = ' . $customer->id)
			->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'))
			->where($dbo->qn('id_parent') . ' = 0')
			->order($dbo->qn('id') . ' DESC');

		$totalQuery = $q;

		$dbo->setQuery($q, $rs_start, $rs_limit);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$column = $dbo->loadColumn();

			// get total number of reservations
			$dbo->setQuery('SELECT FOUND_ROWS();');
			$rs_count = (int) $dbo->loadResult();

			// iterate reservations found
			foreach ($column as $id_res)
			{
				// get reservation details
				$reservations[] = VREOrderFactory::getReservation($id_res, $langtag);
			}

			// create restaurant pagination
			$pageNav = new JPagination($rs_count, $rs_start, $rs_limit, 'restaurant');
			$rs_nav = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';

			// calculate total spent
			$totalQuery->clear('select')
				->select(sprintf(
					'SUM(IF (%1$s > %2$s, %1$s, %2$s)) AS %3$s',
					$dbo->qn('bill_value'),
					$dbo->qn('deposit'),
					$dbo->qn('total')
				));

			$dbo->setQuery($q);
			$dbo->execute();

			$rs_total = (float) $dbo->loadResult();
		}

		$this->reservations      = &$reservations;
		$this->restaurantNav     = &$rs_nav;
		$this->totalReservations = &$rs_count;
		$this->restaurantTotal   = &$rs_total;

		// get take-away orders made by the customer
		$tk_start = $this->getListLimitStart(array(), $id, 'takeaway');
		$tk_limit = 10;
		$tk_nav   = '';
		$tk_count = 0;
		$tk_total = 0;

		$orders = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS ' . $dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_takeaway_reservation'))
			->where($dbo->qn('id_user') . ' = ' . $customer->id)
			->where($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'))
			->order($dbo->qn('id') . ' DESC');

		$totalQuery = $q;

		$dbo->setQuery($q, $tk_start, $tk_limit);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$column = $dbo->loadColumn();

			// get total number of orders
			$dbo->setQuery('SELECT FOUND_ROWS();');
			$tk_count = (int) $dbo->loadResult();

			// iterate orders found
			foreach ($column as $id_res)
			{
				// get order details
				$orders[] = VREOrderFactory::getOrder($id_res, $langtag);
			}

			// create take-away pagination
			$pageNav = new JPagination($tk_count, $tk_start, $tk_limit, 'takeaway');
			$tk_nav = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';

			// calculate total spent
			$totalQuery->clear('select')
				->select(sprintf('SUM(%s) AS %s', $dbo->qn('total_to_pay'), $dbo->qn('total')));

			$dbo->setQuery($q);
			$dbo->execute();

			$tk_total = (float) $dbo->loadResult();
		}

		$this->orders        = &$orders;
		$this->takeawayNav   = &$tk_nav;
		$this->totalOrders   = &$tk_count;
		$this->takeawayTotal = &$tk_total;

		// display the template
		parent::display($tpl);
	}
}
