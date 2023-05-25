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
 * Template file used to display the custom fields.
 *
 * @since 1.8
 */

$vik = VREApplication::getInstance();

?>

<div id="vrordererrordiv" class="vrordererrordiv" style="display: none;">
	
</div>

<div class="vrcustomfields">
	<?php
	$userFields = $this->user ? $this->user->fields->takeaway : array();

	/**
	 * Extract name components from registered user name
	 * and fill the related custom fields.
	 *
	 * @since 1.8
	 */
	VikRestaurants::extractNameFields($this->customFields, $userFields);

	/**
	 * Extract address components from last searched address
	 * and fill the related custom fields.
	 *
	 * @since 1.8
	 */
	VikRestaurants::extractAddressFields($this->customFields, $userFields);

	$address_response_box = false;

	// iterate custom fields
	foreach ($this->customFields as $cf)
	{
		// fetch custom field name
		$langName = empty($cf['langname']) ? JText::_($cf['name']) : $cf['langname'];
		
		$textval = '';

		if (!empty($userFields[$cf['name']]))
		{
			// use value found in custom fields
			$textval = $userFields[$cf['name']];
		}

		$displayData = array(
			'label' => $langName,
			'value' => $textval,
			'field' => $cf,
			'user'  => $this->user,
		);

		if (VRCustomFields::isInputText($cf) && VRCustomFields::isPhoneNumber($cf))
		{
			// use tel input
			$input = 'tel';
		}
		else
		{
			// use custom field type
			$input = $cf['type'];
		}

		/**
		 * Fetch here the class to use for the field.
		 *
		 * @since 1.8.3
		 */
		if (VRCustomFields::isAddress($cf))
		{
			$displayData['class'] = 'vrtk-address-field';
		}
		else if (VRCustomFields::isZip($cf))
		{
			$displayData['class'] = 'vrtk-zip-field';
		}
		else if (VRCustomFields::isCity($cf))
		{
			$displayData['class'] = 'vrtk-city-field';
		}
		else if (VRCustomFields::isDeliveryNotes($cf))
		{
			$displayData['class'] = 'vrtk-delivery-notes-field';	
		}

		// display address response only once (in case of address/zip)
		if (!$address_response_box && (VRCustomFields::isAddress($cf) || VRCustomFields::isZip($cf) || VRCustomFields::isCity($cf)))
		{
			$displayData['addressResponseBox'] = $address_response_box = true;
		}
		?>
		<div class="vr-field-wrapper">
			<?php
			/**
			 * The form field is displayed from the layout below:
			 * /components/com_vikrestaurants/layouts/form/fields/[TYPE].php
			 *
			 * @since 1.8
			 */
			echo JLayoutHelper::render('form.fields.' . $input, $displayData);
			?>
		</div>
		<?php
	}

	/**
	 * Trigger event to retrieve an optional field that could be used
	 * to confirm the subscription to a mailing list.
	 *
	 * @param 	array 	$user     The user details.
	 * @param 	array 	$options  An array of options.
	 *
	 * @return  string  The HTML to display.
	 *
	 * @since 	1.8
	 */
	$html = VREFactory::getEventDispatcher()->triggerOnce('onDisplayMailingSubscriptionInput', array((array) $this->user));
	
	// display field if provided
	if ($html)
	{
		?>
		<div>
			<span class="cf-value"><?php echo $html; ?></span>
		</div>
		<?php
	}

	/**
	 * Only in case of guest users, try to display the 
	 * ReCAPTCHA validation form.
	 *
	 * @since 1.8.2
	 */
	$is_captcha = !$this->user && $vik->isGlobalCaptcha();

	if ($is_captcha)
	{
		?>
		<div>
			<span class="cf-value"><?php echo $vik->reCaptcha(); ?></span>
		</div>
		<?php
	}
	?>
</div>

<script>

	var vrCustomFieldsValidator;

	(function($) {
		$(function() {
			vrCustomFieldsValidator = new VikFormValidator('#vrpayform', 'vrinvalid');

			/**
			 * Overwrite getLabel method to properly access the
			 * label by using our custom layout.
			 *
			 * @param 	mixed 	input  The input element.
			 *
			 * @param 	mixed 	The label of the input.
			 */
			vrCustomFieldsValidator.getLabel = function(input) {
				if (jQuery(input).is(':checkbox')) {
					// get label next to the checkbox
					var label = jQuery(input).next('label');

					// check if we have a popup link
					if (label.find('a')) {
						return label.find('a');
					}

					return label;
				}

				return jQuery(input).closest('.cf-value').find('.cf-label *[id^="vrcf"]');
			}

			<?php
			if ($is_captcha)
			{
				?>
				/**
				 * Add callback to validate whether the ReCAPTCHA quiz
				 * was completed or not.
				 *
				 * @return 	boolean  True if completed, false otherwise.
				 */
				vrCustomFieldsValidator.addCallback(function() {
					// get recaptcha elements
					var captcha = jQuery('#vrpayform .g-recaptcha').first();
					var iframe  = captcha.find('iframe').first();

					// get widget ID
					var widget_id = captcha.data('recaptcha-widget-id');

					// check if recaptcha instance exists
					// and whether the recaptcha was completed
					if (typeof grecaptcha !== 'undefined'
						&& widget_id !== undefined
						&& !grecaptcha.getResponse(widget_id)) {
						// captcha not completed
						iframe.addClass('vrinvalid');
						return false;
					}

					// captcha completed
					iframe.removeClass('vrinvalid');
					return true;
				});
				<?php
			}
			?>
		});
	})(jQuery);

	<?php
	$pool = array();

	if ($this->user)
	{
		foreach ($this->user->locations as $addr)
		{
			$pool[$addr->id] = $addr;
		}
	}
	?>
	var USER_LOCATIONS_POOL = <?php echo json_encode($pool); ?>;

	jQuery(document).ready(function() {

		// CUSTOM FIELDS effect

		var inputs = jQuery('.vrcustomfields .cf-value').find('input,textarea,select');

		function handleCustomFieldFocus(input) {
			jQuery(input).closest('.cf-value')
				.find('.cf-bar, .cf-label')
					.addClass('focus');
		}

		function handleCustomFieldBlur(input) {
			var val = jQuery(input).val();

			// remove class from label only if empty
			if (!val || val.length == 0) {
				jQuery(input).closest('.cf-value')
					.find('.cf-label')
						.removeClass('focus');
			}

			jQuery(input).closest('.cf-value')
				.find('.cf-bar')
					.removeClass('focus');
		}

		// add/remove has-value class during change and blur events
		inputs.on('change blur', function() {
			var val = jQuery(this).val();

			if (val && val.length) {
				jQuery(this).addClass('has-value');
			} else {
				jQuery(this).removeClass('has-value');
			}
		});

		// handle focus classes on siblings (on focus)
		inputs.on('focus', function() {
			handleCustomFieldFocus(this);
		});

		// handle focus classes on siblings (on blur)
		inputs.on('blur', function() {
			handleCustomFieldBlur(this);
		});

		// prepare each field on ready
		jQuery(inputs).each(function() {
			handleCustomFieldFocus(this);
			handleCustomFieldBlur(this);
		});

		// handle global address field change
		jQuery('#vrtk-user-address-sel').on('change', function() {
			// get selected location ID
			var id = parseInt(jQuery(this).val());

			var addr = jQuery('.vrtk-address-field');
			var zip  = jQuery('.vrtk-zip-field');
			var city = jQuery('.vrtk-city-field');
			var note = jQuery('.vrtk-delivery-notes-field');

			if (USER_LOCATIONS_POOL.hasOwnProperty(id)) {
				// get selected location
				var loc = USER_LOCATIONS_POOL[id];

				// fetch base address
				var addrStr = loc.address;

				if (loc.address_2) {
					// append extra notex
					addrStr += ' ' + loc.address_2;
				}

				if (city.length) {
					// set city within the related field
					city.val(loc.city || loc.state).trigger('blur');
				} else if (loc.city || loc.state) {
					// append city to address string
					addrStr += ', ' + (loc.city || loc.state);
				}

				if (zip.length) {
					// set ZIP code within the related field
					zip.val(loc.zip).trigger('blur');
				} else if (loc.zip) {
					// append ZIP Code to address string
					addrStr += ', ' + loc.zip;
				}

				if (note.length) {
					// set delivery notes within the related field
					note.val(loc.note).trigger('blur');
				}

				addr.val(addrStr).trigger('blur');
			} else {
				// location not found
				addr.val('').trigger('blur');
				zip.val('').trigger('blur');
				city.val('').trigger('blur');
				note.val('').trigger('blur');
			}

			if (addr.length) {
				// trigger address change to start the validation
				addr.trigger('change');
			} else {
				// fallback to ZIP code
				zip.trigger('change');
			}
		});

	});

	function vrToggleServiceRequiredFields(delivery) {
		let field   = null;
		let control = null;

		<?php
		foreach ($this->customFields as $cf)
		{
			// make fields required/optional according to the selected service
			if ($cf['required_delivery'] > 0)
			{
				?>
				onInstanceReady(() => {
					if (typeof vrCustomFieldsValidator !== 'undefined') {
						return vrCustomFieldsValidator;
					}

					return false;
				}).then(() => {
					// get field
					field = jQuery('#vrcfinput<?php echo $cf['id']; ?>');

					let flag = delivery;

					if (<?php echo $cf['required_delivery']; ?> == 2) {
						// reverse delivery in case of required pickup
						flag = delivery ? 0 : 1;
					}

					if (flag) {
						// make sure the field is not required
						if (!field.hasClass('required')) {
							// register field as required
							vrCustomFieldsValidator.registerFields(field);
						}
					} else {
						// check if the field is currently required
						if (field.hasClass('required')) {
							// register field as optional
							vrCustomFieldsValidator.unregisterFields(field);
						}
					}
				});
				<?php
			}

			/**
			 * Make sure the field is not always mandatory for delivery rule.
			 *
			 * @since 1.7.4
			 */
			if ($cf['required_delivery'] == 1 || $cf['required'] == 0)
			{
				// toggle visibility of certain fields
				if (VRCustomFields::isAddress($cf) || VRCustomFields::isZip($cf) || VRCustomFields::isCity($cf) || VRCustomFields::isDelivery($cf) || VRCustomFields::isDeliveryNotes($cf))
				{
					?>
					// get field control
					control = jQuery('#vrcfinput<?php echo $cf['id']; ?>').closest('.vr-field-wrapper');

					if (delivery) {
						// show field in case of delivery
						control.show();
					} else {
						// hide field in case of pickup
						control.hide();
					}
					<?php
				}
			}

			/**
			 * Make sure the field is not always mandatory for pickup rule.
			 *
			 * @since 1.8
			 */
			if ($cf['required_delivery'] == 2 || $cf['required'] == 0)
			{
				// toggle visibility of certain fields
				if (VRCustomFields::isPickup($cf))
				{
					?>
					// get field control
					control = jQuery('#vrcfinput<?php echo $cf['id']; ?>').closest('.vr-field-wrapper');

					if (delivery) {
						// hide field in case of delivery
						control.hide();
					} else {
						// show field in case of pickup
						control.show();
					}
					<?php
				}
			}
		}
		?>
	}

</script>
