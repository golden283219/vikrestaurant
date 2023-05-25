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

// include parent class in order to extend the configuration without errors
VRELoader::import('library.config.wrapper');

/**
 * Utility class working with a physical configuration stored into the Joomla database.
 *
 * @since  	1.7
 * @since 	1.8.2 Renamed from UIConfig
 */
class VREConfig extends VREConfigWrapper
{
	/**
	 * The error level for failure attempts: 1 for Development, 0 for Simple.
	 *
	 * @var integer
	 */
	protected $error_level;

	/**
	 * Specify 1 if you want to cache into the session the settings used.
	 *
	 * @var boolean
	 */
	protected $cache;

	/**
	 * The Joomla global application object.
	 *
	 * @var JApplicationCms 	
	 */
	private static $app = null;

	/**
	 * The Joomla global database driver object.
	 *
	 * @var JDatabaseDriver
	 */
	private static $dbo = null;

	/**
	 * The Joomla global session handler object.
	 *
	 * @var JSession
	 */
	private static $session = null;

	/**
	 * Class constructor.
	 *
	 * @param   int  $error_level 	The level of the error to evaluate failure attempts.
	 * @param   bool $cache 		True to cache the settings retrieved, false to read 
	 *								the settings always from the database.
	 *
	 * @uses 	setErrorLevel		Error level option setter.
	 * @uses 	setCache			Cache option setter.
	 */
	public function __construct($error_level = 0, $cache = true)
	{
		$this->setErrorLevel($error_level)
			->setCache($cache);
	}

	/**
	 * Set the error level to evaluate failure attempts.
	 *
	 * @return  self  This object to support chaining.
	 */
	public function setErrorLevel($error_level)
	{
		if ($error_level < self::SIMPLE || $error_level > self::DEVELOPMENT)
		{
			$error_level = self::SIMPLE;
		}

		$this->error_level = $error_level;

		return $this;
	}

	/**
	 * Set the cache option to maintain the settings used.
	 *
	 * @return  self  This object to support chaining.
	 */
	public function setCache($cache)
	{
		$this->cache = $cache;

		return $this;
	}

	/**
	 * Cache all the future settings into the session.
	 *
	 * @return  self  This object to support chaining.
	 *
	 * @uses 	setCache() 	Cache option setter.
	 */
	public function startCaching()
	{
		return $this->setCache(true);
	}

	/**
	 * Ignore caching for all the future settings.
	 * Recover the settings always from the database.
	 *
	 * @return  self  This object to support chaining.
	 *
	 * @uses 	setCache() 	Cache option setter.
	 */
	public function stopCaching()
	{
		return $this->setCache(false);
	}

	/**
	 * @override
	 * Retrieve the value of the setting stored in the Joomla database.
	 * When cache is enable and the user is in the front-end store 
	 * the setting value into the session to speed up future usages.
	 *
	 * @param   string   $key 	The name of the setting.
	 *
	 * @return  mixed 	The value of the setting if exists, otherwise false.
	 *
	 * @uses 	getResources() 	Load the resources needed.
	 *
	 * @throws 	Exception if the error reporting is set to DEVELOPMENT and the setting does not exist.
	 */
	protected function retrieve($key)
	{
		// load the resources
		list($app, $dbo, $session) = self::getResources();

		$value = '';
		
		// read the setting from DB only if you are in the admin section 
		// or the setting is not stored in the session 
		// or the cache is disabled
		if ($app->isClient('administrator') || !$session->has($key, 'vrconfig') || $this->cache === false)
		{
			// read value from database
			$value = $this->getFromDatabase($key);

			// register the setting if it exists
			// and you are in the site section
			// and it is possible to cache
			if ($value !== false && $app->isClient('site') && $this->cache)
			{
				// push setting in the session
				$session->set($key, $value, 'vrconfig');
			}
		}
		else
		{
			// access this statement only if you are in the site section
			// and the setting is stored in the session
			// and cache is enabled

			// otherwise get the setting from the session
			$value = $session->get($key, false, 'vrconfig');
		}

		// if the setting does not exist and the error level is set to DEVELOPMENT
		if ($value === false && $this->error_level === self::DEVELOPMENT)
		{
			// throw an exception and stop the flow
			throw new Exception("VikRestaurants - Configuration key not found [$key]");
		}

		return $value;

	}

	/**
	 * @override
	 * Register the value of the setting into the Joomla database.
	 * All the array and objects will be stringified in JSON.
	 * @uses 	getResources 	Load the resources needed.
	 *
	 * @param   string  $key 	The name of the setting.
	 * @param   mixed   $val 	The value of the setting.
	 *
	 * @return  bool 	True in case of success, otherwise false.
	 */
	protected function register($key, $val)
	{
		if (is_array($val) || is_object($val))
		{
			$val = json_encode($val);
		}

		$app = JFactory::getApplication();

		if ($app->isClient('site'))
		{
			// always load tables from the back-end
			JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		}

		// get config table
		$config = JTableVRE::getInstance('configuration', 'VRETable');

		// save configuration setting
		return $config->save(array(
			'param'   => $key,
			'setting' => $val,
		));
	}

	/**
	 * Read the value of the specified setting from the database.
	 * @uses 	getResources 	Load the resources needed.
	 *
	 * @param   string   $key 	The name of the setting.
	 *
	 * @return  mixed 	The value of the setting.
	 *
	 * @throws 	Exception is the error reporting is set to DEVELOPMENT and the setting does not exist.
	 */
	private function getFromDatabase($key)
	{
		list($app, $dbo, $session) = self::getResources();

		$query = $dbo->getQuery(true);

		$query->select($dbo->qn('setting'))
			->from($dbo->qn('#__vikrestaurants_config'))
			->where($dbo->qn('param') . ' = ' . $dbo->q($key));

		$dbo->setQuery($query, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			return $dbo->loadResult();
		}

		return false;
	}

	/**
	 * Load the resources needed to retrieve the settings.
	 *
	 * @return array 	An array containing JApplicationCms, JDatabaseDriver and JSession objects
	 */
	protected static function getResources()
	{
		if (self::$app === null)
		{
			self::$app = JFactory::getApplication();
		}

		if (self::$dbo === null)
		{
			self::$dbo = JFactory::getDbo();
		}

		if (self::$session === null)
		{
			self::$session = JFactory::getSession();
		}

		return array(self::$app, self::$dbo, self::$session);
	}

	/**
	 * The SIMPLE error level identifier.
	 *
	 * @var integer
	 */
	const SIMPLE = 0;

	/**
	 * The DEVELOPMENT error level identifier.
	 *
	 * @var integer
	 */
	const DEVELOPMENT = 1;
}
