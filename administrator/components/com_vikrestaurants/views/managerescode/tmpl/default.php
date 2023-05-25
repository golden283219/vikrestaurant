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

JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.fancybox');

$code = $this->code;

$vik = VREApplication::getInstance();

VRELoader::import('library.rescodes.handler');
// get all supported drivers
$drivers = ResCodesHandler::getSupportedRules();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
			
				<!-- CODE - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGERESCODE2') . '*'); ?>
					<input type="text" name="code" value="<?php echo $this->escape($code->code); ?>" class="required" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- ICON - File -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGERESCODE3'));
				echo JHtml::_('vrehtml.mediamanager.field', 'icon', $code->icon);
				echo $vik->closeControl();
				?>

				<!-- TYPE - Dropdown -->
				<?php
				$groups = JHtml::_('vrehtml.admin.groups', array(1, 2));

				/**
				 * Added support for "Food" group.
				 *
				 * @since 1.8
				 */
				$groups[] = JHtml::_('select.option', 3, 'VRCONFIGFIELDSETFOOD');

				echo $vik->openControl(JText::_('VRMANAGERESCODE4')); ?>
					<select name="type" id="vr-type-sel">
						<?php echo JHtml::_('select.options', $groups, 'value', 'text', $code->type, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewRescode". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				echo $this->onDisplayManageView();
				?>

				<!-- NOTES - Editor -->
				<?php echo $vik->openControl(JText::_('VRMANAGERESCODE5')); ?>
					<textarea name="notes" class="full-width" style="height: 160px;resize: vertical;"><?php echo $code->notes; ?></textarea>
				<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>

				<!-- RULE - Dropdown -->
				<?php
				$rules = array();

				foreach ($drivers as $driver)
				{
					$rules[] = JHtml::_('select.option', $driver->getID(), $driver->getName());
				}

				echo $vik->openControl(JText::_('VRMANAGECUSTOMF11'), 'multi-field');

				?>
				<select name="rule" id="vr-rule-sel">
					<option></option>
					<?php echo JHtml::_('select.options', $rules, 'value', 'text', $code->rule); ?>
				</select>
				<?php echo $vik->closeControl(); ?>

				<?php
				foreach ($drivers as $d)
				{
					$desc = $d->getDescription();

					if ($desc)
					{
						?>
						<div class="rule-help" id="rule-help-<?php echo $d->getID(); ?>" style="<?php echo $code->rule == $d->getID() ? '' : 'display:none;'; ?>">
							<?php echo $vik->alert($desc, 'info'); ?>
						</div>
						<?php
					}
				}
				?>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $code->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_FILTER_SELECT_RULE');
?>

<script type="text/javascript">

	var RESCODES_RULES_LOOKUP = <?php echo json_encode($drivers); ?>;

	jQuery(document).ready(function(){

		jQuery('#vr-type-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-rule-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_RULE'),
			allowClear: true,
			width: 200,
		});

		jQuery('#vr-rule-sel').on('change', function() {	
			// toggle rule help
			jQuery('.rule-help').hide();

			var rule = jQuery(this).val();

			if (rule) {
				jQuery('#rule-help-' + rule).show();
			}
		});

		jQuery('#vr-type-sel').on('change', function() {
			// get selected group
			var group;

			switch (parseInt(jQuery(this).val())) {
				case 1:
					group = 'restaurant';
					break;

				case 2:
					group = 'takeaway';
					break;

				case 3:
					group = 'food';
					break;
			}

			// iterate all rules
			jQuery('#vr-rule-sel option').each(function() {
				// get rule ID
				var rule = jQuery(this).val();
				// check if the rule supports the selected group
				var supported = RESCODES_RULES_LOOKUP.hasOwnProperty(rule)
					&& RESCODES_RULES_LOOKUP[rule].groups.indexOf(group) !== -1;

				// enable/disable option
				if (supported) {
					jQuery(this).prop('disabled', false);
				} else {
					jQuery(this).prop('disabled', true);
				}
			});

			// refresh rule selection in order to unset the option in
			// case it is no more available for the selected group
			jQuery('#vr-rule-sel').select2('val', jQuery('#vr-rule-sel').select2('val')).trigger('change');
		}).trigger('change');

	});
	
	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate()) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

</script>
