<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_deals
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$dotted_navigation 	= $params->get('dotnav');
$arrow_navigation 	= $params->get('arrownav');
$duration_frame_ms 	= max(array(intval($params->get('slideframe_ms')), 1000));

$date = new JDate();

?>

<div class="vrtk-deals-module">
	
	<ul class="vrtk-dealsmod-slides">
		<?php
		for ($i = 0; $i < count($deals); $i++)
		{
			?>
			<input type="radio" name="vrtkdeal-radio-btn" value="<?php echo $i; ?>" id="vrtk-deal-<?php echo $i; ?>" <?php echo ($i == 0 ? 'checked="checked"' : ''); ?> class="vrtkdeal-radio-btn" />

			<li class="vrtk-dealsmod-slide-container">
				<div class="vrtk-dealsmod-slide">
					<div class="vrtk-dealmod-info">
						<?php echo $deals[$i]['name']; ?>
					</div>
				</div>

				<?php
				if (count($deals) > 1 && $arrow_navigation)
				{
					?>
					<div class="vrtk-dealsmod-nav">
						<label for="vrtk-deal-<?php echo ($i > 0 ? $i-1 : count($deals)-1); ?>" class="prev">&#x2039;</label>
						<label for="vrtk-deal-<?php echo ($i < count($deals)-1 ? $i+1 : 0); ?>" class="next">&#x203a;</label>
					</div>
					<?php
				}
				?>
			</li>
			<?php
		}

		if (count($deals) > 1 && $dotted_navigation)
		{
			?>
			<li class="vrtk-dealsmod-nav-dots">
				<?php
				for ($i = 0; $i < count($deals); $i++)
				{
					?>
					<label for="vrtk-deal-<?php echo $i; ?>" class="dot <?php echo ($i == 0 ? 'checked' : ''); ?>" id="vrtk-deal-dot-<?php echo $i; ?>"></label>
					<?php
				}
				?>
			</li>
			<?php
		}
		?>
	</ul>
	
	<div class="vrtk-dealsmod-listfull" style="display: none;">
		<?php
		foreach ($deals as $deal)
		{ 
			if ($deal['days_filter'][0] == 0 && count($deal['days_filter']) > 1)
			{
				// always pop Sunday as last element
				$deal['days_filter'][] = array_shift($deal['days_filter']);
			}
			?>
			<div class="vrtk-dealsmod-offer <?php echo (empty($deal['active']) ? 'not-active' : ''); ?>">
				<div class="vrtk-dealsmod-offer-avdays">
					<?php
					for ($i = 0; $i < count($deal['days_filter']); $i++)
					{
						if ($i != 0)
						{
							if ($i < count($deal['days_filter']) - 1)
							{
								echo ', ';
							}
							else
							{
								echo ' & ';
							}
						}

						echo $date->dayToString($deal['days_filter'][$i], true);
					}
					?>
				</div>

				<div class="vrtk-dealsmod-offer-title">
					<?php echo $deal['name']; ?>
				</div>

				<div class="vrtk-dealsmod-offer-desc">
					<?php echo $deal['description']; ?>
				</div>
			</div>
			<?php
		}
		?>
		
		<div class="vrtk-dealsmod-listfull-toggle">
			<button onClick="jQuery('.vrtk-dealsmod-listfull').slideUp();">
				<?php echo JText::_("VRTKDEALTOGGLE"); ?>
			</button>
		</div>
	</div>
	
</div>

<script type="text/javascript">
	
	jQuery(document).ready(function() {
		
		var slide_count      = jQuery('.vrtk-dealsmod-slide').length;
		var current_slide    = 0;
		var slide_is_running = true;
		
		var loop = setInterval(function() {
			if (slide_is_running) {
				current_slide = ((current_slide + 1 < slide_count) ? (current_slide + 1) : 0);
				jQuery('#vrtk-deal-' + current_slide).prop('checked', true);
				jQuery('.vrtk-dealsmod-nav-dots .dot').removeClass('checked');
				jQuery('#vrtk-deal-dot-' + current_slide).addClass('checked');
			}
		}, <?php echo $duration_frame_ms; ?>);
		
		jQuery('.vrtk-dealsmod-slides').hover(function() {
			slide_is_running = false;
		}, function() {
			current_slide = parseInt(jQuery('.vrtkdeal-radio-btn:checked').val());
			slide_is_running = true;
		});
		
		jQuery('.vrtk-dealsmod-nav-dots .dot').on('click', function() {
			jQuery('.vrtk-dealsmod-nav-dots .dot').removeClass('checked');
			jQuery(this).addClass('checked');
		});
		
		jQuery('.vrtkdeal-radio-btn').on('change', function() {
			var id = jQuery(this).val();
			jQuery('.vrtk-dealsmod-nav-dots .dot').removeClass('checked');
			jQuery('#vrtk-deal-dot-' + id).addClass('checked');
		});
		
		jQuery('.vrtk-dealmod-info').on('click', function() {
			if (jQuery('.vrtk-dealsmod-listfull').is(':visible')) {
				jQuery('.vrtk-dealsmod-listfull').slideUp();
			} else {
				jQuery('.vrtk-dealsmod-listfull').slideDown();
			}
		});
		
	});
	
</script>
