<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

JLoader::register('SpauthorarchiveHelper', JPATH_SITE . '/components/com_spauthorarchive/helpers/helper.php');

JHtml::_('jquery.framework');
$doc = JFactory::getDocument();

// Include CSS files
$doc->addStylesheet( JURI::root(true) . '/components/com_spauthorarchive/assets/css/spauthorarchive.css' );
$doc->addStylesheet( JURI::root(true) . '/components/com_spauthorarchive/assets/css/spauthorarchive-structure.css' );

$controller = JControllerLegacy::getInstance('Spauthorarchive');
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));
$controller->redirect();
