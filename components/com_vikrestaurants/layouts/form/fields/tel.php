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

$label = $displayData['label'];
$value = $displayData['value'];
$cf    = $displayData['field'];
$user  = $displayData['user'];

// check if the dial code selection is allowed
$is_dial = VREFactory::getConfig()->getBool('phoneprefix');

$config = array(
	// validate phone number field to make sure
	// the specified value is a valid phone
	'validator' => 'vrCustomFieldsValidator',
	// custom data to be passed when initializing
	// international tel input
	'data' => array(
		// display flags dropdown according to the
		// global configuration (Show Prefix Selection)
		'allowDropdown' => $is_dial,
	),
);

// render input using intltel
JHtml::_('vrehtml.assets.intltel', '#vrcfinput' . $cf['id'], $config);

// fetch wrapper class suffix
$wrap_sfx = $is_dial ? ' phone-field' : ' phone-field-plain';

$class = '';
$class .= strlen($value) ? ' has-value' : '';
$class .= $cf['required'] ? ' required' : '';
?>

<div>

	<div class="cf-value<?php echo $wrap_sfx; ?>">

		<?php
		/**
		 * Added a hidden label before the input to fix the auto-complete
		 * bug on Safari, which always expects to have the inputs displayed
		 * after their labels.
		 *
		 * @since 1.8.2
		 */
		?>
		<label for="vrcfinput<?php echo $cf['id']; ?>" style="display: none;"><?php echo $label; ?></label>
		
		<input
			type="tel"
			name="vrcf<?php echo $cf['id']; ?>"
			id="vrcfinput<?php echo $cf['id']; ?>"
			value="<?php echo $value; ?>"
			class="vrinput<?php echo $class; ?>"
			size="40"
		/>

		<span class="cf-highlight"><!-- input highlight --></span>

		<span class="cf-bar"><!-- input bar --></span>

		<span class="cf-label">
			
			<?php if ($cf['required']) { ?>

				<span class="vrrequired"><sup>*</sup></span>

			<?php } ?>

			<span id="vrcf<?php echo $cf['id']; ?>"><?php echo $label; ?></span>

		</span>

	</div>

	<input type="hidden" name="vrcf<?php echo $cf['id']; ?>_dialcode" value="" />
	<input type="hidden" name="vrcf<?php echo $cf['id']; ?>_country" value="" />
	
</div>


<script>
	
	jQuery(document).ready(function() {
		// save "country code" and "dial code" every time the phone number changes
		jQuery('#vrcfinput<?php echo $cf['id']; ?>').on('change countrychange', function() {
			var country = jQuery(this).intlTelInput('getSelectedCountryData');

			if (!country) {
				return false;
			}

			if (country.iso2) {
				jQuery('input[name="vrcf<?php echo $cf['id']; ?>_country"]').val(country.iso2.toUpperCase());
			}

			if (country.dialCode) {
				var dial = '+' + country.dialCode.toString().replace(/^\+/);

				if (country.areaCodes) {
					dial += ' ' + country.areaCodes[0];
				}

				jQuery('input[name="vrcf<?php echo $cf['id']; ?>_dialcode"]').val(dial);
			}
		}).trigger('change');
	});

</script>
