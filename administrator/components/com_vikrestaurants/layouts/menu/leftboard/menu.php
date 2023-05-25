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

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $html        The menu HTML.
 * @var   boolean  $compressed  True if the menu is compressed.
 */

?>

<a class="btn mobile-only" id="vre-menu-toggle-phone">
	<i class="fas fa-bars"></i>
	<?php echo JText::_('VRE_MENU'); ?>
</a>

<div class="vre-leftboard-menu<?php echo $compressed ? ' compressed' : ''; ?>" id="vre-main-menu">
	<?php echo $html; ?>
</div>