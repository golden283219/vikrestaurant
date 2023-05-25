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
 * VikRestaurants reservations/orders statistics view.
 *
 * @since 1.5
 */
class VikRestaurantsViewstatistics extends JViewVRE
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

		// get group
		$group = $input->get('group', 'restaurant', 'string');

		// search for a layout mode
		$layout = $input->get('layoutmode', 'floating', 'string');
		
		// set the toolbar
		$this->addToolBar($group);

		VRELoader::import('library.statistics.factory');

		// load active widgets
		$dashboard = VREStatisticsFactory::getDashboard($group, 'statistics');
		
		$this->dashboard = &$dashboard;
		$this->group     = &$group;
		$this->layout    = &$layout;
		
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
	private function addToolBar($group)
	{
		// add menu title and some buttons to the page
		if ($group == 'restaurant')
		{
			JToolbarHelper::title(JText::_('VRMAINTITLEVIEWSTATISTICS'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLEVIEWTKSTATISTICS'), 'vikrestaurants');
		}

		/**
		 * Calculate the ACL rule according to
		 * the specified request data.
		 *
		 * @since 1.8.3
		 */
		$acl = $this->getACL(array('group' => $group));

		if (JFactory::getUser()->authorise($acl, 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('statistics.add', JText::_('VRE_TOOLBAR_NEW_WIDGET'));
		}

		if ($group == 'restaurant')
		{
			JToolbarHelper::cancel('reservation.cancel');
		}
		else
		{
			JToolbarHelper::cancel('tkreservation.cancel');
		}
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

		$group = isset($data['group']) ? $data['group'] : '';

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

		return $acl;
	}
}
