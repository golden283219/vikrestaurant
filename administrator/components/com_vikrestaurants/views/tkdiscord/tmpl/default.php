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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');

$order = $this->order;

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<div class="row-fluid">
		
		<div class="span8">

			<!-- DISCOUNT DETAILS -->
			
			<div class="row-fluid">
			
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRDISCOUNT')); ?>

						<!-- COUPON CODE - Label -->
						<?php echo $vik->openControl(JText::_('VRDISCOUNT')); ?>
							<div class="control-html-value">
								<?php
								if ($order->coupon)
								{
									echo $order->coupon->code . ' : ';
								}

								if ($order->discount_val != 0)
								{
									echo $currency->format($order->discount_val * -1);
								}
								else
								{
									echo '--';
								}
								?>
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- METHOD - Dropdown -->
						<?php
						$options = array(
							JHtml::_('select.option', '', ''),
						);

						if (empty($order->coupon))
						{
							// apply coupon (disable if there are no coupons)
							$options[] = JHtml::_('select.option', 1, JText::_('VRORDDISCMETHOD1'), 'value', 'text', !count($this->coupons));
						}
						else
						{
							// replace existing coupon (disable if there are no coupns)
							$options[] = JHtml::_('select.option', 2, JText::_('VRORDDISCMETHOD2'), 'value', 'text', !count($this->coupons));
							// remove coupon
							$options[] = JHtml::_('select.option', 3, JText::_('VRORDDISCMETHOD3'));
						}

						if ($order->discount_val == 0)
						{
							// add discount
							$options[] = JHtml::_('select.option', 4, JText::_('VRORDDISCMETHOD4'));
						}
						else
						{
							// change discount
							$options[] = JHtml::_('select.option', 5, JText::_('VRORDDISCMETHOD5'));
							// remove discount
							$options[] = JHtml::_('select.option', 6, JText::_('VRORDDISCMETHOD6'));
						}
						
						echo $vik->openControl(JText::_('VRMANAGETKORDDISC4')); ?>
							<select name="method" id="vr-method-sel">
								<?php echo JHtml::_('select.options', $options); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- COUPON CODE - Dropdown -->
						<?php
						$options = array(
							JHtml::_('select.option', '', ''),
						);

						foreach ($this->coupons as $coupon)
						{
							$coupon_label = $coupon->code . ' : ' . ($coupon->percentot == 1 ? $coupon->value . '%' : $currency->format($coupon->value));
							
							$options[] = JHtml::_('select.option', $coupon->id, $coupon_label);
						}
						
						echo $vik->openControl(JText::_('VRMANAGETKORDDISC3') . '*', 'vr-coupon-field', array('style' => 'display:none;')); ?>
							<select name="id_coupon" id="vr-coupon-sel">
								<?php echo JHtml::_('select.options', $options); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- AMOUNT - Number -->
						<?php
						$options = array(
							JHtml::_('select.option', 1, '%'),
							JHtml::_('select.option', 2, $currency->getSymbol()),
						);
						
						echo $vik->openControl(JText::_('VRMANAGETKORDDISC5') . '*', 'multi-field'); ?>
							<input type="number" name="amount" value="0" step="any" id="vr-amount-input" disabled />

							<select name="percentot" id="vr-percentot-sel" disabled>
								<?php echo JHtml::_('select.options', $options, 'value', 'text', 2); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<!-- TIP DETAILS -->

			<div class="row-fluid">

				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRTIP')); ?>

						<!-- TIP - Label -->
						<?php echo $vik->openControl(JText::_('VRTIP')); ?>
							<div class="control-html-value">
								<?php
								if ($order->tip_amount != 0)
								{
									echo $currency->format($order->tip_amount);
								}
								else
								{
									echo '--';
								}
								?>
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- METHOD - Dropdown -->
						<?php
						$options = array(
							JHtml::_('select.option', '', ''),
						);

						if ($order->tip_amount == 0)
						{
							// add tip
							$options[] = JHtml::_('select.option', 1, JText::_('VRORDTIPMETHOD1'));
						}
						else
						{
							// change tip
							$options[] = JHtml::_('select.option', 2, JText::_('VRORDTIPMETHOD2'));
							// remove tip
							$options[] = JHtml::_('select.option', 3, JText::_('VRORDTIPMETHOD3'));
						}
						
						echo $vik->openControl(JText::_('VRMANAGETKORDDISC4')); ?>
							<select name="tip_method" id="vr-tipmethod-sel">
								<?php echo JHtml::_('select.options', $options); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- AMOUNT - Number -->
						<?php
						$options = array(
							JHtml::_('select.option', 1, '%'),
							JHtml::_('select.option', 2, $currency->getSymbol()),
						);

						echo $vik->openControl(JText::_('VRMANAGETKORDDISC5') . '*', 'multi-field'); ?>
							<input type="number" name="tip_amount" value="0" step="any" id="vr-tip-amount-input" disabled />

							<select name="tip_percentot" id="vr-tip-percentot-sel" disabled>
								<?php echo JHtml::_('select.options', $options, 'value', 'text', 2); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		</div>

		<!-- CART -->

		<div class="span4">
			<?php
			echo $vik->openFieldset(JText::_('VRTKORDERCARTFIELDSET3'));
			echo $this->loadTemplate('cart');
			echo $vik->closeFieldset();
			?>
		</div>

	</div>
	
	<input type="hidden" name="id" value="<?php echo $order->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRORDDISCMETHOD0');

$js_coupons = array();

foreach ($this->coupons as $coupon)
{
	$js_coupons[$coupon->id] = $coupon;
}
?>

<script type="text/javascript">

	var COUPONS = <?php echo json_encode($js_coupons); ?>;

	jQuery(document).ready(function() {

		jQuery('#vr-method-sel, #vr-tipmethod-sel').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRORDDISCMETHOD0'),
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-coupon-sel').select2({
			placeholder: '--',
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-percentot-sel, #vr-tip-percentot-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 130,
		});

		jQuery('#vr-method-sel').on('change', function(){
			var val = jQuery(this).val();

			if (val == 1 || val == 2) {
				jQuery('.vr-coupon-field').show();
				validator.registerFields('#vr-coupon-sel');
			} else {
				jQuery('.vr-coupon-field').hide();
				validator.unregisterFields('#vr-coupon-sel');
			}

			if (!val || val == 3 || val == 6) {
				jQuery('#vr-amount-input').val(0).prop('disabled', true);
				jQuery('#vr-percentot-sel').select2('val', 2).prop('disabled', true);
			} else {
				jQuery('#vr-amount-input').prop('disabled', false);
				jQuery('#vr-percentot-sel').prop('disabled', false);
			}
		});

		jQuery('#vr-coupon-sel').on('change', function(){
			if (jQuery(this).val().length) {
				
				var couponID = jQuery(this).val();

				jQuery('#vr-amount-input').val(COUPONS[couponID].value);
				jQuery('#vr-percentot-sel').select2('val', COUPONS[couponID].percentot);
			}
		});

		jQuery('#vr-tipmethod-sel').on('change', function(){
			var val = jQuery(this).val();

			if (!val || val == 3) {
				jQuery('#vr-tip-amount-input').val(0).prop('disabled', true);
				jQuery('#vr-tip-percentot-sel').select2('val', 2).prop('disabled', true);
			} else {
				jQuery('#vr-tip-amount-input').prop('disabled', false);
				jQuery('#vr-tip-percentot-sel').prop('disabled', false);
			}
		});

	});

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate()) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

</script>
