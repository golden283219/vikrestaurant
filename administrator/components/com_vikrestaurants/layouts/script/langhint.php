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

$langselector = isset($displayData['langselector']) ? $displayData['langselector'] : '#vre-lang-sel';

JText::script('JGLOBAL_NO_MATCHING_RESULTS');
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRE_LANG_HINT_TOOLTIP');
JText::script('VRE_LANG_HINT_SINGLE');
JText::script('VRE_LANG_HINT_MULTI');
JText::script('JGLOBAL_SELECT_AN_OPTION');
JText::script('JYES');
JText::script('JNO');
JText::script('JCANCEL');
JText::script('VROK');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('.translation-hint').each(function() {
			// find hint button and register click event
			var hintButton = jQuery(this).find('button,a');

			hintButton.attr('title', Joomla.JText._('VRE_LANG_HINT_TOOLTIP')).tooltip({container: 'body'});

			// get translation input
			var translationInput = jQuery(this).find('input,textarea');
			// get input containing original value
			var originalInput = jQuery('input,textarea').filter('[data-link="' + translationInput.data('id') + '"]');

			jQuery(hintButton).on('click', function() {
				if (hintButton.find('i').hasClass('fa-spin')) {
					// already fetching the translation, do not go ahead
					return false;
				}

				// start loading animation
				hintButton.find('i').addClass('fa-spin');
				// deny changes to input
				translationInput.prop('readonly', true);

				// make AJAX request to obtain a list of suggestions
				UIAjax.do(
					'index.php?option=com_vikrestaurants&task=get_suggested_translations',
					{
						term: originalInput.val(),
						tag:  jQuery('<?php echo $langselector; ?>').val(),
					},
					function(resp) {
						// stop loading animation
						hintButton.find('i').removeClass('fa-spin');
						// allow changes to input
						translationInput.prop('readonly', false);

						try {
							// decode JSON response
							resp = JSON.parse(resp);
						} catch (err) {
							console.warn(err);

							// an error occurred, alert generic message
							alert(Joomla.JText._('VRSYSTEMCONNECTIONERR'));

							return false;
						}

						// display confirmation for one result only
						if (resp.length == 1) {
							// fetch message to display
							var message = Joomla.JText._('VRE_LANG_HINT_SINGLE').replace(/%s/, resp[0]);

							// define arguments to use in confirmation callback
							var args = {
								input: translationInput,
								hint:  resp[0],
							};

							// set up message
							singleConfirm.setMessage(message);
							// display confirmation by registering the specified arguments
							singleConfirm.show(args);
						}
						// display confirmation for multiple results
						else if (resp.length > 1) {
							// fetch message to display
							var message = Joomla.JText._('VRE_LANG_HINT_MULTI');

							message += '<div style="margin: 5px 0 20px 0;"><select id="hint-multi-select">';
							message += '<option></option>';
							for (var i = 0; i < resp.length; i++) {
								message += '<option value="' + resp[i] + '">' + resp[i] + '</option>';
							}
							message += '</select></div>';

							// define arguments to use in confirmation callback
							var args = {
								input:  translationInput,
								select: '#hint-multi-select',
							};

							// set up message
							multiConfirm.setMessage(message);

							// pre build dialog in order to attach some events
							multiConfirm.build();

							// register event to render select before showing the dialog
							jQuery('#' + multiConfirm.id).off('beforeshow').on('beforeshow', function() {
								jQuery(multiConfirm.args.select).select2({
									minimumResultsForSearch: -1,
									placeholder: Joomla.JText._('JGLOBAL_SELECT_AN_OPTION'),
									allowClear: true,
									width: '100%',
								});

								jQuery('.select2-drop').css('z-index', '99999');
							});

							// destroy the select while dismissing the dialog
							jQuery('#' + multiConfirm.id).off('dismiss').on('dismiss', function() {
								jQuery(multiConfirm.args.select).select2('destroy');
							});

							// display confirmation by registering the specified arguments
							multiConfirm.show(args);
						} else {
							alert(Joomla.JText._('JGLOBAL_NO_MATCHING_RESULTS'));
						}
					},
					function(err) {
						// stop loading animation
						hintButton.find('i').removeClass('fa-spin');
						// allow changes to input
						translationInput.prop('readonly', false);

						// an error occurred, alert generic message
						alert(Joomla.JText._('VRSYSTEMCONNECTIONERR'));
					}
				);
			});
		});

		// create single confirmation prompt
		var singleConfirm = new VikConfirmDialog(null, 'vik-single-confirm');

		// confirm resulting record
		singleConfirm.addButton(Joomla.JText._('JYES'), function(args, event) {
			// apply translation
			jQuery(args.input).val(args.hint);
		});

		// discard result
		singleConfirm.addButton(Joomla.JText._('JNO'));

		// create multiple confirmation prompt
		var multiConfirm = new VikConfirmDialog(null, 'vik-multi-confirm');

		// confirm resulting record
		multiConfirm.addButton(Joomla.JText._('VROK'), function(args, event) {
			// recover translation from select
			var val = jQuery(args.select).val();
			// apply translation
			jQuery(args.input).val(val);
		});

		// discard result
		multiConfirm.addButton(Joomla.JText._('JCANCEL'));

		window.singleConfirm = singleConfirm;
		window.multiConfirm = multiConfirm;

	});

</script>
