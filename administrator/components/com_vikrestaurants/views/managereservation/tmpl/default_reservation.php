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
JHtml::_('vrehtml.scripts.updateshifts', 1, '_vrUpdateWorkingShifts');

$reservation = $this->reservation;

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

?>
				
<!-- DATE - Calendar -->
<?php
echo $vik->openControl(JText::_('VRMANAGERESERVATION13').'*');

$attributes = array();
$attributes['class'] 	= 'required';
$attributes['onChange'] = "vrUpdateWorkingShifts();vrUpdateAvailableTables();";

echo $vik->calendar($reservation->date, 'date', 'vrdatefilter', null, $attributes);

echo $vik->closeControl();
?>

<!-- TIME - Dropdown -->
<?php
// calculate available times
$times = JHtml::_('vikrestaurants.times', 1, $reservation->date);

$attrs = array(
	'id'    => 'vr-hour-sel',
	'class' => 'required',
);

echo $vik->openControl(JText::_('VRMANAGERESERVATION14') . '*', 'multi-field');
echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $reservation->hourmin, $times, $attrs);
?>
	<a href="javascript: void(0);" id="busytime" onclick="vrOpenJModal(this.id, null, true); return false;" style="margin-left: 5px;" class="no-underline">
		<i class="fas fa-calendar-alt big"></i>
	</a>

	<a href="javascript: void(0);" id="staytime" onclick="vrOpenStayTime(this)" style="margin-left: 15px;" class="no-underline">
		<i class="fas fa-chevron-down"></i>
	</a>
<?php echo $vik->closeControl(); ?>

<!-- STAY TIME - Number -->
<hr style="border-top-width: 1px; border-top-style: dashed; border-top-color: rgb(204, 204, 204); width: 100%; display: none;" class="staytime-field" />

<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION25'), 'staytime-field', array('style' => 'display:none;')); ?>
	<div class="input-append">
		<input type="number" name="stay_time" id="vr-stay-time" value="<?php echo $reservation->stay_time; ?>" min="15" max="9999" step="5" />
		<button type="button" class="btn"><?php echo JText::_('VRSHORTCUTMINUTE'); ?></button>
	</div>
<?php echo $vik->closeControl(); ?>

<hr style="border-top-width: 1px; border-top-style: dashed; border-top-color: rgb(204, 204, 204); width: 100%; display: none;" class="staytime-field" />

<!-- PEOPLE - Dropdown -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION4') . '*'); ?>
	<select name="people" id="vr-people-sel" class="required" onchange="peopleNumberChanged();">
		<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.people'), 'value', 'text', $reservation->people); ?>
	</select>
<?php echo $vik->closeControl(); ?>

<!-- TABLE - Dropdown -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION5') . '*', 'multi-field with-icon'); ?>
	<select name="id_table" id="vr-table-sel" class="required">
		<option class="placeholder"></option>
		<?php
		foreach ($this->rooms as $room)
		{
			?>
			<optgroup label="<?php echo $room->name; ?>">
				<?php
				foreach ($room->tables as $table)
				{
					// fetch option name
					$name = $table->name . ' (' . $table->min_capacity . '-' . $table->max_capacity . ')';

					?>
					<option value="<?php echo $table->id; ?>" <?php echo $table->id == $reservation->id_table ? 'selected="selected"' : ''; ?> data-capacity="<?php echo $table->min_capacity . '-' . $table->max_capacity; ?>" data-name="<?php echo $name; ?>" data-shared="<?php echo $table->multi_res; ?>">
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

	<a href="javascript: void(0);" onclick="unlockDisabledTables(this);" id="unlock-tables-link" style="margin-left: 5px;" class="no-underline">
		<i class="fas fa-lock big"></i>
	</a>
<?php echo $vik->closeControl(); ?>

<!-- USER - Dropdown -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION22'), 'multi-field with-icon'); ?>
	<input type="hidden" name="id_user" class="vr-users-select" value="<?php echo $reservation->id_user > 0 ? $reservation->id_user : ''; ?>" />
	
	<a href="javascript: void(0);" id="addcustomer" onclick="vrOpenJModal(this.id, null, true); return false;" style="margin-left: 5px;" class="no-underline">
		<?php
		if ($reservation->id_user > 0)
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

<!-- NOMINATIVE - Text -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION18')); ?>
	<input class="vr-nominative-field" type="text" name="purchaser_nominative" value="<?php echo $this->escape($reservation->purchaser_nominative); ?>" size="40" />
<?php echo $vik->closeControl(); ?>

<!-- EMAIL - Text -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION6')); ?>
	<input type="email" name="purchaser_mail" value="<?php echo $reservation->purchaser_mail; ?>" size="40" onblur="composeMailFields(this);" />
<?php echo $vik->closeControl(); ?>

<!-- PHONE NUMBER - Text -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION16')); ?>
	<input type="tel" name="purchaser_phone" value="<?php echo $reservation->purchaser_phone; ?>" size="40" id="vrphone" onblur="composePhoneFields(this);" />

	<input type="hidden" name="purchaser_prefix" value="<?php echo $reservation->purchaser_prefix; ?>" />
	<input type="hidden" name="purchaser_country" value="<?php echo $reservation->purchaser_country; ?>" />
<?php echo $vik->closeControl(); ?>

<!-- DEPOSIT - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION9')); ?>
	<div class="input-prepend currency-field">
		<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

		<input type="number" name="deposit" value="<?php echo $reservation->deposit; ?>" size="8" min="0" max="999999" step="any"/>
	</div>
<?php echo $vik->closeControl(); ?>

<!-- BILL VALUE - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION10')); ?>
	<div class="input-prepend currency-field">
		<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>
	
		<input type="number" name="bill_value" value="<?php echo $reservation->bill_value; ?>" size="8" min="0" max="999999" step="any"/>
	</div>
<?php echo $vik->closeControl(); ?>

<!-- BILL CLOSED - Radio Button -->
<?php
$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $reservation->bill_closed);
$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$reservation->bill_closed);

echo $vik->openControl(JText::_('VRMANAGERESERVATION11'));
echo $vik->radioYesNo('bill_closed', $elem_yes, $elem_no, false);
echo $vik->closeControl();
?>

<!-- STATUS - Dropdown -->
<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION12')); ?>
	<select name="status" id="vr-status-sel">
		<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.orderstatuses'), 'value', 'text', $reservation->status); ?>
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

echo $vik->openControl(JText::_('VRMANAGERESERVATION20')); ?>
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
						<?php echo $payment->id == $reservation->id_payment ? 'selected="selected"' : ''; ?>
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
JText::script('VRE_FILTER_SELECT_TABLE');
JText::script('VRMANAGERESERVATION23');
JText::script('VRMANAGECONFIG32');
JText::script('VRTABNOTAV');
JText::script('VRTABNOTFIT');
?>

<script>

	var IS_AJAX_CALLING = false;
	
	var SELECTED_TABLE  = <?php echo $reservation->id_table; ?>;
	var SELECTED_PEOPLE = <?php echo $reservation->people; ?>;
	
	var BILLING_USER_LIST = [];
	
	jQuery(document).ready(function() {

		jQuery('#vr-hour-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-people-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-table-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_TABLE'),
			allowClear: false,
			width: 200,
			formatResult: formatTablesSelect,
			formatSelection: formatTablesSelect,
			escapeMarkup: function(m) { return m; },
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

		jQuery('.vr-users-select').on('change', function() {
			// auto-fill customer form fields
			handleUserChange(jQuery(this).select2('val'));
		});
		
		jQuery('.vr-users-select').select2({
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
									name:    item.billing_name,
									mail:    item.billing_mail,
									phone:   item.billing_phone,
									country: item.country_code,
									fields:  item.fields,
								};
							}

							return {
								text: item.text,
								id: item.id,
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
				if (jQuery.isEmptyObject(data.billing_name)) {
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

		// update available tables
		jQuery('#vrdatefilter, #vr-hour-sel, #vr-people-sel, #vr-stay-time').on('change', function() {
			vrUpdateAvailableTables();
		});

		jQuery('#vr-table-sel').on('change', function() {
			// refresh selected table flag
			SELECTED_TABLE = jQuery(this).val();
		});

		// refresh available tables
		vrUpdateAvailableTables();

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

		// do not submit the form in case we have any pending requests
		validator.addCallback(function() {
			if (IS_AJAX_CALLING) {
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

	function insertCustomer(data) {
		// update users select
		jQuery('.vr-users-select').select2('data', data);

		// register billing details (or update them if already exist)
		BILLING_USER_LIST[data.id] = {
			name:    data.billing_name,
			mail:    data.billing_mail,
			phone:   data.billing_phone,
			country: data.country_code,
			fields:  data.fields,
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

		}
	}
	
	function peopleNumberChanged() {
		var people = jQuery('#vr-people-sel').val();

		jQuery('.vrmenuquant').each(function() {
			var max = parseInt(jQuery(this).prop('max')) + (people - SELECTED_PEOPLE);
			jQuery(this).prop('max', max);
			
			if (jQuery(this).val() > max) {
				jQuery(this).val(max);
			}
		});
		
		SELECTED_PEOPLE = people;
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

	function vrUpdateAvailableTables() {

		jQuery('#vr-table-sel').prop('disabled', true);

		jQuery.noConflict();

		IS_AJAX_CALLING = true;

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=reservation.availabletablesajax&tmpl=component',
			{
				date:     jQuery('#vrdatefilter').val(),
				hourmin:  jQuery('#vr-hour-sel').val(),
				people:   jQuery('#vr-people-sel').val(),
				staytime: jQuery('#vr-stay-time').val(),
				id_res:   <?php echo (int) $reservation->id; ?>,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp); 

				LAST_TABLES = obj;
				
				enableAvailableTables(obj, UNLOCK_TABLES);

				jQuery('#vr-table-sel').prop('disabled', false);

				IS_AJAX_CALLING = false;
			},
			function(error) {
				enableAvailableTables([], UNLOCK_TABLES);

				LAST_TABLES = [];

				jQuery('#vr-table-sel').prop('disabled', false);

				IS_AJAX_CALLING = false;
			}
		);
	}

	function enableAvailableTables(arr, force) {
		var people = jQuery('#vr-people-sel').val();

		// convert the IDs to integers
		arr = arr.map(function(id) {
			return parseInt(id);
		});

		jQuery('#vr-table-sel option:not(option.placeholder)').each(function() {
			// make sure the table is available or we are forcing the availability
			var _in = (force !== undefined || jQuery.inArray(parseInt(jQuery(this).val()), arr) !== -1);

			// get table name
			var txt = jQuery(this).data('name');

			if (!_in) {
				// table not available, try to fetch message
				var capacity = jQuery(this).data('capacity').split('-');

				if (parseInt(capacity[0]) <= people && people <= parseInt(capacity[1])) {
					// room/table unpublished or already occupied
					txt += ' : ' + Joomla.JText._('VRTABNOTAV');
				} else {
					// the party doesn't fit
					txt += ' : ' + Joomla.JText._('VRTABNOTFIT');
				}

				// turn on table in case it was already selected,
				// but leave the error message
				if (jQuery(this).val() == SELECTED_TABLE) {
					_in = true;
				}
			}

			// enable/disable option
			jQuery(this).prop('disabled', !_in);

			// set text
			jQuery(this).text(txt);
		});

		// re-set selected value, so that disabled options will be unchecked
		jQuery('#vr-table-sel').select2('val', jQuery('#vr-table-sel').val());
	}

	var UNLOCK_TABLES = undefined;
	var LAST_TABLES   = [];

	function unlockDisabledTables(link) {
		if (UNLOCK_TABLES) {
			UNLOCK_TABLES = undefined;

			jQuery(link).find('i').removeClass('fa-unlock').addClass('fa-lock');
		} else {
			UNLOCK_TABLES = true;

			jQuery(link).find('i').removeClass('fa-lock').addClass('fa-unlock');
		}

		enableAvailableTables(LAST_TABLES, UNLOCK_TABLES);
	}

	function formatTablesSelect(opt) {
		if (!opt.id) {
			// optgroup
			return opt.text;
		}

		var html = opt.text;

		if (jQuery(opt.element).data('shared') == '1') {
			html = '<i class="fas fa-users" style=""></i> ' + html;
		}

		return html;
	}

	var INITIAL_STAY_TIME = <?php echo $reservation->stay_time; ?>;

	function vrOpenStayTime(icon) {
		icon = jQuery(icon).find('i');

		if (jQuery(icon).hasClass('fa-chevron-down')) {
			jQuery(icon).removeClass('fa-chevron-down').addClass('fa-chevron-up');

			jQuery('.staytime-field').show();

			var val = parseInt(jQuery('input[name="stay_time"]').val());
			
			if (val == 0) {
				jQuery('input[name="stay_time"]').val(<?php echo VikRestaurants::getAverageTimeStay(); ?>);
			}
		} else {
			jQuery(icon).removeClass('fa-chevron-up').addClass('fa-chevron-down');

			jQuery('.staytime-field').hide();

			jQuery('input[name="stay_time"]').val(INITIAL_STAY_TIME).trigger('change');
		}
	}
	
</script>
