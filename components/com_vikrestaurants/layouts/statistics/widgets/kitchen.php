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

/**
 * Layout variables
 * -----------------
 * @var  VREStatisticsWidget  $widget  The instance of the widget to be displayed.
 */
extract($displayData);

/**
 * Preload status codes popup for items.
 *
 * @since 1.8.3
 */
JHtml::_('vrehtml.statuscodes.popup', 3);

JText::script('VRSYSTEMCONNECTIONERR');

?>

<div class="canvas-align-top">
	
	<!-- widget contents go here -->

</div>

<script>

</script>
