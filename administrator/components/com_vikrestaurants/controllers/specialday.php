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
 * VikRestaurants special day controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerSpecialday extends VREControllerAdmin
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

		// unset user state for being recovered again
		$app->setUserState('vre.specialday.data', $data);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managespecialday');

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
		$app->setUserState('vre.specialday.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managespecialday&cid[]=' . $cid[0]);

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
			$this->setRedirect('index.php?option=com_vikrestaurants&view=specialdays');
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

			$url = 'index.php?option=com_vikrestaurants&task=specialday.add';

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
		$args['name']              = $input->getString('name');
		$args['start_ts']          = $input->getString('start_ts');
		$args['end_ts']            = $input->getString('end_ts');
		$args['working_shifts']    = $input->get('working_shifts', array(), 'string');
		$args['days_filter']       = $input->get('days_filter', array(), 'string');
		$args['askdeposit']        = $input->getUint('askdeposit', 0);
		$args['depositcost']       = $input->getFloat('depositcost', 0);
		$args['perpersoncost']     = $input->getUint('perpersoncost', 0);
		$args['peopleallowed']     = $input->getInt('peopleallowed', 0);
		$args['markoncal']         = $input->getUint('markoncal', 0);
		$args['ignoreclosingdays'] = $input->getUint('ignoreclosingdays', 0);
		$args['priority']          = $input->getUint('priority', 0);
		$args['choosemenu']        = $input->getUint('choosemenu', 0);
		$args['freechoose']        = $input->getUint('freechoose', 0);
		$args['minorder']          = $input->getFloat('minorder', 0);
		$args['delivery_service']  = $input->getInt('delivery_service', -1);
		$args['delivery_areas']    = $input->getUint('delivery_areas', array());
		$args['images']            = $input->get('images', array(), 'string');
		$args['group']             = $input->getUint('group', 0);
		$args['id'] 	           = $input->getUint('id', 0);

		// get selected menus
		$menus = $input->getUint('id_menu', array());

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
		$specialday = JTableVRE::getInstance('specialday', 'VRETable');

		// try to save arguments
		if (!$specialday->save($args))
		{
			// update user state data by injecting the selected menus
			$data = $app->getUserState('vre.specialday.data', array());
			$data['menus'] = $menus;
			$app->setUserState('vre.specialday.data', $data);

			// get string error
			$error = $specialday->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managespecialday';

			if ($specialday->id)
			{
				$url .= '&cid[]=' . $specialday->id;
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// set special day attached menus
		$specialday->setAttachedMenus($menus);

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=specialday.edit&cid[]=' . $specialday->id);

		return true;
	}

	/**
	 * Changes the "mark on calendar" parameter of the selected records.
	 *
	 * @return 	boolean
	 */
	public function markoncal()
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
		$shift = JTableVRE::getInstance('specialday', 'VRETable');

		$shift->setColumnAlias('published', 'markoncal');
		$shift->publish($cid, $state);

		// back to records list
		$this->cancel();

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
		JTableVRE::getInstance('specialday', 'VRETable')->delete($cid);

		// back to main list
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
		$this->setRedirect('index.php?option=com_vikrestaurants&view=specialdays');
	}

	/**
	 * AJAX end-point used to test the special days.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.2
	 */
	public function test()
	{
		$app    = JFactory::getApplication();
		$config = VREFactory::getConfig();

		$args = array();

		$args['group'] = $app->getUserStateFromRequest('vre.specialdaystest.group', 'group', 'restaurant', 'string');
		$args['date']  = $app->getUserStateFromRequest('vre.specialdaystest.date', 'date', '', 'string');

		// make sure the group is supported
		$args['group'] = JHtml::_('vrehtml.admin.getgroup', $args['group'], array('restaurant', 'takeaway'));
		
		if (!$args['date'])
		{
			// if not specified, use the current date
			$args['date'] = date($config->get('dateformat'), VikRestaurants::now());
		}

		// instantiate special days manager
		$sdManager = new VRESpecialDaysManager($args['group']);

		// set date filter
		$sdManager->setStartDate($args['date']);

		// recover the list of supported special days
		$sdList = $sdManager->getList();

		// check if the specified date is closed
		$closed = VikRestaurants::isClosingDay($args);

		// get global working shifts for the specified date
		$shifts = JHtml::_('vikrestaurants.shifts', $sdManager->getGroup(), $args['date'], $strict = false);

		// prepare layout file
		$layout = new JLayoutFile('blocks.sdtest');
		// render layout
		$html = $layout->render(array(
			'list'   => $sdList,
			'closed' => (bool) $closed,
			'args'   => $args,
			'shifts' => $shifts,
		));

		echo json_encode($html);
		exit;
	}
}
