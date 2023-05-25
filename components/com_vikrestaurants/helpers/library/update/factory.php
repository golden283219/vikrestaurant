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
 * Factory used to handle the update adapters.
 *
 * ------------------------------------------------------------------------------------
 *
 * Update adapters CLASS name must have the following structure:
 * 
 * COMPONENT_NAME (no com_) + "UpdateAdapter" + VERSION (replace dots with underscores)
 * eg. ExampleUpdateAdapter1_2_5 (com_example 1.2.5)
 *
 * ------------------------------------------------------------------------------------
 *
 * Update adapters FILE name must have the following structure:
 * 
 * "upd" + VERSION (replace dots with underscores) + ".php"
 * eg. upd1_2_5.php (com_example 1.2.5)
 *
 * @since 1.8
 */
class VREUpdateFactory
{
	/**
	 * Executes the requested method.
	 *
	 * @param 	string 	 $method   The method name to launch.
	 * @param 	string 	 $version  The version to consider.
	 * @param 	mixed 	 $caller   The object that invoked this method.
	 * 
	 * @return 	boolean  True on success, false otherwise.
	 */
	public static function run($method, $version, $caller = null)
	{
		// get all adapters
		$adapters = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'adapters' . DIRECTORY_SEPARATOR . '*.php');

		// iterate each supported version
		foreach ($adapters as $file)
		{
			// get filename
			$filename = preg_replace("/\.php$/i", '', basename($file));

			// get version suffix from file name
			$safe_suffix = preg_replace("/^upd_?/i", '', $filename);

			// get class name of update adapter for current loop version
			$classname = 'VikRestaurantsUpdateAdapter' . $safe_suffix;

			// get version from suffix
			$v = preg_replace("/_+/", '.', $safe_suffix);

			// in case the software version is lower than loop version
			if (version_compare($version, $v, '<'))
			{
				// load updater adapter file
				$loaded = VRELoader::import('library.update.adapters.' . $filename);

				// in case the file has been loaded
				// and the adapter class owns the specified callback
				if ($loaded && method_exists($classname, $method))
				{
					try
					{
						// then run update callback function
						$success = call_user_func(array($classname, $method), $caller);
					}
					catch (Exception $e)
					{
						// something went wrong
						$success = false;
					}

					if ($success === false)
					{
						// stop adapters in case something gone wrong
						return false;
					}
				}

				// NOTE. it is not needed to check if the class exists because the 
				// method_exists function would return always false
			}

		}

		// no error found
		return true;
	}
}
