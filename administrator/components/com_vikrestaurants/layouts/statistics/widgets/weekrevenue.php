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
	if (typeof WEEKREVENUE_CHARTS === 'undefined') {
		var WEEKREVENUE_CHARTS = {};
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
			config.color = 'e68714';
		}

		// init chart from scratch if NULL
		if (!WEEKREVENUE_CHARTS.hasOwnProperty(id)) {
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
						// do not show y axis
						display: false,
						// hide horizontal grid lines too
						gridLines : {
							display : false,
						},
						// make sure the chart starts at 0
						ticks: {
							beginAtZero: true,
						},
					}],
					// X Axis properties
					xAxes: [{
						// hide vertical grid lines
						gridLines: {
							display: false,
						},
					}],
				},
				// tooltip handling
				tooltips: {
					// tooltip callbacks are used to customize default texts
					callbacks: {
						// format the tooltip text displayed when hovering a point
						label: function(tooltipItem, data) {
							// format value as currency
							var label = Currency.getInstance().format(tooltipItem.value);

							return ' ' + label;
						},
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
			WEEKREVENUE_CHARTS[id] = new Chart(ctx, {
				type:    'line',
				data:    chartData,
				options: options,
			});
		}
		// otherwise update labels and values
		else {
			// update chart data
			WEEKREVENUE_CHARTS[id].data.labels = Object.keys(data);
			WEEKREVENUE_CHARTS[id].data.datasets[0].data = Object.values(data);

			// update chart colors
			WEEKREVENUE_CHARTS[id].data.datasets[0].backgroundColor       = '#' + config.color + '33';
			WEEKREVENUE_CHARTS[id].data.datasets[0].borderColor           = '#' + config.color;
			WEEKREVENUE_CHARTS[id].data.datasets[0].pointBackgroundColor  = '#' + config.color;
			WEEKREVENUE_CHARTS[id].data.datasets[0].pointHoverBorderColor = '#' + config.color;

			// refresh chart
			WEEKREVENUE_CHARTS[id].update();
		}
	}

</script>
