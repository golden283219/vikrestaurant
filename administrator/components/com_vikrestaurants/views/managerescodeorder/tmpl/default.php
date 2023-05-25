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

$status = $this->status;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span8">
			<?php echo $vik->openEmptyFieldset(); ?>
			
				<!-- CODE - Dropdown -->
				<?php
				$codes = JHtml::_('vikrestaurants.rescodes', $status->group);

				echo $vik->openControl(JText::_('VRMANAGERESCODE2') . '*'); ?>
					<select name="id_rescode" id="vr-rescode-sel" class="required">
						<?php echo JHtml::_('select.options', $codes, 'value', 'text', $status->id_rescode); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewRescodeorder". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				echo $this->onDisplayManageView();
				?>
				
				<!-- NOTES - Editor -->
				<?php echo $vik->openControl(JText::_('VRMANAGERESCODE5')); ?>
					<textarea name="notes" class="full-width" style="height: 160px;resize: vertical;"><?php echo $status->notes; ?></textarea>
				<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>

	<input type="hidden" name="id_order" value="<?php echo $status->id_order; ?>" />

	<input type="hidden" name="group" value="<?php echo $status->group; ?>" />
	
	<input type="hidden" name="id" value="<?php echo $status->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />

</form>

<script type="text/javascript">
	
	// VALIDATION

	jQuery(document).ready(function(){

		jQuery('#vr-rescode-sel').select2({
			allowClear: false,
			width: 300,
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
