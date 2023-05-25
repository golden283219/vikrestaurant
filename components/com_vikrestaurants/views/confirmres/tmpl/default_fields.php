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
	$userFields = $this->user ? $this->user->fields->restaurant : array();

	/**
	 * Extract name components from registered user name
	 * and fill the related custom fields.
	 *
	 * @since 1.8
	 */
	VikRestaurants::extractNameFields($this->customFields, $userFields);

	// iterate custom fields
	foreach ($this->customFields as $cf)
	{
		// fetch custom field name
		$langName = empty($cf['langname']) ? JText::_($cf['name']) : $cf['langname'];
		
		$textval = '';

		if (!empty($userFields[$cf['name']]))
		{
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

		try
		{
			/**
			 * The form field is displayed from the layout below:
			 * /components/com_vikrestaurants/layouts/form/fields/[TYPE].php
			 *
			 * @since 1.8
			 */
			echo JLayoutHelper::render('form.fields.' . $input, $displayData);
		}
		catch (Exception $e)
		{
			// type not supported
		}
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

	});

</script>
