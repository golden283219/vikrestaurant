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
 * VikRestaurants credit card details view.
 *
 * @since 1.7
 */
class VikRestaurantsViewccdetails extends JViewVRE
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
		
		$id    = $input->get('id', 0, 'uint');
		$group = $input->get('tid', 0, 'uint');

		// check whether the credit card details should be removed
		$real_hash = $this->checkForRemove($id, $group);

		// get credit card details
		$table = '#__vikrestaurants_' . ($group == 0 ? '' : 'takeaway_') . 'reservation';
		
		$q = $dbo->getQuery(true)
			->select($dbo->qn('checkin_ts'))
			->select($dbo->qn('cc_details'))
			->from($dbo->qn($table))
			->where($dbo->qn('id') . ' = ' . $id);
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$order = $dbo->loadObject();

			// extract credit card from order
			$card = $this->getCreditCard($order);
		}
		else
		{
			$order = null;
		}

		$this->creditCard = &$card;
		$this->order      = &$order;

		$this->id     = &$id;
		$this->group  = &$group;
		$this->rmHash = &$real_hash;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Extracts the credit card details of the specified order.
	 *
	 * @param 	object 	$order  The order details.
	 * 
	 * @return 	mixed 	An object with the credit details, null otherwise.
	 *
	 * @since 	1.8
	 */
	private function getCreditCard($order)
	{
		if (!strlen($order->cc_details))
		{
			// credit card details empty
			return null;
		}

		VikRestaurants::loadCryptLibrary();

		/**
		 * Since the decryption is made using mcrypt package, an exception
		 * could be thrown as the server might not have it installed.
		 * 			
		 * We need to wrap the code below within a try/catch and take
		 * the plain string without decrypting it. This was just an 
		 * additional security layer that doesn't alter the compliance
		 * with PCI/DSS rules.
		 *
		 * @since 1.8
		 */
		try
		{
			// unmask encrypted string
			$cipher = SecureCipher::getInstance();

			$card = $cipher->safeEncodingDecryption($order->cc_details);
		}
		catch (Exception $e)
		{
			// This server doesn't support current decryption algorithm.
			// Try decoding plain text
			$card = base64_decode($order->cc_details);
		}

		// decode credit card JSON-string
		return json_decode($card);
	}

	/**
	 * Checks whether the credit card details of the
	 * specified order should be removed.
	 *
	 * @param 	integer  $id     The order ID.
	 * @param 	integer  $group  The order group (0: restaurant, 1: takeaway).
	 *
	 * @return 	string   The hash to use to complete the cancellation.
	 */
	private function checkForRemove($id, $group)
	{
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();

		// obtain remove hash from request
		$rm_hash = $app->input->get('rmhash', '', 'string');

		// generate hash to complete remove process
		$real_hash = md5($id . ':' . $group);

		// make sure both the hash strings are equals
		if (!empty($rm_hash) && !strcmp($rm_hash, $real_hash))
		{
			$table = $group == 0 ? 'reservation' : 'tkreservation';

			// get table instance
			$order = JTableVRE::getInstance($table, 'VRETable');

			// prepare save data
			$data = array(
				'id'         => $id,
				'cc_details' => '',
			);

			// remove credit card details
			if ($order->save($data))
			{
				$this->removed = true;
			}
		}

		return $real_hash;
	}
}
