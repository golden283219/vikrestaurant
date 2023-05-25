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

// require only once the file containing all the defines
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'defines.php';

// if VRELoader does not exist, include it
if (!class_exists('VRELoader'))
{
	include VRELIB . DIRECTORY_SEPARATOR . 'loader' . DIRECTORY_SEPARATOR . 'loader.php';
	
	// append helpers folder to the base path
	VRELoader::$base .= DIRECTORY_SEPARATOR . 'helpers';
}

// fix filenames with dots
VRELoader::registerAlias('lib.vikrestaurants', 'lib_vikrestaurants');
VRELoader::registerAlias('pdf.constraints', 'constraints'); // this will be loaded specifically

// load factory
VRELoader::import('library.system.error');
VRELoader::import('library.system.factory');

// load adapters
VRELoader::import('library.adapter.version.listener');
VRELoader::import('library.adapter.application');
VRELoader::import('library.adapter.bc');

// load mvc
VRELoader::import('library.mvc.controller');
VRELoader::import('library.mvc.table');
VRELoader::import('library.mvc.view');

// load helpers
VRELoader::import('library.availability.search');
VRELoader::import('library.availability.takeaway');
VRELoader::import('library.custfields.fields');
VRELoader::import('library.order.factory');
VRELoader::import('library.specialdays.manager');

// load component helper
VRELoader::import('lib_vikrestaurants');

// configure HTML helpers
if (JFactory::getApplication()->isClient('administrator'))
{
	JHtml::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');
	JTable::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
}

JHtml::addIncludePath(VRELIB . DIRECTORY_SEPARATOR . 'html');
