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
 * VikRestaurants dashboard view.
 *
 * @since 1.0
 */
class VikRestaurantsViewrestaurant extends JViewVRE
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

		// get wizard instance
		$wizard = VREFactory::getWizard();

		if ($wizard->isDone())
		{
			VRELoader::import('library.statistics.factory');

			// load active widgets
			$dashboard = array(
				'restaurant' => VREStatisticsFactory::getDashboard('restaurant', 'dashboard'),
				'takeaway'   => VREStatisticsFactory::getDashboard('takeaway', 'dashboard'),
			);
			
			$this->dashboard = &$dashboard;
			$this->layout    = 'dashboard';
		}
		else
		{
			/**
			 * Added support for wizard page.
			 *
			 * @since 1.8.3
			 */
			$this->setLayout('wizard');

			$this->wizard = &$wizard;
		}

		// set the toolbar
		$this->addToolBar();
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWDASHBOARD'), 'vikrestaurants');

		if (JFactory::getUser()->authorise('core.access.dashboard', 'com_vikrestaurants'))
		{
			if (isset($this->dashboard))
			{
				// add button to manage dashboard widgets
				JToolbarHelper::addNew('statistics.add', JText::_('VRE_TOOLBAR_NEW_WIDGET'));
			}
			else
			{
				// add button to dismiss the wizard
				JToolbarHelper::custom('wizard.done', 'cancel', 'cancel', JText::_('VRWIZARDBTNDONE'), false);
			}

			JToolbarHelper::preferences('com_vikrestaurants');
		}
	}
}
