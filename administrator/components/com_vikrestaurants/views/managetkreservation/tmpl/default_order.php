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

JHtml::_('vrehtml.assets.intltel', '[name="purchaser_phone"]');
JHtml::_('vrehtml.scripts.updateshifts', 2, '_vrUpdateWorkingShifts');

$order = $this->order;

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

// get origin addresses
$origin_addresses = VikRestaurants::getTakeAwayOriginAddresses();

?>
	
<!-- DATE - Calendar -->
<?php
echo $vik->openControl(JText::_('VRMANAGETKRES10') . '*');

$attributes = array();
$attributes['class'] 	= 'required';
$attributes['onChange'] = 'vrUpdateWorkingShifts();';

echo $vik->calendar($order->date, 'date', 'vrdatefilter', null, $attributes);

echo $vik->closeControl();
?>

<!-- TIME - Dropdown -->
<?php
// calculate available times
$times = JHtml::_('vikrestaurants.times', 2, $order->date);

$attrs = array(
	'id'    => 'vr-hour-sel',
	'class' => 'required',
);

echo $vik->openControl(JText::_('VRMANAGETKRES11') . '*', 'multi-field');
echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $order->hourmin, $times, $attrs);
?>
	<a href="javascript: void(0);" id="busytime" onclick="vrOpenJModal(this.id, null, true); return false;" style="margin-left: 5px;" class="no-underline">
		<i class="fas fa-calendar-alt big"></i>
	</a>
<?php echo $vik->closeControl(); ?>

<!-- SERVICE - Dropdown -->
<?php
$options = array(
	JHtml::_('select.option', 1, 'VRMANAGETKRES14'),
	JHtml::_('select.option', 0, 'VRMANAGETKRES15'),
);

echo $vik->openControl(JText::_('VRMANAGETKRES13') . '*'); ?>
	<select name="delivery_service" id="vr-service-select">
		<?php echo JHtml::_('select.options', $options, 'value', 'text', $order->delivery_service, true); ?>   
	</select>
<?php echo $vik->closeControl(); ?>

<?php
if (count($origin_addresses))
{
	?>
	<!-- ORIGIN ADDRESS - Dropdown -->
	<?php
	$elements = array(
		JHtml::_('select.option', '', ''),
	);

	foreach ($origin_addresses as $origin)
	{
		$elements[] = JHtml::_('select.option', $origin, $origin);
	}

	if (count($origin_addresses) == 1)
	{
		$selected = $origin_addresses[0];
	}
	else if (!empty($order->route->origin))
	{
		$selected = $order->route->origin;
	}
	else
	{
		$selected = null;
	}
	
	echo $vik->openControl(JText::_('VRMANAGETKRES32')); ?>
		<select name="route[origin]" id="vr-origin-select">
			<?php echo JHtml::_('select.options', $elements, 'value', 'text', $selected); ?>
		</select>
	<?php echo $vik->closeControl(); ?>

	<!-- ROUTE DETAILS - Info -->
	<?php
	$route_details = '';
	if ($order->route)
	{
		$lookup = array(
			'distancetext' => 'road',
			'durationtext' => 'stopwatch',
		);

		foreach ($lookup as $k => $icon)
		{
			if (!empty($order->route->{$k}))
			{
				$marginleft = 0;

				if (strlen($route_details))
				{
					$marginleft = 15;
				}

				$route_details .= '<i class="fas fa-' . $icon . '" style="margin-right:5px;margin-left:' . $marginleft . 'px;"></i>' . $order->route->{$k};
			}
		}
	}

	$control = array();
	$control['style'] = empty($route_details) ? 'display:none;' : '';
	
	echo $vik->openControl(JText::_('VRMANAGETKRES33'), 'vrroutedetailswrap', $control); ?>
		<div id="vrroutedetails" class="control-html-value"><?php echo $route_details; ?></div>
	<?php echo $vik->closeControl(); ?>

	<input type="hidden" name="route[distance]" value="<?php echo (!empty($order->route->distance) ? $order->route->distance : ''); ?>" id="vrorigindistance" />
	<input type="hidden" name="route[duration]" value="<?php echo (!empty($order->route->duration) ? $order->route->duration : ''); ?>" id="vroriginduration" />

	<input type="hidden" name="route[distancetext]" value="<?php echo (!empty($order->route->distancetext) ? $order->route->distancetext : ''); ?>" id="vrorigindistancetext" />
	<input type="hidden" name="route[durationtext]" value="<?php echo (!empty($order->route->durationtext) ? $order->route->durationtext : ''); ?>" id="vrorigindurationtext" />

	<?php
}
?>

<div style="border-top: 1px dashed #ccc;width: 70%;">&nbsp;</div>

<!-- USER - Dropdown -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION22'), 'multi-field with-icon'); ?>
	<input type="hidden" name="id_user" id="vr-users-select" value="<?php echo $order->id_user > 0 ? $order->id_user : ''; ?>" />
	
	<a href="javascript: void(0);" id="addcustomer" onclick="vrOpenJModal(this.id, null, true); return false;" style="margin-left: 5px;" class="no-underline">
		<?php
		if ($order->id_user > 0)
		{
			?><i class="fas fa-pen-square big"></i><?php
		}
		else
		{
			?><i class="fas fa-user-plus big"></i><?php
		}
		?>
	</a>
<?php echo $vik->closeControl(); ?>

<!-- USER ADDRESS - Dropdown -->
<?php
$elements = array(
	JHtml::_('select.option', '', ''),
);

$max_percent = 0;
$selected    = null;

if ($this->customer)
{
	foreach ($this->customer->locations as $delivery)
	{
		// get a string representation of the delivery address (exclude country and address notes)
		$addr = VikRestaurants::deliveryAddressToStr($delivery, array('country', 'address_2'));

		// insert address in list
		$elements[] = JHtml::_('select.option', $delivery->id, $addr);

		// compare the purchaser address with the current delivery address
		similar_text($addr, $order->purchaser_address, $percent);

		if ($percent >= 75 && $percent > $max_percent)
		{
			// mark delivery address as selected in case those strings are
			// similar at least at 75%
			$selected = $delivery->id;
		}

		$max_percent = max(array($max_percent, $percent));
	}
}

echo $vik->openControl(JText::_('VRMANAGETKRES29')); ?>
	<select name="id_useraddr" id="vr-user-address">
		<?php echo JHtml::_('select.options', $elements, 'value', 'text', $selected); ?>
	</select>
<?php echo $vik->closeControl(); ?>

<!-- NOMINATIVE - Text -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES25')); ?>
	<input class="vr-nominative-field" type="text" name="purchaser_nominative" value="<?php echo $this->escape($order->purchaser_nominative); ?>" size="40" />
<?php echo $vik->closeControl(); ?>

<!-- EMAIL - Text -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES5')); ?>
	<input type="email" name="purchaser_mail" value="<?php echo $order->purchaser_mail; ?>" size="40" id="vremail" onblur="composeMailFields(this);" />
<?php echo $vik->closeControl(); ?>

<!-- PHONE NUMBER - Text -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES23')); ?>
	<input type="tel" name="purchaser_phone" value="<?php echo $order->purchaser_phone; ?>" size="40" id="vrphone" onblur="composePhoneFields(this);" />

	<input type="hidden" name="purchaser_prefix" value="<?php echo $order->purchaser_prefix; ?>" />
	<input type="hidden" name="purchaser_country" value="<?php echo $order->purchaser_country; ?>" />
<?php echo $vik->closeControl(); ?>

<div style="border-top: 1px dashed #ccc;width: 70%;" class="managetkres-separator">&nbsp;</div>

<!-- TOTAL TO PAY - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES8'), 'multi-field with-icon'); ?>
	<div class="input-prepend currency-field">
		<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

		<input type="number" name="total_to_pay" value="<?php echo $order->total_to_pay; ?>" id="vr-total-cost" size="8" min="0" max="99999999" step="any" />
	</div>

	<a href="javascript:void(0);" onClick="toggleTotalCostDetails(this);" style="margin-left: 5px;" class="no-underline">
		<i class="fas fa-chevron-down"></i>
	</a>
<?php echo $vik->closeControl(); ?>

<div style="border-top: 1px dashed #ccc;width: 70%;display: none;" class="managetkres-separator vr-cost-detailed">&nbsp;</div>

<!-- TAXES - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES21'), 'vr-cost-detailed', array('style' => 'display:none;')); ?>
	<div class="input-prepend currency-field">
		<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

		<input type="number" name="taxes" value="<?php echo $order->taxes; ?>" id="vr-taxes-charge" size="8" min="0" max="99999999" step="any" />
	</div>
<?php echo $vik->closeControl(); ?>

<!-- DELIVERY CHARGE - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES31'), 'vr-cost-detailed', array('style' => 'display:none;')); ?>
	<div class="input-prepend currency-field">
		<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

		<input type="number" name="delivery_charge" value="<?php echo $order->delivery_charge; ?>" id="vr-delivery-charge" size="8" min="-99999999" max="99999999" step="any" />
	</div>
<?php echo $vik->closeControl(); ?>

<!-- PAYMENT CHARGE - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES30'), 'vr-cost-detailed', array('style' => 'display:none;')); ?>
	<div class="input-prepend currency-field">
		<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

		<input type="number" name="pay_charge" value="<?php echo $order->pay_charge; ?>" id="vr-pay-charge" size="8" min="-99999999" max="99999999" step="any" />
	</div>
<?php echo $vik->closeControl(); ?>

<div style="border-top: 1px dashed #ccc;width: 70%;display: none;" class="managetkres-separator vr-cost-detailed">&nbsp;</div>

<!-- STATUS - Dropdown -->
<?php echo $vik->openControl(JText::_('VRMANAGETKRES9')); ?>
	<select name="status" id="vr-status-sel">
		<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.orderstatuses'), 'value', 'text', $order->status); ?>
	</select>
<?php echo $vik->closeControl(); ?>

<!-- PAYMENT - Dropdown -->
<?php
$payments = array();

foreach (JHtml::_('vikrestaurants.payments') as $payment)
{
	$k = JText::_('VRSYSPUBLISHED' . $payment->published);

	if (!isset($payments[$k]))
	{
		$payments[$k] = array();
	}

	$payments[$k][] = $payment;
}

echo $vik->openControl(JText::_('VRMANAGETKRES27')); ?>
	<select name="id_payment" id="vr-payment-sel">
		<option></option>
		<?php
		foreach ($payments as $group => $list)
		{
			?>
			<optgroup label="<?php echo $group; ?>">
				<?php
				foreach ($list as $payment)
				{
					$name = $payment->name;

					if ($payment->charge != 0)
					{
						$name .= $payment->charge > 0 ? ' +' : ' ';

						if ($payment->percentot == 1)
						{
							$name .= (float) $payment->charge . '%';
						}
						else
						{
							$name .= $currency->format($payment->charge);
						}
					}
					?>
					<option
						value="<?php echo $payment->id; ?>"
						data-charge="<?php echo $payment->charge; ?>"
						data-percentot="<?php echo $payment->percentot; ?>"
						<?php echo $payment->id == $order->id_payment ? 'selected="selected"' : ''; ?>
					>
						<?php echo $name; ?>
					</option>
					<?php
				}
				?>
			</optgroup>
			<?php
		}
		?>
	</select>
<?php echo $vik->closeControl(); ?>

<!-- NOTIFY CUSTOMER - Radio Button -->
<?php
$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), false);
$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), true);

echo $vik->openControl(JText::_('VRMANAGERESERVATION15'));
echo $vik->radioYesNo('notify_customer', $elem_yes, $elem_no, false);
echo $vik->closeControl();
?>

<?php
/**
 * Preload BILLING_USER_LIST with details of current customer.
 *
 * @since 1.8
 */
if ($this->customer)
{
	$billing_json = array();

	// insert customer details
	$billing_json[$this->customer->id] = array(
		'name'     => $this->customer->billing_name,
		'mail'     => $this->customer->billing_mail,
		'phone'    => $this->customer->billing_phone,
		'country'  => $this->customer->country_code,
		'fields'   => $this->customer->fields,
		'delivery' => array(),
	);

	// assign delivery locations to their PKs
	foreach ($this->customer->locations as $loc)
	{
		$billing_json[$this->customer->id]['delivery'][$loc->id] = $loc;
	}

	// encode in JSON for JavaScript usage
	$billing_json = json_encode($billing_json);
}
else
{
	// use empty object
	$billing_json = '{}';
}

JText::script('VRMANAGERESERVATION23');
JText::script('VRMANAGECONFIG32');
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRTKRESADDRESSNOTVALID');
JText::script('VRTKROUTEDELIVERYERR');
?>

<script>

	var IS_AJAX_CALLING = false;
	
	var BILLING_USER_LIST = <?php echo $billing_json; ?>;
	
	jQuery(document).ready(function() {

		jQuery('#vr-hour-sel, #vr-service-select').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-payment-sel').select2({
			allowClear: true,
			placeholder: Joomla.JText._('VRMANAGECONFIG32'),
			width: 200,
		});

		jQuery('#vr-status-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-origin-select').select2({
			placeholder: '--',
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-user-address').select2({
			placeholder: Joomla.JText._('VRMANAGECONFIG32'),
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-users-select').on('change', function() {
			// auto-fill customer form fields
			handleUserChange(jQuery(this).select2('val'));
		});
		
		jQuery('#vr-users-select').select2({
			placeholder: Joomla.JText._('VRMANAGERESERVATION23'),
			allowClear: true,
			width: 300,
			minimumInputLength: 2,
			ajax: {
				url: 'index.php?option=com_vikrestaurants&task=search_users&tmpl=component',
				dataType: 'json',
				type: 'POST',
				quietMillis: 50,
				data: function(term) {
					return {
						term: term,
					};
				},
				results: function(data) {
					return {
						results: jQuery.map(data, function (item) {
							if (jQuery.isEmptyObject(BILLING_USER_LIST[item.id])) {
								BILLING_USER_LIST[item.id] = {
									name:     item.billing_name,
									mail:     item.billing_mail,
									phone:    item.billing_phone,
									country:  item.country_code,
									fields:   item.tkfields,
									delivery: item.delivery,
								};
							}

							return {
								text: item.text,
								id:   item.id,
							};
						}),
					};
				},
			},
			initSelection: function(element, callback) {
				// the input tag has a value attribute preloaded that points to a preselected repository's id
				// this function resolves that id attribute to an object that select2 can render
				// using its formatResult renderer - that way the repository name is shown preselected
				if (jQuery(element).val().length) {
					callback({billing_name: '<?php echo ($this->customer === null ? '' : addslashes($this->customer->billing_name)); ?>'});
				}
			},
			formatSelection: function(data) {
				if (!data.billing_name) {
					// display data retured from ajax parsing
					return data.text;
				}
				// display pre-selected value
				return data.billing_name;
			},
			dropdownCssClass: 'bigdrop',
		});

		// update working shifts
		// @deprecated (the event is triggered directly from the calendar)
		jQuery('#vrdatefilter').on('change', function() {
			vrUpdateWorkingShifts();
		});

		// save "country code" and "dial code" every time the phone number changes
		jQuery('input[name="purchaser_phone"]').on('change countrychange', function() {
			var country = jQuery(this).intlTelInput('getSelectedCountryData');

			if (!country) {
				return false;
			}

			if (country.iso2) {
				jQuery('input[name="purchaser_country"]').val(country.iso2.toUpperCase());
			}

			if (country.dialCode) {
				var dial = '+' + country.dialCode.toString().replace(/^\+/);

				if (country.areaCodes) {
					dial += ' ' + country.areaCodes[0];
				}

				jQuery('input[name="purchaser_prefix"]').val(dial);
			}
		});

		// refresh custom fields when switching address
		jQuery('#vr-user-address').on('change', function() {
			var id_user     = jQuery('#vr-users-select').val();
			var id_delivery = jQuery(this).val();

			if (!BILLING_USER_LIST.hasOwnProperty(id_user)) {
				// user not found
				return false;
			}

			var user = BILLING_USER_LIST[id_user];

			if (!user.delivery.hasOwnProperty(id_delivery)) {
				// delivery not found
				return false;
			}

			var delivery = user.delivery[id_delivery];

			var data = {
				address: (delivery.address + ' ' + delivery.address_2).trim(),
				zip:     delivery.zip,
				city:    delivery.city,
				state:   delivery.state,
			};

			// set up address, zip, city and states custom fields
			setAddressFields(data);
		});

		jQuery('#vr-service-select').on('change', function(){
			if (jQuery(this).val() == 1) {
				// delivery service selected
				jQuery('#vr-user-address').prop('disabled', false);
				jQuery('.vr-delivery-field').prop('readonly', false);

				// re-calculate the delivery charge
				jQuery('#vr-user-address').trigger('change');
			} else {
				// pickup service selected
				jQuery('#vr-user-address').prop('disabled', true);
				jQuery('.vr-delivery-field').prop('readonly', true);

				// re-calculate the pickup charge
				updatePickupCharge();

				// reset route
				calculateRoute('', '');
			}
		});

		// enable/disable delivery fields
		jQuery('#vr-service-select').trigger('change');

		// do not submit the form in case we have any pending requests
		validator.addCallback(function() {
			if (IS_AJAX_CALLING || UIAjax.isDoing()) {
				/**
				 * @todo 	Should we prompt an alert?
				 * 			e.g. "Please wait for the request completion."
				 */

				return false;
			}

			return true;
		});

		// do not submit the form in case the purchaser e-mail owns an invalid address
		validator.addCallback(function() {
			// validate e-mail first and get result
			return validateOptionalMail('input[name="purchaser_mail"]');
		});

	});

	// Customer

	function insertCustomer(data) {
		// update users select
		jQuery('#vr-users-select').select2('data', data);

		// register billing details (or update them if already exist)
		BILLING_USER_LIST[data.id] = {
			name:     data.billing_name,
			mail:     data.billing_mail,
			phone:    data.billing_phone,
			country:  data.country_code,
			fields:   data.fields,
			delivery: data.locations,
		};

		// auto-fill customer form fields
		handleUserChange(data.id);
	}

	function handleUserChange(id) {
		if (!id) {
			jQuery('#addcustomer i').removeClass('fa-pencil-square').addClass('fa-user-plus');
		} else {
			jQuery('#addcustomer i').removeClass('fa-user-plus').addClass('fa-pencil-square');
		}

		var addr_html = '<option></option>';

		// fill billing fields
		if (!jQuery.isEmptyObject(BILLING_USER_LIST[id])) {
			// nominative
			if (jQuery('.vr-nominative-field').length <= 2) {
				// all the fields found are FULL NAME
				jQuery('.vr-nominative-field').each(function() {
					jQuery(this).val(BILLING_USER_LIST[id].name);
				});
			} else {
				// otherwise only the first is FULL NAME
				jQuery('.vr-nominative-field').first().val(BILLING_USER_LIST[id].name);
			}

			// mail
			jQuery('input[name="purchaser_mail"]').each(function() {
				jQuery(this).val(BILLING_USER_LIST[id].mail);
			});

			// phone number
			jQuery('input[name="purchaser_phone"]').each(function() {
				jQuery(this).intlTelInput('setNumber', BILLING_USER_LIST[id].phone);
			});

			// fill all remaining custom fields
			jQuery.each(BILLING_USER_LIST[id].fields, function(cf_name, cf_val) {
				
				var input = jQuery('*[data-cfname="' + cf_name + '"]');

				if (input.length) {
					if (input.is('select')) {
						if (input.find('option[value="' + cf_val + '"]').length) {
							// refresh select value if the option exists
							input.select2('val', cf_val);
						} else {
							// otherwise select the first option
							input.select2('val', input.find('option').first().val());
						}
					} else if (input.is('checkbox')) {
						// check/uncheck the input
						input.prop('checked', cf_val ? true : false);
					} else if (input.hasClass('phone-field')) {
						// update phone number
						input.intlTelInput('setNumber', cf_val);
					} else {
						// otherwise refresh as default input
						input.val(cf_val);
					}
				}

			});

			// push addresses within the dropdown HTML
			jQuery.each(BILLING_USER_LIST[id].delivery, function(id, delivery) {
				addr_html += '<option value="' + id + '">' + delivery.fullString + '</option>';
			});

			// try always to re-calculate delivery price
			evaluateCoordinatesFromAddress(getAddressString());
		}

		// update HTML of address dropdown
		jQuery('#vr-user-address').html(addr_html);
		jQuery('#vr-user-address').select2('val', '');
	}
	
	function composeMailFields(input) {
		/**
		 * Do not replace anymore the e-mail custom fields.
		 *
		 * @since 1.8
		 */
		// jQuery('.mail-field').val(jQuery(input).val());
	}
	
	function composePhoneFields(input) {
		var number = jQuery(input).intlTelInput('getNumber');

		/**
		 * Do not replace anymore the phone number custom fields.
		 *
		 * @since 1.8
		 */
		// jQuery('.phone-field').intlTelInput('setNumber', number);
	}

	// Total Cost

	var TOTAL_NET_COST = null;

	jQuery(document).ready(function() {

		getTotalNetPrice();

		// payment
		jQuery('#vr-payment-sel').on('change', function() {

			var tcost = getTotalNetPrice();

			var charge    = parseFloat(jQuery(this).find(':selected').data('charge'));
			var percentot = parseInt(jQuery(this).find(':selected').data('percentot'));

			if (isNaN(charge)) {
				charge = 0;
				percentot = 2;
			}

			var curr_pay_charge = charge;

			if (percentot == 1) {
				curr_pay_charge = tcost * charge / 100.0;
			}

			// update pay charge
			jQuery('#vr-pay-charge').val(curr_pay_charge);

			updateTotalCost(tcost);
		});

		// validate address when something changes
		jQuery('#vr-user-address, .address-field, .zip-field, .city-field, .state-field').on('change', function(){
			evaluateCoordinatesFromAddress(getAddressString());
		});

		// re-calculate total cost when something changes
		jQuery('#vr-pay-charge, #vr-taxes-charge, #vr-delivery-charge').on('change', function(){
			updateTotalCost(TOTAL_NET_COST);
		});

	});

	function updatePickupCharge() {
		var base_charge = <?php echo VikRestaurants::getTakeAwayPickupAddPrice(); ?>;
		var percentot   = <?php echo VikRestaurants::getTakeAwayPickupPercentOrTotal(); ?>;

		// check if we are going to offer a discount
		if (percentot == 1 && base_charge < 0) {
			<?php
			if ($order->id && $order->delivery_service == 0)
			{
				?>
				// do not refresh the pickup discount when editing
				// an order that is already assigned to pickup service
				return false;
				<?php
			}
			?>
		}

		updateServiceCharge(base_charge, percentot, 0);
	}

	function updateDeliveryCharge(ch) {
		if (ch === undefined) {
			ch = 0;
		}

		var base_charge = <?php echo VikRestaurants::getTakeAwayDeliveryServiceAddPrice(); ?>;
		var percentot   = <?php echo VikRestaurants::getTakeAwayDeliveryServicePercentOrTotal(); ?>;

		updateServiceCharge(base_charge, percentot, ch);
	}

	function updateServiceCharge(base, percentot, ch) {
		// get taxes settings
		var tax_ratio = parseFloat(<?php echo VikRestaurants::getTakeAwayTaxesRatio(); ?>);
		var tax_usage = parseInt(<?php echo VikRestaurants::isTakeAwayTaxesUsable(); ?>);

		// get current taxes
		var all_tax = getInputPrice('#vr-taxes-charge');
		var charge  = getInputPrice('#vr-delivery-charge');

		/**
		 * Every time the delivery cost is going to be changed, we need to 
		 * remove the current delivery taxes. So, the code below should never
		 * be manually invoked:
		 * jQuery('#vr-delivery-charge').val(x);
		 *
		 * The delivery cost MUST be always updated by using a specific method,
		 * in order to handle the related taxes properly.
		 */

		// remove taxes from input
		if (charge > 0) {
			if (tax_usage == 0) {
				// included
				// all_tax -= charge * 100 / (100 - tax_ratio) - charge;
				all_tax -= charge * (100 + tax_ratio) / 100 - charge;
			} else {
				// excluded
				all_tax -= charge * tax_ratio / 100;
			}
		}

		var net = getTotalNetPrice();

		if (percentot == 1) {
			base = (net * base / 100).roundTo(2);
		}

		base = (base + ch).roundTo(2);

		// do not alter global taxes and base cost in case we have a discount
		if (base > 0) {
			var service_tax = 0

			if (tax_usage == 0) {
				// included, find taxes
				service_tax = (base - (base * 100 / (tax_ratio + 100))).roundTo(2);

				// subtract taxes from base charge
				base -= service_tax;
			} else {
				// excluded, calculate additional taxes
				service_tax = (base * tax_ratio / 100).roundTo(2);
			}

			// re-add taxes in input
			all_tax += service_tax;
		}

		// update charge
		jQuery('#vr-delivery-charge').val(base.roundTo(2));

		// update global taxes
		jQuery('#vr-taxes-charge').val(all_tax.roundTo(2));

		updateTotalCost(net);
	}

	function getInputPrice(selector) {
		var price = parseFloat(jQuery(selector).val());

		if (isNaN(price)) {
			return 0;
		}

		return price;
	}

	function getTotalNetPrice() {
		var grand_total = getInputPrice('#vr-total-cost');
		var pay_charge  = getInputPrice('#vr-pay-charge');
		var taxes       = getInputPrice('#vr-taxes-charge');
		var service_ch  = getInputPrice('#vr-delivery-charge');

		// update cached value
		TOTAL_NET_COST = grand_total - pay_charge - taxes - service_ch;

		return TOTAL_NET_COST;
	}

	function updateTotalCost(net) {
		var pay_charge  = getInputPrice('#vr-pay-charge');
		var taxes       = getInputPrice('#vr-taxes-charge');
		var service_ch  = getInputPrice('#vr-delivery-charge');
		var grand_total = net + pay_charge + taxes + service_ch;

		jQuery('#vr-total-cost').val(grand_total.roundTo(2));
	}

	function toggleTotalCostDetails(link) {
		if (jQuery('#vr-taxes-charge').is(':visible')) {
			jQuery('.vr-cost-detailed').hide();

			jQuery(link).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
		} else {
			jQuery('.vr-cost-detailed').show();

			jQuery(link).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
		}
	}

	// Location

	jQuery(document).ready(function() {

		// listen console to catch any interesting error
		VikMapsFailure.listenConsole();

		// auto-complete delivery address
		if (typeof google.maps.places !== 'undefined') {
			<?php
			if (VikRestaurants::isGoogleMapsApiEnabled('places'))
			{
				// include JavaScript code to support the addresses autocompletion
				// only in case the Places API is enabled in the configuration

				?>
				// use Google Autocomplete feature
				var googleAddress = new google.maps.places.Autocomplete(
					jQuery('.address-field')[0], {}
				);

				googleAddress.addListener('place_changed', function() {
					var place = googleAddress.getPlace();

					// extract data from place
					data = VikGeo.extractDataFromPlace(place);

					// set address custom fields
					setAddressFields(data);

					jQuery('.address-field').trigger('change');
				});

				jQuery(window).on('google.autherror google.apidisabled.places', function() {
					// disable autocomplete on failure
					VikMapsFailure.disableAutocomplete(jQuery('.address-field')[0], googleAddress);
				});
				<?php
			}
			?>
		}

	});

	function getAddressString() {
		var parts = [];

		if (!jQuery('.address-field').val()) {
			// return empty string in case the address is missing
			return '';
		}

		// extract address from custom fields
		jQuery('.address-field, .zip-field, .city-field, .state-field').each(function() {
			var val = jQuery(this).val();

			if (val.length) {
				parts.push(val);
			}
		});

		return parts.join(', ');
	}

	function setAddressFields(data) {
		// set ZIP into the related field, if any
		if (jQuery('.zip-field').length) {
			jQuery('.zip-field').val(data.zip);

			// unset zip from data to avoid including it within the address too
			delete data.zip;
		}

		// set CITY into the related field, if any
		if (jQuery('.city-field').length) {
			jQuery('.city-field').val(data.city);

			// unset city from data to avoid including it within the address too
			delete data.city;
		}

		// set STATE into the related field, if any
		if (jQuery('.state-field').length) {
			jQuery('.state-field').val(data.state);

			// unset state from data to avoid including it within the address too
			delete data.state;
		}

		var parts = [];

		// use address
		parts.push(data.address);

		var block = [];

		if (data.zip) {
			block.push(data.zip);
		}

		if (data.city) {
			block.push(data.city);
		}

		if (data.state) {
			block.push(data.state);
		}

		if (block.length) {
			parts.push(block.join(' '));
		}

		// Set ADDRESS into the related field.
		// The address will contain also the information of
		// those fields that are not configured as custom fields.
		jQuery('.address-field').val(parts.join(', '));
	}

	function evaluateCoordinatesFromAddress(address) {
		if (address.length == 0 || jQuery('#vr-service-select').val() == 0) {
			// unset delivery charge in case of missing address or if the 
			// service is currently set to "pickup"
			updateDeliveryCharge(0);
			return;
		}

		// do not go ahead in case Google Maps failed the authentication
		if (VikMapsFailure.hasError()) {
			return;
		}

		// make sure Geocoder API are supported before proceeding with the validation
		if (typeof google.maps.Geocoder !== 'undefined') {
			// disable user address field while validating the address
			jQuery('#vr-user-address').prop('disabled', true);

			var geocoder = new google.maps.Geocoder();

			var coord = null;

			IS_AJAX_CALLING = true;

			geocoder.geocode({'address': address}, function(results, status) {
				if (status == 'OK') {
					coord = {
						lat: results[0].geometry.location.lat(),
						lng: results[0].geometry.location.lng(),
					};

					var zip = '';

					jQuery.each(results[0].address_components, function() {
						if (this.types[0] == 'postal_code') {
							zip = this.short_name;
						}
					});

					// calculate route on address change
					jQuery('#vr-origin-select').trigger('change');

					getLocationDeliveryInfo(coord, zip);

					IS_AJAX_CALLING = false;
				} else {
					// re-enable user address field
					jQuery('#vr-user-address').prop('disabled', false);

					IS_AJAX_CALLING = false;
				}
			});
		}
	}

	function getLocationDeliveryInfo(coord, zip, elem) {
		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=tkarea.getinfoajax&tmpl=component',
			{
				lat:  coord.lat,
				lng:  coord.lng,
				zip:  zip,
				json: 1,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				if (obj.status == 1) {	
					// update delivery charge
					updateDeliveryCharge(obj.area.charge);
				} else {
					// address not allowed, ask for a manual confirmation
					var r = confirm(Joomla.JText._('VRTKRESADDRESSNOTVALID'));

					if (r) {
						// ACCEPTED, use maximum charge for an address outside
						// of the delivery areas
						updateDeliveryCharge(<?php echo $this->maxDeliveryCharge; ?>);
					} else {
						// REFUSED, reset delivery charge and clear address dropdown
						jQuery('#vr-user-address').select2('val', '');
						updateDeliveryCharge(0);
					}
				}

				// re-enable user address field
				jQuery('#vr-user-address').prop('disabled', false);
			},
			function(error) {
				// reset delivery charge in case of failure
				updateDeliveryCharge(0);

				// Do not unset address fields because we might lose temporary details
				// jQuery('.address-field, .zip-field, .city-field, .state-field').val('');
				jQuery('#vr-user-address').select2('val', '');

				// re-enable user address field
				jQuery('#vr-user-address').prop('disabled', false);

				if (!error.responseText) {
					// use default connection lost error
					error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
				}

				// raise error
				alert(error.responseText);
			}
		);
	}

	// Route

	var routeDirectionService = null;

	jQuery(document).ready(function() {

		if (typeof google.maps.DirectionsService !== 'undefined') {
			<?php
			if (VikRestaurants::isGoogleMapsApiEnabled('directions'))
			{
				// include JavaScript code to support the Directions Service
				// only in case the Directions API is enabled in the configuration

				?>
				routeDirectionService = new google.maps.DirectionsService;
				
				jQuery('#vr-origin-select').on('change', function() {
					var origin 		= jQuery(this).val();
					var destination = getRouteDestination();

					calculateRoute(origin, destination);
				});
				<?php
			}
			?>
		}

	});

	function getRouteDestination() {
		// get selected user delivery address
		var destination = jQuery('#vr-user-address option:selected').text();

		if (destination.length == 0) {
			// no specified address, get full address from custom fields
			destination = getAddressString();
		}

		return destination;
	}

	function calculateRoute(origin, destination) {
		if (origin.length == 0 || destination.length == 0 || jQuery('#vr-service-select').val() == 0) {
			// unset route response from form
			fillRouteResponse(null);
			// clear route information
			displayRouteResponse('', '');
			return;
		}

		// get cached route, if any
		var registered = getRegisteredRoute(origin, destination);

		if (registered !== null) {
			// found cached route, use it
			fillRouteResponse(registered);
			displayRouteResponse(registered.distance.text, registered.duration.text);
			return;
		}

		// prepare route search arguments
		var route_prop = {
			origin:        origin,
			destination:   destination,
			travelMode:    google.maps.TravelMode.DRIVING,
			avoidHighways: true,
			avoidTolls:    true,
			drivingOptions: {
				departureTime: getDepartureTime(),
				trafficModel:  google.maps.TrafficModel.BEST_GUESS,
				// trafficModel: google.maps.TrafficModel.PESSIMISTIC,
			},
		};

		IS_AJAX_CALLING = true;

		// fetch route details
		routeDirectionService.route(route_prop, function(response, status) {
			if (status === google.maps.DirectionsStatus.OK) {
				// cache route details
				registerRoute(origin, destination, response.routes[0].legs[0]);

				// fill form and display route details
				fillRouteResponse(response.routes[0].legs[0]);
				displayRouteResponse(response.routes[0].legs[0].distance.text, response.routes[0].legs[0].duration.text);
			} else {
				// unset route details
				fillRouteResponse(null);
				displayRouteResponse('', '');

				if (status !== 'REQUEST_DENIED') {
					// alert error only in case it is not a REQUEST DENIED (Directions API disabled)
					window.alert(Joomla.JText._('VRTKROUTEDELIVERYERR').replace(/%s/, status));
				}
			}

			IS_AJAX_CALLING = false;
		});

	}

	function fillRouteResponse(leg) {
		jQuery('#vrorigindistance').val(leg !== null ? leg.distance.value : '');
		jQuery('#vroriginduration').val(leg !== null ? leg.duration.value : '');

		jQuery('#vrorigindistancetext').val(leg !== null ? leg.distance.text : '');
		jQuery('#vrorigindurationtext').val(leg !== null ? leg.duration.text : '');
	}

	function displayRouteResponse(distance, duration) {
		if (distance.length && duration.length) {
			jQuery('#vrroutedetails').html(
				'<i class="fas fa-road" style="margin-right:5px;"></i>' + distance +
				'<i class="fas fa-stopwatch" style="margin-right:5px;margin-left:15px;"></i>' + duration
			);

			jQuery('.vrroutedetailswrap').show();
		} else {
			jQuery('#vrroutedetails').html('');
			jQuery('.vrroutedetailswrap').hide();
		}
	}

	var MAP_DB_ROUTE = {};

	function registerRoute(origin, destination, leg) {
		MAP_DB_ROUTE[(origin + destination).hashCode()] = leg;
	}

	function getRegisteredRoute(origin, destination) {
		var hash = (origin + destination).hashCode();

		if (MAP_DB_ROUTE.hasOwnProperty(hash)) {
			return MAP_DB_ROUTE[hash];
		}

		return null;
	}

	function getDepartureTime() {
		return getDateTimeObject(
			// date input ID
			'vrdatefilter',
			// time input ID
			'vr-hour-sel',
			// date format
			'<?php echo VREFactory::getConfig()->get('dateformat'); ?>'
		);
	}

	// AJAX

	function vrUpdateWorkingShifts() {
		// making an AJAX request
		IS_AJAX_CALLING = true;

		_vrUpdateWorkingShifts(
			'#vrdatefilter',
			'#vr-hour-sel',
			function(resp) {
				// request has finished
				IS_AJAX_CALLING = false;
			},
			function(error) {
				// request has finished
				IS_AJAX_CALLING = false;
			}
		);
	}
	
</script>
