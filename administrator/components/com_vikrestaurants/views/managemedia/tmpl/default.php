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

$media = $this->media;
$thumb = $this->thumb;

$vik = VREApplication::getInstance();

$settings = VikRestaurants::getMediaProperties();

?>

<form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" id="adminForm">

	<?php echo $vik->openCard(); ?>

		<div class="span12">

			<div class="row-fluid">
			
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRMEDIAFIELDSET1'), 'form-horizontal'); ?>
					
						<!-- NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA1') . '*'); ?>
							<div class="input-append full-width">
								<input type="text" name="name" value="<?php echo $this->escape($media['name_no_ext']); ?>" class="required" size="64" />
								<button type="button" class="btn"><?php echo $media['file_ext']; ?></button>
							</div>
						<?php echo $vik->closeControl(); ?>
						
						<!-- ACTION - Dropdown -->
						<?php
						$elements = array(
							JHtml::_('select.option', '', ''),
							JHtml::_('select.option', 1, JText::_('VRMEDIAACTION1')),
							JHtml::_('select.option', 2, JText::_('VRMEDIAACTION2')),
							JHtml::_('select.option', 3, JText::_('VRMEDIAACTION3')),
						);
						
						echo $vik->openControl(JText::_('VRMANAGEMEDIA5')); ?>
							<select name="action" id="vr-media-action">
								<?php echo JHtml::_('select.options', $elements); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- MEDIA - File -->
						<?php
						echo $vik->openControl(JText::_('VRMANAGEMEDIA4') . '*', 'vr-action-child', array('style' => 'display:none;')); ?>
							<input type="file" name="image" class="vr-action-child-field" size="32" />
						<?php echo $vik->closeControl(); ?>

						<!-- Resize - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement(1, JText::_('VRYES'), $settings['resize'], 'onClick="resizeValueChanged(1);"');
						$elem_no  = $vik->initRadioElement(0, JText::_('VRNO'), !$settings['resize'], 'onClick="resizeValueChanged(0);"');
						
						echo $vik->openControl(JText::_('VRMANAGEMEDIA6'), 'vr-replace-child', array('style' => 'display:none;'));
						echo $vik->radioYesNo('resize', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- Resize Width - Number -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA7'), 'vr-replace-child', array('style' => 'display:none;')); ?>
							<div class="input-append">
								<input type="number" name="resize_value" value="<?php echo $settings['resize_value']; ?>" min="16" step="1" id="vr-resize-field" <?php echo ($settings['resize'] ? '' : 'readonly="readonly"'); ?> />
								<button type="button" class="btn">px</button>
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- Thumb Width - Number -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA8'), 'vr-replace-child', array('style' => 'display:none;')); ?>
							<div class="input-append">
								<input type="number" name="thumb_value" value="<?php echo $settings['thumb_value']; ?>" min="16" step="1" />
								<button type="button" class="btn">px</button>
							</div>
						<?php echo $vik->closeControl(); ?>
					
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<div class="row-fluid">

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMEDIAFIELDSET2'), 'form-horizontal'); ?>

						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA2')); ?>
							<span class="control-text-value">
								<span class="badge badge-info"><?php echo $media['size']; ?></span>
								&nbsp;
								<span class="badge badge-success"><?php echo $media['width'] . 'x' . $media['height'] . ' pixel'; ?></span>
							</span>
						<?php echo $vik->closeControl(); ?>

						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA3')); ?>
							<span class="control-text-value badge badge-important"><?php echo $media['creation']; ?></span>
						<?php echo $vik->closeControl(); ?>

						<div class="control">
							<img src="<?php echo VREMEDIA_URI . $media['name'] . '?' . time(); ?>" />
						</div>

					<?php echo $vik->closeFieldset(); ?>
				</div>

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMEDIAFIELDSET3'), 'form-horizontal'); ?>

						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA2')); ?>
							<span class="control-text-value">
								<span class="badge badge-info"><?php echo $thumb['size']; ?></span>
								&nbsp;
								<span class="badge badge-success"><?php echo $thumb['width'] . 'x' . $thumb['height'] . ' pixel'; ?></span>
							</span>
						<?php echo $vik->closeControl(); ?>

						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA3')); ?>
							<span class="control-text-value badge badge-important"><?php echo $thumb['creation']; ?></span>
						<?php echo $vik->closeControl(); ?>

						<div class="control">
							<img src="<?php echo VREMEDIA_SMALL_URI . $thumb['name'] . '?' . time(); ?>" />
						</div>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="media" value="<?php echo $media['name']; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRMEDIAACTION0');
?>

<script type="text/javascript">

	jQuery(document).ready(function(){

		jQuery('#vr-media-action').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRMEDIAACTION0'),
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-media-action').on('change', function(){
			var val = '';

			if ((val = jQuery(this).val()).length) {
				jQuery('.vr-action-child').show();
				jQuery('.vr-action-child-field').addClass('required');
				validator.registerFields('.vr-action-child-field');

				if (val == '3') {
					jQuery('.vr-replace-child').show();
				} else {
					jQuery('.vr-replace-child').hide();
				}
			} else {
				jQuery('.vr-action-child, .vr-replace-child').hide();
				validator.unregisterFields('.vr-action-child-field');
			}
		});

	});

	function resizeValueChanged(s) {
		jQuery('#vr-resize-field').prop('readonly', s ? false : true);
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
