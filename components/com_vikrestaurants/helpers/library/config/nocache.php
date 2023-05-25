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
VRELoader::import('library.config.config');

/**
 * Utility class working with a physical configuration stored into the Joomla database.
 *
 * @see 	VREConfig 	This class extends the configuration wrapper to avoid always cache.
 *
 * @since  	1.7
 * @since 	1.8.2 Renamed from UIConfigNoCache
 */
class VREConfigNoCache extends VREConfig
{
	/**
	 * Class constructor.
	 *
	 * @param   int  $error_level 	The level of the error to evaluate failure attempts.
	 */
	public function __construct($error_level = 0)
	{
		parent::__construct($error_level, false);
	}

	/**
	 * @override parent method
	 * Disable always the cache to force recovery from the database.
	 *
	 * @return  self  This object to support chaining.
	 */
	public function setCache($cache = false)
	{
		$this->cache = false;

		return $this;
	}
}
