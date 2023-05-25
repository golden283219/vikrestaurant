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

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$count = count($this->payments);

?>

<div class="vr-overlay" id="vrpaymentoverlay" style="display: none;">

	<div class="vr-modal-box">

		<div class="vr-modal-head">

			<div class="vr-modal-head-title">
				<h3><?php echo JText::_('VREORDERFOOD_PAY_NOW'); ?></h3>
			</div>

			<div class="vr-modal-head-dismiss">
				<a href="javascript: void(0);" onClick="vrClosePaymentOverlay();">×</a>
			</div>

		</div>

		<div class="vr-modal-body">

			<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=orderdish.paynow' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" name="dishesPaymentForm">

				<!-- PAYMENT METHOD -->

				<div class="vr-payments-list">

					<div class="vrtkdeliverytitlediv"><?php echo JText::_('VRMETHODOFPAYMENT'); ?></div>

					<div class="vr-payments-container">

						<?php
						foreach ($this->payments as $i => $p)
						{
							?>
							<div class="vr-payment-wrapper vr-payment-block">

								<div class="vr-payment-title">
									<?php
									if ($count)
									{
										?>
										<input
											type="radio"
											name="id_payment"
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
										<input type="hidden" name="id_payment" value="<?php echo $p->id; ?>" />
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

										<span><?php echo $p->name; ?></span>
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
					
				</div>

				<!-- GRATUITY -->

				<div class="vr-bill-gratuity">

					<div class="vrtkdeliverytitlediv"><?php echo JText::_('VRTIPFORPROPERTY'); ?></div>

					<div class="vrtk-additem-quantity-box">

						<div class="vrtk-additem-quantity-box-inner">

							<span class="quantity-actions">
								<a href="javascript: void(0);" data-role="tip.remove" class="vrtk-action-remove disabled">
									<i class="fas fa-minus"></i>
								</a>

								<input type="text" name="gratuity" value="0" size="4" id="vrtk-gratuity-input" onkeypress="return (event.keyCode >= 48 && event.keyCode <= 57) || event.keyCode == 13;" />

								<a href="javascript: void(0);" data-role="tip.add" class="vrtk-action-add">
									<i class="fas fa-plus"></i>
								</a>
							</span>

						</div>

						<div class="vrtk-ceil-tip" style="display: none;">
							<input type="checkbox" value="1" name="ceiltip" id="vrtk-ceil-tip-checkbox" />
							<label for="vrtk-ceil-tip-checkbox"><?php echo JText::_('VRTIPROUNDED'); ?></label>
						</div>

					</div>

				</div>

				<!-- ACTIONS BAR -->

				<div class="dish-item-overlay-footer">

					<button type="button" class="btn" data-role="close">
						<?php echo JText::_('VRTKADDCANCELBUTTON'); ?>
					</button>

					<button type="button" class="btn" data-role="save">
						<?php echo JText::_('VRCARTPAYNOWTOTALBTN'); ?>
					</button>

				</div>

				<input type="hidden" name="option" value="com_vikrestaurants" />
				<input type="hidden" name="task" value="orderdish.paynow" />
				<input type="hidden" name="ordnum" value="<?php echo $this->reservation->id; ?>" />
				<input type="hidden" name="ordkey" value="<?php echo $this->reservation->sid; ?>" />

			</form>

			<!-- end body -->

		</div>

	</div>

</div>

<?php
JText::script('VRCARTPAYNOWTOTALBTN');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#vrtk-gratuity-input').on('change', function() {
			// get gratuity
			var tip = parseInt(jQuery(this).val());
			
			if (tip > 0) {
				// allow (-) button again
				jQuery('#vrpaymentoverlay .vrtk-action-remove').removeClass('disabled');
			} else {
				// disable (-) button
				jQuery('#vrpaymentoverlay .vrtk-action-remove').addClass('disabled');
			}

			vrUpdatePaymentTotal();
		});

		jQuery('.quantity-actions *[data-role]').on('click', function() {
			// get gratuity input
			var input = jQuery('#vrtk-gratuity-input');

			// get current gratuity
			var tip = parseInt(input.val());

			// fetch units to add/decrease
			var units = jQuery(this).data('role') == 'tip.add' ? 1 : -1;
			
			if (tip + units >= 0) {
				// update only in case the gratuity is equals or higher than 0
				input.val(tip + units);

				// update gratuity
				input.trigger('change');
			}
		});

		jQuery('#vrtk-ceil-tip-checkbox').on('change', function() {
			vrUpdatePaymentTotal();
		});

		// Actions

		jQuery('#vrpaymentoverlay .dish-item-overlay-footer button[data-role="close"]').on('click', function() {
			vrClosePaymentOverlay();
		});

		jQuery('#vrpaymentoverlay .dish-item-overlay-footer button[data-role="save"]').on('click', function() {
			document.dishesPaymentForm.submit();
		});

	});

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

	function vrUpdatePaymentTotal(total) {
		if (isNaN(total)) {
			// get cart total
			total = vrGetCartTotal();
		}

		// sum tip
		var tip = parseInt(jQuery('#vrtk-gratuity-input').val());

		if (!isNaN(tip) && tip > 0) {
			total += tip;
		}

		// check if we should ceil the total amount
		if (jQuery('#vrtk-ceil-tip-checkbox').is(':checked')) {
			total = Math.ceil(total);
		}

		// format total as currency
		total = Currency.getInstance().format(total);
		// fetch total text
		total = Joomla.JText._('VRCARTPAYNOWTOTALBTN').replace(/%s/, total);
		// update text
		jQuery('#vrpaymentoverlay button[data-role="save"]').text(total);
	}

	function vrOpenPaymentOverlay() {
		var total = vrGetCartTotal();

		// show gratuity checkbox in case of decimals
		if (Math.ceil(total) != total) {
			jQuery('#vrpaymentoverlay .vrtk-ceil-tip').show();
		}

		// refresh total
		vrUpdatePaymentTotal(total);

		// show modal
		jQuery('#vrpaymentoverlay').show();

		// prevent body from scrolling
		jQuery('body').css('overflow', 'hidden');
	}

	function vrClosePaymentOverlay() {
		// make body scrollable again
		jQuery('body').css('overflow', 'auto');

		// hide overlay
		jQuery('#vrpaymentoverlay').hide();
	}

	jQuery('.vr-modal-box').on('click', function(e) {
		// ignore outside click
		e.stopPropagation();
	});

	jQuery('#vrpaymentoverlay').on('click', function() {
		// close overlay when the background is clicked
		vrClosePaymentOverlay();
	});

</script>
