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

<style>

	.canvas-align-center .rog {
		text-align: center;
	}
	.canvas-align-center .rog .rog-earning {
		font-size: 40px;
		margin-bottom: 20px;
		color: #476799;
		font-weight: bold;
	}
	.canvas-align-center .rog .rog-percent {
		font-size: 26px;
		color: #002243;
		font-weight: bold;
	}
	.canvas-align-center .rog .rog-percent > .down {
		color: #ec4d56;
	}
	.canvas-align-center .rog .rog-percent > .up {
		color: #29a449;
	}
	.canvas-align-center .rog .rog-percent i {
		margin-left: 5px;
	}

</style>

<div class="canvas-align-center">
	
	<div class="rog">
		<div class="rog-earning"></div>
		<div class="rog-percent"></div>
	</div>

</div>

<div class="no-results" style="display:none;">
	<?php echo VREApplication::getInstance()->alert(JText::_('VRTKSTATSTOCKSNODATA')); ?>
</div>

<div class="widget-floating-box">

	<span class="badge badge-info pull-left month" style="margin-right:4px;"></span>
	<span class="badge badge-important pull-left year"></span>

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
		// JSON decode response
		var data = jQuery.parseJSON(json);

		if (data.nodata) {
			// hide rog information
			jQuery(widget).find('.canvas-align-center').hide();
			// show "no results" box
			jQuery(widget).find('.no-results').show();
		} else {
			// hide "no results" box
			jQuery(widget).find('.no-results').hide();
			// show rog information
			jQuery(widget).find('.canvas-align-center').show();

			// fetch rog
			var rog = parseFloat(data.rogPercent);
			var rogIcon = '';
			var sfx = 'equals';

			if (rog > 0) {
				rogIcon = '<i class="fas fa-arrow-up"></i>';
				sfx = 'up';
			} else if (rog < 0) {
				rogIcon = '<i class="fas fa-arrow-down"></i>';
				sfx = 'down';
			}

			// strip decimals in case the rog is higher than 10 or lower than -10
			if (Math.abs(rog) > 10) {
				rog = Math.round(rog);
			}

			var rogHtml = '<span class="' + sfx + '">' + rog + '%' + rogIcon + '</span>';

			// update total earning
			jQuery(widget).find('.rog-earning').text(Currency.getInstance().format(data.currEarning));
			jQuery(widget).find('.rog-percent').html(rogHtml);
		}

		// update badges
		jQuery(widget).find('.badge.month').text(data.currMonth);
		jQuery(widget).find('.badge.year').text(data.currYear);
	}

</script>
