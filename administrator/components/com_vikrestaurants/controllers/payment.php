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
 * VikRestaurants payment controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerPayment extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data  = array();

		$group = $app->input->getUint('group');

		if ($group)
		{
			$data['group'] = $group;
		}

		$file = $app->input->getString('file', '');

		if ($file)
		{
			$data['file'] = $file;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.payment.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managepayment');

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.payment.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managepayment&cid[]=' . $cid[0]);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->setRedirect('index.php?option=com_vikrestaurants&view=payments');
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return 	void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			// recover group from request
			$group = JFactory::getApplication()->input->getUint('group');

			$url = 'index.php?option=com_vikrestaurants&task=payment.add';

			if ($group)
			{
				$url .= '&group=' . $group;
			}

			$this->setRedirect($url);
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();
		
		$args = array();
		$args['name']         = $input->getString('name');
		$args['file']         = $input->getString('file');
		$args['published']    = $input->getUint('published', 0);
		$args['enablecost']   = $input->getInt('enablecost_factor') * abs($input->getFloat('enablecost_amount'));
		$args['trust']        = $input->getUint('trust', 0);
		$args['charge']       = $input->getFloat('charge');
		$args['percentot']    = $input->getUint('percentot', 0);
		$args['setconfirmed'] = $input->getUint('setconfirmed', 0);
		$args['selfconfirm']  = $input->getUint('selfconfirm', 0);
		$args['icontype']     = $input->getUint('icontype', 0);
		$args['position']     = $input->getString('position', '');
		$args['prenote']      = $input->get('prenote', '', 'raw');
		$args['note']         = $input->get('note', '', 'raw');
		$args['group']        = $input->getUint('group', 0);
		$args['id']           = $input->getInt('id', 0);

		switch ($args['icontype'])
		{
			case 1:
				$args['icon'] = $input->getString('font_icon', '');
				break;

			case 2:
				$args['icon'] = $input->getString('upload_icon', '');
				break;

			default:
				$args['icon'] = '';
		}

		if ($args['selfconfirm'])
		{
			// always unset auto-confirmation in case of self-confirmation
			// in order to avoid backward compatibility issues
			$args['setconfirmed'] = 0;
		}

		$rule = 'core.' . ($args['id'] > 0 ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$payment = JTableVRE::getInstance('payment', 'VRETable');

		try
		{
			// get payment configuration
			$config = VREApplication::getInstance()->getPaymentConfig($args['file']);

			$args['params'] = array();

			// load configuration from request
			foreach ($config as $k => $p)
			{
				$args['params'][$k] = $input->get('gp_' . $k, '', 'string');
			}
		}
		catch (Exception $e)
		{
			// unset file to raise error before saving the payment
			$args['file'] = false;
		}

		// try to save arguments
		if (!$payment->save($args))
		{
			// get string error
			$error = $payment->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managepayment';

			if ($payment->id)
			{
				$url .= '&cid[]=' . $payment->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=payment.edit&cid[]=' . $payment->id);

		return true;
	}

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
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
		JTableVRE::getInstance('payment', 'VRETable')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Changes the state of the selected records.
	 *
	 * @return 	boolean
	 */
	public function publish()
	{
		$app   = JFactory::getApplication();
		$cid   = $app->input->get('cid', array(), 'uint');
		$state = $app->input->get('state', 0, 'int');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// change state of selected records
		JTableVRE::getInstance('payment', 'VRETable')->publish($cid, $state);

		// back to records list
		$this->cancel();

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=payments');
	}

	/**
	 * AJAX end-point used to retrieve the configuration
	 * of the selected driver.
	 *
	 * @return 	void
	 */
	public function driverfields()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$driver = $input->getString('driver');
		$id     = $input->getUint('id', 0);
		
		// access payment config through platform handler
		$form = VREApplication::getInstance()->getPaymentConfig($driver);
		
		$params = array();

		// retrieve payment configuration in case of update
		if ($id)
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_gpayments'))
				->where($dbo->qn('id') . ' = ' . $id);
			
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$payment = $dbo->loadAssoc();

				if (!empty($payment['params']))
				{
					$params = json_decode($payment['params'], true);
				}
			}
		}
		
		// build display data
		$data = array(
			'fields' => $form,
			'params' => $params,
			'prefix' => 'gp_',
		);

		// render payment form
		$html = JLayoutHelper::render('form.fields', $data);
		
		echo json_encode(array($html));
		die;
	}
}
