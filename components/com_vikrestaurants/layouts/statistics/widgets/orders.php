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

// get active tab
$active = JFactory::getApplication()->input->cookie->get('vre_widget_' . $widget->getName() . '_active_' . $widget->getID(), 'latest');

$vik = VREApplication::getInstance();

JText::script('VRSYSTEMCONNECTIONERR');

?>

<style>

	.more-details {
		display: none;
	}

</style>

<div class="canvas-align-top">
	
	<!-- widget container -->

	<div class="vrdash-container">

		<!-- widget tabs -->

		<div class="vrdash-tab-head">
			<div class="vrdash-tab-button">
				<a href="javascript: void(0);" data-pane="latest" class="<?php echo ($active == 'latest' ? 'active' : ''); ?>">
					<?php echo JText::_('VRE_STATS_WIDGET_RESERVATIONS_LATEST_FIELD'); ?>
				</a>
			</div>

			<div class="vrdash-tab-button">
				<a href="javascript: void(0);" data-pane="incoming" class="<?php echo ($active == 'incoming' ? 'active' : ''); ?>">
					<?php echo JText::_('VRE_STATS_WIDGET_RESERVATIONS_INCOMING_FIELD'); ?>
				</a>
			</div>

			<div class="vrdash-tab-button">
				<a href="javascript: void(0);" data-pane="current" class="<?php echo ($active == 'current' ? 'active' : ''); ?>">
					<?php echo JText::_('VRE_STATS_WIDGET_RESERVATIONS_CURRENT_FIELD'); ?>
				</a>
			</div>
		</div>

		<!-- widget latest orders pane -->

		<div class="vrdash-tab-pane" data-pane="latest" style="<?php echo $active == 'latest' ? '' : 'display:none'; ?>">

		</div>

		<!-- widget incoming orders pane -->

		<div class="vrdash-tab-pane" data-pane="incoming" style="<?php echo $active == 'incoming' ? '' : 'display:none'; ?>">

		</div>

		<!-- widget current orders pane -->

		<div class="vrdash-tab-pane" data-pane="current" style="<?php echo $active == 'current' ? '' : 'display:none'; ?>">

		</div>

	</div>

</div>

<script>

	jQuery(document).ready(function() {
		// get widget element
		var widget = jQuery('#widget-<?php echo $widget->getID(); ?>');

		// register click event for tab buttons
		jQuery(widget).find('.vrdash-tab-head a').on('click', function() {
			// get button pane
			var pane = jQuery(this).data('pane');

			// deactivate all buttons
			jQuery(widget).find('.vrdash-tab-head a').removeClass('active');
			// active clicked button
			jQuery(this).addClass('active');

			// hide all panes
			jQuery(widget).find('.vrdash-tab-pane').hide();
			// show selected pane
			jQuery(widget).find('.vrdash-tab-pane[data-pane="' + pane + '"]').show();

			// register selected button in cookie
			document.cookie = 'vre.widget.<?php echo $widget->getName(); ?>.active.<?php echo $widget->getID(); ?>=' + pane + '; path=/';
		});
	});

	/**
	 * Lookup of orders widgets used to keep track
	 * of the "read more" rows that should be kept
	 * open after the widgets get refreshed.
	 *
	 * @var object
	 */
	if (typeof ORDERS_DETAILS_LOOKUP === 'undefined') {
		var ORDERS_DETAILS_LOOKUP = {};
	}

	/**
	 * Flag used to track the ID of the latest order 
	 * fetched. In this way, we can play a sound every
	 * time a new order is higher the the latest one.
	 *
	 * USE the same variable for each widget in order
	 * to avoid playing the sound more than once.
	 *
	 * @var integer
	 */
	if (typeof LATEST_ORDER_FETCHED === 'undefined') {
		var LATEST_ORDER_FETCHED = 0;
	}

	/**
	 * Register callback to be executed before
	 * launching the update request.
	 *
	 * @param 	mixed 	widget  The widget selector.
	 * @param 	object  config  The widget configuration.
	 *
	 * @return 	void
	 */
	WIDGET_PREFLIGHTS[<?php echo $widget->getID(); ?>] = function(widget, config) {
		// count disabled panes
		var disabled = 0;
		// reference to first enabled tab
		var firstEnabled = null;
		// flag to check if the current active pane is disabled
		var activeDisabled = false;

		var id = jQuery(widget).attr('id');

		// iterate panes and toggle them according to the widget config
		jQuery(widget).find('.vrdash-tab-head a').each(function() {
			var pane = jQuery(this).data('pane');

			if (config[pane]) {
				jQuery(this).show()
					.parent()
						.show();

				// register tab as first available, only
				// if the flag is still empty
				if (!firstEnabled) {
					firstEnabled = this;
				}
			} else {
				jQuery(this).hide()
					.parent()
						.hide();

				// increase disabled counter
				disabled++;
				// inform the caller that the active pane is disabled
				activeDisabled = activeDisabled || jQuery(this).hasClass('active');
			}
		});

		// hide all tabs in case only one is enabled
		if (disabled < 2) {
			jQuery(widget).find('.vrdash-tab-head').show();
		} else {
			jQuery(widget).find('.vrdash-tab-head').hide();
		}

		// in case the active pane was disabled, we should display the first one available
		if (activeDisabled && firstEnabled) {
			jQuery(firstEnabled).trigger('click');
		}

		// reset widget lookup
		ORDERS_DETAILS_LOOKUP[id] = [];

		// find all "more-details" rows currently open
		jQuery(widget).find('tr.more-details.open').each(function() {
			// register details in list
			ORDERS_DETAILS_LOOKUP[id].push(jQuery(this).data('id'));
		});
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
		// JSON decode response
		var data = jQuery.parseJSON(json);

		var id = jQuery(widget).attr('id');

		jQuery(widget).find('.vrdash-tab-pane').each(function() {
			// get pane id
			var pane = jQuery(this).data('pane');

			if (data[pane] !== undefined) {
				// fill body with returned HTML
				jQuery(this).html(data[pane]);
			} else {
				// set empty string
				jQuery(this).html('');
			}
		});

		// re-open details that were already open before refreshing the widget
		for (var i = 0; i < ORDERS_DETAILS_LOOKUP[id].length; i++) {
			var handle_id = ORDERS_DETAILS_LOOKUP[id][i];
			var handle    = jQuery(widget).find('a.more-details-handle[data-id="' + handle_id + '"]')[0];

			// toggle more details row
			toggleOrderDetails(handle);
		}

		var tmp_latest_id   = LATEST_ORDER_FETCHED;
		var firstDownload   = LATEST_ORDER_FETCHED == 0;
		var shouldPlaySound = false;

		// iterate all order IDs to look for a newer one
		jQuery(widget).find('tr[data-orderid]').each(function() {
			// extract order ID
			var id_order = parseInt(jQuery(this).data('orderid'));

			// the order is higher than the current one
			if (id_order > LATEST_ORDER_FETCHED) {
				// update flag
				LATEST_ORDER_FETCHED = id_order;
			}

			// Make sure we are not doing the first download of the session.
			// Compare order ID with previous one in order to mark all the
			// new records instead of the latest one.
			if (!firstDownload  && id_order > tmp_latest_id) {
				shouldPlaySound = true;

				// mark record as "to be seen"
				jQuery(this).find('td')
					.first().find('.actions-group')
						.append(
							'<a href="javascript:void(0);" onclick="jQuery(this).remove();">\n'+
								'<i class="fas fa-eye"></i>\n'+
							'</a>\n'
						);
			}
		});

		jQuery(widget).find('.hasTooltip').tooltip();

		// play notification sound in case of new orders
		// and in case we are not doing the first download
		if (shouldPlaySound) {
			playNotificationSound();
		}
	}

	/**
	 * The callback below is invoked every time the 
	 * CONFIRM button is clicked. The button is shown
	 * only for PENDING orders and it is needed to
	 * change the status to CONFIRMED.
	 *
	 * Declares the function only once.
	 *
	 * @param 	integer  id 	 The reservation to confirm.
	 * @param 	integer  widget  The ID of the widget.
	 * @param 	mixed 	 btn 	 The button clicked.
	 *
	 * @return 	void
	 */
	if (typeof ordersConfirmEvent !== 'function') {
		function ordersConfirmEvent(id, widget, btn) {
			// make confirmation request
			ordersMakeRequest('confirmajax', {cid: [id]}, widget, btn);
		}
	}

	/**
	 * The callback below is invoked every time the 
	 * REFUSE button is clicked. The button is shown
	 * only for PENDING orders and it is needed to
	 * change the status to REMOVED.
	 *
	 * Declares the function only once.
	 *
	 * @param 	integer  id 	 The reservation to refuse.
	 * @param 	integer  widget  The ID of the widget.
	 * @param 	mixed 	 btn 	 The button clicked.
	 *
	 * @return 	void
	 */
	if (typeof ordersRefuseEvent !== 'function') {
		function ordersRefuseEvent(id, widget, btn) {
			// make refuse request
			ordersMakeRequest('refuseajax', {cid: [id]}, widget, btn);
		}
	}

	/**
	 * The callback below is invoked every time the 
	 * NOTIFY button is clicked. The button is shown
	 * only for CONFIRMED orders that have been 
	 * accepted through this widget. A notification
	 * e-mail is sent to the customers.
	 *
	 * Declares the function only once.
	 *
	 * @param 	integer  id 	 The reservation to refuse.
	 * @param 	integer  widget  The ID of the widget.
	 * @param 	mixed 	 btn 	 The button clicked.
	 *
	 * @return 	void
	 */
	if (typeof ordersNotifyEvent !== 'function') {
		function ordersNotifyEvent(id, widget, btn) {
			// make refuse request
			ordersMakeRequest('notifyajax', {cid: [id]}, widget, btn);
		}
	}

	/**
	 * The callback below is used to request an action
	 * to the orders controller.
	 * Declares the function only once.
	 *
	 * @param 	string 	 task    The task to reach.
	 * @param 	object   data    The data to post.
	 * @param 	integer  widget  The ID of the widget.
	 * @param 	mixed 	 btn 	 The button clicked.
	 *
	 * @return 	void
	 */
	if (typeof ordersMakeRequest !== 'function') {
		function ordersMakeRequest(task, data, widget, btn) {
			// first of all, hide the buttons to avoid
			// the users click it twice
			jQuery(btn).parent().hide();

			var url = '<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&tmpl=component&group=2', false); ?>';
			url += '&task=oversight.' + task;

			// make request
			UIAjax.do(
				url,
				data,
				function(resp) {
					// refresh the contents of the widget
					updateWidgetContents(widget);
				},
				function(error) {
					// show buttons again on failure
					jQuery(btn).parent().show();

					if (!error.responseText) {
						// use default connection lost error
						error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
					}

					// raise error
					alert(error.responseText);
				}
			);
		}
	}

	/**
	 * Toggles the "read more" rows of the tables.
	 * Declares function only one.
	 *
	 * @param 	mixed 	link  The clicked link.
	 *
	 * @return 	void
	 */
	if (typeof toggleOrderDetails !== 'function') {
		function toggleOrderDetails(link) {
			jQuery(link).closest('tr')
				.next('tr.more-details')
					.toggle()
					.toggleClass('open');
		}
	}

	/**
	 * Function used to play a notification sound.
	 * Declares function only once.
	 *
	 * @return 	void
	 */
	if (typeof playNotificationSound !== 'function') {
		function playNotificationSound() {
			// fetch sound path
			var src = '<?php echo VikRestaurants::getNotificationSound(); ?>';
			// Try to play the sound.
			// Make sure the same sound is not played
			// again for the next 5 seconds.
			SoundTry.playOnce(src, 5000);
		}
	}

</script>
