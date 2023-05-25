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
 * VikRestaurants export reservations/orders controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerExportres extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data = array();
		$type = $app->input->get('type');
		$cid  = $app->input->get('cid', array(), 'uint');

		if ($type)
		{
			$data['type'] = $type;
		}

		if ($cid)
		{
			$data['cid'] = $cid;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.exportres.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=exportres');

		return true;
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

		$driver   = $input->get('driver', '', 'string');
		$type     = $input->get('type', '', 'string');
		$filename = $input->get('filename', '', 'string');
		
		$args = array();
		$args['fromdate'] = $input->get('fromdate', '', 'string');
		$args['todate']   = $input->get('todate', '', 'string');
		$args['cid']      = $input->get('cid', array(), 'uint');

		$rule = 'core.access.' . ($type == 'restaurant' ? 'reservations' : 'tkorders');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		VRELoader::import('library.order.export.factory');

		try
		{
			// get driver instance ready to the usage
			$driver = VREOrderExportFactory::getDriver($driver, $type, $args);

			// save driver parameters before exporting, otherwise
			// the database update won't be performed
			$driver->saveParams();

			// download the exported data
			$driver->download();
		}
		catch (Exception $e)
		{
			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $e->getMessage()), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=exportres&type=' . $type;

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// do not go ahead to avoid including template resources
		exit;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$app = JFactory::getApplication();

		$type = $app->input->get('type');

		if ($type == 'restaurant')
		{
			$view = 'reservations';
		}
		else
		{
			$view = 'tkreservations';
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=' . $view);
	}

	/**
	 * AJAX end-point used to retrieve the configuration
	 * of the selected driver.
	 *
	 * @return 	void
	 */
	public function getdriverformajax()
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$driver = $input->getString('driver');
		$type   = $input->getString('type');
		
		VRELoader::import('library.order.export.factory');

		// get driver instance
		$driver = VREOrderExportFactory::getInstance($driver, $type);

		// get configuration form
		$form = $driver->getForm();
		
		// get configuration params
		$params = $driver->getParams();
		
		// build display data
		$data = array(
			'fields' => $form,
			'params' => $params,
			'prefix' => '',
		);

		// render form by using the payment fields layout
		$html = JLayoutHelper::render('form.fields', $data);

		// get driver description
		$description = $driver->getDescription();

		if ($description)
		{
			// include description within the form
			$html = VREApplication::getInstance()->alert($description, 'info') . $html;
		}
		
		echo json_encode(array($html));
		die;
	}
}
