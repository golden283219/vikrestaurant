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

$closure = $this->closure;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- ROOM - Dropdown -->
				<?php echo $vik->openControl(JText::_('VRMANAGEROOMCLOSURE1') . '*'); ?>
					<select name="id_room" class="required" id="vr-rooms-sel">
						<?php echo JHtml::_('select.options', $this->rooms, 'id', 'name', $closure->id_room); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- START CLOSURE - Form -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGEROOMCLOSURE2') . '*');
				
				$attr = array();
				$attr['onChange'] = 'vrStartDateChanged();';
				$attr['class']	  = 'required';
				$attr['showTime'] = true;

				echo $vik->calendar($closure->start_ts, 'start_date', 'vrstartdate', null, $attr);
					
				echo $vik->closeControl();
				?>
				
				<!-- END CLOSURE - Form -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGEROOMCLOSURE3') . '*');

				$attr = array();
				$attr['class']	  = 'required';
				$attr['showTime'] = true;

				echo $vik->calendar($closure->end_ts, 'end_date', 'vrenddate', null, $attr);
					
				echo $vik->closeControl();
				?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $closure->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">
	
	jQuery(document).ready(function(){

		jQuery('#vr-rooms-sel').select2({
			allowClear: false,
			width: 300,
		});

	});

	function vrStartDateChanged() {
		if (jQuery('#vrenddate').val().length == 0) {
			jQuery('#vrenddate').val(jQuery('#vrstartdate').val());
		}
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
