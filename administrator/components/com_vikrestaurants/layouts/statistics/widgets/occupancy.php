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

// load percentage circle
VREApplication::getInstance()->addStyleSheet(VREASSETS_ADMIN_URI . 'css/percentage-circle.css');

?>

<style>
	@media screen and (min-width: 1620px) {
		.percentage-chart {
			font-size: 240px;
		}
	}
	@media screen and (min-width: 1280px) and (max-width: 1619px) {
		.percentage-chart {
			font-size: 180px;
		}
	}
	@media screen and (min-width: 769px) {
		.canvas-align-center .percentage-chart.center {
			/* subtract badge heights to make chart more centered */
			margin-bottom: 18px;
		}
	}
</style>

<div class="canvas-align-center">
	
	<div class="c100 p0 center percentage-chart" data-start="0" data-end="100">
		<span class="amount">0%</span>
		<div class="slice">
			<div class="bar"></div>
			<div class="fill"></div>
		</div>
	</div>

</div>

<div class="widget-floating-box">

	<span class="badge badge-info pull-left date" style="margin-right:4px"></span>
	<span class="badge badge-important pull-left time"></span>

	<span class="badge badge-warning pull-right guests"></span>

</div>

<script>

	/**
	 * Defines a pool of chart timeouts, if undefined.
	 *
	 * @var object
	 */
	if (typeof OCCUPANCY_TIMEOUT === 'undefined') {
		var OCCUPANCY_TIMEOUT = {};
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

		// get widget ID
		var id = jQuery(widget).attr('id');

		// fetch color
		if (config.color && config.color.match(/^#?[0-9a-f]{6,6}$/i))
		{
			// use specified color
			config.color = config.color.replace(/^#/, '');
		}
		else
		{
			// use default one
			config.color = '307bbb';
		}

		// update badges
		jQuery(widget).find('.badge.date').text(data.date);
		jQuery(widget).find('.badge.time').text(data.time);

		jQuery(widget).find('.badge.guests').html(data.guests + ' <i class="fas fa-male"></i><i class="fas fa-male"></i>');

		// get chart box
		var chart = jQuery(widget).find('.percentage-chart');

		// reset animation only if the occupancy has changed
		if (data.occupancy != chart.attr('data-end')) {
			// clear existing timeout (if any) before starting a new one
			if (OCCUPANCY_TIMEOUT.hasOwnProperty(id)) {
				clearTimeout(OCCUPANCY_TIMEOUT[id]);
			}

			// get current progress
			var progress = chart.attr('data-start');

			// update chart steps
			chart.attr('data-start', 0)
				.attr('data-end', data.occupancy)
				.removeClass('p' + progress)
				.addClass('p0')
					.find('amount')
						.html('0%');

			// start animation
			OCCUPANCY_TIMEOUT[id] = updateOccupancyChartAnimation(widget);
		}

		// update chart colors
		chart.find('.fill, .bar').css('border-color', '#' + config.color);
		chart.find('.amount').css('color', '#' + config.color);
	}

	/**
	 * Define function to update the percentage progress only once.
	 *
	 * @var function
	 */
	if (typeof updateOccupancyChartAnimation !== 'function') {
		function updateOccupancyChartAnimation(widget) {
			// get chart
			var chart = jQuery(widget).find('.percentage-chart');

			// get current progress
			var progress = parseInt(chart.attr('data-start'));
			var updated  = progress + 1;
			var ceil     = parseInt(chart.attr('data-end'));

			// make sure the progress didn't exceed the maximum amount
			updated = Math.min(updated, ceil);

			// go to next animation step
			chart.removeClass('p' + progress).addClass('p' + updated);
			// update percentage text
			chart.find('.amount').text(updated + '%');

			// update progress
			chart.attr('data-start', updated);

			// re-launch animation recursively, in case it didn't end
			if (updated < ceil) {
				// get widget ID
				var id = jQuery(widget).attr('id');

				// register timeout
				OCCUPANCY_TIMEOUT[id] = setTimeout(function() {
					updateOccupancyChartAnimation(widget);
				}, 16);
			}
		}
	}

</script>
