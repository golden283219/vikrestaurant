<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_trading
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.application.component.model');

require_once JPATH_SITE . '/libraries/src/Filesystem/Folder.php';
require_once JPATH_SITE . '/components/com_vikrestaurants/helpers/library/loader/autoload.php';


/**
 * tkreservation Api.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_api
 *
 * @since       1.0
 */
class VikRestaurantsApiResourceEmail extends ApiResource
{
	/**
	 * Function get for tkreservation record.
	 *
	 * @return void
	 */
	public function post()
	{
        $input = JFactory::getApplication()->input;
		$type = $input->get('type');
		$id = $input->get('id');
        $message = $input->json->getString('message');
		$message = str_replace("{br}", "<br>", $message);
		
		VRELoader::import('library.mail.factory');
		$mail = VREMailFactory::getInstance($type, 'message', $id);
		$mail->setMessage($message);
		$mail->send();
        $result = ['message' => $message];
        $this->plugin->setResponse($result);
	}
}
