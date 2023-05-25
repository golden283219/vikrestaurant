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
 * VikRestaurants file management view.
 *
 * @since 1.3
 */
class VikRestaurantsViewmanagefile extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$input = JFactory::getApplication()->input;

		// check if we should use a blank component layout
		$blank = $input->get('tmpl') == 'component';

		if (!$blank)
		{
			// set the toolbar
			$this->addToolBar();
		}
		
		// get files
		$file = $input->get('cid', array(), 'string');

		// keep only the first one
		$file = array_shift($file);

		// make sure the file exists
		if (!$file || !is_file($file))
		{
			// file not found, try to decode from base64
			if ($file)
			{
				$file = base64_decode($file);
			}

			if (!is_file($file))
			{
				// file not found
				throw new Exception(sprintf('File [%s] not found', $file), 404);
			}
		}
		
		$buffer = '';

		// read file using a buffer
		$handle = fopen($file, 'r');

		while (!feof($handle))
		{
			$buffer .= fread($handle, 8192);
		}

		fclose($handle);
		
		$this->file    = &$file;
		$this->content = &$buffer;
		$this->blank   = &$blank;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string  $type  The view type ('edit' or 'new').
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title('VikRestaurants - Manage File', 'vikrestaurants');
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.admin', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('file.save', JText::_('VRSAVE'));
			JToolbarHelper::save('file.savecopy', JText::_('VRSAVEASCOPY'));
		}
		
		JToolbarHelper::cancel('file.cancel', JText::_('VRCANCEL'));
	}
}
