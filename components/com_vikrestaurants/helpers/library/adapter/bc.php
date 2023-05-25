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

// import Joomla controller library
jimport('joomla.application.component.controller');
// import Joomla view library
jimport('joomla.application.component.view');

// this should be already loaded from autoload.php
VRELoader::import('library.adapter.version.listener');

if (class_exists('JViewLegacy'))
{
	/* Joomla 3.x adapters */

	if (!class_exists('JViewBaseUI'))
	{
		class_alias('JViewLegacy', 'JViewBaseUI');
	}

	if (!class_exists('JControllerBaseUI'))
	{
		class_alias('JControllerLegacy', 'JControllerBaseUI');
	}
}
else
{
	/* Joomla 2.5 adapters */

	if (!class_exists('JViewBaseUI'))
	{
		class_alias('JView', 'JViewBaseUI');
	}

	if (!class_exists('JControllerBaseUI'))
	{
		class_alias('JController', 'JControllerBaseUI');
	}
}

if (VersionListener::isJoomla())
{
	// add class aliases for BC
	if (!class_exists('UIFactory'))
	{
		class_alias('VREFactory', 'UIFactory');
	}

	if (!class_exists('UILoader'))
	{
		class_alias('VRELoader', 'UILoader');
	}
}
