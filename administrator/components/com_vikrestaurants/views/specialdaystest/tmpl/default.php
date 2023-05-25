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

JHtml::_('formbehavior.chosen');
JHtml::_('vrehtml.assets.fontawesome');

$vik = VREApplication::getInstance();

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<div class="row-fluid">

		<div class="span5">
			<?php echo $vik->openEmptyFieldset('use-margin-for-alignment'); ?>

				<!-- GROUP - Select -->
				<?php
				$groups = JHtml::_('vrehtml.admin.groups', array('restaurant', 'takeaway'));

				echo $vik->openControl(JText::_('VRMANAGESPDAY16')); ?>
					<select name="group" id="vr-group-sel">
						<?php echo JHtml::_('select.options', $groups, 'value', 'text', $this->args['group'], true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- DATE - Calendar -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGESPDAY2'));
				echo $vik->calendar($this->args['date'], 'date', 'vrdate');
				echo $vik->closeControl();
				?>

				<!-- SUBMIT - Button -->
				<?php echo $vik->openControl(''); ?>
					<button type="button" class="btn" id="sd-test-button"><?php echo JText::_('VRTESTSPECIALDAYS'); ?></button>
				<?php echo $vik->closeControl(); ?>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<div class="span7" id="sd-test-wrapper" style="display: none;">
			<?php echo $vik->openEmptyFieldset('sd-test-response'); ?>

				<!-- test response go here -->

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	</div>

</form>

<?php
JText::script('VRSYSTEMCONNECTIONERR');
?>

<script>

	var validator = new VikFormValidator('#adminForm');

	jQuery(document).ready(function() {
		// render select with chosen
		VikRenderer.chosen('#adminForm');

		// register submit click event
		jQuery('#sd-test-button').on('click', function() {
			// hide table on submit
			jQuery('#sd-test-wrapper').hide();

			// validate form
			if (!validator.validate()) {
				return false;
			}

			var btn = this;

			// disable submit button
			jQuery(btn).attr('disabled', true);

			UIAjax.do(
				'index.php?option=com_vikrestaurants&task=specialday.test',
				jQuery('#adminForm').serialize(),
				function(resp) {
					try {
						// decode response
						resp = JSON.parse(resp);

						// append received HTML
						jQuery('.sd-test-response').html(resp);

						// render tooltip
						jQuery('.sd-test-response .hasTooltip').tooltip({container: 'body'});

						// show test table
						jQuery('#sd-test-wrapper').show();
					} catch (err) {
						// alert error message
						alert(err);
					}

					// enable submit button
					jQuery(btn).attr('disabled', false);
				},
				function(error) {
					// display error
					if (!error.responseText) {
						// use default connection lost error
						error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
					}

					// alert error message
					alert(error.responseText);

					// enable submit button
					jQuery(btn).attr('disabled', false);
				}
			);
		});
	});

</script>
