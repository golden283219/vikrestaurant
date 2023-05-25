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
 * @var  string 	$widget    The name of the widget to instantiate.
 * @var  string 	$group     The group to which the widget belongs.
 * @var  mixed 		$config    An optional configuration array for the widget.
 * @var  integer 	$timer     The timeout interval in seconds.
 * @var  mixed 		$itemid    An optional menu item ID.
 */
extract($displayData);

VRELoader::import('library.statistics.factory');

// instantiate specified widget
$widget = VREStatisticsFactory::getInstance($widget, $group);

if (empty($config))
{
	// use empty object for configuration
	$config = new stdClass;
}

if (empty($timer))
{
	// use default timer interval (1 minute)
	$timer = 60;
}

if (!isset($itemid))
{
	// use current itemid
	$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');
}

$vik = VREApplication::getInstance();
?>

<script>

	/**
	 * A lookup of preflights to be used before refreshing
	 * the contents of the widgets.
	 *
	 * If needed, a widget can register its own callback
	 * to be executed before the AJAX request is started.
	 *
	 * The property name MUST BE equals to the ID of 
	 * the widget that is registering its callback.
	 *
	 * @var object
	 */
	var WIDGET_PREFLIGHTS = {};

	/**
	 * A lookup of callbacks to be used when refreshing
	 * the contents of the widgets.
	 *
	 * If needed, a widget can register its own callback
	 * to be executed once the AJAX request is completed.
	 *
	 * The property name MUST BE equals to the ID of 
	 * the widget that is registering its callback.
	 *
	 * @var object
	 */
	var WIDGET_CALLBACKS = {};

</script>

<div class="row-fluid" style="margin-top:2px;">

	<div class="dashboard-widgets-container" data-position="center">

		<div
			class="dashboard-widget"
			id="widget-<?php echo $widget->getID(); ?>"
			data-widget="<?php echo $widget->getName(); ?>"
			style="flex:1;"
		>

			<div class="widget-wrapper">
				<div class="widget-body">
					<?php echo $widget->display(); ?>
				</div>

				<div class="widget-error-box" style="display: none;">
					<?php echo JText::_('VRE_AJAX_GENERIC_ERROR'); ?>
				</div>
			</div>

		</div>

	</div>

</div>

<script>

	var DASHBOARD_THREAD;
	var DASHBOARD_THREAD_START_TIME;
	var DASHBOARD_THREAD_INTERVAL = <?php echo $timer * 1000; ?>;

	jQuery(document).ready(function() {

		// immediately load contents
		refreshDashboardListener();

	});

	function startDashboardListener(ms) {
		// refresh dashboard every minute
		DASHBOARD_THREAD = setTimeout(refreshDashboardListener, ms ? ms : DASHBOARD_THREAD_INTERVAL);
	}

	function stopDashboardListener() {
		// clear dashboard thread
		clearTimeout(DASHBOARD_THREAD);
	}

	function refreshDashboardListener() {
		if (DASHBOARD_THREAD) {
			clearTimeout(DASHBOARD_THREAD);
		}

		DASHBOARD_THREAD_START_TIME = new Date();

		updateWidgetContents(<?php echo $widget->getID(); ?>);

		startDashboardListener();
	}

	function waitListenerForAction(promise) {
		// freeze current time
		var now = new Date();

		// stop dashboard listener
		stopDashboardListener();

		// calculate remaining time since the last execution
		var remaining = Math.abs(DASHBOARD_THREAD_INTERVAL - Math.floor(now - DASHBOARD_THREAD_START_TIME));

		// wait until the promise ends
		promise.finally(() => {
			// restart thread	
			startDashboardListener(remaining);
		});
	}

	/**
	 * A pool containing the active AJAX requests for each
	 * widget, so that we can abort an existing request
	 * before launching a new one.
	 *
	 * @var object
	 */
	var CHARTS_REQUESTS_POOL = {};

	function updateWidgetContents(id, config) {
		// abort any existing request already made for this widget
		if (CHARTS_REQUESTS_POOL.hasOwnProperty(id)) {
			CHARTS_REQUESTS_POOL[id].abort();
		}

		if (typeof config !== 'object') {
			config = <?php echo json_encode($config); ?>;
		}

		// keep a reference to the widget
		var box = jQuery('#widget-' + id);

		// get widget class
		var widget = box.data('widget');

		// prepare request data
		Object.assign(config, {
			id:     id,
			widget: widget,
			group:  '<?php echo $group; ?>',
		});

		// hide generic error message
		jQuery(box).find('.widget-error-box').hide();
		// show widget body
		jQuery(box).find('.widget-body').show();

		if (WIDGET_PREFLIGHTS.hasOwnProperty(id)) {
			// let the widget prepares the contents without
			// waiting for the request completion
			WIDGET_PREFLIGHTS[id](box, config);
		}

		// make request to load widget dataset
		var xhr = UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=oversight.loadwidgetdata&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			config,
			function(resp) {
				// delete request from pool
				delete CHARTS_REQUESTS_POOL[id];

				// check if the widget registered its own update method
				if (WIDGET_CALLBACKS.hasOwnProperty(id)) {
					// let the widget callback finalizes the update
					WIDGET_CALLBACKS[id](box, resp, config);
				} else {
					var html = resp;

					// otherwise auto-replace the widget body
					try {
						// try to decode HTML
						html = JSON.parse(resp);

						if (Array.isArray(html)) {
							// join the responses
							html = html.join("\n");
						}
					} catch (err) {
						// no JSON string, plain HTML was returned
					}

					// replace widget body
					jQuery(box).find('.widget-body').html(html);
				}
			},
			function(error) {
				// delete request from pool
				delete CHARTS_REQUESTS_POOL[id];

				// hide widget body
				jQuery(box).find('.widget-body').hide();
				// show generic error message
				jQuery(box).find('.widget-error-box').show();
			}
		);

		// update request pool
		CHARTS_REQUESTS_POOL[id] = xhr;
	}

</script>
