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
 * Template file used to display a list of payment
 * gateways available to leave a deposit.
 *
 * @since 1.8
 */

$currency = VREFactory::getCurrency();

$count = count($this->payments);

$vik = VREApplication::getInstance();

?>

<div class="vrtkdeliverytitlediv"><?php echo JText::_('VRMETHODOFPAYMENT'); ?></div>

<div class="vr-payments-container">

	<?php
	foreach ($this->payments as $i => $p)
	{
		$cost_str = '';

		if ((float) $p->charge != 0)
		{
			$cost_str = floatval($p->charge);

			if ($cost_str > 0)
			{
				$cost_str = '+' . $cost_str;
			}

			if ($p->percentot == 1)
			{
				$cost_str .= '%';
			}
			else
			{
				$cost_str = $currency->format($cost_str);
			}
		}
		?>
		<div class="vr-payment-wrapper vr-payment-block">

			<div class="vr-payment-title">
				<?php
				if ($count > 1)
				{
					?>
					<input
						type="radio"
						name="vrpaymentradio"
						value="<?php echo $p->id; ?>"
						id="vrpayradio<?php echo $p->id; ?>"
						onchange="vrPaymentRadioChanged(this);"
						<?php echo $i == 0 ? 'checked="checked"' : '' ?>
					/>
					<?php
				}
				else
				{
					?>
					<input type="hidden" name="vrpaymentradio" value="<?php echo $p->id; ?>" />
					<?php
				}
				?>

				<label for="vrpayradio<?php echo $p->id; ?>" class="vr-payment-title-label">
					<?php
					if ($p->icontype == 1)
					{
						?>
						<i class="<?php echo $p->icon; ?>"></i>&nbsp;
						<?php
					}
					else if ($p->icontype == 2)
					{
						?>
						<img src="<?php echo VREMEDIA_URI . $p->icon; ?>" />&nbsp;
						<?php
					}
					?>

					<span><?php echo $p->name . ($cost_str ? ' (' . $cost_str . ')' : ''); ?></span>
				</label>
			</div>

			<?php
			if (strlen($p->prenote))
			{
				// assign notes to temporary variable
				$content = $p->prenote;

				/**
				 * Render HTML description to interpret attached plugins.
				 * 
				 * @since 1.8
				 */
				$vik->onContentPrepare($content, $full = false);
				?>
				<div class="vr-payment-description" id="vr-payment-description<?php echo $p->id; ?>" style="<?php echo ($count > 1 && $i ? 'display: none;' : ''); ?>">
					<?php echo $content->text; ?>
				</div>
				<?php
			}
			?>

		</div>
		
		<?php
	}
	?>

</div>

<script>

	function vrPaymentRadioChanged(input) {
		// get input parent
		var block = jQuery(input).closest('.vr-payment-block');
		// get description block
		var desc = jQuery(block).find('.vr-payment-description');
		// check if a description was visible
		var was = jQuery('.vr-payment-description:visible').length > 0;

		if (desc.length == 0) {
			// hide previous description with animation
			// only if the selected payment doesn't
			// have a description to display
			jQuery('.vr-payment-description').slideUp();
		} else {
			// otherwise hide as quick as possible
			jQuery('.vr-payment-description').hide();
		}

		if (was) {
			// in case a description was already visible,
			// show new description without animation
			desc.show();
		} else {
			// animate in case there was no active payment
			desc.slideDown();
		}
	}

</script>
