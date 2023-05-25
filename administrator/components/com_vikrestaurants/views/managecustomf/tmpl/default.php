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

$field = $this->field;

$vik = VREApplication::getInstance();

?>
		
<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRMAPDETAILSBUTTON')); ?>

				<!-- GROUP - Dropdown -->
				<?php
				$groups = JHtml::_('vrehtml.admin.groups');

				echo $vik->openControl(JText::_('VRMANAGECUSTOMF7')); ?>
					<select name="group" id="vr-group-sel">
						<?php echo JHtml::_('select.options', $groups, 'value', 'text', $field->group, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGECUSTOMF1') . '*'); ?>
					<input type="text" name="name" class="required" value="<?php echo $this->escape($field->name); ?>" size="30" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- TYPE - Dropdown -->
				<?php 
				$options = array(
					JHtml::_('select.option', 'text', 'VRCUSTOMFTYPEOPTION1'),
					JHtml::_('select.option', 'textarea', 'VRCUSTOMFTYPEOPTION2'),
					JHtml::_('select.option', 'date', 'VRCUSTOMFTYPEOPTION3'),
					JHtml::_('select.option', 'select', 'VRCUSTOMFTYPEOPTION4'),
					JHtml::_('select.option', 'checkbox', 'VRCUSTOMFTYPEOPTION5'),
					JHtml::_('select.option', 'separator', 'VRCUSTOMFTYPEOPTION6'),
				);
				
				echo $vik->openControl(JText::_('VRMANAGECUSTOMF2')); ?>
					<select name="type" id="vr-type-sel">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $field->type, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- REQUIRED - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $field->required, 'onClick="requiredStatusChanged(1);"');
				$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$field->required, 'onClick="requiredStatusChanged(0);"');
				
				echo $vik->openControl(JText::_('VRMANAGECUSTOMF3'));
				echo $vik->radioYesNo('required', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>

				<!-- REQUIRED DELIVERY - Dropdown -->
				<?php
				$options = array(
					JHtml::_('select.option', 0, 'VRCUSTFIELDREQOPT1'),
					JHtml::_('select.option', 1, 'VRCUSTFIELDREQOPT2'),
					JHtml::_('select.option', 2, 'VRCUSTFIELDREQOPT3'),
				);

				$control = array();
				$control['idparent'] = 'vr-reqdel-field';
				$control['style']    = $field->required && $field->group == 1 ? '' : 'display:none;';

				echo $vik->openControl('', '', $control); ?>
					<select name="required_delivery" id="vr-reqdel-sel">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $field->required_delivery, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- RULE - Dropdown -->
				<?php
				$options = array(
					JHtml::_('select.option', '',                  ''),
					JHtml::_('select.option',  1,  'VRCUSTFIELDRULE1'),
					JHtml::_('select.option',  2,  'VRCUSTFIELDRULE2'),
					JHtml::_('select.option',  3,  'VRCUSTFIELDRULE3'),
					JHtml::_('select.option',  4,  'VRCUSTFIELDRULE4'),
					JHtml::_('select.option',  7,  'VRCUSTFIELDRULE7'),
					JHtml::_('select.option',  6,  'VRCUSTFIELDRULE6'),
					JHtml::_('select.option',  5,  'VRCUSTFIELDRULE5'),
					JHtml::_('select.option',  9,  'VRCUSTFIELDRULE9'),
					JHtml::_('select.option', 10, 'VRCUSTFIELDRULE10'),
					JHtml::_('select.option', 11, 'VRCUSTFIELDRULE11'),
				);
				
				echo $vik->openControl(JText::_('VRMANAGECUSTOMF11')); ?>
					<select name="rule" id="vr-rule-sel">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $field->rule, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
						
			<?php echo $vik->closeFieldset(); ?>
		</div>

		<div class="span6" id="right-container">

			<!-- SELECT type fieldset -->

			<div class="row-fluid custom-field-metabox" id="vr-customf-select-box" style="<?php echo ($field->type == 'select' ? '' : 'display: none;'); ?>">
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRCUSTFIELDSLEGEND2'), 'form-horizontal'); ?>

						<?php
						$elem_yes = $vik->initRadioElement('', '', $field->multiple == 1);
						$elem_no  = $vik->initRadioElement('', '', $field->multiple == 0);
						
						echo $vik->openControl(JText::_('VRMULTIPLE'));
						echo $vik->radioYesNo('multiple', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<?php echo $vik->openControl(JText::_('VRCUSTOMFTYPEOPTION4')); ?>
							<div id="vr-customf-select-choose">

								<?php
								$options_list = array_filter(explode(';;__;;', $field->choose));
								foreach ($options_list as $i => $v)
								{
									?>
									<div id="vrchoose<?php echo $i; ?>" class="vrtk-entry-var">
										<span class="manual-sort-handle hidden-phone"><i class="fas fa-ellipsis-v"></i></span>
										
										<input type="text" name="choose[]" value="<?php echo $this->escape($v); ?>" size="40" class="form-control" />

										<a href="javascript: void(0);" class="trash-button-link" onClick="removeElement(<?php echo $i; ?>);">
											<i class="fas fa-times"></i>
										</a>

										<a href="javascript:void(0);" class="manual-sort-arrow mobile-only" onclick="moveBlockDown(<?php echo $i; ?>);">
											<i class="fas fa-chevron-down"></i>
										</a>

										<a href="javascript:void(0);" class="manual-sort-arrow mobile-only" onclick="moveBlockUp(<?php echo $i; ?>);">
											<i class="fas fa-chevron-up"></i>
										</a>
									</div>
									<?php
								} ?>

							</div>

							<div style="margin-top: 10px;">
								<button type="button" class="btn" onclick="addSelectOption();">
									<?php echo JText::_('VRCUSTOMFSELECTADDANSWER'); ?>
								</button>
							</div>
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>
			</div>

			<!-- CHECKBOX type fieldset -->

			<div class="row-fluid custom-field-metabox" id="vr-customf-checkbox-box" style="<?php echo ($field->type == 'checkbox' ? '' : 'display: none;'); ?>">
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRCUSTFIELDSLEGEND2'), 'form-horizontal'); ?>

						<?php
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGECUSTOMF5'),
							'content' => JText::_('VRMANAGECUSTOMF5_DESC'),
						));

						echo $vik->openControl(JText::_('VRMANAGECUSTOMF5') . $help); ?>
							<input type="text" name="poplink" value="<?php echo $field->poplink; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>
			</div>

			<!-- SEPARATOR type fieldset -->

			<div class="row-fluid custom-field-metabox" id="vr-customf-separator-box" style="<?php echo ($field->type == 'separator' ? '' : 'display: none;'); ?>">
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRCUSTFIELDSLEGEND2'), 'form-horizontal'); ?>

						<?php echo $vik->openControl(JText::_('VRSUFFIXCLASS')); ?>
							<input type="text" name="sep_suffix" value="<?php echo $field->choose; ?>" size="30" />
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>
			</div>

			<!-- PHONE NUMBER rule fieldset -->

			<div class="row-fluid custom-field-metabox" id="vr-rule-phone-box" style="<?php echo ($field->rule == 3 ? '' : 'display: none;'); ?>margin-left: 0px;">
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRCUSTFIELDSLEGEND3'), 'form-horizontal'); ?>

						<!-- DEFAULT PREFIX - Text -->
						<?php
						$code = $field->rule == 3 && !empty($field->choose) ? $field->choose : null;

						$options = array();
						$options[] = JHtml::_('select.option', '', '');

						$options = array_merge($options, JHtml::_('vrehtml.admin.countries'));
						
						echo $vik->openControl(JText::_('VRMANAGECUSTOMF10')); ?>
							<select name="def_prfx" id="vr-country-sel">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $code); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>
			</div>

			<?php
			/**
			 * Trigger event to display custom HTML.
			 * In case it is needed to include any additional fields,
			 * it is possible to create a plugin and attach it to an event
			 * called "onDisplayViewCustomf". The event method receives the
			 * view instance as argument.
			 *
			 * @since 1.8
			 */
			$custom = $this->onDisplayManageView();

			if ($custom)
			{
				?>
				<div class="row-fluid" id="custom-fieldset">
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
		</div>

	<?php echo $vik->closeCard(); ?>
		
	<input type="hidden" name="id" value="<?php echo $field->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />

</form>

<?php
JText::script('VRCUSTFIELDRULE0');
JText::script('VRE_FILTER_SELECT_COUNTRY');

$deliveryRules = array(
	VRCustomFields::DELIVERY,
	VRCustomFields::ADDRESS,
	VRCustomFields::ZIP,
	VRCustomFields::CITY,
	VRCustomFields::STATE,
	VRCustomFields::DELIVERY_NOTES,
);
?>

<script>
	
	jQuery(document).ready(function() {

		jQuery('#vr-type-sel').select2({
			allowClear: false,
			width: 300,
		});

		jQuery('#vr-rule-sel').select2({
			placeholder: Joomla.JText._('VRCUSTFIELDRULE0'),
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-reqdel-sel, #vr-group-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 300,
		});

		jQuery('#vr-country-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_COUNTRY'),
			allowClear: true,
			width: 300
		});

		jQuery('#vr-group-sel').on('change', function(){
			var val = parseInt(jQuery(this).val());

			if (val == 1) {
				// takeaway
				requiredStatusChanged(jQuery('input[name="required"]').is(':checked'));
			} else {
				// restaurant
				requiredStatusChanged(0);
			}

			// trigger type change
			jQuery('#vr-type-sel').trigger('change');
		});

		jQuery('#vr-type-sel').on('change', function() {
			var val = jQuery(this).val();

			jQuery('#vr-rule-sel').find('option').prop('disabled', false);

			if (parseInt(jQuery('#vr-group-sel').val()) != 1) {
				// disable rules when "takeaway" group is not selected
				var selector = createRulesSelector([
					 4, // address
					 5, // delivery
					 6, // zip code
					 7, // state
					 8, // city
					 9, // pickup
					11, // delivery notes
				]);

				jQuery('#vr-rule-sel').find(selector).prop('disabled', true);
			}

			jQuery('#right-container div.custom-field-metabox').not('#custom-fieldset').hide();

			isSelectValueChanged(val == 'select');
			isCheckboxValueChanged(val == 'checkbox');	
			isDateValueChanged(val == 'date');
			isSeparatorValueChanged(val == 'separator');

			// trigger rule change
			jQuery('#vr-rule-sel').trigger('change');
		});

		jQuery('#vr-rule-sel').on('change', function() {
			var val = jQuery(this).val();

			isPhoneValueChanged(val == 3);
		});

		jQuery('#vr-group-sel').trigger('change');

		makeSortable();

	});

	function createRulesSelector(rules) {
		return rules.map((rule) => {
			return 'option[value="' + rule + '"]';
		}).join(',');
	}

	// types

	function isSelectValueChanged(is) {
		if (is) {
			jQuery('#vr-customf-select-box').show();

			// disable name, mail, phone rules for "select" type
			jQuery('#vr-rule-sel')
				.find(createRulesSelector([1, 2, 3]))
					.prop('disabled', true);
		}
	}

	function isCheckboxValueChanged(is) {
		if (is) {
			jQuery('#vr-customf-checkbox-box').show();
			
			// disable all rules
			jQuery('#vr-rule-sel').find('option').prop('disabled', true);
			if (parseInt(jQuery('#vr-group-sel').val()) == 1) {
				// enable delivery and pickup rules
				jQuery('#vr-rule-sel').find(createRulesSelector([5, 9])).prop('disabled', false);
			}
		}
	}

	function isDateValueChanged(is) {
		if (is) {
			// disable all rules
			jQuery('#vr-rule-sel').find('option').prop('disabled', true);
			if (parseInt(jQuery('#vr-group-sel').val()) == 1) {
				// enable delivery and pickup rules
				jQuery('#vr-rule-sel').find(createRulesSelector([5, 9])).prop('disabled', false);
			}
		}
	}

	function isSeparatorValueChanged(is) {
		if (is) {
			jQuery('#vr-customf-separator-box').show();

			jQuery('#vr-rule-sel').find('option').prop('disabled', true);
			if (parseInt(jQuery('#vr-group-sel').val()) == 1) {
				// enable delivery and pickup rules
				jQuery('#vr-rule-sel').find(createRulesSelector([5, 9])).prop('disabled', false);
			}
		}
	}

	// conditions

	function isPhoneValueChanged(is) {
		if (is) {
			jQuery('#vr-rule-phone-box').show();
		} else {
			jQuery('#vr-rule-phone-box').hide();
		}
	}

	function requiredStatusChanged(is) {
		if (is && parseInt(jQuery('#vr-group-sel').val()) == 1) {
			jQuery('#vr-reqdel-field').show();
		} else {
			jQuery('#vr-reqdel-field').hide();
		}
	}

	// select handler

	var CHOOSE_COUNT = <?php echo count($options_list); ?>;

	function addSelectOption() {
		jQuery('#vr-customf-select-choose').append(
			'<div id="vrchoose' + CHOOSE_COUNT + '" class="vrtk-entry-var">\n' +
				'<span class="manual-sort-handle hidden-phone"><i class="fas fa-ellipsis-v"></i></span>\n' +
				'<input type="text" name="choose[]" value="" size="40" class="form-control" />\n' +
				'<a href="javascript: void(0);" class="trash-button-link" onClick="removeElement(' + CHOOSE_COUNT + ');">\n' +
					'<i class="fas fa-times"></i>\n' +
				'</a>\n' +
				'<a href="javascript:void(0);" class="manual-sort-arrow mobile-only" onclick="moveBlockDown(' + CHOOSE_COUNT + ');">\n' +
					'<i class="fas fa-chevron-down"></i>\n' +
				'</a>\n' +
				'<a href="javascript:void(0);" class="manual-sort-arrow mobile-only" onclick="moveBlockUp(' + CHOOSE_COUNT + ');">\n' +
					'<i class="fas fa-chevron-up"></i>\n' +
				'</a>\n' +
			'</div>\n'
		);

		CHOOSE_COUNT++;	
	}

	function removeElement(id) {
		jQuery('#vrchoose' + id).remove();
	}

	function makeSortable() {
		jQuery('#vr-customf-select-choose').sortable({
			axis:   'y',
			cursor: 'move',
			handle: '.manual-sort-handle',
			revert: false,
		});
	}

	function moveBlockDown(id) {
		var next_id = jQuery('#vrchoose' + id).next('.vrtk-entry-var').attr('id');
		jQuery('#vrchoose' + id).insertAfter('#' + next_id);
	}

	function moveBlockUp(id) {
		var prev_id = jQuery('#vrchoose' + id).prev('.vrtk-entry-var').attr('id');
		jQuery('#vrchoose' + id).insertBefore('#' + prev_id);
	}

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
