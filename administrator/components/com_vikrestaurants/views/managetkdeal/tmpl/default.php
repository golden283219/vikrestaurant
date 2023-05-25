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

$deal = $this->deal;

$currency = VREFactory::getCurrency();

if ($deal->start_ts == -1 || $deal->end_ts == -1)
{
	$deal->start_ts = '';
	$deal->end_ts   = '';
}

$vik = VREApplication::getInstance();

$editor = $vik->getEditor();

$deal_food_count = $free_food_count = 0;

// always use default tab while creating a new record
$active_tab = $deal->id ? $this->getActiveTab('tkdeal_details', $deal->id) : 'tkdeal_details';

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->bootStartTabSet('tkdeal', array('active' => $active_tab, 'cookie' => $this->getCookieTab($deal->id)->name)); ?>

		<!-- DETAILS -->

		<?php echo $vik->bootAddTab('tkdeal', 'tkdeal_details', JText::_('VRMAPDETAILSBUTTON')); ?>
	
			<div class="row-fluid">

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRTKDEALFIELDSET1'), 'form-horizontal'); ?>
				
						<!-- NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGETKDEAL2') . '*'); ?>
							<input class="required" type="text" name="name" value="<?php echo $this->escape($deal->name); ?>" size="40" />
						<?php echo $vik->closeControl(); ?>
						
						<!-- TYPE - Dropdown -->
						<?php
						$options = array(
							JHtml::_('select.option', '', ''),
						);

						foreach ($this->deals as $d)
						{
							$options[] = JHtml::_('select.option', $d->getID(), $d->getName());
						}
						
						echo $vik->openControl(JText::_('VRMANAGETKDEAL8') . '*'); ?>
							<select name="type" id="vrtk-type-select" class="required">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $deal->type, true); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- MAX QUANTITY - Number -->
						<?php
						$options = array(
							JHtml::_('select.option', 1, 'VRTKDEALQUANTITYOPT1'),
							JHtml::_('select.option', 2, 'VRTKDEALQUANTITYOPT2'),
						);

						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGETKDEAL6'),
							'content' => JText::_('VRMANAGETKDEAL6_DESC'),
						));
						
						echo $vik->openControl(JText::_('VRMANAGETKDEAL6') . $help, 'multi-field'); ?>
							<select name="tkquant_type" id="vrtk-quantity-select">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $deal->max_quantity <= 0 ? 1 : 2, true); ?>
							</select>

							<input type="number" name="max_quantity" value="<?php echo $deal->max_quantity; ?>" min="0" max="9999" style="<?php echo ($deal->max_quantity <= 0 ? 'display: none;' : ''); ?>" />
						<?php echo $vik->closeControl(); ?>

						<!-- PUBLISHED - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $deal->published);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$deal->published);
						
						echo $vik->openControl(JText::_('VRMANAGETKDEAL7'));
						echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- START DATE - Calendar -->
						<?php
						echo $vik->openControl(JText::_('VRMANAGETKDEAL4'));
						echo $vik->calendar($deal->start_ts, 'start_ts', 'start_ts');
						echo $vik->closeControl();
						?>
						
						<!-- END DATE - Calendar -->
						<?php
						echo $vik->openControl(JText::_('VRMANAGETKDEAL5'));
						echo $vik->calendar($deal->end_ts, 'end_ts', 'end_ts');
						echo $vik->closeControl();
						?>
						
						<!-- DAYS FILTER - Dropdown -->
						<?php echo $vik->openControl(JText::_('VRMANAGETKDEAL13')); ?>
							<select name="days[]" id="vrtk-days-select" multiple="multiple">
								<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.days'), 'value', 'text', $deal->days); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- WORKING SHIFTS - Dropdown -->
						<?php
						$shifts = JHtml::_('vrehtml.admin.shifts', 2);

						if (count($shifts))
						{
							echo $vik->openControl(JText::_('VRMANAGESPDAY4'));
							?>
								<select name="shifts[]" id="vrtk-shifts-select" multiple="multiple">
									<?php echo JHtml::_('select.options', $shifts, 'value', 'text', $deal->shifts); ?>
								</select>
							<?php
							echo $vik->closeControl();
						}
						?>

						<!-- DELIVERY SERVICE - Select -->
						<?php 
						$options = array(
							JHtml::_('select.option', 2, 'VRSPDAYSERVICEOPT2'),
							JHtml::_('select.option', 1, 'VRSPDAYSERVICEOPT3'),
							JHtml::_('select.option', 0, 'VRSPDAYSERVICEOPT4'),
						);
						
						echo $vik->openControl(JText::_('VRMANAGESPDAY22')); ?>
							<select name="service" id="vrtk-service-sel">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $deal->service, true); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- CUSTOM -->

						<?php
						/**
						 * Trigger event to display custom HTML.
						 * In case it is needed to include any additional fields,
						 * it is possible to create a plugin and attach it to an event
						 * called "onDisplayViewTkdeal". The event method receives the
						 * view instance as argument.
						 *
						 * @since 1.8
						 */
						echo $this->onDisplayManageView();
						?>
				
					<?php echo $vik->closeFieldset(); ?>
				</div>
		
				<div class="span6">
					<?php
					echo $vik->openFieldset(JText::_('VRMANAGETKDEAL3'));
					echo $editor->display('description', $deal->description, 400, 200, 20, 20);
					echo $vik->closeFieldset();
					?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<!-- PARAMETERS -->

		<?php echo $vik->bootAddTab('tkdeal', 'tkdeal_params', JText::_('VRTKDEALFIELDSET2')); ?>

			<?php echo $this->loadTemplate('params'); ?>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $deal->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRTKDEALTYPE0');
JText::script('VRMANAGEMENU25');
JText::script('VRMANAGEMENU24');
?>

<script>

	jQuery(document).ready(function(){

		jQuery('#vrtk-quantity-select').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150,
		});

		jQuery('#vrtk-service-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vrtk-type-select').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			placeholder: Joomla.JText._('VRTKDEALTYPE0'),
			width: 250,
		});

		jQuery('#vrtk-days-select').select2({
			placeholder: Joomla.JText._('VRMANAGEMENU25'),
			allowClear: true,
			width: 350,
		});

		jQuery('#vrtk-shifts-select').select2({
			placeholder: Joomla.JText._('VRMANAGEMENU24'),
			allowClear: true,
			width: 350,
		});

		jQuery('#vrtk-quantity-select').on('change', function() {
			if (jQuery(this).val() == 1) {
				jQuery('input[name="max_quantity"]').hide();
				jQuery('input[name="max_quantity"]').val(-1);
			} else {
				jQuery('input[name="max_quantity"]').val(1);
				jQuery('input[name="max_quantity"]').show();
			}
		});
		
		jQuery('#vrtk-type-select').on('change', function() {
			var type = jQuery(this).val();

			if (type.length == 0) {
				return false;
			}

			// toggle type help
			jQuery('.type-help').hide();

			if (type) {
				jQuery('#type-help-' + type).show();
			}

			// toggle type rules
			jQuery('.deal-rule-fieldset').hide().find('*[data-name]').each(function() {
				// unset input name to avoid duplicate fields when submitting the form
				jQuery(this).attr('name', null);
			});

			jQuery('#deal-rule-' + type).show().find('*[data-name]').each(function() {
				// restore input name to properly submit the rule data
				jQuery(this).attr('name', jQuery(this).data('name'));
			});

			// force number of applies to "1" when certain deal types are selected
			if ([4, 5, 6].indexOf(parseInt(type)) != -1) {
				jQuery('#vrtk-quantity-select').select2('val', 2)
					.prop('disabled', true)
					.trigger('change');

				jQuery('input[name="max_quantity"]').prop('readonly', true);
			} else {
				jQuery('#vrtk-quantity-select').prop('disabled', false);

				jQuery('input[name="max_quantity"]').prop('readonly', false);
			}
		}).trigger('change');

		<?php if ($deal->type) { ?>
			// auto fill active deal inputs
			jQuery('#deal-rule-<?php echo $deal->type; ?>').find('*[data-name]').each(function() {
				// restore input name to properly submit the rule data
				jQuery(this).attr('name', jQuery(this).data('name'));
			});
		<?php } ?>
		
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
