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

$vik = VREApplication::getInstance();

?>

<div class="inspector-form" id="inspector-delivery-form">

	<!-- DELIVERY TYPE - Select -->
	<?php
	$options = array(		
		JHtml::_('select.option', 1, 'VRE_DELIVERY_LOCATION_TYPE_HOME'),
		JHtml::_('select.option', 2, 'VRE_DELIVERY_LOCATION_TYPE_OFFICE'),
		JHtml::_('select.option', 0, 'VRE_DELIVERY_LOCATION_TYPE_OTHER'),
	);
	
	echo $vik->openControl(JText::_('VRMANAGETKAREA2')); ?>
		<select id="delivery_type" class="field">
			<?php echo JHtml::_('select.options', $options, 'value', 'text', 1, true); ?>
		</select>
	<?php echo $vik->closeControl(); ?>

	<!-- DELIVERY COUNTRY - Select -->
	<?php
	$options = JHtml::_('vrehtml.admin.countries');
	array_unshift(
		$options,
		JHtml::_('select.option', '', '')
	);
	
	echo $vik->openControl(JText::_('VRMANAGECUSTOMER5')); ?>
		<select id="delivery_country" class="vr-countries-sel field">
			<?php echo JHtml::_('select.options', $options, 'value', 'text', VRCustomFields::getDefaultCountryCode()); ?>
		</select>
	<?php echo $vik->closeControl(); ?>
	
	<!-- DELIVERY STATE - Text -->
	<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER6')); ?>
		<input type="text" id="delivery_state" value="" class="field" size="40" />
	<?php echo $vik->closeControl(); ?>
	
	<!-- DELIVERY CITY - Text -->
	<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER7')); ?>
		<input type="text" id="delivery_city" value="" class="field" size="40" />
	<?php echo $vik->closeControl(); ?>
	
	<!-- DELIVERY ADDRESS - Text -->
	<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER8')); ?>
		<input type="text" id="delivery_address" value="" class="field required" size="40" />
	<?php echo $vik->closeControl(); ?>
	
	<!-- DELIVERY ADDRESS 2 - Text -->
	<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER19')); ?>
		<input type="text" id="delivery_address_2" value="" class="field" size="40" />
	<?php echo $vik->closeControl(); ?>
	
	<!-- DELIVERY ZIP CODE - Text -->
	<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER9')); ?>
		<input type="text" id="delivery_zip" value="" class="field required" size="40" />
	<?php echo $vik->closeControl(); ?>

	<!-- DELIVERY NOTES - Text -->
	<?php echo $vik->openControl(JText::_('VRMANAGERESCODE5')); ?>
		<textarea id="delivery_note" class="field"></textarea>
	<?php echo $vik->closeControl(); ?>

	<input type="hidden" id="delivery_lat" class="field" value="" />
	<input type="hidden" id="delivery_lng" class="field" value="" />

	<input type="hidden" id="delivery_id" class="field" value="" />

</div>

<script>

	var locationValidator = new VikFormValidator('#inspector-delivery-form');

	jQuery(document).ready(function() {

		jQuery('#delivery_type').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		// auto-complete delivery address
		// do not go ahead until "google" object is ready for usage
		onInstanceReady(isGoogleReady).then((google) => {
			if (typeof google.maps.places !== 'undefined') {

				<?php
				if (VikRestaurants::isGoogleMapsApiEnabled('places'))
				{
					// include JavaScript code to support the addresses autocompletion
					// only in case the Places API is enabled in the configuration

					?>
					// use Google Autocomplete feature
					var googleAddress = new google.maps.places.Autocomplete(
						jQuery('#delivery_address')[0], {}
					);

					googleAddress.addListener('place_changed', function() {
						var place = googleAddress.getPlace();

						// extract data from place
						data = VikGeo.extractDataFromPlace(place);

						// keep current ID and Notes to avoid replacing them
						data.id   = jQuery('#delivery_id').val();
						data.note = jQuery('#delivery_note').val();

						// fill form with data
						fillDeliveryLocationForm(data);
					});

					jQuery(window).on('google.autherror google.apidisabled.places', function() {
						// disable autocomplete on failure
						VikMapsFailure.disableAutocomplete(jQuery('#delivery_address')[0], googleAddress);
					});

					// Retrieve user coordinates only when the inspector opens.
					// This to avoid asking for the position twice.
					jQuery('#delivery-location-inspector').on('inspector.show', function() {
						VikGeo.getCurrentPosition().then(function(coord) {
							// coordinates retrieved, set up google bounds
							var circle = new google.maps.Circle({
								center: coord,
								radius: 100,
							});

			      			googleAddress.setBounds(circle.getBounds());
						}).catch(function(error) {
							// unable to obtain current position, show error
							console.error(error);
						});
					});
					<?php
				}
				?>
			}
		});

	});

	function fillDeliveryLocationForm(data) {
		if (data.country) {
			// update country
			jQuery('#delivery_country').select2('val', data.country);
		}

		if (data.type) {
			// update type
			jQuery('#delivery_type').select2('val', data.type);
		}

		if (!data.id) {
			data.id = 0;
		}

		jQuery('#inspector-delivery-form')
			.find('input,textarea')
				.filter('[id^="delivery_"]')
					.each(function() {
						var key = jQuery(this).attr('id').replace(/^delivery_/, '');

						if (!data.hasOwnProperty(key)) {
							data[key] = '';
						}

						jQuery(this).val(data[key]);

						locationValidator.unsetInvalid(this);
					});
	}

	function getDeliveryData() {
		var data = {};

		jQuery('#inspector-delivery-form')
			.find('input,select,textarea')
				.filter('[id^="delivery_"]')
					.each(function() {
						var name  = jQuery(this).attr('id').replace(/^delivery_/, '');
						var value = jQuery(this).val();

						data[name] = value;						
					});

		return data;
	}

</script>
