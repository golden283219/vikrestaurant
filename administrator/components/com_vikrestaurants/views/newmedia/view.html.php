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
 * VikRestaurants media upload view.
 *
 * @since 1.3
 */
class VikRestaurantsViewnewmedia extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		// set the toolbar
		$this->addToolBar();
	
		$prop = VikRestaurants::getMediaProperties();

		$this->properties = &$prop;

		/**
		 * Check if we should prompt a message to
		 * guide the user about chaning the default
		 * size of the thumbnails.
		 *
		 * @since 1.8.2
		 */
		$this->showHelp = $app->input->getBool('configure');

		// display the template
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
		JToolbarHelper::title(JText::_('VRMAINTITLENEWMEDIA'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('media.save', JText::_('VRSAVE'));
			JToolbarHelper::divider();
		}
		
		JToolbarHelper::cancel('media.cancel', JText::_('VRCANCEL'));
	}
}
