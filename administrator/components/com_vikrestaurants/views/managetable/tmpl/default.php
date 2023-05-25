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

$table = $this->table;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGETABLE1') . '*'); ?>
					<input class="required" type="text" name="name" value="<?php echo $this->escape($table->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- MIN CAPACITY - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGETABLE2') . '*'); ?>
					<input class="required" type="number" name="min_capacity" value="<?php echo $table->min_capacity; ?>" size="4" min="1" max="9999" step="1" />
				<?php echo $vik->closeControl(); ?>
			
				<!-- MAX CAPACITY - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGETABLE3') . '*'); ?>
					<input class="required" type="number" name="max_capacity" value="<?php echo $table->max_capacity; ?>" size="4" min="1" max="9999" step="1" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- CAN BE SHARED - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $table->multi_res == 1, 'onClick="jQuery(\'#vr-cluster-sel\').prop(\'disabled\', true);"');
				$elem_no  = $vik->initRadioElement('', JText::_('JNO'), $table->multi_res == 0, 'onClick="jQuery(\'#vr-cluster-sel\').prop(\'disabled\', false);"');
				
				echo $vik->openControl(JText::_('VRMANAGETABLE12'));
				echo $vik->radioYesNo('multi_res', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>

				<!-- PUBLISHED - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $table->published == 1);
				$elem_no = $vik->initRadioElement('', JText::_('JNO'), $table->published == 0);
				?>
				<?php echo $vik->openControl(JText::_('VRMANAGEROOM3')); ?>
					<?php echo $vik->radioYesNo('published', $elem_yes, $elem_no, false); ?>
				<?php echo $vik->closeControl(); ?>
				
				<!-- ROOM - Dropdown -->
				<?php echo $vik->openControl(JText::_('VRMANAGETABLE4') . '*'); ?>
					<select name="id_room" class="required" id="vr-room-sel">
						<?php echo JHtml::_('select.options', $this->rooms, 'id', 'name', $table->id_room); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- TABLES CLUSTER - Select -->
				<?php
				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGETABLE13'),
					'content' => JText::_('VRMANAGETABLE13_DESC'),
				));

				$options = isset($this->allTables[$table->id_room]) ? $this->allTables[$table->id_room] : array();

				echo $vik->openControl(JText::_('VRMANAGETABLE13') . $help); ?>
					<select name="cluster[]" id="vr-cluster-sel" multiple <?php echo $table->multi_res ? 'disabled' : ''; ?>>
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $this->cluster); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewTable". The event method receives the
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
	
	<input type="hidden" name="id" value="<?php echo $table->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('#vr-room-sel, #vr-cluster-sel').select2({
			allowClear: false,
			width: 300,
		});

		jQuery('#vr-room-sel').on('change', function() {
			// get all room tables
			var tables = <?php echo json_encode($this->allTables); ?>;
			var room   = jQuery(this).val();

			var list = tables.hasOwnProperty(room) ? tables[room] : [];

			var html = '';

			for (var i = 0; i < list.length; i++) {
				html += '<option value="' + list[i].value + '">' + list[i].text + '</option>\n';
			}

			jQuery('#vr-cluster-sel').html(html).select2('val', []);
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
