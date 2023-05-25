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
 * Template file used to to display the search bar.
 * It is possible to choose from here the check-in date
 * (if allowed), the check-in time and the type of service
 * (delivery or pick-up). 
 *
 * @since 1.8
 */

$config = VREFactory::getConfig();

$currency = VREFactory::getCurrency();

// calculate total NET
$total_net = $this->cart->getRealTotalNet($config->getUint('tkusetaxes'));

/**
 * Validate free delivery by using the apposite helper method.
 *
 * @since 1.8.3
 */
if (VikRestaurants::isTakeAwayFreeDeliveryService($this->cart))
{
	$is_free_delivery = true;
}
else
{
	$is_free_delivery = false;
}

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$vik = VREApplication::getInstance();

?>

<div class="vrtk-service-dt-wrapper">
		
	<!-- DATE AND TIME -->

	<div class="vrtkdatetimediv">

		<div class="vrtkdeliverytitlediv">
			<?php
			if ($config->getBool('tkallowdate'))
			{
				// date selection is allowed
				echo JText::_('VRTKDATETIMELEGEND');
			}
			else
			{
				// pre-orders for future days are disabled
				echo JText::_('VRTKONLYTIMELEGEND');
			}
			?>
		</div>
		
		<?php
		if ($config->getBool('tkallowdate'))
		{
			// display datepicker only if date selection is allowed
			JHtml::_('vrehtml.sitescripts.datepicker', '#vrtkcalendar:input', 'takeaway');
			?>
			<div class="vrtkdatetimeinputdiv vrtk-date-box">
				
				<label class="vrtkdatetimeinputlabel" for="vrtkcalendar">
					<?php echo JText::_('VRDATE'); ?>
				</label>
				
				<div class="vrtkdatetimeinput vre-calendar-wrapper">
					<input class="vrtksearchdate vre-calendar" type="text" value="<?php echo $this->args['date']; ?>" id="vrtkcalendar" name="date" size="20" />
				</div>

			</div>
			<?php
		}
		
		if (count($this->times))
		{
			// display times dropdown only in case there is at least a time available
			?>
			<div class="vrtkdatetimeinputdiv vrtk-time-box">
				
				<label class="vrtkdatetimeinputlabel" for="vrtktime">
					<?php echo JText::_('VRTIME'); ?>
				</label>
				
				<div class="vrtkdatetimeselect vre-select-wrapper">
					<?php
					$attrs = array(
						'id'    => 'vrtktime',
						'class' => 'vre-select',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $this->args['hourmin'], $this->times, $attrs);
					?>
				</div>
			
			</div>
			<?php
		}
		else
		{
			// no available times, the restaurant is probably closed or it is out of orders
			?>
			<div class="vrtkdatetimeerrmessdiv">
				<div class="vrtkdatetimenoselectdiv"><?php echo JText::_('VRTKNOTIMEAVERR'); ?></div>
			</div>
			<?php
		}
		?>
	</div>

	<!-- DELIVERY/PICK-UP SERVICE -->

	<div class="vrtkdeliveryservicediv">

		<div class="vrtkdeliverytitlediv">
			<?php echo JText::_('VRTKSERVICELABEL'); ?>
		</div>

		<div class="vrtkdeliveryradiodiv">
			<?php
			// calculate delivery charge
			$delivery_charge = VikRestaurants::getTakeAwayDeliveryServiceAddPrice($total_net);

			if ($delivery_charge > 0)
			{
				if ($is_free_delivery)
				{
					// didplay FREE label only in case of offer
					$label = '(' . JText::_('VRTKDELIVERYFREE') . ')';
				}
				else
				{
					// display charge
					$label = '(+' . $currency->format($delivery_charge) . ')';
				}
			}
			else
			{
				$label = '';
			}

			if ($this->delivery)
			{
				?>
				<span class="vrtkdeliverysp">
					<input type="radio" name="delivery" value="1" id="vrtkdelivery1" onChange="vrServiceChanged(1);" <?php echo $this->args['delivery'] == 1 ? 'checked="checked"' : ''; ?> />

					<label for="vrtkdelivery1"><?php echo trim(JText::sprintf('VRTKDELIVERYLABEL', $label)); ?></label>
				</span>
				<?php
			}

			// calculate pickup charge
			$pickup_charge = VikRestaurants::getTakeAwayPickupAddPrice($total_net);

			if ($pickup_charge != 0)
			{
				$label = '(' . ($pickup_charge > 0 ? '+' : '') . $currency->format($pickup_charge) . ')';
			}
			else
			{
				$label = '';
			}

			if ($this->pickup)
			{
				?>
				<span class="vrtkpickupsp">
					<input type="radio" name="delivery" value="0" id="vrtkdelivery0" onChange="vrServiceChanged(0);" <?php echo $this->args['delivery'] == 0 ? 'checked="checked"' : ''; ?> />
					
					<label for="vrtkdelivery0"><?php echo trim(JText::sprintf('VRTKPICKUPLABEL', $label)); ?></label>
				</span>
				<?php
			}
			?>
		</div>

	</div>

</div>

<?php
JText::script('VRTKDELIVERYADDRNOTFULL');
JText::script('VRTKDELIVERYADDRNOTFOUND');
JText::script('VRTKDELIVERYMINCOST');
JText::script('VRTKDELIVERYSURCHARGE');
JText::script('VRTKADDITEMERR2');
?>

<script>

	var TK_DELIVERY_COST      = <?php echo $is_free_delivery ? 0 : $delivery_charge; ?>;
	var TK_FREE_DELIVERY      = <?php echo $is_free_delivery ? 1 : 0; ?>;
	var TK_DELIVERY_SURCHARGE = 0;
	var TK_PICKUP_COST        = <?php echo $pickup_charge; ?>;

	jQuery(document).ready(function() {

		// refresh page in case the check-in date changes
		jQuery('#vrtkcalendar').on('change', function() {
			jQuery('#vrtkconfirmform').submit();
		});

		jQuery('#vrtktime').on('change', function() {
			<?php
			if ($this->refreshTimeNeeded)
			{
				// refresh page after changing the time because the
				// system might support different services/menus
				?>
				jQuery('#vrtkconfirmform').submit();
				<?php
			}
			?>
		});

		<?php
		if ($this->refreshServiceNeeded)
		{
			// refresh page after changing the service because the
			// system might support different deals
			?>
			jQuery('input[name="delivery"]').on('change', function() {
				jQuery('#vrtkconfirmform').submit();
			});
			<?php
		}
		?>

		// wait until the validator is ready
		onInstanceReady(() => {
			if (typeof vrCustomFieldsValidator !== 'undefined') {
				return vrCustomFieldsValidator;
			}

			return false;
		}).then(() => {
			// add address validation callback to form validator
			vrCustomFieldsValidator.addCallback(function(form) {
				// get address and ZIP code fields
				var fields = jQuery('.vrtk-address-field, .vrtk-zip-field, .vrtk-city-field');

				if (!DELIVERY_ADDRESS_STATUS) {
					// set fields as invalid
					form.setInvalid(fields);
				} else {
					// unset fields as invalid
					form.unsetInvalid(fields);
				}

				// valid in case the specified address is accepted or
				// in case there are no fields to use for the validation
				return DELIVERY_ADDRESS_STATUS || fields.length == 0;
			});
		});

	});

	function vrIsDelivery() {
		// check whether the customer selected the delivery service
		return jQuery('form#vrtkconfirmform input[name="delivery"]:checked').val() == 1;
	}

	function vrServiceChanged(status) {
		if (typeof status === 'undefined') {
			// get selected delivery
			status = vrIsDelivery();
		}

		if (status == 1) {
			calculateServiceCharge(TK_DELIVERY_COST + TK_DELIVERY_SURCHARGE);

			if (DELIVERY_ADDRESS_STATUS) {
				// trigger address change for validation
				jQuery('.vrtk-address-field').trigger('change');
			}
		} else {
			calculateServiceCharge(TK_PICKUP_COST);

			// Always valid for pickup service.
			// Mainly used to hide messages and delivery costs.
			vrIsAddressAccepted(
				LAST_COORDS_FOUND,
				LAST_COMPONENTS_FOUND
			);
		}

		// toggle custom fields according to the selected service
		vrToggleServiceRequiredFields(status);
	}

	// ADDRESS VALIDATION

	var DELIVERY_ADDRESS_STATUS = <?php echo (int) $this->args['delivery']; ?> ? 0 : 1;

	var LAST_COORDS_FOUND     = {lat: null, lng: null};
	var LAST_COMPONENTS_FOUND = {};

	// flag used to check whether the Google API Key
	// was badly configured
	var GOOGLE_AUTH_ERROR = false;

	jQuery(window).on('google.autherror', function() {
		// google hasn't been properly configured
		GOOGLE_AUTH_ERROR = true;

		// reset components found
		LAST_COMPONENTS_FOUND = {};

		// get ZIP value
		var zipField = jQuery('.vrtk-zip-field');

		if (zipField.length) {
			LAST_COMPONENTS_FOUND.zip = zipField.val();
		}

		// get CITY value
		var cityField = jQuery('.vrtk-city-field');

		if (cityField.length) {
			LAST_COMPONENTS_FOUND.city = cityField.val();
		}

		// make sure the fields were filled
		if (LAST_COMPONENTS_FOUND.zip || LAST_COMPONENTS_FOUND.city) {
			console.log('validate against', LAST_COMPONENTS_FOUND);

			// validate address according to the specified ZIP/CITY
			vrIsAddressAccepted(LAST_COORDS_FOUND, LAST_COMPONENTS_FOUND);
		}
	});

	jQuery(document).ready(function() {

		// toggle service change to update delivery/pickup charge
		vrServiceChanged();

		jQuery('.vrtk-address-field').on('change', function() {
			vrValidateAddress(vrGetAddressString());
		});

		// Used to avoid the "double click" issue.
		// The issue comes when you click the "continue" button
		// before the "change" event is triggered.
		// jQuery('.vrtk-address-field').on(
		// 	'keyup', 
		// 	__debounce(function(){
		// 		vrValidateAddress(vrGetAddressString());
		// 	}, 500)
		// );

		jQuery('.vrtk-address-field').prop('autocomplete', 'off');

		if (jQuery('.vrtk-address-field').length && jQuery('.vrtk-address-field').val().length) {
			jQuery('.vrtk-address-field').trigger('change');
		}

		// zip

		jQuery('.vrtk-zip-field').on('change', function() {
			// re-validate address only in case the post code changed
			if (LAST_COMPONENTS_FOUND.zip != jQuery(this).val()) {
				// overwrite post code
				LAST_COMPONENTS_FOUND.zip = jQuery(this).val();

				if (jQuery('.vrtk-address-field').length == 0 || GOOGLE_AUTH_ERROR) {
					vrIsAddressAccepted(LAST_COORDS_FOUND, LAST_COMPONENTS_FOUND);
				} else {
					vrValidateAddress(vrGetAddressString());
				}
			}
		});

		if (jQuery('.vrtk-zip-field').length && jQuery('.vrtk-zip-field').val().length) {
			jQuery('.vrtk-zip-field').trigger('change');
		}

		// city

		jQuery('.vrtk-city-field').on('change', function() {
			// re-validate address only in case the city changed
			if (LAST_COMPONENTS_FOUND.city != jQuery(this).val()) {
				// overwrite city
				LAST_COMPONENTS_FOUND.city = jQuery(this).val();

				if (jQuery('.vrtk-address-field').length == 0 || GOOGLE_AUTH_ERROR) {
					vrIsAddressAccepted(LAST_COORDS_FOUND, LAST_COMPONENTS_FOUND);
				} else {
					vrValidateAddress(vrGetAddressString());
				}
			}
		});

		if (jQuery('.vrtk-city-field').length && jQuery('.vrtk-city-field').val().length) {
			jQuery('.vrtk-city-field').trigger('change');
		}

	});

	function vrGetAddressString() {

		var parts = [];

		jQuery('.vrtk-address-field, .vrtk-zip-field, .vrtk-city-field').each(function() {

			var val = jQuery(this).val();

			if (val.length) {
				parts.push(val);
			}

		});

		return parts.join(' ');
	}

	function vrSetAddressResponse(text, error) {
		// get address response box
		var addr = jQuery('.vrtk-address-response');

		if (error) {
			addr.addClass('fail');
		} else {
			addr.removeClass('fail');
		}

		if (text) {
			// set error
			addr.html(text).show();
		} else {
			// clear error
			addr.hide();
		}
	}

	function vrIsDeliveryMap() {
		return typeof VRTK_ADDR_MARKER !== 'undefined';
	}

	function vrValidateAddress(address) {
		// unset address error
		vrSetAddressResponse();

		DELIVERY_ADDRESS_STATUS = 0;
		TK_DELIVERY_SURCHARGE   = 0;

		// force service changed
		vrServiceChanged();
		
		if (vrIsDeliveryMap() && VRTK_ADDR_MARKER !== null) {
			// unset marker from map module
			VRTK_ADDR_MARKER.setMap(null);
		}

		if (address.length == 0) {
			return false;
		}

		var geocoder = new google.maps.Geocoder();

		var coord = null;

		geocoder.geocode({'address': address}, function(results, status) {
			if (status == 'OK') {
				coord = {
					lat: results[0].geometry.location.lat(),
					lng: results[0].geometry.location.lng(),
				};

				// extract data from best result
				var components = VikGeo.extractDataFromPlace(results[0]);
				// include full address
				components.fullAddress = results[0].formatted_address;

				if (!components.street.name && !components.street.number) {
					// set address error
					vrSetAddressResponse(Joomla.JText._('VRTKDELIVERYADDRNOTFULL'), true);
					return false;
				}

				if (vrIsDeliveryMap()) {
					if (VRTK_ADDR_MARKER) {
						// update position of existing marker
						VRTK_ADDR_MARKER.setPosition(coord);
					} else {
						// create marker from scratch
						VRTK_ADDR_MARKER = new google.maps.Marker({
							position: coord,
						});
					}

					VRTK_ADDR_MARKER.setAnimation(google.maps.Animation.DROP);
					VRTK_ADDR_MARKER.setMap(VRTK_MAP);

					VRTK_MAP.setCenter(VRTK_ADDR_MARKER.position);
				}

				// VALIDATION

				vrIsAddressAccepted(coord, components);
			} else {
				<?php
				/**
				 * Raise an error message as it wasn't possible
				 * to find the specified address.
				 *
				 * @since 1.7.4
				 */
				?>
				vrSetAddressResponse(Joomla.JText._('VRTKDELIVERYADDRNOTFOUND'), true);
				return false;
			}
		});
	}

	function vrIsAddressAccepted(coord, components) {

		DELIVERY_ADDRESS_STATUS = 0;

		LAST_COMPONENTS_FOUND = components;
		LAST_COORDS_FOUND     = coord;

		// unset address error
		vrSetAddressResponse();
			
		if (!vrIsDelivery()) {
			// don't need to validate the address
			DELIVERY_ADDRESS_STATUS = 1;
			return;
		}

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=get_location_delivery_info&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				lat:     coord.lat,
				lng:     coord.lng,
				zip:     components.zip,
				city:    components.city,
				address: components,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				if (obj.status == 1) {

					/**
					 * Compare minimum cost with grand total
					 * without discounts.
					 *
					 * @since 1.8
					 */
					if (obj.area.minCost > TK_BASE_TOTAL) {

						// set min delivery cost error
						vrSetAddressResponse(Joomla.JText._('VRTKDELIVERYMINCOST').replace('%s', obj.area.minCostLabel), true);

					} else {
						if (!TK_FREE_DELIVERY) {
							TK_DELIVERY_SURCHARGE = obj.area.charge;

							if (TK_DELIVERY_SURCHARGE != 0) {
								// set delivery surcharge notice
								vrSetAddressResponse(Joomla.JText._('VRTKDELIVERYSURCHARGE').replace('%s', obj.area.chargeLabel));
							}

							// force service changed
							vrServiceChanged();
						}

						DELIVERY_ADDRESS_STATUS = 1;

					}

				} else {
					// set address error
					vrSetAddressResponse(obj.error, true);
				}
			},
			function(error) {
				if (!error.responseText || error.responseText.length > 1024) {
					// use default generic error
					error.responseText = Joomla.JText._('VRTKADDITEMERR2');
				}

				alert(error.responseText);
			}
		);
	}

</script>
