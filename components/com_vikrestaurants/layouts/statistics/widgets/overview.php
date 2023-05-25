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
 * Preload status codes popup for restaurant reservations.
 *
 * @since 1.8.3
 */
JHtml::_('vrehtml.statuscodes.popup', 1);

?>

<div class="canvas-align-top">
	
	<!-- widget contents go here -->

</div>

<script>

	/**
	 * Creates callback used to switch the selected room.
	 * Declares function only once.
	 *
	 * @param 	mixed 	link  The clicked element.
	 *
	 * @return 	void
	 */
	if (typeof overviewSwitchRoom !== 'function') {
		function overviewSwitchRoom(link) {
			// get widget element
			var widget = jQuery(link).closest('.dashboard-widget');

			// get button pane
			var pane = jQuery(link).data('pane');

			// deactivate all buttons
			jQuery(widget).find('.vrdash-tab-head a').removeClass('active');
			// active clicked button
			jQuery(link).addClass('active');

			// hide all panes
			jQuery(widget).find('.vrdash-tab-pane').hide();
			// show selected pane
			jQuery(widget).find('.vrdash-tab-pane[data-pane="' + pane + '"]').show();

			// register selected button in cookie
			document.cookie = 'vre.widget.<?php echo $widget->getName(); ?>.active.<?php echo $widget->getID(); ?>=' + pane + '; path=/';
		}
	}

	/**
	 * Register callback to be executed after
	 * completing the update request.
	 *
	 * @param 	mixed 	widget  The widget selector.
	 * @param 	string 	json    The JSON response.
	 * @param 	object  config  The widget configuration.
	 *
	 * @return 	void
	 */
	WIDGET_CALLBACKS[<?php echo $widget->getID(); ?>] = function(widget, json, config) {
		// get active table (wrapper)
		var table  = jQuery(widget).find('.vr-dash-roomcont-table:visible');
		var scroll = null;

		if (table.length) {
			// get table scroll left
			scroll = table.scrollLeft();

			if (typeof Storage !== 'undefined') {
				// register current table scroll
				localStorage.setItem('overviewTableScrollLeft', scroll);
			}
		} else {
			// recover scroll from storage
			scroll = localStorage.getItem('overviewTableScrollLeft');
		}

		// decode returned HTML
		var html = JSON.parse(json);

		// write HTML in document
		jQuery(widget).find('.canvas-align-top').html(html);

		if (!isNaN(scroll)) {
			// restore scroll position
			jQuery(widget).find('.vr-dash-roomcont-table:visible').scrollLeft(scroll);
		}
	}

</script>
