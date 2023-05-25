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

/**
 * VikRestaurants API plugin table.
 *
 * @since 1.8
 */
class VRETableApiplugin extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		// Use CONFIG database table just to avoid errors while
		// instantiating this class
		parent::__construct('#__vikrestaurants_config', 'id', $db);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed    $ids   Either the record ID or a list of records.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($ids = null)
	{
		if (!$ids)
		{
			return false;
		}

		$apis = VREFactory::getApis();

		$res = false;

		foreach ($ids as $id)
		{
			// check if the file exists
			$path = $apis->getEventPath($id);

			if ($path)
			{
				$base = dirname($path);

				/**
				 * Just rename the plugin to make it inaccessible
				 * instead of drastically deleting it.
				 *
				 * @since 1.8.2
				 */
				$res = rename($path, $base . DIRECTORY_SEPARATOR . '$__' . $id . '.php') || $res;
			}
		}

		return $res;
	}
}
