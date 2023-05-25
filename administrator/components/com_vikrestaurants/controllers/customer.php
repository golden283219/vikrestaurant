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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants customer controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerCustomer extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return  boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.customer.data', array());

		// check if we should use a blank template
		$blank = $app->input->get('tmpl') === 'component';

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			if ($blank)
			{
				// throw exception in order to avoid unexpected behaviors
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), '403');
			}

			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$url = 'index.php?option=com_vikrestaurants&view=managecustomer';

		if ($blank)
		{
			$url .= '&tmpl=component';
		}

		$this->setRedirect($url);

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return  boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.customer.data', array());

		// check if we should use a blank template
		$blank = $app->input->get('tmpl') === 'component';

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			if ($blank)
			{
				// throw exception in order to avoid unexpected behaviors
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), '403');
			}

			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$url = 'index.php?option=com_vikrestaurants&view=managecustomer&cid[]=' . $cid[0];

		if ($blank)
		{
			$url .= '&tmpl=component';
		}

		$this->setRedirect($url);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return  void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->setRedirect('index.php?option=com_vikrestaurants&view=customers');
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return  void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			$this->setRedirect('index.php?option=com_vikrestaurants&task=customer.add');
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return  boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['jid']                = $input->get('jid', 0, 'int');
		$args['billing_name']       = $input->get('billing_name', '', 'string');
		$args['billing_mail']       = $input->get('billing_mail', '', 'string');
		$args['billing_phone']      = $input->get('billing_phone', '', 'string');
		$args['country_code']       = $input->get('country_code', '', 'string');
		$args['billing_state']      = $input->get('billing_state', '', 'string');
		$args['billing_city']       = $input->get('billing_city', '', 'string');
		$args['billing_address']    = $input->get('billing_address', '', 'string');
		$args['billing_address_2']  = $input->get('billing_address_2', '', 'string');
		$args['billing_zip']        = $input->get('billing_zip', '', 'string');
		$args['company']            = $input->get('company', '', 'string');
		$args['vatnum']             = $input->get('vatnum', '', 'string');
		$args['ssn']                = $input->get('ssn', '', 'string');
		$args['notes']              = $input->get('notes', '', 'string');
		$args['image']              = $input->get('image', '', 'string');
		$args['id']                 = $input->get('id', 0, 'int');

		// fill user fields only if we need to create them
		if ($input->getBool('create_new_user'))
		{
			// user fields
			$args['user'] = array();
			$args['user']['username'] = $input->get('username', '', 'string');
			$args['user']['usermail'] = $input->get('usermail', '', 'string');
			$args['user']['password'] = $input->get('password', '', 'string');
			$args['user']['confirm']  = $input->get('confpassword', '', 'string');
		}

		// retrieve custom fields for both the sections
		$args['fields']   = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_RESTAURANT, $match, $strict = false);
		$args['tkfields'] = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_TAKEAWAY, $match, $strict = false);

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check if we should use a blank template
		$blank = $app->input->get('tmpl') === 'component';

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$customer = JTableVRE::getInstance('customer', 'VRETable');

		// try to save arguments
		if (!$customer->save($args))
		{
			// update user state data by injecting the user groups and username
			$data = $app->getUserState('vre.customer.data', array());
			$data['username'] = $args['user']['username'];
			$data['usermail'] = $args['user']['usermail'];
			$app->setUserState('vre.customer.data', $data);

			// get string error
			$error = $customer->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managecustomer';

			if ($customer->id)
			{
				$url .= '&cid[]=' . $customer->id;
			}

			if ($blank)
			{
				$url .= '&tmpl=component';
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// get user location table
		$location = JTable::getInstance('userlocation', 'VRETable');

		// check if we should auto-create a delivery location based on billing details
		if ($input->getBool('delivery_as_billing') && $customer->billing_address && $customer->billing_zip)
		{
			$delivery = array(
				'country'   => $customer->country_code,
				'state'     => $customer->billing_state,
				'city'      => $customer->billing_city,
				'address'   => $customer->billing_address,
				'address_2' => $customer->billing_address_2,
				'zip'       => $customer->billing_zip,
				'latitude'  => $input->getFloat('billing_lat', null),
				'longitude' => $input->getFloat('billing_lng', null),
				'id_user'   => $customer->id,
				'ordering'  => 1,
			);

			$location->save($delivery);
			$location->reset();
		}

		// delete delivery locations
		$delete_delivery = $input->get('delete_delivery', array(), 'uint');

		if ($delete_delivery)
		{
			$location->delete($delete_delivery);
		}

		// fetch delivery locations
		$delivery_type      = $input->get('delivery_type', array(), 'uint');
		$delivery_country   = $input->get('delivery_country', array(), 'string');
		$delivery_state     = $input->get('delivery_state', array(), 'string');
		$delivery_city      = $input->get('delivery_city', array(), 'string');
		$delivery_address   = $input->get('delivery_address', array(), 'string');
		$delivery_address_2 = $input->get('delivery_address_2', array(), 'string');
		$delivery_zip       = $input->get('delivery_zip', array(), 'string');
		$delivery_note      = $input->get('delivery_note', array(), 'string');
		$delivery_lat       = $input->get('delivery_lat', array(), 'string');
		$delivery_lng       = $input->get('delivery_lng', array(), 'string');
		$delivery_id        = $input->get('delivery_id', array(), 'uint');

		for ($i = 0; $i < count($delivery_id); $i++)
		{
			$delivery = array();
			$delivery['id']        = $delivery_id[$i];
			$delivery['type']      = $delivery_type[$i];
			$delivery['country']   = $delivery_country[$i];
			$delivery['state']     = $delivery_state[$i];
			$delivery['city']      = $delivery_city[$i];
			$delivery['address']   = $delivery_address[$i];
			$delivery['address_2'] = $delivery_address_2[$i];
			$delivery['zip']       = $delivery_zip[$i];
			$delivery['note']      = $delivery_note[$i];
			$delivery['latitude']  = $delivery_lat[$i];
			$delivery['longitude'] = $delivery_lng[$i];
			$delivery['ordering']  = $i + 1;
			$delivery['id_user']   = $customer->id;

			$location->save($delivery);
			$location->reset();
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		$url = 'index.php?option=com_vikrestaurants&task=customer.edit&cid[]=' . $customer->id;

		if ($blank)
		{
			// keep blank template when returning to edit page
			$url .= '&tmpl=component';
		}

		// redirect to edit page
		$this->setRedirect($url);

		return true;
	}

	/**
	 * AJAX end-point used to auto-save the customer notes.
	 *
	 * @return 	void
	 */
	public function savenotesajax()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['notes'] = $input->get('notes', '', 'string');
		$args['id']    = $input->get('id', 0, 'uint');

		$rule = 'core.edit';

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants') || !$args['id'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get record table
		$customer = JTableVRE::getInstance('customer', 'VRETable');

		// try to save arguments
		if (!$customer->save($args))
		{
			// get string error
			$error = $customer->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		// notes saved
		exit;
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return  boolean
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'uint');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		JTableVRE::getInstance('customer', 'VRETable')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=customers');
	}

	/**
	 * Sends a custom SMS to the specified customer.
	 *
	 * @return 	void
	 */
	public function sendsms()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$cid = $input->get('cid', array(), 'uint');

		// check user permissions
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$user->authorise('core.access.customers', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to send SMS notifications
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		try
		{
			// get current SMS instance
			$smsapi = VREApplication::getInstance()->getSmsInstance();
		}
		catch (Exception $e)
		{
			// back to main list, SMS API not configured
			$app->enqueueMessage(JText::_('VRSMSESTIMATEERR1'), 'error');
			$this->cancel();

			return false;
		}

		// load message from request
		$message = $input->get('sms_message', '', 'string');

		// make sure the message is not empty
		if (!$message)
		{
			// missing contents, back to main list
			$this->cancel();

			return false;
		}

		$notified = 0;
		$errors   = array();

		foreach ($cid as $id)
		{
			// get customer details
			$customer = VikRestaurants::getCustomer($id);

			if ($customer && $customer->billing_phone)
			{
				// send message
				$response = $smsapi->sendMessage($customer->billing_phone, $message);

				// validate response
				if ($smsapi->validateResponse($response))
				{
					// successful notification
					$notified++;
				}
				else
				{
					// unable to send the notification, register error message
					$errors[] = $smsapi->getLog();
				}
			}
		}

		// update default message if needed
		if ($input->getBool('sms_keep_def'))
		{
			// alter configuration
			VREFactory::getConfig()->set('smstextcust', $message);
		}

		if ($notified)
		{
			// successful message
			$app->enqueueMessage(JText::plural('VRCUSTOMERSMSSENT', $notified));
		}
		else
		{
			// no notifications sent
			$app->enqueueMessage(JText::plural('VRCUSTOMERSMSSENT', $notified), 'warning');
		}

		// display any returned errors
		if ($errors)
		{
			// do not display duplicate or empty errors
			$errors = array_unique(array_filter($errors));

			foreach ($errors as $err)
			{
				$app->enqueueMessage($err, 'error');
			}
		}

		// back to main list
		$this->cancel();
	}
}
