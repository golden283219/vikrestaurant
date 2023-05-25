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

// do nothing if the class already exists
if (!class_exists('VersionListener'))
{
	VRELoader::import('library.adapter.version.recognizer');

	/**
	 * Version listener class to check whether the installed platform version is supported.
	 *
	 * @see 	VersionRecognizer  This class extends the version recognizer.
	 *
	 * @since  	1.2 	The native methods of this class have been 
	 *					moved to the parent VersionRecognizer class.
	 */
	final class VersionListener extends VersionRecognizer
	{
		/**
		 * @override
		 * Checks whether the installed platform is supported.
		 * 
		 * Here's a list of all the supported platforms:
		 * - Joomla 2.5+
		 * - Wordpress 4+
		 *
		 * @return 	boolean  True if the current platform version is supported, false otherwise.
		 *
		 * @since 	1.7
		 */
		public static function isSupported()
		{
			// invoke parent first
			if (!parent::isSupported())
			{
				// platform is already not supported
				return false;
			}

			if (self::isJoomla())
			{
				// supported starting from J2.5
				return self::getID() >= self::J25;
			}
			else if (self::isWordpress())
			{
				// supported starting from WP4
				return self::getID() >= self::WP4;
			}

			// impossible to find the platform
			return false;
		}
	}
}
