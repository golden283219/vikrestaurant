<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_quickres
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// require autoloader
if (defined('JPATH_SITE') && JPATH_SITE !== 'JPATH_SITE')
{
	require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';

// backward compatibility

$options = array(
	'version' => '1.5.2',
);

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_quickres/mod_vikrestaurants_quickres.css', $options);
$vik->addStyleSheet(VREASSETS_URI . 'css/jquery-ui.min.css');

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');
$vik->addScript(VREASSETS_URI . 'js/jquery-ui.min.js');

/**
 * Load fancybox and font awesome to support CF popup link.
 *
 * @since 1.3.1
 */
JHtml::_('vrehtml.assets.fancybox');
JHtml::_('vrehtml.assets.fontawesome');

/**
 * Load VikRestaurants utils.
 *
 * @since 1.4
 */
JHtml::_('vrehtml.assets.utils');
$vik->addScript(VREASSETS_URI . 'js/vikrestaurants.js');

/**
 * Use default style for <select> defined by VikRestaurants.
 *
 * @since 1.4
 */
$vik->addStyleSheet(VREASSETS_URI . 'css/select.css');

/**
 * Use empty list of special days just
 * to avoid errors with layout overrides
 * that still relies to this variable.
 *
 * @deprecated 1.5
 */
$special_days = array();

// get custom fields

$custom_fields = VikRestaurantsQuickResHelper::getCustomFields();

// get user fields

$user_fields = VikRestaurantsQuickResHelper::getUserFields();

/**
 * Use language texts defined by the component instead of
 * creating duplicates translations.
 *
 * @since 1.4
 */
VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag());

/**
 * Always use current item id for AJAX requests, because
 * they must point to a self-page in order to properly
 * retrieve the module parameters on server side.
 *
 * @since 1.5
 */
$itemid = JFactory::getApplication()->input->getUint('Itemid', 0);

if (VersionListener::isWordpress())
{
	// force the configuration Item ID for WP platform
	$itemid = $params->get('itemid');
}

// load tmpl/default.php

require JModuleHelper::getLayoutPath('mod_vikrestaurants_quickres', $params->get('layout'));
