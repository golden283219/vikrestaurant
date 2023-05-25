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

// require autoloader
require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));

if (!VersionListener::isSupported())
{
	die('This Joomla version is not supported!');
}

// Set the component css/js
VikRestaurants::load_css_js();

/**
 * Helper method used to setup the application according
 * to the platform version.
 *
 * @since 1.8.3
 */
VREApplication::getInstance()->setup();

// import joomla controller library
jimport('joomla.application.component.controller');
// Get an instance of the controller prefixed by VikRestaurants
$controller = JControllerVRE::getInstance('VikRestaurants');
// Perform the request task
$controller->execute(JFactory::getApplication()->input->get('task'));
// Redirect if set by the controller
$controller->redirect();
