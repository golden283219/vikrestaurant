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
 * VikRestaurants platform factory class.
 * @final 	This class cannot be extended.
 *
 * @see 	VRELoader 	Used to load external files.
 * @see 	VREConfig 	The configuration of the software.
 *
 * @since  	1.7
 * @since 	1.8.2 Renamed from UIFactory
 */
final class VREFactory
{
	/**
	 * The configuration handler of VikRestaurants.
	 *
	 * @var VREConfig
	 */
	private static $config = null;

	/**
	 * The API Framework instance.
	 *
	 * @var FrameworkAPIs
	 */
	private static $apis = null;

	/**
	 * Application event dispatcher.
	 *
	 * @var VREEventDispatcher
	 *
	 * @since 1.7.4
	 */
	private static $eventDispatcher = null;

	/**
	 * The currency object.
	 *
	 * @var VRECurrency
	 *
	 * @since 1.8
	 */
	private static $currency = null;

	/**
	 * The translator object.
	 *
	 * @var VRELanguageTranslator
	 *
	 * @since 1.8
	 */
	private static $translator = null;

	/**
	 * The wizard object.
	 *
	 * @var VREWizard
	 *
	 * @since 1.8.3
	 */
	private static $wizard = null;

	/**
	 * Class constructor.
	 */
	private function __construct()
	{
		// this class cannot be instantiated
	}

	/**
	 * Class cloner.
	 */
	private function __clone()
	{
		// cloning function not accessible
	}

	/**
	 * Instantiate a new configuration object.
	 *
	 * @param   int  $error_level 	The level of the error to evaluate failure attempts.
	 * @param   bool $cache 		True to cache the settings retrieved, false to read 
	 *								the settings always from the database.
	 *
	 * @return 	VREConfig 	The configuration object.
	 */
	public static function getConfig($level = 0, $cache = false)
	{
		if (self::$config === null)
		{
			VRELoader::import('library.config.nocache');

			/**
			 * The configuration settings are no more
			 * cached within the PHP session.
			 *
			 * @since 1.7.4
			 */
			self::$config = new VREConfigNoCache($level, $cache);
		}
		else
		{

			// re-define always config params because they may 
			// be different depending on the section of the program
			self::$config->setErrorLevel($level)->setCache($cache);
		}

		return self::$config;
	}

	/**
	 * Instantiate a new Framework API object.
	 *
	 * @return 	FrameworkAPIs 	The API framework object.
	 */
	public static function getApis()
	{
		if (self::$apis === null) {
			
			// include APIs lib and framework overrides
			VikRestaurants::loadFrameworkApis();

			// instantiate APIs Framework
			// leave constructor empty to select default plugins folder: 
			// components/com_vikrestaurants/helpers/library/apislib/apis/plugins/
			self::$apis = FrameworkAPIs::getInstance();

			// get event dispatcher
			$dispatcher = static::getEventDispatcher();

			/**
			 * Trigger event to let the plugins alter the application framework.
			 * It is possible to use this event to include third-party applications.
			 * 
			 * In example:
			 * $api->addIncludePath($path);
			 * $api->addIncludePaths([$path1, $path2, ...]);
			 *
			 * @param  	FrameworkAPIs  &$api  The API framework instance.
			 *
			 * @return 	void
			 *
			 * @since 	1.8.2
			 */
			$dispatcher->trigger('onInitApplicationFramework', array(&self::$apis));

			// get config handler
			$config = self::getConfig(1, false);

			// set apis configuration
			self::$apis->set('max_failure_attempts', $config->getUint('apimaxfail', 10));

		}

		return self::$apis;
	}

	/**
	 * Returns the internal event dispatcher instance.
	 *
	 * @return 	VREEventDispatcher 	The event dispatcher.
	 *
	 * @since 	1.7.4
	 */
	public static function getEventDispatcher()
	{
		if (static::$eventDispatcher === null)
		{
			VRELoader::import('library.event.dispatcher');

			// obtain the software version always from the database
			$version = static::getConfig()->get('version', VIKRESTAURANTS_SOFTWARE_VERSION);

			// build options array
			$options = array(
				'alias' 	=> 'com_vikrestaurants',
				'version' 	=> $version,
				'admin' 	=> JFactory::getApplication()->isClient('administrator'),
				'call' 		=> null, // call is useless as it would be always the same
			);

			static::$eventDispatcher = VREEventDispatcher::getInstance($options);
		}

		return static::$eventDispatcher;
	}

	/**
	 * Instantiate a new currency object.
	 *
	 * @return 	VRECurrency
	 *
	 * @since 	1.8
	 */
	public static function getCurrency()
	{
		if (static::$currency === null)
		{
			$config = static::getConfig();

			VRELoader::import('library.currency.currency');

			// obtain configuration data
			$data = array(
				'currencyname'   => $config->getString('currencyname', 'EUR'),
				'currencysymb'   => $config->getString('currencysymb', '€'),
				'symbpos'        => $config->getUint('symbpos', 1),
				'currdecimalsep' => $config->getString('currdecimalsep', '.'),
				'currdecimaldig' => $config->getUint('currdecimaldig', 2),
			);

			/**
			 * Try to translate currency.
			 *
			 * @since 1.8
			 */
			VikRestaurants::translateConfig($data);

			static::$currency = new VRECurrency(
				$data['currencyname'],
				$data['currencysymb'],
				$data['symbpos'],
				$data['currdecimalsep'],
				$data['currdecimaldig']
			);
		}

		return static::$currency;
	}

	/**
	 * Instantiate a new translator object.
	 *
	 * @return 	VRELanguageTranslator
	 *
	 * @since 	1.8
	 */
	public static function getTranslator()
	{
		if (static::$translator === null)
		{
			VRELoader::import('library.language.translator');

			static::$translator = VRELanguageTranslator::getInstance();
		}

		return static::$translator;
	}

	/**
	 * Instantiate a new wizard object.
	 *
	 * @return 	VREWizard
	 *
	 * @since 	1.8.3
	 */
	public static function getWizard()
	{
		if (static::$wizard === null)
		{
			VRELoader::import('library.wizard.wizard');

			// get global wizard instance
			$wizard = VREWizard::getInstance();

			// complete setup only if not yet completed
			if (!$wizard->isDone())
			{
				// define list of steps to load
				$steps = array(
					'system',
					'sections',
					'openings',
					'rooms',
					'tables',
					'products',
					'menus',
					'tkattributes',
					'tkmenus',
					'tktoppings',
					'tkgroups',
					'tkservices',
					'tkareas',
					'payments',
				);

				// set up wizard
				$wizard->setup($steps);

				// set up steps dependencies
				$wizard['openings']->addDependency($wizard['sections']);
				
				$wizard['rooms']->addDependency($wizard['sections']);
				$wizard['tables']->addDependency($wizard['sections'], $wizard['rooms']);
				$wizard['products']->addDependency($wizard['sections']);
				$wizard['menus']->addDependency($wizard['sections'], $wizard['products']);

				$wizard['tkattributes']->addDependency($wizard['sections']);
				$wizard['tkmenus']->addDependency($wizard['sections']);
				$wizard['tktoppings']->addDependency($wizard['sections']);
				$wizard['tkgroups']->addDependency($wizard['sections'], $wizard['tkmenus'], $wizard['tktoppings']);
				$wizard['tkservices']->addDependency($wizard['sections']);
				$wizard['tkareas']->addDependency($wizard['sections'], $wizard['tkservices']);
			}

			// cache wizard
			static::$wizard = $wizard;
		}

		return static::$wizard;
	}
}
