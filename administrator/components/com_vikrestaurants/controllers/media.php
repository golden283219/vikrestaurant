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
 * VikRestaurants media controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerMedia extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.media.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$url = 'index.php?option=com_vikrestaurants&view=newmedia';

		if ($app->input->getBool('configure'))
		{
			// append configuration flag
			$url .= '&configure=1';
		}

		$this->setRedirect($url);

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
		$app->setUserState('vre.media.data', array());

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$cid = $app->input->getString('cid', array(''));

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managemedia&cid[]=' . $cid[0]);

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
			$this->setRedirect('index.php?option=com_vikrestaurants&view=media');
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
			$this->setRedirect('index.php?option=com_vikrestaurants&task=media.add');
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
		$args['id']           = $input->get('media', null, 'string');
		$args['name']         = $input->get('name', null, 'string');
		$args['action']       = $input->get('action', 0, 'uint');
		$args['resize']       = $input->get('resize', 0, 'uint');
		$args['resize_value'] = $input->get('resize_value', 0, 'uint');
		$args['thumb_value']  = $input->get('thumb_value', 0, 'uint');

		$args['image'] = $input->files->get('image', null, 'array');

		$rule = 'core.' . ($args['id'] ? 'edit' : 'create');

		// check user permissions
		if (!$user->authorise($rule, 'com_vikrestaurants'))
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get record table
		$media = JTableVRE::getInstance('media', 'VRETable');

		// try to save arguments
		if (!$media->save($args))
		{
			// get string error
			$error = $media->getError(null, true);

			// display error message
			$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');

			if ($media->id)
			{
				$url = 'index.php?option=com_vikrestaurants&view=managemedia&cid[]=' . $media->id;
			}
			else
			{
				$url = 'index.php?option=com_vikrestaurants&view=newmedia';
			}

			// redirect to new/edit page
			$this->setRedirect($url);
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// redirect to edit page
		$this->setRedirect('index.php?option=com_vikrestaurants&task=media.edit&cid[]=' . $media->id);

		return true;
	}

	/**
	 * Task used to upload files via AJAX.
	 *
	 * @return 	void
	 */
	public function dropupload()
	{
		$input = JFactory::getApplication()->input;
		
		$prop = array();    
		
		$prop = array();
		$prop['resize'] 	  = $input->get('resize', 0, 'uint');
		$prop['resize_value'] = $input->get('resize_value', 0, 'uint');
		$prop['thumb_value']  = $input->get('thumb_value', 0, 'uint');

		if (empty($prop['resize']) && empty($prop['resize_value']) && empty($prop['thumb_value']))
		{
			// properties will be retrieved from uploadMedia
			$prop = null;

			VREFactory::getConfig()->set('firstmediaconfig', 0);
		}
		else
		{
			// update media properties
			VikRestaurants::storeMediaProperties($prop);
		}

		// check if a custom path was passed
		$path = $input->getBase64('path', null);
		
		if ($path)
		{
			// upload the image in the given path
			$resp = VikRestaurants::uploadFile('image', base64_decode($path), 'jpeg,jpg,png,gif,bmp');
		}
		else
		{
			// upload media file (original and thumb)
			$resp = VikRestaurants::uploadMedia('image', $prop);
		}
		
		if (!$resp->esit)
		{
			// return response on error
			echo json_encode($resp);
		}
		else
		{
			// retrieve media properties
			$file = RestaurantsHelper::getFileProperties($resp->path);

			// return file details
			echo json_encode($file);
		}
		
		exit;
	} 

	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'string');
		$ajax = $app->input->getBool('ajax');
		$path = $app->input->getBase64('path', null);

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			if ($ajax)
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}
			else
			{
				// back to main list, not authorised to delete records
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$this->cancel();

				return false;
			}
		}

		// delete selected records
		$res = JTableVRE::getInstance('media', 'VRETable')->delete($cid, $path);

		if ($ajax)
		{
			echo json_encode($res);
			exit;
		}

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
		$this->setRedirect('index.php?option=com_vikrestaurants&view=media');
	}
}
