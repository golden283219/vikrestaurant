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

JText::script('VRRESERVATIONSTATUSCONFIRMED');
JText::script('VRRESERVATIONSTATUSPENDING');
JText::script('VRRESERVATIONSTATUSREMOVED');
JText::script('VRRESERVATIONSTATUSCANCELLED');
JText::script('VRRESERVATIONSTATUSCLOSURE');

?>

<style>

	.canvas-align-center {
		height: calc(100% - 20px) !important;
	}

</style>

<div class="canvas-align-center chart">
	<canvas></canvas>
</div>

<div class="no-results" style="display:none;">
	<?php echo VREApplication::getInstance()->alert(JText::_('VRTKSTATSTOCKSNODATA')); ?>
</div>

<div class="widget-floating-box">

	<span class="badge badge-info pull-left range"></span>

	<span class="badge badge-info pull-left datefrom" style="margin-right:4px"></span>
	<span class="badge badge-important pull-left dateto"></span>

	<span class="badge badge-warning pull-right shift"></span>

</div>

<script>

	/**
	 * Defines a pool of charts, if undefined.
	 *
	 * @var object
	 */
	if (typeof STATUSRES_CHART === 'undefined') {
		var STATUSRES_CHART = {};
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
		if (STATUSRES_CHART.hasOwnProperty(id)) {
			// destroy it before creating a new one
			STATUSRES_CHART[id].destroy();
		}

		if (data.length == 0) {
			// show "no results" box in case of empty list
			jQuery(widget).find('.canvas-align-center.chart').hide();
			jQuery(widget).find('.no-results').show();
		} else {
			// hide "no results" box if there is at least a status
			jQuery(widget).find('.no-results').hide();
			jQuery(widget).find('.canvas-align-center.chart').show();
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

		jQuery.each(data, function(k, v) {
			// push reservations count
			chartData.datasets[0].data.push(v);
			// hide highlight on hover
			chartData.datasets[0].hoverBorderColor.push('#0000');
			// push label
			chartData.labels.push(Joomla.JText._('VRRESERVATIONSTATUS' + k));

			var lookup = {
				CONFIRMED: '008000',
				PENDING:   'ff7000',
				REMOVED:   '990000',
				CANCELLED: 'f01b17',
				CLOSURE:   'ac8b91',
			};

			// fetch background color
			chartData.datasets[0].backgroundColor.push('#' + lookup[k]);
		});
		
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
		STATUSRES_CHART[id] = new Chart(ctx, {
			type:    'doughnut',
			data:    chartData,
			options: options,
		});

		// update badges
		if (config.datefrom || config.dateto) {
			// at least a date was selected, show "from" and "to"
			jQuery(widget).find('.badge.datefrom').text(config.datefrom ? config.datefrom : '--');
			jQuery(widget).find('.badge.dateto').text(config.dateto ? config.dateto : '--');

			// hide default range
			jQuery(widget).find('.badge.range').text('');
		} else {
			// hide both empty dates
			jQuery(widget).find('.badge.datefrom').text('');
			jQuery(widget).find('.badge.dateto').text('');

			// retrieve selected range text
			var range = jQuery('select[name="<?php echo $widget->getName() . '_' . $widget->getID(); ?>_range"]')
				.find('option[value="' + config.range + '"]')
					.text();

			// show range
			jQuery(widget).find('.badge.range').text(range);
		}

		// retrieve selected shift
		var shift = jQuery('select[name="<?php echo $widget->getName() . '_' . $widget->getID(); ?>_shift"]')
				.find('option[value="' + config.shift + '"]')
					.text();

		// show shift name (only if selected)
		jQuery(widget).find('.badge.shift').text(config.shift != 0 ? shift : '');
	}

</script>
