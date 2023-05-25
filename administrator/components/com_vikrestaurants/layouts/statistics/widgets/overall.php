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

	.canvas-align-center .overall {
		text-align: center;
	}
	.canvas-align-center .overall .overall-earning {
		font-size: 40px;
		margin-bottom: 20px;
		color: #476799;
		font-weight: bold;
	}
	.canvas-align-center .overall .overall-count {
		font-size: 26px;
		color: #002243;
		font-weight: 500;
	}
	.canvas-align-center .overall .overall-guests {
		margin-top: 10px;
		font-size: 18px;
		color: #617792;
	}

</style>

<div class="canvas-align-center">
	
	<div class="overall">
		<div class="overall-earning"></div>
		<div class="overall-count"></div>

		<?php
		if ($widget->isGroup('restaurant'))
		{
			?><div class="overall-guests"></div><?php
		}
		?>
	</div>

</div>

<div class="no-results" style="display:none;">
	<?php echo VREApplication::getInstance()->alert(JText::_('VRTKSTATSTOCKSNODATA')); ?>
</div>

<div class="widget-floating-box">

	<span class="badge badge-warning pull-left shift"></span>

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

		if (!data) {
			// hide overall information
			jQuery(widget).find('.canvas-align-center').hide();
			// show "no results" box
			jQuery(widget).find('.no-results').show();
		} else {
			// hide "no results" box
			jQuery(widget).find('.no-results').hide();
			// show overall information
			jQuery(widget).find('.canvas-align-center').show();

			// update total earning
			jQuery(widget).find('.overall-earning').text(data.formattedTotal);
			jQuery(widget).find('.overall-count').text(data.formattedCount);

			<?php
			if ($widget->isGroup('restaurant'))
			{
				?>
				jQuery(widget).find('.overall-guests').text(config.people ? data.formattedGuests : '');
				<?php
			}
			?>
		}

		// retrieve selected shift
		var shift = jQuery('select[name="<?php echo $widget->getName() . '_' . $widget->getID(); ?>_shift"]')
				.find('option[value="' + config.shift + '"]')
					.text();

		// show shift name (only if selected)
		jQuery(widget).find('.badge.shift').text(config.shift != 0 ? shift : '');
	}

</script>
