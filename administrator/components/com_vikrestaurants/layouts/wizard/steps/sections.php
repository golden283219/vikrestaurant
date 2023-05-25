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

$is_rest = $step->isRestaurant();
$is_take = $step->isTakeAway();

if (!$step->isCompleted())
{
	?>
	<div class="wizard-form">

		<!-- RESTAURANT - Checkbox -->

		<?php
		$yes = $vik->initRadioElement('', '', $is_rest);
		$no  = $vik->initRadioElement('', '', !$is_rest);

		echo $vik->openControl(JText::_('VRCUSTOMFGROUPOPTION1'));
		echo $vik->radioYesNo("wizard[{$id}][restaurant]", $yes, $no);	
		echo $vik->closeControl();
		?>

		<!-- TAKE-AWAY - Checkbox -->

		<?php
		$yes = $vik->initRadioElement('', '', $is_take);
		$no  = $vik->initRadioElement('', '', !$is_take);

		echo $vik->openControl(JText::_('VRCUSTOMFGROUPOPTION2'));
		echo $vik->radioYesNo("wizard[{$id}][takeaway]", $yes, $no);
		echo $vik->closeControl();
		?>

	</div>

	<?php
	JText::script('VRE_WIZARD_STEP_SECTIONS_WARN');
	?>

	<script>

		VREWizard.addPreflight('<?php echo $id; ?>', function(role, step) {
			if (role != 'process') {
				return true;
			}

			// make sure at least one option has been selected
			var is = jQuery('input[name="wizard[<?php echo $id; ?>][restaurant]"]').is(':checked')
				|| jQuery('input[name="wizard[<?php echo $id; ?>][takeaway]"]').is(':checked');

			if (!is) {
				// raise warning
				alert(Joomla.JText._('VRE_WIZARD_STEP_SECTIONS_WARN'));

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
			<i class="fas fa-<?php echo $is_rest ? 'check-circle ok' : 'dot-circle no'; ?> medium"></i>
			<b><?php echo JText::_('VRCUSTOMFGROUPOPTION1'); ?></b>
		</li>
		<li>
			<i class="fas fa-<?php echo $is_take ? 'check-circle ok' : 'dot-circle no'; ?> medium"></i>
			<b><?php echo JText::_('VRCUSTOMFGROUPOPTION2'); ?></b>
		</li>
	</ul>
	<?php
}
?>
