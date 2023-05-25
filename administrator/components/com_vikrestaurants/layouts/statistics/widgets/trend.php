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

JText::script('VRE_N_PEOPLE');
JText::script('VRE_N_PEOPLE_1');
JText::script('VRE_N_RESERVATIONS');
JText::script('VRE_N_RESERVATIONS_1');

?>

<div class="canvas-align-bottom">
	<canvas></canvas>
</div>

<script>

	/**
	 * Defines a pool of charts, if undefined.
	 *
	 * @var object
	 */
	if (typeof TREND_CHARTS === 'undefined') {
		var TREND_CHARTS = {};
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
			config.color = '32acd1';
		}

		/**
		 * Create callback used to format the values displayed
		 * on the Y axis, according to the saved configuration.
		 *
		 * @param 	mixed 	value  The value passed by the chart.
		 *
		 * @return 	string  The formatted value.
		 */
		function formatAxisY(value) {
			// format as currency in case of earning
			if (config.valuetype == 'earning') {
				// do not display decimal values on Y axis
				return Currency.getInstance().format(value, 0);
			}

			// otherwise just return the plain number
			return value;
		}

		/**
		 * Create callback used to format the values displayed
		 * with the tooltips of the hovered points, according
		 * to the saved configuration.
		 *
		 * @param 	mixed 	item  The item to display.
		 * @param 	mixed 	data  The chart data.
		 *
		 * @return 	string  The formatted value.
		 */
		function formatPointTooltip(item, data) {
			var label = '';

			// format as currency in case of earning
			if (config.valuetype == 'earning') {
				label = Currency.getInstance().format(item.value);
			}
			// format number of guests/reservations
			else
			{
				var count = parseInt(item.value);
				var langk = config.valuetype == 'guests' ? 'VRE_N_PEOPLE' : 'VRE_N_RESERVATIONS';

				// format label by fetching singular/plural form
				if (count == 1)
				{
					label = Joomla.JText._(langk + '_1');
				} else {
					label = Joomla.JText._(langk).replace(/%d/, count);
				}
			}

			return ' ' + label;
		}

		// init chart from scratch if NULL
		if (!TREND_CHARTS.hasOwnProperty(id)) {
			// prepare chart data
			var chartData = {
				labels: Object.keys(data),
				datasets: [
					{
						// the label string that appears when hovering the mouse above the lines intersection points
						label: "Dataset",
						// the background color drawn behind the line (33 = 20% opacity)
						backgroundColor: "#" + config.color + "33",
						// the fill color of the line
						borderColor: "#" + config.color,
						// the fill color of the points
						pointBackgroundColor: "#" + config.color,
						// the border color of the points
						pointBorderColor: "#fff",
						// the radius of the points (in pixel)
						pointRadius: 4,
						// the fill color of the points when hovered
						pointHoverBackgroundColor: "#fff",
						// the border color of the points when hovered
						pointHoverBorderColor: "#" + config.color,
						// the radius of the points (in pixel) when hovered
						pointHoverRadius: 5,
						// the line dataset
						data: Object.values(data),
					},
				],
			};
			
			// prepare chart configuration
			var options = {
				// turn off legend
				legend: {
					display: false,
				},
				// axes handling
				scales: {
					// Y Axis properties
					yAxes: [{
						// make sure the chart starts at 0
						ticks: {
							// format value as currency
							callback: formatAxisY,
							beginAtZero: true,
						},
					}],
				},
				// tooltip handling
				tooltips: {
					// tooltip callbacks are used to customize default texts
					callbacks: {
						// format the tooltip text displayed when hovering a point
						label: formatPointTooltip,
						// change label colors because, by default, the legend background is blank
						labelColor: function(tooltipItem, chart) {
							// get tooltip item meta data
							var meta = chart.data.datasets[tooltipItem.datasetIndex];

							return {
								// use white border
								borderColor: 'rgb(0,0,0)',
								// use same item background color
								backgroundColor: meta.borderColor,
							};
						},
					},
				},
			};

			// get 2D canvas for LINE chart
			var canvas = jQuery(widget).find('canvas')[0];
			var ctx    = canvas.getContext('2d');

			// init chart from scratch if undefined
			TREND_CHARTS[id] = new Chart(ctx, {
				type:    'line',
				data:    chartData,
				options: options,
			});
		}
		// otherwise update labels and values
		else {
			// update chart data
			TREND_CHARTS[id].data.labels = Object.keys(data);
			TREND_CHARTS[id].data.datasets[0].data = Object.values(data);

			// update chart colors
			TREND_CHARTS[id].data.datasets[0].backgroundColor       = '#' + config.color + '33';
			TREND_CHARTS[id].data.datasets[0].borderColor           = '#' + config.color;
			TREND_CHARTS[id].data.datasets[0].pointBackgroundColor  = '#' + config.color;
			TREND_CHARTS[id].data.datasets[0].pointHoverBorderColor = '#' + config.color;

			// update format callbacks
			TREND_CHARTS[id].options.scales.yAxes[0].ticks.callback = formatAxisY;
			TREND_CHARTS[id].options.tooltips.callbacks.label = formatPointTooltip;

			// refresh chart
			TREND_CHARTS[id].update();
		}
	}

</script>
