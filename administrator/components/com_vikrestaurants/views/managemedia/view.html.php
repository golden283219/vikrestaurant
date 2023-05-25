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
 * Vikestaurants media management view.
 *
 * @since 1.3
 */
class VikRestaurantsViewmanagemedia extends JViewVRE
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
		
		// set the toolbar
		$this->addToolBar();
		
		$filename = $input->get('cid', array(''), 'string');
		$filename = $filename[0];

		if (empty($filename) || !file_exists(VREMEDIA . DIRECTORY_SEPARATOR . $filename))
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=media');
			exit;
		}

		$media = RestaurantsHelper::getFileProperties(VREMEDIA . DIRECTORY_SEPARATOR . $filename);
		$thumb = RestaurantsHelper::getFileProperties(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $filename);
		
		$this->media = &$media;
		$this->thumb = &$thumb;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar() {
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEEDITMEDIA'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('media.save', JText::_('VRSAVE'));
			JToolbarHelper::save('media.saveclose', JText::_('VRSAVEANDCLOSE'));
			JToolbarHelper::save2new('media.savenew', JText::_('VRSAVEANDNEW'));
			JToolbarHelper::divider();
		}
		
		JToolbarHelper::cancel('media.cancel', JText::_('VRCANCEL'));
	}
}
