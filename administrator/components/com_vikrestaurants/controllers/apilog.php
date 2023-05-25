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
 * VikRestaurants API log controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerApilog extends VREControllerAdmin
{
	/**
	 * Deletes a list of records set in the request.
	 *
	 * @return 	boolean
	 */
	public function delete()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'string');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// delete selected records
		$res = JTableVRE::getInstance('apilog', 'VRETable')->delete($cid);

		// back to main list
		$this->cancel();

		return true;
	}

	/**
	 * Truncates the table.
	 *
	 * @return 	boolean
	 */
	public function truncate()
	{
		$app  = JFactory::getApplication();
		$cid  = $app->input->get('cid', array(), 'string');

		// check user permissions
		if (!JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to delete records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// truncate all records
		$res = JTableVRE::getInstance('apilog', 'VRETable')->truncate();

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
		$id_login = JFactory::getApplication()->input->getUint('id_login', 0);

		$url = 'index.php?option=com_vikrestaurants&view=apilogs';

		if ($id_login)
		{
			$url .= '&id_login=' . $id_login;
		}

		$this->setRedirect($url);
	}
}
