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
 * VikRestaurants API plugin details view.
 *
 * @since 1.5
 */
class VikRestaurantsViewmanageapiplugin extends JViewVRE
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
		
		// set the toolbar
		$this->addToolBar();

		$apis = VREFactory::getApis();
		
		$ids = $input->get('cid', array(''), 'string');

		// search for specified plugin
		$plugins = $apis->getPluginsList($ids[0]);

		if (count($plugins) == 0)
		{
			// plugin not found, back to the list
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'error');
			$app->redirect('index.php?option=com_vikrestaurants&view=apiplugins');
			exit;
		}
		
		$this->plugin = &$plugins[0];

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
		JToolbarHelper::title(JText::_('VRMAINTITLEEDITAPIPLUGIN'), 'vikrestaurants');
		
		JToolbarHelper::cancel('apiplugin.cancel', JText::_('VRCANCEL'));
	}
}
