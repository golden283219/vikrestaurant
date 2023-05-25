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
 * VikRestaurants file controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerFile extends VREControllerAdmin
{
	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.file.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getString('cid', array(''));

		$url = 'index.php?option=com_vikrestaurants&view=managefile&cid[]=' . $cid[0];

		if ($app->input->get('tmpl') == 'component')
		{
			$url .= '&tmpl=component';
		}

		$this->setRedirect($url);

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
	 * Task used to save the record data as a copy of the current item.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	void
	 */
	public function savecopy()
	{
		$input = JFactory::getApplication()->input;

		// get directory and file name from request
		$directory = $input->getString('dir');
		$filename  = $input->getString('filename');

		// check if directory exists
		if (!is_dir($directory))
		{
			// try to decode from base64
			$directory = base64_decode($directory);
		}

		// build final path
		$file = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

		// inject file in request
		$input->set('file', $file);

		// launch save method
		$this->save();
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
		$args['id']      = $input->get('file', '', 'string');
		$args['content'] = $input->get('content', '', 'raw');

		// check if blank layout
		$tmpl = $input->get('tmpl') == 'component';

		// check user permissions
		if (!$user->authorise('core.admin', 'com_vikrestaurants'))
		{
			if ($tmpl)
			{
				// throw exception in case of blank layout
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}
			
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$file = JTableVRE::getInstance('file', 'VRETable');

		// try to save arguments
		if (!$file->save($args))
		{
			// get string error
			$error = $file->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			$url = 'index.php?option=com_vikrestaurants&view=managefile&cid[]=' . base64_encode($file->id);

			if ($tmpl)
			{
				$url .= '&tmpl=component';
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		$url = 'index.php?option=com_vikrestaurants&task=file.edit&cid[]=' . base64_encode($file->id);

		if ($tmpl)
		{
			$url .= '&tmpl=component';
		}

		// redirect to edit page
		$this->setRedirect($url);

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=editconfig');
	}
}
