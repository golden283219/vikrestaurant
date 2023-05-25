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

// require helper files
require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));

if (!VersionListener::isSupported())
{
	die('This CMS version is not supported!');
}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_vikrestaurants'))
{
	if (VersionListener::getID() >= VersionListener::J35)
	{
		// the exception will be handled by the Joomla core
		throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
	}
	else
	{
		// return the error to the control page of Joomla
		return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
	}
}

// require helper files
require_once implode(DIRECTORY_SEPARATOR, array(JPATH_ADMINISTRATOR, 'components', 'com_vikrestaurants', 'helpers', 'helper.php'));

/**
 * Look for any Backward Compatibility adjustments.
 *
 * @since 1.8
 */
VRELoader::import('library.update.factory');
VREUpdateFactory::run('afterupdate', VREFactory::getConfig()->get('bcv'));

// Add CSS file and JS for all pages
RestaurantsHelper::load_css_js();

// remove expired credit cards
// check every 15 minutes only
VikRestaurants::removeExpiredCreditCards();

// check updater fields : add them in case are missing
RestaurantsHelper::registerUpdaterFields();

/**
 * Helper method used to setup the application according
 * to the platform version.
 *
 * @since 1.8.3
 */
VREApplication::getInstance()->setup();

// import joomla controller library
jimport('joomla.application.component.controller');
// Get an instance of the controller prefixed by Restaurants
$controller = JControllerVRE::getInstance('VikRestaurants');
// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));
// Redirect if set by the controller
$controller->redirect();
