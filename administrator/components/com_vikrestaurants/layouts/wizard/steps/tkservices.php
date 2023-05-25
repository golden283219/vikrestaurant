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
 * Layout variables
 * -----------------
 * @var  VREWizardStep  $step  The wizard step instance.
 */
extract($displayData);

$vik = VREApplication::getInstance();

$id = $step->getID();

// get services configuration
$is_delivery = $step->isDelivery();
$is_pickup   = $step->isPickup();

if (!$step->isCompleted())
{
	?>
	<div class="wizard-form">

		<!-- DELIVERY - Checkbox -->

		<?php
		$yes = $vik->initRadioElement('', '', $is_delivery );
		$no  = $vik->initRadioElement('', '', !$is_delivery);

		echo $vik->openControl(JText::_('VRTKORDERDELIVERYOPTION'));
		echo $vik->radioYesNo("wizard[{$id}][delivery]", $yes, $no);	
		echo $vik->closeControl();
		?>

		<!-- PICKUP - Checkbox -->

		<?php
		$yes = $vik->initRadioElement('', '', $is_pickup);
		$no  = $vik->initRadioElement('', '', !$is_pickup);

		echo $vik->openControl(JText::_('VRTKORDERPICKUPOPTION'));
		echo $vik->radioYesNo("wizard[{$id}][pickup]", $yes, $no);
		echo $vik->closeControl();
		?>

	</div>

	<?php
	JText::script('VRE_WIZARD_STEP_TKSERVICES_WARN');
	?>

	<script>

		VREWizard.addPreflight('<?php echo $id; ?>', function(role, step) {
			if (role != 'process') {
				return true;
			}

			// make sure at least one option has been selected
			var is = jQuery('input[name="wizard[<?php echo $id; ?>][delivery]"]').is(':checked')
				|| jQuery('input[name="wizard[<?php echo $id; ?>][pickup]"]').is(':checked');

			if (!is) {
				// raise warning
				alert(Joomla.JText._('VRE_WIZARD_STEP_TKSERVICES_WARN'));

				// prevent request
				return false;
			}

			return true;
		});

	</script>
	<?php
}
else
{
	?>
	<ul class="wizard-step-summary">
		<li>
			<i class="fas fa-<?php echo $is_delivery ? 'check-circle ok' : 'dot-circle no'; ?> medium"></i>
			<b><?php echo JText::_('VRTKORDERDELIVERYOPTION'); ?></b>
		</li>
		<li>
			<i class="fas fa-<?php echo $is_pickup ? 'check-circle ok' : 'dot-circle no'; ?> medium"></i>
			<b><?php echo JText::_('VRTKORDERPICKUPOPTION'); ?></b>
		</li>
	</ul>
	<?php
}
?>
