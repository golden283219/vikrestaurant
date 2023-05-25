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

JText::script('VRE_STATS_WIDGET_CUSTOMERS_NEW');
JText::script('VRE_STATS_WIDGET_CUSTOMERS_RETURNING');

?>

<style>

	.canvas-align-center {
		height: calc(100% - 20px) !important;
	}

</style>

<div class="canvas-align-center">
	<canvas></canvas>
</div>

<div class="no-results" style="display:none;">
	<?php echo VREApplication::getInstance()->alert(JText::_('VRTKSTATSTOCKSNODATA')); ?>
</div>

<div class="widget-floating-box">

	<span class="badge badge-info pull-left month" style="margin-right:4px"></span>
	<span class="badge badge-important pull-left year"></span>

</div>

<script>

	/**
	 * Defines a pool of charts, if undefined.
	 *
	 * @var object
	 */
	if (typeof CUSTOMERS_CHART === 'undefined') {
		var CUSTOMERS_CHART = {};
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

		// check if the chart already exists
		if (CUSTOMERS_CHART.hasOwnProperty(id)) {
			// destroy it before creating a new one
			CUSTOMERS_CHART[id].destroy();
		}

		if (data.returning == 0 && data.new == 0) {
			// show "no results" box in case of empty list
			jQuery(widget).find('.canvas-align-center').hide();
			jQuery(widget).find('.no-results').show();
		} else {
			// hide "no results" box if there is at least a status
			jQuery(widget).find('.no-results').hide();
			jQuery(widget).find('.canvas-align-center').show();
		}

		// fetch RETURNING color
		if (config.retcolor && config.retcolor.match(/^#?[0-9a-f]{6,6}$/i))
		{
			// use specified color
			config.retcolor = config.retcolor.replace(/^#/, '');
		}
		else
		{
			// use default one
			config.retcolor = 'ffd635';
		}

		// fetch NEW color
		if (config.newcolor && config.newcolor.match(/^#?[0-9a-f]{6,6}$/i))
		{
			// use specified color
			config.newcolor = config.newcolor.replace(/^#/, '');
		}
		else
		{
			// use default one
			config.newcolor = '1c81ea';
		}

		// prepare chart data
		var chartData = {
			// dataset options
			datasets: [{
				// dataset values
				data: [],
				// dataset color
				backgroundColor: [],
				hoverBorderColor: [],
			}],
			// dataset labels
			labels: [],
		};

		// ADD RETURNING CUSTOMERS

		// push customers count
		chartData.datasets[0].data.push(data.returning);
		// hide highlight on hover
		chartData.datasets[0].hoverBorderColor.push('#0000');
		// push label
		chartData.labels.push(Joomla.JText._('VRE_STATS_WIDGET_CUSTOMERS_RETURNING'));
		// fetch background color
		chartData.datasets[0].backgroundColor.push('#' + config.retcolor);

		// ADD NEW CUSTOMERS

		// push customers count
		chartData.datasets[0].data.push(data.new);
		// hide highlight on hover
		chartData.datasets[0].hoverBorderColor.push('#0000');
		// push label
		chartData.labels.push(Joomla.JText._('VRE_STATS_WIDGET_CUSTOMERS_NEW'));
		// fetch background color
		chartData.datasets[0].backgroundColor.push('#' + config.newcolor);
		
		// prepare chart configuration
		var options = {
			// hide legend
			legend: {
				display: false,
			},
			// tooltip handling
			tooltips: {
				// tooltip callbacks are used to customize default texts
				callbacks: {
					// format the tooltip text displayed when hovering a point
					label: function(tooltipItem, data) {
						// keep default label
						var label = data.labels[tooltipItem.index] || '';

						if (label) {
							label += ': ';
						}

						label += data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];

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
							backgroundColor: meta.backgroundColor[tooltipItem.index],
						};
					},
				},
			},
			// the percentage of the chart that is cut out of the middle
			cutoutPercentage: 70,
		};
		
		// get 2D canvas for DOUGHNUT chart
		var canvas = jQuery(widget).find('canvas')[0];
		var ctx    = canvas.getContext('2d');

		// init chart from scratch if undefined
		CUSTOMERS_CHART[id] = new Chart(ctx, {
			type:    'doughnut',
			data:    chartData,
			options: options,
		});

		// update badges
		jQuery(widget).find('.badge.month').text(data.month);
		jQuery(widget).find('.badge.year').text(data.year);
	}

</script>
