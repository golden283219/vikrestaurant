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

JHtml::_('behavior.core');
JHtml::_('vrehtml.sitescripts.updateshifts', $restaurant = 1);
JHtml::_('vrehtml.sitescripts.datepicker', '#vrdatefield:input');
JHtml::_('vrehtml.assets.fontawesome');

$reservation = $this->reservation;

$input  = JFactory::getApplication()->input;
$from 	= $input->get('from', '', 'string');
$itemid = $input->get('Itemid', 0, 'uint');

$vik = VREApplication::getInstance();

$statuses = JHtml::_('vikrestaurants.orderstatuses', array('confirmed', 'pending', 'cancelled'));
$rescodes = JHtml::_('vikrestaurants.rescodes', $restaurant = 1);

$rescodes = array_merge(array(JHtml::_('select.option', 0, '--')), $rescodes);

?>

<form name="manageresform" action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opmanageres' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" id="vrmanageform">

	<div class="vrfront-manage-headerdiv">
		<div class="vrfront-manage-titlediv">
			<h2><?php echo JText::_($reservation->id ? 'VREDITQUICKRESERVATION' : 'VRNEWQUICKRESERVATION'); ?></h2>
		</div>
		
		<div class="vrfront-manage-actionsdiv">
			
			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrSaveReservation();" id="vrfront-manage-btnsave" class="vrfront-manage-button"><?php echo JText::_('VRSAVE'); ?></button>
			</div>
			
			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrCloseQuickReservation();" id="vrfront-manage-btnclose" class="vrfront-manage-button"><?php echo JText::_('VRCLOSE'); ?></button>
			</div>

		</div>
	</div>  
	
	<div class="vrfront-manage-form">
		<?php echo $vik->openEmptyFieldset(); ?>
		
			<?php echo $vik->openControl(JText::_('VRDATE') . '*'); ?>
				<div class="vre-calendar-wrapper">
					<input type="text" name="date" id="vrdatefield" class="vre-calendar required" size="20" value="<?php echo $reservation->date; ?>" />
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRTIME') . '*'); ?>
				<div class="vre-select-wrapper">
					<?php
					// get available times
					$times = JHtml::_('vikrestaurants.times', $restaurant = 1, $reservation->date);

					$attrs = array(
						'id'    => 'vrselecthour',
						'class' => 'vre-select required',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $reservation->hourmin, $times, $attrs);
					?>
				</div>
			<?php echo $vik->closeControl(); ?>

			<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION25')); ?>
				<input type="number" name="stay_time" value="<?php echo $reservation->stay_time; ?>" min="15" max="9999" step="5" />
				&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRTABLE') . '*'); ?>
				<div class="vre-select-wrapper">
					<select name="id_table" id="vr-table-sel" class="vre-select required">
						<?php
						foreach ($this->rooms as $room)
						{
							?>
							<optgroup label="<?php echo $room->name; ?>">
								<?php
								foreach ($room->tables as $table)
								{
									// fetch option name
									$name = $table->name . ' (' . $table->min_capacity . '-' . $table->max_capacity . ')';

									?>
									<option value="<?php echo $table->id; ?>" <?php echo $table->id == $reservation->id_table ? 'selected="selected"' : ''; ?> data-capacity="<?php echo $table->min_capacity . '-' . $table->max_capacity; ?>" data-name="<?php echo $name; ?>" data-shared="<?php echo $table->multi_res; ?>">
										<?php echo $name; ?>
									</option>
									<?php
								}
								?>
							</optgroup>
							<?php
						}
						?>
					</select>
				</div>
			<?php echo $vik->closeControl(); ?>

			<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION26')); ?>
				<div class="vre-select-wrapper">
					<select name="reopen" id="vr-reopen-closure" class="vre-select">
						<option value="0"><?php echo JText::_('JNO'); ?></option>
						<option value="1"><?php echo JText::_('JYES'); ?></option>
					</select>
				</div>
			<?php echo $vik->closeControl(); ?>
		
		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
	
	<input type="hidden" name="id" value="<?php echo $reservation->id; ?>" />
	<input type="hidden" name="from" value="<?php echo $from; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="opmanageres" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
</form>

<script>

	var validator;

	jQuery(document).ready(function() {

		validator = new VikFormValidator('#vrmanageform', 'vrinvalid');

		jQuery('#vrdatefield:input').on('change', function() {
			// refresh times
			vrUpdateWorkingShifts('#vrdatefield', '#vrselecthour');
		});

		jQuery('#vr-reopen-closure').on('change', function() {
			// enable/disable other options depending on the "RE-OPEN" checkbox status
			jQuery('input,select')
				.not('input[type="hidden"]')
				.not('select[name="reopen"]')
				.prop('disabled', jQuery(this).val() == 1 ? true : false);
		});

	});

	function vrCloseQuickReservation() {
		Joomla.submitform('opreservation.cancel', document.manageresform);
	}
	
	function vrSaveReservation() {
		if (validator.validate()) {
			Joomla.submitform('oversight.saveclosure', document.manageresform);
		}
	}
	
</script>
