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
 * VikRestaurants take-away order status tracker view.
 *
 * @since 	1.7
 */
class VikRestaurantsViewtrackorder extends JViewVRE
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
		
		$oid = $input->get('oid', 0, 'uint');
		$sid = $input->get('sid', '', 'alnum');
		$tid = ($input->get('tid', 1, 'uint') == 0 ? 1 : 2);

		try
		{
			if ($tid == 1)
			{
				// get restaurant reservation
				$order = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));
			}
			else
			{
				// get take-away order
				$order = VREOrderFactory::getOrder($oid, null, array('sid' => $sid));
			}

			// get order history
			$history = $order->history;
		}
		catch (Exception $e)
		{
			// in case the order doesn't exist, an exception will be thrown
			$order   = null;
			$history = array();
		}

		if ($history)
		{
			// group statuses by day
			$app = array();

			foreach ($history as $status)
			{
				// get day timestamp at midnight
				$day = strtotime('00:00:00', $status->createdon);

				if (empty($app[$day]))
				{
					// create group only if not exists
					$app[$day] = array();
				}

				$app[$day][] = $status;
			}

			// overwrite history
			$history = $app;
		}

		$this->history = &$history;
		$this->order   = &$order;
		
		// display the template
		parent::display($tpl);
	}
}
