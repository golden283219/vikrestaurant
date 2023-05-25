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

JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.fancybox');

$payment = $this->payment;

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

$editor = $vik->getEditor();

?>

<form name="adminForm" id="adminForm" action="index.php" method="post">

	<?php echo $vik->bootStartTabSet('payment', array('active' => $this->getActiveTab('payment_details'), 'cookie' => $this->getCookieTab()->name)); ?>

		<!-- DETAILS TAB -->

		<?php echo $vik->bootAddTab('payment', 'payment_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<div class="row-fluid">

				<!-- PAYMENT -->

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMANAGERESERVATION20')); ?>

						<!-- NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEPAYMENT1') . '*'); ?>
							<input type="text" name="name" class="required" value="<?php echo $this->escape($payment->name); ?>" size="30" />
						<?php echo $vik->closeControl(); ?>

						<!-- CLASS - Dropdown -->
						<?php
						$options = JHtml::_('vrehtml.admin.paymentdrivers');
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT2') . '*'); ?>
							<select name="file" class="required" id="vr-driver-sel">
								<option></option>
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $payment->file); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- PUBLISHED - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $payment->published);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$payment->published);
						
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT3'));
						echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- CHARGE - Number -->
						<?php
						$elements = array(
							JHtml::_('select.option', 1, '%'),
							JHtml::_('select.option', 2, $currency->getSymbol()),
						);
						
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT4'), 'multi-field'); ?>
							<input type="number" name="charge" value="<?php echo $payment->charge; ?>" step="any"/>
							<select name="percentot" id="vr-percentot-sel">
								<?php echo JHtml::_('select.options', $elements, 'value', 'text', $payment->percentot); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- SET CONFIRMED - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', $elem_yes->label, $payment->setconfirmed, 'onclick="setconfirmedValueChanged(1);"');
						$elem_no  = $vik->initRadioElement('', $elem_no->label, !$payment->setconfirmed, 'onclick="setconfirmedValueChanged(0);"');
						
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT5'));
						echo $vik->radioYesNo('setconfirmed', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- SELF CONFIRMATION - Radio Button -->
						<?php
						$control = array();
						$control['style'] = $payment->setconfirmed ? '' : 'display:none;';

						$elem_yes = $vik->initRadioElement('', $elem_yes->label, $payment->selfconfirm);
						$elem_no  = $vik->initRadioElement('', $elem_no->label, !$payment->selfconfirm);

						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGECONFIG91'),
							'content' => JText::_('VRMANAGECONFIG91_HELP2'),
						));
						
						echo $vik->openControl(JText::_('VRMANAGECONFIG91') . $help, 'vr-confirm-field', $control);
						echo $vik->radioYesNo('selfconfirm', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- ICON - Fieldset -->
						<?php
						$elements = array(
							JHtml::_('select.option', '', ''),
							JHtml::_('select.option', 1, JText::_('VRPAYMENTICONOPT1')),
							JHtml::_('select.option', 2, JText::_('VRPAYMENTICONOPT2')),
						);

						$font_icons = array(
							JHtml::_('select.option', '', ''),
							JHtml::_('select.option', 'fab fa-paypal', 'PayPal'),
							JHtml::_('select.option', 'fab fa-cc-paypal', 'PayPal #2'),

							JHtml::_('select.option', 'fas fa-credit-card', 'Credit Card'),
							JHtml::_('select.option', 'far fa-credit-card', 'Credit Card #2'),
							JHtml::_('select.option', 'fab fa-cc-visa', 'Visa'),
							JHtml::_('select.option', 'fab fa-cc-mastercard', 'Mastercard'),
							JHtml::_('select.option', 'fab fa-cc-amex', 'American Express'),
							JHtml::_('select.option', 'fab fa-cc-discover', 'Discovery'),
							JHtml::_('select.option', 'fab fa-cc-jcb', 'JCB'),
							JHtml::_('select.option', 'fab fa-cc-diners-club', 'Diners Club'),
							JHtml::_('select.option', 'fab fa-stripe', 'Stripe'),
							JHtml::_('select.option', 'fab fa-cc-stripe', 'Stripe #2'),
							JHtml::_('select.option', 'fab fa-stripe-s', 'Stripe (S)'),

							JHtml::_('select.option', 'fas fa-euro-sign', 'Euro'),
							JHtml::_('select.option', 'fas fa-dollar-sign', 'Dollar'),
							JHtml::_('select.option', 'fas fa-pound-sign', 'Pound'),
							JHtml::_('select.option', 'fas fa-yen-sign', 'Yen'),
							JHtml::_('select.option', 'fas fa-won-sign', 'Won'),
							JHtml::_('select.option', 'fas fa-rupee-sign', 'Rupee'),
							JHtml::_('select.option', 'fas fa-ruble-sign', 'Ruble'),
							JHtml::_('select.option', 'fas fa-lira-sign', 'Lira'),
							JHtml::_('select.option', 'fas fa-shekel-sign', 'Shekel'),

							JHtml::_('select.option', 'fas fa-money-bill', 'Money'),
							JHtml::_('select.option', 'fas fa-money-bill-wave', 'Money #2'),
							JHtml::_('select.option', 'fas fa-money-check-alt', 'Money #3'),
						);
						
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT12')); ?>
							<select name="icontype" id="vr-icontype-sel">
								<?php echo JHtml::_('select.options', $elements, 'value', 'text', $payment->icontype); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- FONT ICON - Select -->

						<?php
						$control = array();
						$control['style']    = $payment->icontype == 1 ? '' : 'display: none;';
						$control['idparent'] = 'vr-fonticon-wrapper';

						echo $vik->openControl('', 'multi-field no-margin-last-3', $control); ?>
							<select name="font_icon" id="vr-fonticon-sel">
								<?php echo JHtml::_('select.options', $font_icons, 'value', 'text', $payment->icontype == 1 ? $payment->icon : null); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- IMAGE UPLOAD - Media Manager -->

						<?php
						$control = array();
						$control['style']    = $payment->icontype == 2 ? '' : 'display: none;';
						$control['idparent'] = 'vr-iconupload-wrapper';

						echo $vik->openControl('', '', $control);
						echo JHtml::_('vrehtml.mediamanager.field', 'upload_icon', $payment->icontype == 2 ? $payment->icon : null);
						echo $vik->closeControl(); ?>

						<!-- POSITION - Select -->
						<?php
						$elements = array(
							JHtml::_('select.option', '', ''),
							JHtml::_('select.option', 'vr-payment-position-top', JText::_('VRPAYMENTPOSOPT2')),
							JHtml::_('select.option', 'vr-payment-position-bottom', JText::_('VRPAYMENTPOSOPT3')),
						);
						
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT13')); ?>
							<select name="position" id="vr-position-sel">
								<?php echo JHtml::_('select.options', $elements, 'value', 'text', $payment->position); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- TRUST - Number -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $payment->trust, 'onClick="trustValueChanged(1);"');
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$payment->trust, 'onClick="trustValueChanged(0);"');
						
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGEPAYMENT14'),
							'content' => JText::_('VRMANAGEPAYMENT14_DESC'),
						));

						echo $vik->openControl(JText::_('VRMANAGEPAYMENT14') . $help, 'multi-field');
						echo $vik->radioYesNo('trust_check', $elem_yes, $elem_no, false);
						?>
							<input type="number" name="trust" value="<?php echo $payment->trust; ?>" style="<?php echo $payment->trust ? '' : 'display:none;'; ?>" min="<?php echo $payment->trust ? 1 : 0; ?>" max="9999" step="1">
						<?php
						echo $vik->closeControl();
						?>

						<!-- RESTRICTIONS - Number -->
						<?php
						$elements = array(
							JHtml::_('select.option', 0, JText::_('VRPAYRESTROPT1')),
							JHtml::_('select.option', 1, JText::_('VRPAYRESTROPT2')),
							JHtml::_('select.option', -1, JText::_('VRPAYRESTROPT3')),
						);

						if ($payment->enablecost > 0)
						{
							$factor = 1;
						}
						else if ($payment->enablecost < 0)
						{
							$factor = -1;
						}
						else
						{
							$factor = 0;
						}
						
						echo $vik->openControl(JText::_('VRMANAGEPAYMENT10')); ?>
							<select name="enablecost_factor" id="vr-enablecost-sel">
								<?php echo JHtml::_('select.options', $elements, 'value', 'text', $factor); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- ENABLE COST THRESHOLD - Number -->

						<?php
						$control = array();
						$control['style'] = $payment->enablecost == 0 ? 'display: none;' : '';

						echo $vik->openControl('', 'vrenablecost-amount', $control); ?>
							<div class="input-prepend currency-field vrenablecost-amount" style="<?php echo ($payment->enablecost == 0 ? 'display: none;' : ''); ?>">
								<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

								<input type="number" name="enablecost_amount" value="<?php echo abs($payment->enablecost); ?>" size="6" min="0" max="99999999" step="any" />
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- GROUP - Radio Button -->
						<?php
						$groups = JHtml::_('vrehtml.admin.groups', array(1, 2), true, '');

						echo $vik->openControl(JText::_('VRMANAGECUSTOMF7')); ?>
							<select name="group" id="vr-group-sel">
								<?php echo JHtml::_('select.options', $groups, 'value', 'text', $payment->group, true); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

				<!-- PARAMETERS -->

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMANAGEPAYMENT8')); ?>

					<div class="vikpayparamdiv">
						<?php echo $vik->alert(JText::_('VRMANAGEPAYMENT9')); ?>
					</div>

					<div id="vikparamerr" style="display: none;">
						<?php echo $vik->alert(JText::_('VRE_AJAX_GENERIC_ERROR'), 'error'); ?>
					</div>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<!-- NOTES TAB -->

		<?php echo $vik->bootAddTab('payment', 'payment_notes', JText::_('VRMANAGECUSTOMERTITLE4')); ?>

			<div class="row-fluid">

				<!-- NOTES BEFORE PURCHASE -->

				<div class="span6">
					<?php
					echo $vik->openFieldset(JText::_('VRMANAGEPAYMENT11'));
					echo $editor->display('prenote', $payment->prenote, 400, 200, 70, 20);
					echo $vik->closeFieldset();
					?>
				</div>

				<!-- NOTES AFTER PURCHASE -->

				<div class="span6">
					<?php
					echo $vik->openFieldset(JText::_('VRMANAGEPAYMENT7'));
					echo $editor->display('note', $payment->note, 400, 200, 70, 20);
					echo $vik->closeFieldset();
					?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewPayment". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			echo $vik->bootAddTab('payment', 'payment_custom', JText::_('VRE_CUSTOM_FIELDSET'));
			echo $vik->openEmptyFieldset();
			echo $custom;
			echo $vik->closeEmptyFieldset();
			echo $vik->bootEndTab();
		}
		?>

	<?php echo $vik->bootEndTabSet(); ?>
		
	<input type="hidden" name="id" value="<?php echo $payment->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />

</form>

<?php
JText::script('VRE_FILTER_SELECT_DRIVER');
JText::script('VRE_FILTER_SELECT_GROUP');
JText::script('VRPAYMENTICONOPT0');
JText::script('VRPAYMENTPOSOPT1');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#vr-driver-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_DRIVER'),
			allowClear: false,
			width: 300,
		});

		jQuery('#vr-enablecost-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 250,
		});

		jQuery('#vr-group-sel').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_GROUP'),
			allowClear: true,
			width: 250,
		});

		jQuery('#vr-percentot-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 75,
		});

		jQuery('#vr-icontype-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: true,
			placeholder: Joomla.JText._('VRPAYMENTICONOPT0'),
			width: 150,
		});

		jQuery('#vr-position-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: true,
			placeholder: Joomla.JText._('VRPAYMENTPOSOPT1'),
			width: 150,
		});

		jQuery('#vr-fonticon-sel').select2({
			placeholder: '--',
			allowClear: false,
			width: 150,
			formatResult: (opt) => {
				// Use a minimum width for the icons shown within the dropdown options
				// in order to have the texts properly aligned.
				// At the moment, the largest width of the icon seems to be 17px.
				return '<i class="' + opt.id + '" style="min-width:18px;"></i> ' + opt.text;
			},
			formatSelection: (opt) => {
				// Do not use a minimum width for the icon shown within the selection label.
				// Here we don't need to have a large space between the icon and the text.
				return '<i class="' + opt.id + '"></i> ' + opt.text;
			},
		});

		jQuery('#vr-icontype-sel').on('change', function() {
			var val = jQuery(this).val();

			if (val == 1) {
				jQuery('#vr-fonticon-wrapper').show();
				jQuery('#vr-iconupload-wrapper').hide();
			} else if (val == 2) {
				jQuery('#vr-fonticon-wrapper').hide();
				jQuery('#vr-iconupload-wrapper').show();
			} else {
				jQuery('#vr-fonticon-wrapper').hide();
				jQuery('#vr-iconupload-wrapper').hide();
			}

		});

		jQuery('#vr-driver-sel').on('change', vrPaymentGatewayChanged);

		<?php
		if ($payment->file)
		{
			?>
			vrPaymentGatewayChanged();
			<?php
		}
		?>

		jQuery('#vr-enablecost-sel').on('change', function() {
			if (parseInt(jQuery(this).val()) == 0) {
				jQuery('.vrenablecost-amount').hide();
			} else {
				jQuery('.vrenablecost-amount').show();
			}
		});

	});
	
	function vrPaymentGatewayChanged() {
		var gp = jQuery('#vr-driver-sel').val();
		
		jQuery.noConflict();

		// destroy select2 
		jQuery('.vikpayparamdiv select').select2('destroy');
		// unregister form fields
		validator.unregisterFields('.vikpayparamdiv .required');
		
		jQuery('.vikpayparamdiv').html('');
		jQuery('#vikparamerr').hide();

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=payment.driverfields',
			{
				driver: gp,
				id: <?php echo (int) $payment->id; ?>,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				if (!obj) {
					jQuery('#vikparamerr').show();
					return false;
				}

				jQuery('.vikpayparamdiv').html(obj[0]);

				// render select
				jQuery('.vikpayparamdiv select').each(function() {
					jQuery(this).select2({
						// disable search for select with 3 or lower options
						minimumResultsForSearch: jQuery(this).find('option').length > 3 ? 0 : -1,
						allowClear: false,
						width: 285,
					});
				});

				// register form fields for validation
				validator.registerFields('.vikpayparamdiv .required');

				// init helpers
				jQuery('.vikpayparamdiv .vr-quest-popover').popover({sanitize: false, container: 'body'});
			},
			function(error) {
				jQuery('#vikparamerr').show();
			}
		);
	}

	function trustValueChanged(is) {
		if (is) {
			jQuery('input[name="trust"]').attr('min', 1).val(1).show();
		} else {
			jQuery('input[name="trust"]').attr('min', 0).val(0).hide();
		}
	}

	function setconfirmedValueChanged(is) {
		if (is) {
			jQuery('.vr-confirm-field').show();
		} else {
			jQuery('.vr-confirm-field').hide();

			var input = jQuery('input[name="selfconfirm"]');

			if (input.is(':checkbox')) {
				input.prop('checked', false);
			} else {
				input.val(0);
			}
		}
	}

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
