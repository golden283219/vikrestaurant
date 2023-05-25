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

JHtml::_('vrehtml.assets.intltel', '[name="billing_phone"]');

$customer = $this->customer;

$vik = VREApplication::getInstance();

?>

<style>

	.select2-container.vr-mc-users-select,
	#user-create-btn {
		margin-bottom: 5px;
	}

</style>

<div class="row-fluid">
	
	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('VRMANAGECUSTOMERTITLE2'), 'form-horizontal'); ?>
		
			<!-- BILLING NAME - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER2')); ?>
				<input class="required" type="text" name="billing_name" value="<?php echo $this->escape($customer->billing_name); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING MAIL - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER3')); ?>
				<input class="required mail-field" type="email" name="billing_mail" value="<?php echo $customer->billing_mail; ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING PHONE - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER4')); ?>
				<input class="" type="tel" name="billing_phone" value="<?php echo $customer->billing_phone; ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING COUNTRY - Select -->
			<?php
			$options = JHtml::_('vrehtml.admin.countries');
			array_unshift(
				$options,
				JHtml::_('select.option', '', '')
			);
			
			echo $vik->openControl(JText::_('VRMANAGECUSTOMER5')); ?>
				<select name="country_code" id="vr-countries-sel" class="vr-countries-sel">
					<?php echo JHtml::_('select.options', $options, 'value', 'text', $customer->country_code); ?>
				</select>
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING STATE - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER6')); ?>
				<input type="text" name="billing_state" value="<?php echo $this->escape($customer->billing_state); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING CITY - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER7')); ?>
				<input type="text" name="billing_city" value="<?php echo $this->escape($customer->billing_city); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING ADDRESS - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER8')); ?>
				<input type="text" name="billing_address" value="<?php echo $this->escape($customer->billing_address); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING ADDRESS 2 - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER19')); ?>
				<input type="text" name="billing_address_2" value="<?php echo $this->escape($customer->billing_address_2); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING ZIP CODE - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER9')); ?>
				<input type="text" name="billing_zip" value="<?php echo $this->escape($customer->billing_zip); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>

			<!-- SAME DELIVERY AS BILLING - Checkbox -->
			<?php
			if ($customer->id == 0)
			{
				echo $vik->openControl(''); ?>
					<input type="checkbox" value="1" name="delivery_as_billing" id="delivery_as_billing" checked="checked" />
					<label for="delivery_as_billing"><?php echo JText::_('VRMANAGECUSTOMER22'); ?></label>
				<?php echo $vik->closeControl();
			}
			?>
			
			<!-- BILLING COMPANY - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER10')); ?>
				<input type="text" name="company" value="<?php echo $this->escape($customer->company); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING VAT NUMBER - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER11')); ?>
				<input type="text" name="vatnum" value="<?php echo $this->escape($customer->vatnum); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- BILLING SSN - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER20')); ?>
				<input type="text" name="ssn" value="<?php echo $this->escape($customer->ssn); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
		<?php echo $vik->closeFieldset(); ?>
	</div>

	<div class="span6">

		<div class="row-fluid">

			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRMANAGECUSTOMERTITLE1')); ?>
			
					<!-- JOOMLA USER - Dropdown -->
					<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER12')); ?>

						<input type="hidden" name="jid" class="vr-mc-users-select" id="vr-managecust-users" value="<?php echo $customer->jid ? $customer->jid : ''; ?>" />
						<button type="button" class="btn" id="user-create-btn"><?php echo JText::_('VRMANAGECUSTOMER16'); ?></button>
						<input type="hidden" name="create_new_user" value="0" />

						<a href="javascript:void(0);" id="avatar-handle">
							<?php
							if (empty($customer->image))
							{
								?>
								<img src="<?php echo VREASSETS_URI . 'css/images/default-profile.png'; ?>" class="vr-customer-image" />
								<?php
							}
							else
							{
								?>
								<img src="<?php echo VRECUSTOMERS_AVATAR_URI . $customer->image; ?>" class="vr-customer-image" />
								<?php
							}
							?>
						</a>

						<?php
						/**
						 * Added the possibility to select an image from the back-end.
						 *
						 * Render modal outside the hidden div because otherwise
						 * it wouldn't be displayed.
						 *
						 * @since 1.8
						 */
						echo JHtml::_('vrehtml.mediamanager.modal');

						?>
						<div style="display:none;">
							<?php echo JHtml::_('vrehtml.mediamanager.field', 'image', $customer->image, 'vr-customer-image', array('path' => VRECUSTOMERS_AVATAR)); ?>
						</div>

					<?php echo $vik->closeControl(); ?>
					
					<!-- JOOMLA USER NAME - Text -->
					<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR11'), 'vr-account-row', array('style' => 'display:none;')); ?>
						<input class="" type="text" name="username" value="<?php echo $customer->username; ?>" size="40" />
					<?php echo $vik->closeControl(); ?>
					
					<!-- JOOMLA USER MAIL - Text -->
					<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER3'), 'vr-account-row', array('style' => 'display:none;')); ?>
						<input class="mail-field" type="email" name="usermail" value="<?php echo $customer->usermail; ?>" size="40" />
					<?php echo $vik->closeControl(); ?>
					
					<!-- JOOMLA USER GENERATE PWD - Button -->
					<?php echo $vik->openControl('', 'vr-account-row', array('style' => 'display:none;')); ?>
						<button type="button" id="vr-genpwd-button" class="btn"><?php echo JText::_('VRMANAGECUSTOMER17'); ?></button>
					<?php echo $vik->closeControl(); ?>
					
					<!-- JOOMLA USER PWD - Password -->
					<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER13'), 'vr-account-row', array('style' => 'display:none;')); ?>
						<input class="vr-genpwd-input" type="password" name="password" size="40" />
					<?php echo $vik->closeControl(); ?>
					
					<!-- JOOMLA USER CONFIRM PWD - Password -->
					<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMER14'), 'vr-account-row', array('style' => 'display:none;')); ?>
						<input class="vr-genpwd-input" type="password" name="confpassword" size="40" />
					<?php echo $vik->closeControl(); ?>
					
				<?php echo $vik->closeFieldset(); ?>
			</div>

		</div>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewCustomer". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			?>
			<div class="row-fluid">
				<div class="span12">
					<?php
					echo $vik->openFieldset(JText::_('VRE_CUSTOM_FIELDSET'));
					echo $custom;
					echo $vik->closeFieldset();
					?>
				</div>
			</div>
			<?php
		}
		?>

		<div class="row-fluid">

			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRMANAGECUSTOMERTITLE4')); ?>
					<div class="control-group">
						<textarea name="notes" class="full-width" style="height: 200px;resize:vertical;"><?php echo $customer->notes; ?></textarea>
					</div>
				<?php echo $vik->closeFieldset(); ?>
			</div>

		</div>

	</div>

	<input type="hidden" name="billing_lat" value="" />
	<input type="hidden" name="billing_lng" value="" />

</div>

<?php
JText::script('VRE_FILTER_SELECT_COUNTRY');
JText::script('VRMANAGECUSTOMER15');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		// auto-fill username when billing name changes its value
		jQuery('input[name="billing_name"]').on('change', function() {
			if (jQuery('input[name="username"]').val().length == 0) {
				jQuery('input[name="username"]').val(jQuery(this).val());
			}
		});
		// auto-fill billing when username changes its value
		jQuery('input[name="username"]').on('change', function() {
			if (jQuery('input[name="billing_name"]').val().length == 0) {
				jQuery('input[name="billing_name"]').val(jQuery(this).val());
			}
		});

		// auto-fill user mail when billing mail changes its value
		jQuery('input[name="billing_mail"]').on('change', function() {
			if (jQuery('input[name="usermail"]').val().length == 0) {
				jQuery('input[name="usermail"]').val(jQuery(this).val());
			}
		});
		// auto-fill billing mail when user mail changes its value
		jQuery('input[name="usermail"]').on('change', function() {
			if (jQuery('input[name="billing_mail"]').val().length == 0) {
				jQuery('input[name="billing_mail"]').val(jQuery(this).val());
			}
		});

		jQuery('#vr-genpwd-button').on('click', function(){
			var pwd = generatePassword(8);

			jQuery('.vr-genpwd-input').attr('type', 'text');
			jQuery('.vr-genpwd-input').val(pwd);
		});
		
		jQuery('.vr-countries-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_COUNTRY'),
			allowClear: true,
			width: 300
		});

		// use aync callback to avoid the strange behavior that occurred on WordPress
		instantCallbackAsync().then(() => {
			jQuery("#vr-managecust-users").select2({
				placeholder: Joomla.JText._('VRMANAGECUSTOMER15'),
				allowClear: true,
				width: 300,
				minimumInputLength: 2,
				ajax: {
					url: 'index.php?option=com_vikrestaurants&task=search_jusers&tmpl=component&id=<?php echo $customer->jid; ?>',
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
								return {
									text: item.text,
									id: item.id,
									disabled: (item.disabled == 1 ? true : false),
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
						callback({name: '<?php echo (empty($this->juser->name) ? '' : addslashes($this->juser->name)); ?>'});
					}
				},
				formatSelection: function(data) {
					if (jQuery.isEmptyObject(data.name)) {
						// display data retured from ajax parsing
						return data.text;
					}
					// display pre-selected value
					return data.name;
				},
				dropdownCssClass: 'bigdrop',
			});
		});

		jQuery('#vr-countries-sel').on('change', function() {
			var phone = jQuery('input[name="billing_phone"]');

			if (phone.val().length == 0) {
				phone.intlTelInput('setCountry', jQuery(this).val());
			}
		});

		// Observe customerValidator instance and wait until it is ready.
		// Workaround needed to avoid the issue that occurs on WordPress.
		onInstanceReady(() => {
			if (typeof customerValidator === 'undefined') {
				return false;
			}

			return customerValidator;
		}).then((customerValidator) => {
			// register callback for password fields
			customerValidator.addCallback(vrValidatePassword);
		});

		jQuery('#user-create-btn').on('click', function() {
			userSelectValueChanged(this);
		});

		<?php
		if (!empty($customer->username))
		{
			?>
			// auto-toggle user fields
			userSelectValueChanged(jQuery('#user-create-btn'));
			<?php
		}
		?>

		// display media manager modal when the avatar is clicked
		jQuery('#avatar-handle').on('click', function() {
			<?php
			if (!$this->isTmpl)
			{
				// show media manager modal by using an alternative path
				?>
				jQuery('#vr-customer-image').mediamanager('show', '<?php echo base64_encode(VRECUSTOMERS_AVATAR); ?>');
				<?php
			}
			else
			{
				// fade image preview in order to prevent a modal-in-modal effect
				?>
				jQuery('#vr-customer-image').mediamanager('preview', '<?php echo addslashes(VRECUSTOMERS_AVATAR_URI); ?>');
				<?php
			}
			?>
		});

		jQuery('#vr-customer-image').on('change', function() {
			// get selected image
			var image = jQuery(this).mediamanager('val');
			var url   = '';

			if (image) {
				// use specified image
				url = '<?php echo VRECUSTOMERS_AVATAR_URI; ?>' + image;
			} else {
				// use default image instead
				url = '<?php echo VREASSETS_URI . 'css/images/default-profile.png'; ?>';
			}

			// preload image
			var tile = new Image();
			tile.src = url;
			tile.onload = function() {
				// change image only when loaded
				jQuery('.vr-customer-image').attr('src', this.src);
			};
		});

	});

	function userSelectValueChanged(btn) {
		if (jQuery(btn).hasClass('active')) {
			jQuery(btn).removeClass('active');

			jQuery('#vr-managecust-users').prop('disabled', false);

			jQuery('.vr-account-row').hide();
			
			customerValidator.unregisterFields('.vr-account-row input');

			jQuery('input[name="create_new_user"]').val(0);
		} else {
			jQuery(btn).addClass('active');

			jQuery('#vr-managecust-users').prop('disabled', true);

			customerValidator.registerFields('.vr-account-row input');
			
			jQuery('.vr-account-row').show();

			jQuery('input[name="create_new_user"]').val(1);
		}
	}
	
	function generatePassword(length) {
		var charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789[]{}()#!.-_";
		var pwd = "";

		for (var i = 0; i < length; i++) {
			pwd += charset.charAt(Math.floor(Math.random() * charset.length));
		}

		return pwd;
	}

	function vrValidatePassword() {
		var pass = [];
		pass[0] = jQuery('input[name="password"]');
		pass[1] = jQuery('input[name="confpassword"]');

		if (pass[0].hasClass('required') && (pass[0].val() != pass[1].val() || pass[0].val().length == 0)) {
			customerValidator.setInvalid(pass[0]);
			customerValidator.setInvalid(pass[1]);

			return false;
		}

		customerValidator.unsetInvalid(pass[0]);
		customerValidator.unsetInvalid(pass[1]);

		return true;
	}

	function isGoogleReady() {
		if (typeof google === 'undefined') {
			return false;
		}

		return google;
	}

	// auto-complete billing address

	jQuery(document).ready(function() {
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
						jQuery('input[name="billing_address"]')[0], {}
					);

					googleAddress.addListener('place_changed', function() {
						var place = googleAddress.getPlace();

						// extract data from place
						data = VikGeo.extractDataFromPlace(place);

						// fetch country
						if (data.country) {
							jQuery('select[name="country_code"]').select2('val', data.country).trigger('change');
						}

						// fetch address details
						jQuery('input[name="billing_state"]').val(data.state);
						jQuery('input[name="billing_city"]').val(data.city);
						jQuery('input[name="billing_zip"]').val(data.zip);
						jQuery('input[name="billing_address"]').val(data.address);

						// fill latitude and longitude
						jQuery('input[name="billing_lat"]').val(data.lat);
						jQuery('input[name="billing_lng"]').val(data.lng);
					});

					jQuery(window).on('google.autherror google.apidisabled.places', function() {
						// disable autocomplete on failure
						VikMapsFailure.disableAutocomplete(jQuery('input[name="billing_address"]')[0], googleAddress);
					});

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
					<?php
				}
				?>
			}
		});

	});

</script>
