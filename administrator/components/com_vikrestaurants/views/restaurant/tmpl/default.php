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

JHtml::_('formbehavior.chosen');
JHtml::_('vrehtml.assets.chartjs');
JHtml::_('vrehtml.assets.fontawesome');

$vik = VREApplication::getInstance();

$rest_on_dash = VikRestaurants::isRestaurantOnDashboard();
$take_on_dash = VikRestaurants::isTakeAwayOnDashboard();

$reservations_allowed = VikRestaurants::isReservationsAllowed();
$tkorders_allowed     = VikRestaurants::isTakeAwayReservationsAllowed();

// get active tab
$active = JFactory::getApplication()->input->cookie->get('vre_dashboard_active', 'restaurant');

if ($active == 'restaurant' && !$rest_on_dash)
{
	// switch to take-away
	$active = 'takeaway';
}
else if ($active == 'takeaway' && !$take_on_dash)
{
	// switch to restaurant
	$active = 'restaurant';
}

$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_vikrestaurants');

?>

<script type="text/javascript">
	
	/**
	 * Prepare any chart to be responsive.
	 */
	Chart.defaults.global.responsive = true;

	/**
	 * Keep a reference of the widget that was clicked
	 * to update its configuration.
	 *
	 * @var integer
	 */
	var SELECTED_WIDGET = null;

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

<!-- TRYING CUTOM STYLE -->

<style>

	<?php
	if ($this->layout == 'floating')
	{
		/* change body background when the layout is FLOATING */
		?>
		body {
			background-color: #f3f2f6;
		}
		<?php
	}
	?>

</style>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<div class="vr-dash-main">

		<div class="vrdash-tab-head">
			<?php
			if ($rest_on_dash)
			{
				?>
				<div class="vrdash-tab-button section-tab">
					<a href="javascript: void(0);" onClick="switchDashboardSection('restaurant', this);" class="<?php echo ($active == 'restaurant' ? 'active' : ''); ?>">
						<strong><?php echo JText::_('VRMANAGECONFIGTITLE1'); ?></strong>
					</a>
				</div>
				<?php
			}

			if ($take_on_dash)
			{
				?>
				<div class="vrdash-tab-button section-tab">
					<a href="javascript: void(0);" onClick="switchDashboardSection('takeaway', this);" class="<?php echo ($active == 'takeaway' ? 'active' : ''); ?>">
						<strong><?php echo JText::_('VRMANAGECONFIGTITLE2'); ?></strong>
					</a>
				</div>
				<?php
			}
			?>
		</div>

		<div class="vrdash-refresh-time">
			<!-- do not display timer by default -->
			<a href="javascript: void(0);" onclick="toggleDashboardListener();" style="display:none;">
				<?php echo JText::_('VRREFRESHIN'); ?>&nbsp;<span id="refresh-time">--</span>
			</a>

			<?php
			if ($user->authorise('core.access.reservations', 'com_vikrestaurants') && $canEdit)
			{
				?>
				<!-- don't panic button for restaurant -->
				<span class="restaurant-elem" style="<?php echo $active == 'restaurant' ? '' : 'display:none;'; ?>">
					<?php
					if ($reservations_allowed)
					{
						$btn  = 'btn-danger';
						$text = JText::_('VRSTOPINCOMINGRES');
					}
					else
					{
						$btn  = 'btn-success';
						$text = JText::_('VRSTARTINCOMINGRES');
					}
					?>
					<button type="button" class="btn <?php echo $btn; ?>" onClick="openStatusReservationsDialog(<?php echo $reservations_allowed ? 0 : 1; ?>, 'restaurant');">
						<?php echo $text; ?>
					</button>
				</span>
				<?php
			}

			if ($user->authorise('core.access.tkorders', 'com_vikrestaurants') && $canEdit)
			{
				?>
				<!-- don't panic button for take-away -->
				<span class="takeaway-elem" style="<?php echo $active == 'takeaway' ? '' : 'display:none;'; ?>">
					<?php
					if ($tkorders_allowed)
					{
						$btn  = 'btn-danger';
						$text = JText::_('VRSTOPINCOMINGORD');
					}
					else
					{
						$btn  = 'btn-success';
						$text = JText::_('VRSTARTINCOMINGORD');
					}
					?>
					<button type="button" class="btn <?php echo $btn; ?>" onClick="openStatusReservationsDialog(<?php echo $tkorders_allowed ? 0 : 1; ?>, 'takeaway');">
						<?php echo $text; ?>
					</button>
				</span>
				<?php
			}
			?>
		</div>

		<?php
		if ($rest_on_dash)
		{
			?>
			<div class="vr-dash-section" id="vr-dash-section-restaurant" style="<?php echo ($active == 'restaurant' ? '' : 'display:none;'); ?>">
				<?php
				// register restaurant dashboard in a property of
				// the view for being recovered in the sub-template
				$this->currentDashboard = $this->dashboard['restaurant'];

				echo $this->loadTemplate('group_dashboard');
				?>
			</div>
			<?php
		}

		if ($take_on_dash)
		{
			?>
			<div class="vr-dash-section" id="vr-dash-section-takeaway" style="<?php echo ($active == 'takeaway' ? '' : 'display:none;'); ?>">
				<?php
				// register restaurant dashboard in a property of
				// the view for being recovered in the sub-template
				$this->currentDashboard = $this->dashboard['takeaway'];

				echo $this->loadTemplate('group_dashboard');
				?>
			</div>
			<?php
		}
		?>

	</div>
	
	<input type="hidden" name="group" value="<?php echo $active; ?>" />
	<input type="hidden" name="location" value="dashboard" />
	<input type="hidden" name="view" value="restaurant" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
// order details modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-respinfo',
	array(
		'title'       => JText::_('VRMANAGERESERVATION7'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'footer'      => '<a href="#" class="btn btn-success" id="edit-reservation-btn">' . JText::_('VREDIT') . '</a>',
		'url'		  => '', // it will be filled dinamically
	)
);

// customer details modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-custinfo',
	array(
		'title'       => JText::_('VRMANAGERESERVATION17'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);

// render inspector to manage widgets configuration
echo JHtml::_(
	'vrehtml.inspector.render',
	'widget-config-inspector',
	array(
		'title'       => JText::_('VRMENUCONFIG'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => '<button type="button" class="btn btn-success" id="widget-save-config" data-role="save">' . JText::_('JAPPLY') . '</button>',
		'width'       => 400,
	),
	$this->loadTemplate('widget_config')
);

JText::script('VRSHORTCUTSECOND');
JText::script('VRSHORTCUTMINUTE');

JText::script('VRSTARTRESDIALOGMESSAGE');
JText::script('VRSTOPRESDIALOGMESSAGE');
JText::script('VRSTARTORDDIALOGMESSAGE');
JText::script('VRSTOPORDDIALOGMESSAGE');

JText::script('JYES');
JText::script('JCANCEL');
?>

<script>

	/**
	 * A constant containing the seconds needed to
	 * launch the dashboard refresh.
	 *
	 * @var integer
	 */
	const DASH_REFRESH_TIME = <?php echo VikRestaurants::getDashRefreshTime(); ?>;

	/**
	 * Counts the seconds passed since the last refresh.
	 *
	 * @var integer
	 */
	var DASH_REFRESH_COUNT = 0;

	/**
	 * The dashboard interval timer.
	 *
	 * @var mixed
	 */
	var DASH_THREAD = null;

	/**
	 * Opens the page related to the selected tab.
	 *
	 * @param 	string 	page
	 * @param 	mixed 	link
	 *
	 * @return 	void
	 */
	function switchDashboardSection(page, link) {
		// deactivate all sections
		jQuery('.vrdash-tab-button.section-tab a').removeClass('active');
		// make clicked link active
		jQuery(link).addClass('active');
		
		// hide all sections
		jQuery('.vr-dash-section').hide();
		// show selected section
		jQuery('#vr-dash-section-' + page).show();

		if (page == 'restaurant') {
			jQuery('.takeaway-elem').hide();
			jQuery('.restaurant-elem').show();
		} else {
			jQuery('.restaurant-elem').hide();
			jQuery('.takeaway-elem').show();
		}

		// update group input for widgets management
		jQuery('#adminForm input[name="group"]').val(page);
		
		/**
		 * Register dashboard active tab in a cookie.
		 *
		 * @since 1.8
		 */
		document.cookie = 'vre.dashboard.active=' + page + '; path=/';
	}

	/**
	 * Starts the dashboard timer for next refresh.
	 *
	 * @return 	void
	 */
	function startDashboardListener() {
		if (DASH_THREAD) {
			// stop thread first if it is already running
			stopDashboardListener();
		}

		// refreshes the timer expiration label
		updateRemainingTime(DASH_REFRESH_TIME - DASH_REFRESH_COUNT);

		// creates the interval
		DASH_THREAD = setInterval(refreshDashboardListener, 1000);
	}

	/**
	 * Stops the dashboard timer.
	 *
	 * @return 	void
	 */
	function stopDashboardListener() {
		clearInterval(DASH_THREAD);

		DASH_THREAD = null;
	}

	/**
	 * Toggles the dashboard timer.
	 * If it was running, the timer is stopped, otherwise
	 * it is restarted from its last position.
	 *
	 * @return 	void
	 */
	function toggleDashboardListener() {
		if (DASH_THREAD) {
			// stop timer if running
			stopDashboardListener();
		} else {
			// restart timer
			startDashboardListener();
		}
	}

	/**
	 * Callback used by the refresh interval.
	 * In case the timeout expired, launches the
	 * request to refresh the widgets.
	 *
	 * @return 	void
	 */
	function refreshDashboardListener() {
		DASH_REFRESH_COUNT++;

		// refresh timer label
		updateRemainingTime(DASH_REFRESH_TIME - DASH_REFRESH_COUNT);

		// check if the timer expired
		if (DASH_REFRESH_COUNT >= DASH_REFRESH_TIME) {
			// reset counter
			DASH_REFRESH_COUNT = 0;

			// stop timer
			stopDashboardListener();

			// launch dashboard refresh
			refreshDashboard();

			// restart dashboard timer
			startDashboardListener();
		}
	}

	/**
	 * Refreshes the seconds within the timer label.
	 *
	 * @param 	integer  remaining  The remaining time in seconds.
	 * @param 	mixed 	 label      An optional label to use to which the
	 * 								time will be appended.
	 * 
	 * @return 	void
	 */
	function updateRemainingTime(remaining, label) {
		if (typeof label !== 'string') {
			label = '';
		}

		// check if remains less than 60 seconds
		if (remaining < 60) {
			// make sure the timer didn't expire
			if (remaining > 0) {
				// append remaining seconds
				label += (label.length > 0 ? ' & ' : '') + remaining + ' ' + Joomla.JText._('VRSHORTCUTSECOND');
			} else if (!label.length) {
				// display 0 seconds
				label = '0 ' + Joomla.JText._('VRSHORTCUTSECOND');
			}

			jQuery('#refresh-time').text(label);
		} else {
			// recursive update
			updateRemainingTime(remaining % 60, label + (Math.floor(remaining / 60)) + ' ' + Joomla.JText._('VRSHORTCUTMINUTE'));
		}
	}
	
	/**
	 * Refreshes all the dashboard widgets.
	 *
	 * @return 	void
	 */
	function refreshDashboard() {
		<?php
		// iterate both groups
		foreach ($this->dashboard as $dashboard)
		{
			// iterate dashboard widgets
			foreach ($dashboard as $widgets)
			{
				// iterate position widgets
				foreach ($widgets as $widget)
				{
					// load widget contents once the page is ready
					?>
					updateWidgetContents('<?php echo $widget->getID(); ?>');
					<?php
				}
			}
		}
		?>
	}

	/**
	 * A pool containing the active AJAX requests for each
	 * widget, so that we can abort an existing request
	 * before launching a new one.
	 *
	 * @var object
	 */
	var CHARTS_REQUESTS_POOL = {};

	jQuery(document).ready(function() {

		// fill the form before showing the inspector
		jQuery('#widget-config-inspector').on('inspector.show', function() {
			setupWidgetConfig(SELECTED_WIDGET);

			// stop dashboard timer as long as the widget configuration is open
			stopDashboardListener();
		});

		jQuery('#widget-config-inspector').on('inspector.close', function() {
			// restart dashboard timer after closing the configuration of the widget
			startDashboardListener();
		});

		// refresh widget
		jQuery('#widget-save-config').on('click', function() {
			// refresh the contents displayed within the widget
			updateWidgetContents(SELECTED_WIDGET);

			// dismiss inspector
			jQuery('#widget-config-inspector').inspector('dismiss');
		});

		// immediately loads the widgets
		refreshDashboard();

		// start refresh timer
		startDashboardListener();
	});

	function openWidgetConfiguration(widget) {
		SELECTED_WIDGET = widget;

		// open inspector
		vreOpenInspector('widget-config-inspector');
	}

	function updateWidgetContents(id, config) {
		if (typeof config === 'undefined') {
			// get widget configuration if not specified
			config = getWidgetConfig(id);
		}

		// abort any existing request already made for this widget
		if (CHARTS_REQUESTS_POOL.hasOwnProperty(id)) {
			CHARTS_REQUESTS_POOL[id].abort();
		}

		// keep a reference to the widget
		var box = jQuery('#widget-' + id);

		// get widget class
		var widget = box.data('widget');

		// prepare request data
		Object.assign(config, {
			id:     id,
			widget: widget,
			group:  getWidgetGroup(id),
		});

		if (!config.widget) {
			// missing widget name, the related section is
			// probably disabled or hidden
			return false;
		}

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
			'index.php?option=com_vikrestaurants&task=statistics.loadwidgetdata&tmpl=component',
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

	function vrOpenJModal(id, cid, group) {
		if (group == 'restaurant') {
			switch (id) {
				case 'respinfo':
					url = 'index.php?option=com_vikrestaurants&view=orderinfo&tmpl=component&id=' + cid;
					jQuery('#edit-reservation-btn').attr('href', 'index.php?option=com_vikrestaurants&from=restaurant&task=reservation.edit&cid[]=' + cid);
					break;

				case 'custinfo':
					url = 'index.php?option=com_vikrestaurants&view=customerinfo&locations=0tmpl=component&id=' + cid;
					break;
			}
		} else {
			switch (id) {
				case 'respinfo':
					url = 'index.php?option=com_vikrestaurants&view=tkorderinfo&tmpl=component&id=' + cid;
					jQuery('#edit-reservation-btn').attr('href', 'index.php?option=com_vikrestaurants&from=restaurant&task=tkreservation.edit&cid[]=' + cid);
					break;

				case 'custinfo':
					url = 'index.php?option=com_vikrestaurants&view=customerinfo&tmpl=component&id=' + cid;
					break;
			}
		}

		var jqmodal = true;

		<?php echo $vik->bootOpenModalJS(); ?>
	}

	/**
	 * Create dialog used to handle start/stop incoming reservations buttons.
	 *
	 * @var VikConfirmDialog
	 */
	var statusDialog = new VikConfirmDialog(null, 'vik-statusres-dialog');

	// submit request
	statusDialog.addButton(Joomla.JText._('JYES'), function(args, event) {
		// fetch controller to use
		var controller = args.group == 'restaurant' ? 'reservation' : 'tkreservation';
		// fetch task
		var task = args.start ? 'startincoming' : 'stopincoming';

		// submit request
		Joomla.submitbutton(controller + '.' + task);
	});

	// cancel request
	statusDialog.addButton(Joomla.JText._('JCANCEL'));

	function openStatusReservationsDialog(start, group) {
		var message;

		if (start) {
			// currently stopped, ask to restart it
			message = group == 'restaurant' ? 'VRSTARTRESDIALOGMESSAGE' : 'VRSTARTORDDIALOGMESSAGE';
		} else {
			// currently active, ask to stop it
			message = group == 'restaurant' ? 'VRSTOPRESDIALOGMESSAGE' : 'VRSTOPORDDIALOGMESSAGE';
		}

		// translate and try to add date (tomorrow)
		var tomorrow = new Date();
		tomorrow.setDate(tomorrow.getDate() + 1);

		message = Joomla.JText._(message).replace(/%s/, tomorrow.toLocaleDateString());

		// update dialog message
		statusDialog.setMessage(message);
		// show dialog
		statusDialog.show({start: start, group: group});
	}
	
</script>
