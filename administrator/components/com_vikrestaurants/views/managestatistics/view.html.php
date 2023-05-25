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
 * VikRestaurants statistics management view.
 *
 * @since 1.8
 */
class VikRestaurantsViewmanagestatistics extends JViewVRE
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

		// recover user state
		$data = $app->getUserState('vre.statistics.data', array());

		if (!empty($data['group']))
		{
			// get group from user state
			$group = $data['group'];
		}
		else
		{
			// get group from request
			$group = $input->get('group', 'restaurant', 'string');
		}

		if (!empty($data['location']))
		{
			// get location from user state
			$location = $data['location'];
		}
		else
		{
			// get location from request
			$location = $input->get('location', 'statistics', 'string');
		}
		
		// set the toolbar
		$this->addToolBar($group, $location);

		VRELoader::import('library.statistics.factory');

		// load active widgets
		$dashboard = VREStatisticsFactory::getDashboard($group, $location);

		// get supported widgets
		$supported = VREStatisticsFactory::getSupportedWidgets($group);

		// get supported positions
		$positions = VREStatisticsFactory::getSupportedPositions($group, $location);
		
		$this->dashboard = &$dashboard;
		$this->supported = &$supported;
		$this->positions = &$positions;
		$this->group     = &$group;
		$this->location  = &$location;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string 	$group  The selected section.
	 *
	 * @return 	void
	 */
	private function addToolBar($group, $location)
	{
		// fetch page title
		if ($location == 'dashboard')
		{
			if ($group == 'restaurant')
			{
				$title = JText::_('VRMAINTITLEVIEWDASHBOARDRS');
			}
			else
			{
				$title = JText::_('VRMAINTITLEVIEWDASHBOARDTK');
			}
		}
		else
		{
			if ($group == 'restaurant')
			{
				$title = JText::_('VRMAINTITLEVIEWSTATISTICS');
			}
			else
			{
				$title = JText::_('VRMAINTITLEVIEWTKSTATISTICS');
			}
		}

		// add menu title and some buttons to the page
		JToolbarHelper::title($title, 'vikrestaurants');

		/**
		 * Calculate the ACL rule according to
		 * the specified request data.
		 *
		 * @since 1.8.3
		 */
		$acl = $this->getACL(array('location' => $location, 'group' => $group));

		if (JFactory::getUser()->authorise($acl, 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('statistics.save', JText::_('VRSAVE'));
			JToolbarHelper::save('statistics.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		JToolbarHelper::cancel('statistics.cancel');
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
