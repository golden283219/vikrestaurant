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

JHtml::_('vrehtml.assets.fontawesome');

$tag = $this->tag;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT2') . '*'); ?>
					<input type="text" name="name" class="required" value="<?php echo $this->escape($tag->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>

				<!-- COLOR - Text -->
				<?php echo $vik->openControl(JText::_('VRE_UISVG_COLOR')); ?>
					<div class="input-append">
						<input type="text" name="color" value="<?php echo $tag->color ? '#' . $tag->color : ''; ?>" />

						<button type="button" class="btn" id="vrcolorpicker">
							<i class="fas fa-eye-dropper"></i>
						</button>
					</div>
				<?php echo $vik->closeControl(); ?>

				<!-- DESCRIPTION - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT3')); ?>
					<textarea name="description" class="full-width" style="height: 140px;resize: vertical;"><?php echo $tag->description; ?></textarea>
				<?php echo $vik->closeControl(); ?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewTag". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				echo $this->onDisplayManageView();
				?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="group" value="<?php echo $tag->group; ?>" />
	<input type="hidden" name="id" value="<?php echo $tag->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {
		jQuery('input[name="color"]').on('change blur', function() {
			// refresh colorpicker on value change
			jQuery('#vrcolorpicker').ColorPickerSetColor(jQuery(this).val());
		});
		
		jQuery('#vrcolorpicker').ColorPicker({
			color: jQuery('input[name="color"]').val(),
			onChange: function (hsb, hex, rgb) {
				jQuery('input[name="color"]').val('#' + hex.toUpperCase());
			},
		});
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
