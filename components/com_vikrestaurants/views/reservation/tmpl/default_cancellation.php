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

VREApplication::getInstance()->addStyleSheet(VREASSETS_URI . 'css/confirmdialog.css');

$reservation = $this->reservation;

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$config = VREFactory::getConfig();

$canc_reason = $config->getUint('cancreason');
?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=cancel_reservation' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" name="vrcancform" id="vrcancform">

	<div class="vrordercancdiv vrcancallbox">
		<button type="button" class="vrordercancbutton" onClick="vrCancelButtonPressed();">
			<?php echo JText::_('VRCANCELORDERTITLE'); ?>
		</button>
	</div>

	<input type="hidden" name="oid" value="<?php echo $reservation->id; ?>" />
	<input type="hidden" name="sid" value="<?php echo $reservation->sid; ?>" />
	<input type="hidden" name="reason" value="" />

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="task" value="cancel_reservation" />

</form>

<div id="dialog-cancel" style="display: none;">
	
	<h4><?php echo JText::_('VRCANCELORDERTITLE');?></h4>

	<p><?php echo JText::_('VRCANCELORDERMESSAGE'); ?></p>

	<?php
	if ($canc_reason > 0)
	{
		?>
		<div>
			<div class="vr-cancreason-err" style="display: none;">
				<?php echo JText::_('VRCANCREASONERR'); ?>
			</div>

			<textarea
				id="vrcancreason"
				placeholder="<?php echo JText::_('VRCANCREASONPLACEHOLDER' . $canc_reason); ?>"
				style="width: 100%; height: 120px; max-height: 50vh; resize: vertical;"
			></textarea>
		</div>
		<?php
	}
	?>
	
</div>

<?php
JText::script('VRCANCELORDEROK');
JText::script('VRCANCELORDERCANC');
?>

<script>

	// CANCELLATION SCRIPT

	const CANC_REASON = <?php echo intval($canc_reason); ?>;

	// create cancellation dialog
	var cancDialog;
	
	jQuery(function($) {
		cancDialog = new VikConfirmDialog('#dialog-cancel');

		// add confirm button
		cancDialog.addButton(Joomla.JText._('VRCANCELORDEROK'), function(args, event) {
			if (CANC_REASON) {
				// get specified reason
				var reason = $(args.textarea).val();

				if ((reason.length > 0 && reason.length < 32)
					|| (reason.length == 0 && CANC_REASON == 2)) {
					$('#vrcancreason').addClass('vrrequiredfield');
					$('.vr-cancreason-err').show();
					return false;
				}

				$('#vrcancreason').removeClass('vrrequiredfield');
				$('.vr-cancreason-err').hide();

				$('#vrcancform input[name="reason"]').val(reason);
			}

			// dispose dialog
			cancDialog.dispose();

			// submit form to complete cancellation
			document.vrcancform.submit();
		}, false);

		// add cancel button
		cancDialog.addButton(Joomla.JText._('VRCANCELORDERCANC'));

		// pre-build dialog
		cancDialog.build();

		if (window.location.hash === '#cancel') {
			vrCancelButtonPressed();
		}
	});

	function vrCancelButtonPressed() {
		var args = {
			textarea: CANC_REASON > 0 ? jQuery('#vrcancreason') : null,
		};

		// Show dialog by passing some arguments.
		// Prevent submit when ENTER is pressed.
		cancDialog.show(args, {submit: false});
	}

</script>
