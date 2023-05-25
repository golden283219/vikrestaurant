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
 * VikRestaurants restaurant reservations statistics controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerStatistics extends VREControllerAdmin
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
		$group = $app->input->get('group', 'restaurant', 'string');

		if ($group)
		{
			$data['group'] = $group;
		}

		$location = $app->input->get('location', 'statistics', 'string');

		if ($location)
		{
			$data['location'] = $location;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.statistics.data', $data);

		/**
		 * Calculate the ACL rule according to
		 * the specified request data.
		 *
		 * @since 1.8.3
		 */
		$acl = $this->getACL($data);

		// check user permissions
		if (!JFactory::getUser()->authorise($acl, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managestatistics');

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
			$this->cancel();
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

		$widgets_id       = $input->get('widget_id', array(), 'uint');
		$widgets_id_user  = $input->get('widget_id_user', array(), 'uint');
		$widgets_name     = $input->get('widget_name', array(), 'string');
		$widgets_class    = $input->get('widget_class', array(), 'string');
		$widgets_position = $input->get('widget_position', array(), 'string');
		$widgets_size     = $input->get('widget_size', array(), 'string');
	    
	    $group = $input->get('group', 'restaurant', 'string');

	    $location = $input->get('location', 'statistics', 'string');

	    /**
		 * Calculate the ACL rule according to
		 * the specified request data.
		 *
		 * @since 1.8.3
		 */
		$acl = $this->getACL(array('location' => $location, 'group' => $group));

		// check user permissions
		if (!$user->authorise($acl, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$widget = JTableVRE::getInstance('statswidget', 'VRETable');

		for ($i = 0; $i < count($widgets_id); $i++)
		{
			// prepare data
			$data = array(
				'id'       => $widgets_id[$i],
				'id_user'  => $widgets_id_user[$i],
				'name'     => $widgets_name[$i],
				'widget'   => $widgets_class[$i],
				'position' => $widgets_position[$i],
				'size'     => $widgets_size[$i],
				'group'    => $group,
				'location' => $location,
				'ordering' => $i + 1,
			);

			// save widget
			$widget->save($data);
		}

		// delete widgets
		$widgets_delete = $input->get('widgets_delete', array(), 'uint');
		$widget->delete($widgets_delete);

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=statistics.add&group=' . $group . '&location='  . $location);

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$input = JFactory::getApplication()->input;

		// get group
		$group = $input->get('group', 'restaurant', 'string');
		// get location
		$view = $input->get('location', 'statistics', 'string');

		if ($view === 'dashboard')
		{
			$view = 'restaurant';
		}

		$this->setRedirect('index.php?option=com_vikrestaurants&view=' . $view . '&group=' . $group);
	}

	/**
	 * AJAX end-point used to obtain the widget contents
	 * or datasets.
	 *
	 * @return 	void
	 */
	public function loadwidgetdata()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// first of all, get selected group
		$group = $input->get('group', 'restaurant', 'string');

		// fetch ACL rule based on group
		$rule = $group == 'restaurant' ? 'reservations' : 'tkorders';

		// check user permissions
		if (!JFactory::getUser()->authorise('core.access.' . $rule, 'com_vikrestaurants'))
		{
			// raise error, not authorised to access statistics
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get widget name and ID
		$widget = $input->get('widget', '', 'string');
		$id     = $input->get('id', 0, 'uint');

		VRELoader::import('library.statistics.factory');

		try
		{
			// try to instantiate the widget
			$widget = VREStatisticsFactory::getWidget($widget, $group);

			// set up widget ID
			$widget->setID($id);
		}
		catch (Exception $e)
		{
			// an error occurred while trying to access the widget
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// fetch widget data
		$data = $widget->getData();

		// save parameters for later use
		$widget->saveParams();

		// encode data in JSON and return them
		echo json_encode($data);
		exit;
	}

	/**
	 * Calculate the ACL rule according to
	 * the specified request data.
	 *
	 * @param 	array 	$data  The request array.
	 *
	 * @return 	string  The related ACL rule.
	 *
	 * @since 	1.8.3
	 */
	protected function getACL(array $data)
	{
		// default super user
		$acl = 'core.admin';

		$location = isset($data['location']) ? $data['location'] : '';
		$group    = isset($data['group'])    ? $data['group']    : '';

		if ($location == 'dashboard')
		{
			// allow dashboard management
			$acl = 'core.access.dashboard';
		}
		else if ($location == 'statistics')
		{
			if ($group == 'restaurant')
			{
				// allow reservations management
				$acl = 'core.access.reservations';
			}
			else if ($group == 'takeaway')
			{
				// allow orders management
				$acl = 'core.access.tkorders';
			}
		}

		return $acl;
	}
}
