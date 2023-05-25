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

JHtml::_('vrehtml.assets.contextmenu');

$vik = VREApplication::getInstance();

JText::script('VRE_STATS_WIDGET_TIMES_ACTION_INCREASE_N');
JText::script('VRE_STATS_WIDGET_TIMES_ACTION_DECREASE_N');
JText::script('VRE_STATS_WIDGET_TIMES_ACTION_BLOCK');

?>

<div class="avail-times-widget-wrapper">
	
	<!-- widget contents go here -->

</div>

<script>

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
		var table  = jQuery(widget).find('.avail-times-table');
		var scroll = null;

		if (table.length) {
			// get table scroll top
			scroll = table.scrollTop();

			if (typeof Storage !== 'undefined') {
				// register current table scroll
				sessionStorage.setItem('availTimesScrollTop', scroll);
			}
		} else {
			// recover scroll from storage
			scroll = sessionStorage.getItem('availTimesScrollTop');
		}

		// decode returned HTML
		var html = JSON.parse(json);

		// write HTML in document
		jQuery(widget).find('.avail-times-widget-wrapper').html(html);

		// get max height of the body
		var maxHeight = jQuery(widget).find('.widget-body').height();

		// register a max height equals to the parent, in order to avoid
		// exceeding with the vertical space
		jQuery(widget).find('.avail-times-table').css('max-height', Math.max(400, maxHeight) + 'px');

		if (!isNaN(scroll)) {
			// restore scroll position
			jQuery(widget).find('.avail-times-table').scrollTop(scroll);
		}
	}

	jQuery(function($) {
		if (typeof $.fn.availTimesPopup === 'function') {
			// function already registered
			return;
		}

		// create function for popup init
		$.fn.availTimesPopup = function() {
			// increase/decrease callback
			var availTimesIncreaseAction = function(amount, root, event) {
				// pause dashboard timer as long as the request is doing
				stopDashboardListener();

				// create request
				new Promise((resolve, reject) => {
					// retrieve slot
					var slot = $(root).closest('[data-time]');

					// perform AJAX request
					UIAjax.do(
						// end-point URL
						'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=tkreservation.increasetimeslotajax'); ?>',
						// POST data
						{
							date: slot.data('date'),
							hourmin: slot.data('time'),
							units: amount,
						},
						// success callback
						function(resp) {
							resolve(resp);
						},
						// failure callback
						function(error) {
							reject(error);
						}
					);
				}).then((data) => {
					// refresh widget on success
					updateWidgetContents('<?php echo $widget->getID(); ?>');
				}).catch((error) => {
					if (!error.responseText) {
						// use generic error
						error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
					}

					// alert error message
					alert(error.responseText);
				}).finally(() => {
					// restart dashboard timer after completing the request
					startDashboardListener();
				});
			};

			// disabled check for decrease buttons
			var availTimesDecreaseDisabled = function(amount, root, config) {
				// retrieve slot
				var slot = $(root).closest('[data-time]');

				// get orders count
				var count = parseInt(slot.attr('data-orders-count'));
				// get max orders
				var max = parseInt(slot.attr('data-orders-max'));

				// disable in case the subtraction results in a value
				// lower than the current number of orders
				return max - amount <= count;
			};

			// set up context menu
			$(this).vikContextMenu({
				// defines buttons list
				buttons: [
					// INCREASE BY ONE
					{
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_INCREASE_N').replace(/%d/, 1),
						action: (root, event) => {
							availTimesIncreaseAction(1, root, event);
						},
					},
					// INCREASE BY TWO
					{
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_INCREASE_N').replace(/%d/, 2),
						action: (root, event) => {
							availTimesIncreaseAction(2, root, event);
						},
					},
					// INCREASE BY FIVE
					{
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_INCREASE_N').replace(/%d/, 5),
						separator: true,
						action: (root, event) => {
							availTimesIncreaseAction(5, root, event);
						},
					},
					// DECREASE BY ONE
					{
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_DECREASE_N').replace(/%d/, 1),
						action: (root, event) => {
							availTimesIncreaseAction(-1, root, event);
						},
						disabled: (root, config) => {
							return availTimesDecreaseDisabled(1, root, config);
						},
					},
					// DECREASE BY TWO
					{
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_DECREASE_N').replace(/%d/, 2),
						action: (root, event) => {
							availTimesIncreaseAction(-2, root, event);
						},
						disabled: (root, config) => {
							return availTimesDecreaseDisabled(2, root, config);
						},
					},
					// DECREASE BY FIVE
					{
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_DECREASE_N').replace(/%d/, 5),
						separator: true,
						action: (root, event) => {
							availTimesIncreaseAction(-5, root, event);
						},
						disabled: (root, config) => {
							return availTimesDecreaseDisabled(5, root, config);
						},
					},
					// BLOCK TIMES
					{
						icon: 'fas fa-times-circle',
						text: Joomla.JText._('VRE_STATS_WIDGET_TIMES_ACTION_BLOCK'),
						class: 'danger',
						action: (root, event) => {
							// retrieve slot
							var slot = $(root).closest('[data-time]');

							// get orders count
							var count = parseInt(slot.attr('data-orders-count'));
							// get max orders
							var max = parseInt(slot.attr('data-orders-max'));

							// decrease by the number needed to close the slot
							availTimesIncreaseAction((max - count) * -1, root, event);
						},
						disabled: (root, config) => {
							return availTimesDecreaseDisabled(0, root, config);
						},
					},
				],
			});
		};
	});
</script>
