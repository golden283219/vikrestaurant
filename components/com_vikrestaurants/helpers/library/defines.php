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

if (defined('WPINC'))
{
	// do not proceed in case of WordPress
	return;
}

// Software version
defined('VIKRESTAURANTS_SOFTWARE_VERSION') or define('VIKRESTAURANTS_SOFTWARE_VERSION', '1.8.5');

// Base path
define('VREBASE', JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikrestaurants');
define('VREBASE_URI', JUri::root() . 'components/com_vikrestaurants/');

// Admin path
define('VREADMIN', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikrestaurants');
define('VREADMIN_URI', JUri::root() . 'administrator/components/com_vikrestaurants/');

// Helpers path
define('VREHELPERS', VREBASE . DIRECTORY_SEPARATOR . 'helpers');

// Libraries path
define('VRELIB', VREBASE . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'library');

// Modules
define('VREMODULES', JPATH_SITE . DIRECTORY_SEPARATOR . 'modules');
define('VREMODULES_URI', JUri::root() . 'modules/');

// Assets URI
define('VREASSETS_URI', JUri::root() . 'components/com_vikrestaurants/assets/');
define('VREASSETS_ADMIN_URI', JUri::root() . 'administrator/components/com_vikrestaurants/assets/');

// Customers Uploads path
define('VRECUSTOMERS_AVATAR', VREBASE . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'customers');

// Customers Uploads path
define('VRECUSTOMERS_AVATAR_URI', VREASSETS_URI . 'customers/');

// Media path
define('VREMEDIA', VREBASE . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'media');

// Media small path
define('VREMEDIA_SMALL', VREBASE . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'media@small');

// Media URI
define('VREMEDIA_URI', JUri::root() . 'components/com_vikrestaurants/assets/media/');

// Media small URI
define('VREMEDIA_SMALL_URI', JUri::root() . 'components/com_vikrestaurants/assets/media@small/');

// Invoice path
define('VREINVOICE', VREHELPERS . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'archive');

// Invoice URI
define('VREINVOICE_URI', JUri::root() . 'components/com_vikrestaurants/helpers/pdf/archive/');
