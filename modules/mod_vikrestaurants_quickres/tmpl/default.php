<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_quickres
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Use the module ID to support multiple instances.
 *
 * @since 1.4
 */
$module_id = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

/**
 * Use VikRestaurants scripts to handle default search events.
 *
 * @since 1.4
 */
JHtml::_('vrehtml.sitescripts.updateshifts', $restaurant = 1);
JHtml::_('vrehtml.sitescripts.datepicker', '#vrqr-calendar-mod' . $module_id . ':input');

// get module params and settings

$header = array(
	'image'    => $params->get('head_image'),
	'title'    => $params->get('head_title'),
	'subtitle' => $params->get('head_subtitle'),
);

$rooms_choosable = (bool) $params->get('chooseroom');

$order_summary_text = $params->get('order_summary');

$auto_redirect = $params->get('auto_redirect');

$config = VREFactory::getConfig();

/**
 * Find first available time.
 * The $date argument is passed by reference and it will
 * be modified by the method.
 *
 * @since 1.3
 */
$date    = null;
$hourmin = VikRestaurants::getClosestTime($date, $next = true);

if ($hourmin === false)
{
	$hourmin = '0:0';
}

if (is_integer($date))
{
	$date = date($config->get('dateformat'), $date);
}

// prepare Item ID for query string
$itemid = $itemid ? '&Itemid=' . $itemid : '';

$vik = VREApplication::getInstance();

?>

<div class="vr-quick-reservation-mod">
	
	<div class="vr-quickres-header">

		<?php
		if (strlen($header['image']))
		{
			?>
			<div class="vr-quickres-head-image">
				<img src="<?php echo $header['image']; ?>"/>
			</div>
			<?php
		}
		?>
		
		<?php
		if (strlen($header['title']) || strlen($header['subtitle']))
		{
			?>

			<div class="vr-quickres-head-content">

				<?php
				if (strlen($header['title']))
				{
					?>
					<h2><?php echo $params->get('head_title'); ?></h2>
					<?php
				}
				
				if(strlen($header['subtitle']))
				{
					?>
					<h3><?php echo $params->get('head_subtitle'); ?></h3>
					<?php
				}
				?>

			</div>

			<?php
		}
		?>

	</div>
	
	<div class="vr-quickres-content">
	
		<div class="vr-quickres-step-unactive-field" id="vrqr-nostep1-<?php echo $module_id; ?>" style="display: none;" data-step="1">
				
		</div>
	
		<!-- DATE - TIME - PEOPLE SELECTION -->

		<div class="vr-quickres-step" id="vrqr-step1-<?php echo $module_id; ?>">
			
			<div class="vr-quickres-step-field quickres-calendar">
				<span class="calendar-icon-append">&nbsp;</span>
				<input type="text" id="vrqr-calendar-mod<?php echo $module_id; ?>" class="vr-quickres-calendar" value="<?php echo $date; ?>" />
			</div>
			
			<div class="vr-quickres-step-field vre-select-wrapper half-size">
				<?php
				// get available times
				$times = JHtml::_('vikrestaurants.times', $restaurant = 1, $date);

				$attrs = array(
					'id'    => 'vrqr-hour-mod' . $module_id,
					'class' => 'vre-select',
				);

				// display times dropdown
				echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $hourmin, $times, $attrs);
				?>
			</div>
			
			<div class="vr-quickres-step-field vre-select-wrapper half-size float-right">
				<?php
				// get people options
				$options = JHtml::_('vikrestaurants.people');

				$attrs = array(
					'id'    => 'vrqr-people-mod' . $module_id,
					'class' => 'vre-select',
				);

				// display times dropdown
				echo JHtml::_('vrehtml.site.peopleselect', 'people', null, $attrs);
				?>
			</div>

			<?php
			/**
			 * Added support for safe distance disclaimer.
			 *
			 * @since 1.4
			 */
			if ($config->getBool('safedistance'))
			{
				// ask to the customer whether all the members of the
				// group belong to the same family due to COVID-19
				// prevention measures
				?>
				<div class="vrsearchinputdivmod checkbox-wrapper">
					<input type="checkbox" id="vrfamilyqrmod<?php echo $module_id; ?>" value="1" />

					<label for="vrfamilyqrmod<?php echo $module_id; ?>">
						<?php echo JText::_('VRSAFEDISTLABEL'); ?>
						<a href="javascript:void(0);" class="vrfamilyqrmod-help" title="<?php echo htmlspecialchars(JText::_('VRSAFEDISTLABEL_TIP')); ?>">
							<i class="fas fa-exclamation-triangle"></i>
						</a>
					</label>
				</div>
				<?php
			}
			?>
			
			<div class="vr-quickres-step-hints" id="vrqr-hints1-<?php echo $module_id; ?>" style="display: none;">
				<div class="vr-quickres-step-hints-title">
					<?php echo JText::_('VRNOTABLESEEHINTS'); ?>
				</div>

				<div class="vr-quickres-step-hints-content">
					
				</div>
			</div>
			
			<div class="vr-quickres-step-error" id="vrqr-error1-<?php echo $module_id; ?>" style="display: none;"></div>
			
			<div class="vr-quickres-step-field">
				<button class="vr-quickres-button" id="vrqr-buttonfind-<?php echo $module_id; ?>" onClick="vrModFindTableCall(this);"><?php echo JText::_('VRFINDTABLE'); ?></button>
			</div>
			
		</div>
		
		<!-- ROOM SELECTION -->

		<?php
		if ($rooms_choosable)
		{
			?>
			<div class="vr-quickres-step-unactive-field" id="vrqr-nostep2-<?php echo $module_id; ?>" data-step="2">
				<?php echo JText::_('VRROOMSELECTION'); ?>
			</div>
			
			<div class="vr-quickres-step" id="vrqr-step2-<?php echo $module_id; ?>" style="display: none;">

				<div class="vr-quickres-step-field vre-select-wrapper full-size">
					<select id="vrqr-room-mod-<?php echo $module_id; ?>" class="vre-select full-size"></select>
				</div>
				
				<div class="vr-quickres-step-field">
					<button class="vr-quickres-button" onClick="vrModRoomSelected(this);"><?php echo JText::_('VRCONTINUE'); ?></button>
				</div>

			</div>
			<?php
		}
		?>
		
		<div class="vr-quickres-step-unactive-field" id="vrqr-nostep3-<?php echo $module_id; ?>" data-step="3">
			<?php echo JText::_('VRFILLCUSTFIELDS'); ?>
		</div>
		
		<!-- FILL IN CUSTOM FIELDS -->

		<div class="vr-quickres-step" id="vrqr-step3-<?php echo $module_id; ?>" style="display: none;">

			<form method="POST" id="vrqr-custfields-modform<?php echo $module_id; ?>">

				<?php
				foreach ($custom_fields as $cf)
				{
					/**
					 * Use the correct translated label of the field.
					 *
					 * @since 1.4.1
					 */
					$langName = empty($cf['langname']) ? JText::_($cf['name']) : $cf['langname'];

					$value = !empty($user_fields[$cf['name']]) ? $user_fields[$cf['name']] : '';

					if (empty($value) && $cf['rule'] == VRCustomFields::isEmail($cf) && isset($user_fields['email']))
					{
						$value = $user_fields['email'];
					}

					$is_dropdown = $cf['type'] == 'select' || ($cf['type'] == 'checkbox' && !$cf['required'] && !$cf['poplink']);

					?>
					<div class="vr-quickres-step-field <?php echo ($is_dropdown ? 'vre-select-wrapper full-size' : ''); ?>">

						<?php
						if ($cf['type'] == 'text')
						{
							/**
							 * Use correct input type according to
							 * the rule of the custom field.
							 *
							 * @since 1.4
							 */
							switch ($cf['rule'])
							{
								case VRCustomFields::EMAIL:
									$type = 'email';
									break;

								case VRCustomFields::PHONE_NUMBER:
									$type = 'tel';
									break;

								default:
									$type = 'text';
							}

							if (VRCustomFields::isPhoneNumber($cf))
							{
								// check if the dial code selection is allowed
								$is_dial = $config->getBool('phoneprefix');

								$config = array(
									// validate phone number field to make sure
									// the specified value is a valid phone
									'validator' => 'vrQuickResCustomFieldsValidator',
									// custom data to be passed when initializing
									// international tel input
									'data' => array(
										// display flags dropdown according to the
										// global configuration (Show Prefix Selection)
										'allowDropdown' => $is_dial,
									),
								);

								// render input using intltel
								JHtml::_('vrehtml.assets.intltel', '#vrcf' . $cf['id'] . '-'. $module_id, $config);
							}
							?>

							<input
								type="<?php echo $type; ?>"
								name="vrcf<?php echo $cf['id']; ?>"
								id="vrcf<?php echo $cf['id']; ?>-<?php echo $module_id; ?>"
								placeholder="<?php echo htmlspecialchars($langName); ?>"
								value="<?php echo $value; ?>"
								<?php echo $cf['required'] ? 'class="required"' : ''; ?>
							/>
						
							<?php
						}
						else if ($cf['type'] == 'textarea')
						{
							?>

							<textarea
								name="vrcf<?php echo $cf['id']; ?>"
								id="vrcf<?php echo $cf['id']; ?>-<?php echo $module_id; ?>"
								placeholder="<?php echo htmlspecialchars($langName); ?>"
								<?php echo $cf['required'] ? 'class="required"' : ''; ?>
							><?php echo $value; ?></textarea>

							<?php
						}
						else if ($cf['type'] == 'date')
						{
							JHtml::_('vrehtml.sitescripts.calendar', '#vrcf' . $cf['id'] . '-' . $module_id . ':input');

							?>
							
							<input
								type="text"
								name="vrcf<?php echo $cf['id']; ?>"
								id="vrcf<?php echo $cf['id']; ?>-<?php echo $module_id; ?>"
								placeholder="<?php echo htmlspecialchars($langName); ?>"
								value="<?php echo htmlspecialchars($value); ?>"
								class="cf-calendar<?php echo $cf['required'] ? ' required' : ''; ?>"
							/>
						
							<?php
						}
						/**
						 * Required checkboxes, with popup link, are now displayed
						 * using the native element instead of a dropdown.
						 *
						 * @since 1.3.1
						 */
						else if ($cf['type'] == 'checkbox' && $cf['required'] && $cf['poplink'])
						{
							?>
							<div class="checkbox-wrapper">
								<input
									type="checkbox"
									name="vrcf<?php echo $cf['id']; ?>"
									id="vrcf<?php echo $cf['id']; ?>-<?php echo $module_id; ?>"
									<?php echo $cf['required'] ? 'class="required"' : ''; ?>
								/>

								<label for="vrcf<?php echo $cf['id']; ?>-<?php echo $module_id; ?>">
									<span><?php echo $langName; ?></span>

									<a href="javascript: void(0);" onclick="vreOpenPopup('<?php echo $cf['poplink']; ?>');">
										<i class="fas fa-external-link-alt"></i>
									</a>
								</label>
							</div>
							<?php
						}
						else if ($cf['type'] == 'select' || $cf['type'] == 'checkbox')
						{ 	
							$options = array();

							if ($cf['type'] == 'checkbox')
							{
								$options = $original = array(
									JText::_('JYES'),
									JText::_('JNO'),
								);
							}
							else
							{
								$original = array_filter(explode(';;__;;', $cf['_choose']));
								$options  = explode(';;__;;', $cf['choose']);

								for ($i = 0; $i < count($options); $i++)
								{
									// use original option when translation is empty
									if (!strlen($options[$i]) && isset($original[$i]))
									{
										$options[$i] = $original[$i];
									}
								}
							}

							if ($cf['multiple'])
							{
								if (strlen($value))
								{
									// decode stringified value
									$values = json_decode($value);

									if (is_null($values))
									{
										// we are probably using a scalar value,
										// push it within an array
										$values = (array) $value;
									}
								}
								else
								{
									// use an empty array
									$values = array();
								}
							}
							else
							{
								// push the value within an array (for in_array compliance)
								$values = array($value);
							}
							?>
							<select
								name="vrcf<?php echo $cf['id'] . ($cf['multiple'] ? '[]' : ''); ?>"
								id="vrcf<?php echo $cf['id']; ?>-<?php echo $module_id; ?>"
								class="vre-select<?php echo $cf['multiple'] ? '-multiple' : ''; ?><?php echo $cf['required'] ? ' required' : ''; ?>"
								<?php echo ($cf['multiple'] ? 'multiple' : ''); ?>
							>
								<option
									value=""
									disabled="disabled"
									<?php echo !$values ? 'selected="selected"' : ''; ?>
								><?php echo $langName; ?></option>
								<?php
								foreach ($options as $i => $opt)
								{
									?>
									<option
										value="<?php echo htmlspecialchars($opt); ?>"
										<?php echo (in_array($original[$i], $values) ? 'selected="selected"' : ''); ?>
									><?php echo $opt; ?></option>
									<?php
								}
								?>
							</select>

							<?php
						}
						?>

					</div>

					<?php
				}

				/**
				 * Trigger event to retrieve an optional field that could be used
				 * to confirm the subscription to a mailing list.
				 *
				 * @param 	array 	$user 	  The user details.
				 * @param 	array 	$options  An array of options.
				 *
				 * @return  string  The HTML to display.
				 *
				 * @since 1.3.2
				 */
				$html = VREFactory::getEventDispatcher()->triggerOnce('onDisplayMailingSubscriptionInput');
				
				// display field if provided
				if ($html)
				{
					?>
					<div class="vr-quickres-step-field">
						<?php echo $html; ?>
					</div>
					<?php
				}

				/**
				 * Added support for ReCAPTCHA validation.
				 *
				 * @since 1.5
				 */
				$is_captcha = $params->get('recaptcha') && $vik->isGlobalCaptcha();

				if ($is_captcha)
				{
					?>
					<div class="vr-quickres-step-field">
						<?php echo $vik->reCaptcha(); ?>
					</div>
					<?php
				}
				?>

			</form>
			
			<div class="vr-quickres-step-error" id="vrqr-error3-<?php echo $module_id; ?>" style="display: none;"></div>
			
			<div class="vr-quickres-step-field">
				<button type="button" class="vr-quickres-button" onClick="vrModValidateCustomFields(this);"><?php echo JText::_('VRCONFIRMRESERVATION'); ?></button>
			</div>

		</div>
		
		<!-- SEE SUMMARY -->

		<?php
		if (!$auto_redirect)
		{
			?>
			<div class="vr-quickres-step-unactive-field" id="vrqr-nostep4-<?php echo $module_id; ?>" data-step="4">
				<?php echo JText::_('VRORDERSUMMARY'); ?>
			</div>
			
			<div class="vr-quickres-step" id="vrqr-step4-<?php echo $module_id; ?>" style="display: none;">

				<div class="vrqr-content vrqr-successfull">
					
				</div>

				<div class="vr-quickres-step-field">
					<button type="button" class="vr-quickres-button" id="vrqr-buttonurl-<?php echo $module_id; ?>" onClick=""><?php echo JText::_('VRVISITORDERPAGE'); ?></button>
				</div>

			</div>
			<?php
		}
		?>
		
	</div>
	
</div>

<?php
JText::script('VRCONNECTIONLOST');
?>

<script type="text/javascript">

	var vrqr_current_step = 1;
	
	jQuery(document).ready(function() {

		jQuery('#vrqr-calendar-mod<?php echo $module_id; ?>:input').on('change', function() {
			// refresh times
			vrUpdateWorkingShifts('#vrqr-calendar-mod<?php echo $module_id; ?>', '#vrqr-hour-mod<?php echo $module_id; ?>');
		});

		jQuery('.vr-quickres-step-unactive-field').on('click', function(){
			vrModStepClicked(jQuery(this).data('step'));
		});
		
		jQuery('.vr-quickres-header, .vr-quickres-step-unactive-field').disableSelection();

		jQuery('.vrfamilyqrmod-help').tooltip();
		
	});
	
	function vrModStepClicked(step) {
		if (vrqr_current_step == 4 || step >= vrqr_current_step) {
			return;
		}
		
		for (var i = step; i <= 3; i++) {
			jQuery('#vrqr-nostep' + i + '-<?php echo $module_id; ?>').removeClass('clickable');
		}
		
		jQuery('.vr-quickres-step-error').hide();
		
		jQuery('.vr-quickres-step').slideUp();
		jQuery('#vrqr-nostep' + step + '-<?php echo $module_id; ?>').hide();
		
		jQuery('#vrqr-step' + step + '-<?php echo $module_id; ?>').slideDown();
		jQuery('.vr-quickres-step-unactive-field:not(#vrqr-nostep' + step + '-<?php echo $module_id; ?>)').show();
		
		vrqr_current_step = step;
	}
	
	// STEP 1
	
	function vrModFindTableCall(button) {
		if (jQuery(button).hasClass('clicked')) {
			return;
		}
		
		jQuery(button).addClass('clicked');
		
		var date 	= jQuery('#vrqr-calendar-mod<?php echo $module_id; ?>').val();
		var hourmin = jQuery('#vrqr-hour-mod<?php echo $module_id; ?>').val();
		var people 	= jQuery('#vrqr-people-mod<?php echo $module_id; ?>').val();
		var family  = jQuery('#vrfamilyqrmod<?php echo $module_id; ?>').is(':checked') ? 1 : 0;
		
		<?php
		/**
		 * The findtable task needs to specify the same Item ID in which
		 * this module is published. This because otherwise it wouldn't be
		 * possible to retrieve the configuration of the module. In fact,
		 * only that are actually published on the specified page can
		 * be retrieved.
		 *
		 * @since 1.4
		 */
		?>

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=quickres.findtable&tmpl=component' . $itemid, false); ?>',
			{
				date: 	 date,
				hourmin: hourmin,
				people:  people,
				family:  family,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);
			
				if (obj[0] == 1) {
					jQuery('#vrqr-error1-<?php echo $module_id; ?>').hide();
					jQuery('#vrqr-hints1-<?php echo $module_id; ?>').hide();
					
					vrModFindTableCompleted(obj[1], obj[2]);
				} else if (obj[0] == -1 && obj[1].length > 0) {
					jQuery('#vrqr-error1-<?php echo $module_id; ?>').hide();
					
					vrModFillHints(obj[1]);
				}
				
				jQuery(button).removeClass('clicked');
			},
			function(err) {
				if (!err.responseText) {
					// use default "connection lost" error
					err.responseText = Joomla.JText._('VRCONNECTIONLOST');
				}

				jQuery('#vrqr-hints1-<?php echo $module_id; ?>').hide();
				
				jQuery('#vrqr-error1-<?php echo $module_id; ?>').html(err.responseText);
				jQuery('#vrqr-error1-<?php echo $module_id; ?>').show();
			
				jQuery(button).removeClass('clicked');
			}
		);
	}
	
	function vrModFindTableCompleted(str, rooms) {
		jQuery('#vrqr-nostep1-<?php echo $module_id; ?>').html(str);
		jQuery('#vrqr-nostep1-<?php echo $module_id; ?>').addClass('clickable');
		
		jQuery('#vrqr-step1-<?php echo $module_id; ?>').slideUp();
		jQuery('#vrqr-nostep1-<?php echo $module_id; ?>').show();
		
		if (<?php echo intval($rooms_choosable); ?> == 1) {
			
			var html = '';
			
			for (var i = 0; i < rooms.length; i++) {
				html += '<option value="' + rooms[i].id + '">' + rooms[i].name + '</option>';
			}

			jQuery('#vrqr-room-mod-<?php echo $module_id; ?>').html(html);
			
			if (rooms.length == 1) {
				jQuery('#vrqr-nostep2-<?php echo $module_id; ?>').html(rooms[0].str);
				jQuery('#vrqr-step3-<?php echo $module_id; ?>').slideDown();
				
				vrqr_current_step = 3;
			} else {
				jQuery('#vrqr-nostep2-<?php echo $module_id; ?>').slideUp();
				jQuery('#vrqr-step2-<?php echo $module_id; ?>').slideDown();
				
				vrqr_current_step = 2;
			}
			
		} else {
			jQuery('#vrqr-step3-<?php echo $module_id; ?>').slideDown();
			vrqr_current_step = 3;
		}
	}
	
	function vrModFillHints(hints) {
		var _html = "";

		for (var i = 0; i < hints.length; i++) {
			if (hints[i]) {
				_html += '<div class="vr-quickres-hint-block"><a href="javascript: void(0);" onClick="vrModHintClicked(\'' + hints[i].hour + ':' + hints[i].min + '\')">' + hints[i].format + '</a></div>\n';
			}
		}

		jQuery('#vrqr-hints1-<?php echo $module_id; ?> .vr-quickres-step-hints-content').html(_html);
		jQuery('#vrqr-hints1-<?php echo $module_id; ?>').show();
	}
	
	function vrModHintClicked(time) {
		jQuery('#vrqr-hour-mod<?php echo $module_id; ?>').val(time);
		
		jQuery('#vrqr-buttonfind-<?php echo $module_id; ?>').trigger('click');
	}
	
	// STEP 2
	
	function vrModRoomSelected(button) {
		if (jQuery(button).hasClass('clicked')) {
			return;
		}
		
		jQuery(button).addClass('clicked');
		
		var room = jQuery('#vrqr-room-mod-<?php echo $module_id; ?>').val();
		
		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=quickres.selectroom&tmpl=component' . $itemid, false); ?>',
			{
				id_room: room,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);
			
				if (obj[0]) {
					jQuery('#vrqr-room-mod-<?php echo $module_id; ?>').removeClass('vrqr-required-field');
					vrModRoomSelectedCompleted(obj[1]);
				} else {
					jQuery('#vrqr-room-mod-<?php echo $module_id; ?>').addClass('vrqr-required-field');
				}
				
				jQuery(button).removeClass('clicked');
			},
			function(err) {
				if (!err.responseText) {
					// use default error message if not provided
					err.responseText = Joomla.JText._('VRCONNECTIONLOST');
				}

				alert(err.responseText);
			
				jQuery(button).removeClass('clicked');
			}
		);
	}
	
	function vrModRoomSelectedCompleted(str) {
		jQuery('#vrqr-nostep2-<?php echo $module_id; ?>').html(str);
		jQuery('#vrqr-nostep2-<?php echo $module_id; ?>').addClass('clickable');
		
		jQuery('#vrqr-step2-<?php echo $module_id; ?>').slideUp();
		jQuery('#vrqr-nostep2-<?php echo $module_id; ?>').show();
		
		jQuery('#vrqr-nostep3-<?php echo $module_id; ?>').slideUp();
		jQuery('#vrqr-step3-<?php echo $module_id; ?>').slideDown();
		
		vrqr_current_step = 3;
	}
	
	// STEP 3 

	var vrQuickResCustomFieldsValidator;

	jQuery(document).ready(function() {
		vrQuickResCustomFieldsValidator = new VikFormValidator(
			'#vrqr-step3-<?php echo $module_id; ?>',
			'vrqr-required-field'
		);

		/**
		 * Overwrite getLabel method to properly access the
		 * label by using our custom layout.
		 *
		 * @param 	mixed 	input  The input element.
		 *
		 * @return 	mixed 	The label of the input.
		 */
		vrQuickResCustomFieldsValidator.getLabel = function(input) {
			if (jQuery(input).is(':checkbox')) {
				// return label in case of checkbox
				return jQuery(input).next();
			}

			// return input itself
			return jQuery(this);
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
			vrQuickResCustomFieldsValidator.addCallback(function() {
				// get recaptcha elements
				var captcha = jQuery('.vr-quickres-step-field .g-recaptcha').first();
				var iframe  = captcha.find('iframe').first();

				// get widget ID
				var widget_id = captcha.data('recaptcha-widget-id');

				// check if recaptcha instance exists
				// and whether the recaptcha was completed
				if (typeof grecaptcha !== 'undefined'
					&& widget_id !== undefined
					&& !grecaptcha.getResponse(widget_id)) {
					// captcha not completed
					iframe.addClass('vrqr-required-field');
					return false;
				}

				// captcha completed
				iframe.removeClass('vrqr-required-field');
				return true;
			});
			<?php
		}
		?>
	});
	
	function vrModValidateCustomFields(button) {
		if (jQuery(button).hasClass('clicked')) {
			return;
		}
		
		jQuery(button).addClass('clicked');
		
		if (vrQuickResCustomFieldsValidator.validate()) {
			vrModRegisterReservation(button);
		} else {
			jQuery(button).removeClass('clicked');
		}
	}
	
	function vrModRegisterReservation(button) {
		var form = jQuery('#vrqr-custfields-modform<?php echo $module_id; ?>');

		// include dial code within the phone number
		jQuery(form).find('.iti input').filter('[type="text"],[type="tel"]').each(function() {
			var input = jQuery(this);
			var phone = input.val();

			if (phone.length) {
				var country = input.intlTelInput('getSelectedCountryData');

				// make sure the phone number doesn't already specify the dial code
				if (phone.indexOf('+' + country.dialCode) === -1) {
					// prepend dial code before submit
					input.val('+' + country.dialCode + ' ' + input.val());
				}
			}
		});

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=quickres.save&tmpl=component' . $itemid, false); ?>',
			form.serialize(),
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				jQuery('#vrqr-error3-<?php echo $module_id; ?>').hide();
				vrModReservationConfirmed(obj[1], obj[2]);
				
				jQuery(button).removeClass('clicked');
			},
			function(err) {
				if (!err.responseText) {
					// use default error message
					err.responseText = Joomla.JText._('VRCONNECTIONLOST');
				}

				jQuery('#vrqr-error3-<?php echo $module_id; ?>').html(err.responseText);
				jQuery('#vrqr-error3-<?php echo $module_id; ?>').show();
			
				jQuery(button).removeClass('clicked');
			}
		);
	}
	
	function vrModReservationConfirmed(custom_fields, url) {
		<?php
		if ($auto_redirect)
		{
			?>
			document.location.href = url;
			return;
			<?php
		}
		?>
		
		jQuery('#vrqr-nostep3-<?php echo $module_id; ?>').html(custom_fields);
		jQuery('#vrqr-nostep3-<?php echo $module_id; ?>').addClass('clickable');
		jQuery('#vrqr-step4-<?php echo $module_id; ?> .vrqr-content').html('<?php echo addslashes($order_summary_text); ?>');
		
		jQuery('#vrqr-buttonurl-<?php echo $module_id; ?>').attr('onClick', "document.location.href='" + url + "';");
		
		jQuery('#vrqr-step3-<?php echo $module_id; ?>').slideUp();
		jQuery('#vrqr-nostep3-<?php echo $module_id; ?>').show();
		
		jQuery('#vrqr-nostep4-<?php echo $module_id; ?>').hide();
		
		jQuery('#vrqr-step4-<?php echo $module_id; ?>').slideDown();
		
		jQuery('.vr-quickres-step-unactive-field').removeClass('clickable');
		
		vrqr_current_step = 4;
	}
	
</script>
