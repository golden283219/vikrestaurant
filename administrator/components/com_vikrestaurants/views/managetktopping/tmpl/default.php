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

$topping = $this->topping;

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKTOPPING1') . '*'); ?>
					<input type="text" name="name" class="required" value="<?php echo $this->escape($topping->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- PRICE - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKTOPPING2')); ?>
					<div class="input-prepend currency-field">
						<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

						<input type="number" name="price" value="<?php echo $topping->price; ?>" min="-99999" max="999999" step="any" />
					</div>
				<?php echo $vik->closeControl(); ?>

				<!-- PRICE QUICK UPDATE - Dropdown -->

				<?php
				if ($topping->id > 0)
				{
					$elements = array(
						JHtml::_('select.option', '', ''),
						JHtml::_('select.option', 1, JText::_('VRTKTOPPINGQUICKOPT1')),
						JHtml::_('select.option', 2, JText::_('VRTKTOPPINGQUICKOPT2')),
					);
					
					echo $vik->openControl(JText::_('VRMANAGETKTOPPING6'), 'vr-quick-update', array('style' => 'display: none;')); ?>
						<select name="update_price" id="vr-quick-update-sel">
							<?php echo JHtml::_('select.options', $elements); ?>
						</select>
					<?php echo $vik->closeControl(); ?>

					<input type="hidden" name="old_price" value="<?php echo $topping->price; ?>" />
					<?php
				}
				?>
				
				<!-- PUBLISHED - Number -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $topping->published);
				$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$topping->published);
				
				echo $vik->openControl(JText::_('VRMANAGETKTOPPING3'));
				echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- SEPARATOR - Dropdown -->
				<?php
				$elements = array(
					JHtml::_('select.option', '', ''),
				);

				$elements = array_merge($elements, $this->separators);
				
				echo $vik->openControl(JText::_('VRMANAGETKTOPPING5')); ?>
					<div class="multi-field">
						<select name="id_separator" id="vr-separator-sel">
							<?php echo JHtml::_('select.options', $elements, 'value', 'text', $topping->id_separator); ?>
						</select>

						<button type="button" class="btn" id="create-separator-btn">
							<?php echo JText::_('VRNEW'); ?>
						</button>
					</div>

					<div style="display: none;margin-top:10px;">
						<input type="text" name="separator_name" value="" size="40" id="vr-separator-name" placeholder="<?php echo JText::_('VRMANAGETKTOPPINGSEP1'); ?>" />
					</div>
				<?php echo $vik->closeControl(); ?>

				<!-- DESCRIPTION - TextArea -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKMENU2')); ?>
					<textarea name="description" class="full-width" style="resize: vertical;height: 120px;" maxlength="256"><?php echo $topping->description; ?></textarea>
				<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewTktopping". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			?>
			<div class="span6">
				<?php
				echo $vik->openEmptyFieldset();
				echo $custom;
				echo $vik->closeEmptyFieldset();
				?>
			</div>
			<?php
		}
		?>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $topping->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRTKTOPPINGQUICKOPT0');
JText::script('VRE_FILTER_SELECT_SEPARATOR');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('#vr-separator-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_SEPARATOR'),
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-separator-sel').on('change', function() {
			if (jQuery(this).val() != 0) {
				validator.unregisterFields(jQuery('#vr-separator-name'));
				jQuery('#vr-separator-name').parent().hide();
				jQuery('#create-separator-btn').prop('disabled', false);
			}
		});

		jQuery('#create-separator-btn').on('click', function() {
			jQuery('#vr-separator-sel').select2('val', '');
			validator.registerFields(jQuery('#vr-separator-name'));
			jQuery('#vr-separator-name').parent().show();
			jQuery(this).prop('disabled', true);
		});

		<?php if ($topping->id > 0) { ?>

			var PRICE_START_VAL = <?php echo $topping->price; ?>;

			jQuery('input[name="price"]').on('change', function() {
				if (parseFloat(jQuery(this).val()) != PRICE_START_VAL) {
					jQuery('.vr-quick-update').show();
				} else {
					jQuery('.vr-quick-update').hide();
					jQuery('#vr-quick-update-sel').select2('val', null);
				}
			});

			jQuery('#vr-quick-update-sel').select2({
				minimumResultsForSearch: -1,
				placeholder: Joomla.JText._('VRTKTOPPINGQUICKOPT0'),
				allowClear: true,
				width: 300,
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
