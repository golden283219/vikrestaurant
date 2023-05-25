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

JText::script('VRE_N_PRODUCTS_SOLD');
JText::script('VRE_N_PRODUCTS_SOLD_1');

?>

<div class="canvas-align-top">
	<canvas></canvas>
</div>

<div class="no-results" style="display:none;">
	<?php echo VREApplication::getInstance()->alert(JText::_('VRTKSTATSTOCKSNODATA')); ?>
</div>

<script>

	/**
	 * Defines a pool of charts, if undefined.
	 *
	 * @var object
	 */
	if (typeof BESTPRODUCTS_CHART === 'undefined') {
		var BESTPRODUCTS_CHART = {};
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

		// fetch color
		if (config.color && config.color.match(/^#?[0-9a-f]{6,6}$/i))
		{
			// use specified color
			config.color = config.color.replace(/^#/, '');
		}
		else
		{
			// use default one
			config.color = 'ad1a3f';
		}

		// get widget ID
		var id = jQuery(widget).attr('id');

		// init chart from scratch if NULL
		if (!BESTPRODUCTS_CHART.hasOwnProperty(id)) {
			// prepare chart data
			var chartData = {
				labels: Object.keys(data),
				datasets: [
					{
						// the label string that appears when hovering the mouse above the lines intersection points
						label: "Dataset",
						// the background color drawn behind the line (99 = 60% opacity)
						backgroundColor: "#" + config.color + "99",
						// the fill color of the line
						borderColor: "#" + config.color,
						// the line dataset
						data: Object.values(data),
					}
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
					// X Axis properties
					xAxes: [{
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
					// Y Axis properties
					yAxes: [{
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
							// get received quantity
							var quantity = parseInt(tooltipItem.value);

							var label = '';

							// format label by fetching singular/plural form
							if (quantity == 1)
							{
								label = Joomla.JText._('VRE_N_PRODUCTS_SOLD_1');
							} else {
								label = Joomla.JText._('VRE_N_PRODUCTS_SOLD').replace(/%d/, quantity);
							}

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
			
			// get 2D canvas for BAR chart
			var canvas = jQuery(widget).find('canvas')[0];
			var ctx    = canvas.getContext('2d');

			// init chart from scratch if undefined
			BESTPRODUCTS_CHART[id] = new Chart(ctx, {
				type:    'horizontalBar',
				data:    chartData,
				options: options,
			});
		}
		// otherwise update labels and values
		else {
			// update chart data
			BESTPRODUCTS_CHART[id].data.labels = Object.keys(data);
			BESTPRODUCTS_CHART[id].data.datasets[0].data = Object.values(data);

			// update chart colors
			BESTPRODUCTS_CHART[id].data.datasets[0].backgroundColor = '#' + config.color + '99';
			BESTPRODUCTS_CHART[id].data.datasets[0].borderColor     = '#' + config.color;

			// refresh chart
			BESTPRODUCTS_CHART[id].update();
		}
	}

</script>
