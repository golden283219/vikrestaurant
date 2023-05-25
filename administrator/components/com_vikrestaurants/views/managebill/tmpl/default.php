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
JHtml::_('vrehtml.assets.currency');
JHtml::_('vrehtml.assets.fontawesome');

$bill = $this->bill;

$vik = VREApplication::getInstance();

$items_optgroup = array(
	'published'   => 'VRSYSPUBLISHED1',
	'unpublished' => 'VRSYSPUBLISHED0',
	'hidden'      => 'VRSYSHIDDEN'
);

$currency = VREFactory::getCurrency();

?>

<style>
	.vr-bill-wrapper {
		display: none;
		padding: 0;
		/* use a transparent border to avoid a "bounce" issue at the end of the slide effect */
		border-bottom: 1px solid transparent;
		margin-bottom: 14px;
	}
</style>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
		
		<div class="span8">

			<!-- BILL DETAILS -->
			
			<div class="row-fluid">
			
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRBILL')); ?>
						
						<!-- BILL VALUE - Number -->
						<?php echo $vik->openControl(JText::_('VRMANAGEBILL2'), 'multi-field extend-buttons-25'); ?>
							<div class="input-prepend currency-field">
								<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

								<input type="number" name="bill_value" value="<?php echo $bill->value; ?>" id="vrtk-total-text" style="text-align: right;" />
							</div>

							&nbsp;

							<button type="button" class="btn" onclick="vrToggleSearchToolsButton(this, 'discount');">
								<?php echo JText::_('VRDISCOUNT'); ?>&nbsp;<i class="fas fa-caret-down vr-tools-caret"></i>
							</button>

							&nbsp;

							<button type="button" class="btn" onclick="vrToggleSearchToolsButton(this, 'tip');">
								<?php echo JText::_('VRTIP'); ?>&nbsp;<i class="fas fa-caret-down vr-tools-caret"></i>
							</button>
						<?php echo $vik->closeControl(); ?>

						<div class="vr-bill-wrappers">

							<div id="vr-search-tools-discount" class="vr-bill-wrapper">

								<!-- COUPON CODE - Label -->
								<?php echo $vik->openControl(JText::_('VRDISCOUNT')); ?>
									<div class="control-html-value">
										<?php
										if ($bill->coupon)
										{
											echo $bill->coupon->code . ' : ';
										}

										if ($bill->discount != 0)
										{
											echo $currency->format($bill->discount * -1);
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

								if (empty($bill->coupon))
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

								if ($bill->discount == 0)
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

							</div>

							<div id="vr-search-tools-tip" class="vr-bill-wrapper">

								<!-- TIP - Label -->
								<?php echo $vik->openControl(JText::_('VRTIP')); ?>
									<div class="control-html-value">
										<?php
										if ($bill->tip != 0)
										{
											echo $currency->format($bill->tip);
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

								if ($bill->tip == 0)
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

							</div>

						</div>

						<!-- DEPOSIT - Number -->
						<?php
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGERESERVATION9'),
							'content' => JText::_('VRORDERBILLDEPOSIT_HELP'),
						));

						echo $vik->openControl(JText::_('VRMANAGERESERVATION9') . $help); ?>
							<div class="input-prepend currency-field">
								<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

								<input type="number" name="deposit" value="<?php echo $bill->deposit; ?>" style="text-align: right;" />
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- TOTAL PAID - Number -->
						<?php echo $vik->openControl(JText::_('VRORDERTOTPAID'), 'multi-field'); ?>
							<div class="input-prepend currency-field" style="margin-right:5px;">
								<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

								<input type="number" name="tot_paid" value="<?php echo $bill->paid; ?>" data-value="<?php echo $bill->paid; ?>" style="text-align: right;" readonly />
							</div>

							<div class="btn-group inline-checkbox" id="paid-condition" style="<?php echo $bill->deposit > $bill->paid ? '' : 'display:none;'; ?>">
								<?php
								$help = $vik->createPopover(array(
									'title'   => JText::_('VRORDERPAID'),
									'content' => JText::_('VRORDERPAID_HELP'),
								));
								?>

								<input type="checkbox" id="paid-checkbox" />
								<label for="paid-checkbox"><?php echo JText::_('VRORDERPAID') . $help; ?></label>
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- BILL CLOSED - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', '', $bill->closed);
						$elem_no  = $vik->initRadioElement('', '', !$bill->closed);

						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGERESERVATION11'),
							'content' => JText::_('VRORDERBILLCLOSED_HELP'),
						));
						
						echo $vik->openControl(JText::_('VRMANAGEBILL3') . $help);
						echo $vik->radioYesNo('bill_closed', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>
						
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<!-- PRODUCT DETAILS -->

			<div class="row-fluid">

				<div class="span12">
					<?php
					echo $vik->openFieldset(JText::_('VRTKORDERCARTFIELDSET2'));
					echo $this->loadTemplate('food');
					echo $vik->closeFieldset();
					?>
				</div>

			</div>
				
		</div>

		<!-- CART -->

		<div class="span4">
			<?php
			echo $vik->openFieldset(JText::_('VRBILL'));
			echo $this->loadTemplate('cart');
			echo $vik->closeFieldset();
			?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="from" value="<?php echo $this->returnTask; ?>" />
	<input type="hidden" name="id" value="<?php echo $bill->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
// render inspector to create new "hidden" products
echo JHtml::_(
	'vrehtml.inspector.render',
	'create-product-inspector',
	array(
		'title'       => JText::_('VRCREATENEWPROD'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => '<button type="button" class="btn btn-success" id="create-product-save">' . JText::_('VRADDTOCART') . '</button>',
	),
	$this->loadTemplate('newitem_modal')
);

// render inspector to add a product to the reservation

$footer  = '<button type="button" class="btn btn-success" id="res-product-save">' . JText::_('VRADDTOCART') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="res-product-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

echo JHtml::_(
	'vrehtml.inspector.render',
	'res-product-inspector',
	array(
		'title'       => JText::_('VRE_ADD_PRODUCT'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
	),
	$this->loadTemplate('additem_modal')
);
?>

<?php
JText::script('VRTKCARTOPTION4');
JText::script('VRORDDISCMETHOD0');
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRTKSTOCKITEMSUCCESS');
JText::script('VRSYSHIDDEN');

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

		jQuery('input[name="bill_value"]').on('change', function() {
			// refresh total cost and remaining balance
			vrCartUpdateTotalCost(parseFloat(jQuery(this).val()));
		});

		jQuery('input[name="deposit"]').on('change', function() {
			if (parseFloat(jQuery(this).val()) == parseFloat(jQuery('input[name="tot_paid"]').val())) {
				// hide "paid" condition in case the deposit amount is equals to the total paid
				jQuery('#paid-condition').hide();
			} else {
				// otherwise show "paid" condition
				jQuery('#paid-checkbox').prop('checked', false);
				jQuery('#paid-condition').show();
			}

			// refresh total cost and remaining balance
			vrCartUpdateTotalCost(parseFloat(jQuery('input[name="bill_value"]').val()));
		});

		jQuery('#paid-checkbox').on('change', function() {
			var paid = jQuery('input[name="tot_paid"]');

			if (jQuery(this).is(':checked')) {
				// set tot paid equals to "deposit" amount
				paid.val(jQuery('input[name="deposit"]').val());
			} else {
				// restore tot paid to original value
				paid.val(paid.data('value'));
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
