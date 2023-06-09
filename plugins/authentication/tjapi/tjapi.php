<?php
/**
 * @package     API
 * @subpackage  Authentication.tjtokenlogin
 *
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Unauthorized Access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;

/**
 * Class for Tjapi Authentication Plugin
 *
 * @since  1.0.0
 */
class PlgAuthenticationTjapi extends CMSPlugin
{
	/**
	 * Verify Api Key
	 *
	 * @param   int     $userId  User id
	 * @param   string  $key     API key
	 *
	 * @return  boolean
	 */
	public function verifyApiKey($userId, $key)
	{
		// Load table
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_api/tables');
		$table = Table::getInstance('Key', 'ApiTable');
		$table->load(array('userid' => $userId));

		if ($key == $table->hash)
		{
			return true;
		}

		return false;
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   &$credentials  Array holding the user credentials
	 * @param   array   $options       Array of extra options
	 * @param   object  &$response     Authentication response object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onUserAuthenticate(&$credentials, $options, &$response)
	{
		$uid = isset($credentials['id']) ? $credentials['id'] : '';
		$key = isset($credentials['key']) ? $credentials['key'] : '';

		$response->type = 'Tjapi';

		if (empty($uid) || empty($key))
		{
			$response->status        = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_NO_USER');
		}
		else
		{
			// Verify the key
			$match = $this->verifyApiKey($uid, $key);

			if ($match === true)
			{
				// Bring this in line with the rest of the authentication
				$user = User::getInstance($uid);

				// Set response data.
				$response->username = $user->username;
				$response->email    = $user->email;
				$response->fullname = $user->name;
				$response->password = $user->password;
				$response->language = $user->getParam('language');

				$response->status        = Authentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				// Invalid password
				$response->status        = Authentication::STATUS_FAILURE;
				$response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		}

		return;
	}
}
