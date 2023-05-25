<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_search
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
	'version' => '1.5.3',
);

$vik = VREApplication::getInstance();

$vik->addStyleSheet(VREMODULES_URI . 'mod_vikrestaurants_search/mod_vikrestaurants_search.css', $options);
$vik->addStyleSheet(VREASSETS_URI . 'css/jquery-ui.min.css');

// since jQuery is a required dependency, the framework should be 
// invoked even if jQuery is disabled
$vik->loadFramework('jquery.framework');
$vik->addScript(VREASSETS_URI . 'js/jquery-ui.min.js');

/**
 * Use FontAwesome to display the icons.
 *
 * @since 1.5
 */
JHtml::_('vrehtml.assets.fontawesome');

/**
 * Use default style for <select> defined by VikRestaurants.
 *
 * @since 1.5
 */
$vik->addStyleSheet(VREASSETS_URI . 'css/select.css');

/**
 * Load VikRestaurants utils.
 *
 * @since 1.5.1
 */
JHtml::_('vrehtml.assets.utils');
$vik->addScript(VREASSETS_URI . 'js/vikrestaurants.js');

// get query string values

$last_values = VikRestaurantsSearchHelper::getViewHtmlReferences();

/**
 * Use empty list of special days just
 * to avoid errors with layout overrides
 * that still relies to this variable.
 *
 * @deprecated 1.6
 */
$special_days = array();

/**
 * Use language texts defined by the component instead of
 * creating duplicates translations.
 *
 * @since 1.5
 */
VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag());

// load tmpl/default.php

require JModuleHelper::getLayoutPath('mod_vikrestaurants_search', $params->get('layout'));
