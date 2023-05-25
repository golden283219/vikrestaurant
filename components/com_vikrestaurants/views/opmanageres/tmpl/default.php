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
JHtml::_('vrehtml.sitescripts.calendar', '.vrcfinput.calendar');
JHtml::_('vrehtml.assets.intltel', '.phone-field');
JHtml::_('vrehtml.assets.fontawesome');

$reservation = $this->reservation;

$input  = JFactory::getApplication()->input;
$from 	= $input->get('from', '', 'string');
$itemid = $input->get('Itemid', 0, 'uint');

$vik = VREApplication::getInstance();

$statuses = JHtml::_('vikrestaurants.orderstatuses');
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
				<button type="button" onClick="vrSaveReservation(0);" id="vrfront-manage-btnsave" class="vrfront-manage-button"><?php echo JText::_('VRSAVE'); ?></button>
			</div>

			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrSaveReservation(1);" id="vrfront-manage-btnsaveclose" class="vrfront-manage-button"><?php echo JText::_('VRSAVEANDCLOSE'); ?></button>
			</div>

			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrEditBill();" id="vrfront-manage-btnbill" class="vrfront-manage-button"><?php echo JText::_('VREDITBILL'); ?></button>
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

			<?php echo $vik->openControl(JText::_('VRPEOPLE') . '*'); ?>
				<div class="vre-select-wrapper">
					<?php
					// get people options
					$options = JHtml::_('vikrestaurants.people');

					$attrs = array(
						'id'    => 'vrpeoplesel',
						'class' => 'vre-select required',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.peopleselect', 'people', $reservation->people, $attrs);
					?>
				</div>
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

			<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION25')); ?>
				<div class="field-value currency">
					<input type="number" name="stay_time" value="<?php echo $reservation->stay_time; ?>" min="15" max="9999" step="5" />
					
					<span><?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</div>
			<?php echo $vik->closeControl(); ?>
		
		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
	
	<div class="vrfront-manage-form">
		<?php echo $vik->openEmptyFieldset(); ?>
		
			<?php echo $vik->openControl(JText::_('VRORDERSTATUS')); ?>
				<div class="vre-select-wrapper">
					<select name="status" class="vre-select">
						<?php echo JHtml::_('select.options', $statuses, 'value', 'text', $reservation->status); ?>
					</select>
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRSTATUSRESCODE')); ?>
				<div class="vre-select-wrapper">
					<select name="rescode" class="vre-select">
						<?php echo JHtml::_('select.options', $rescodes, 'value', 'text', $reservation->rescode); ?>
					</select>

					<input type="hidden" name="prevrescode" value="<?php echo $reservation->rescode; ?>" />
				</div>
			<?php echo $vik->closeControl(); ?>

			<?php
			if ($this->operator->canSeeAll())
			{
				// get operators list
				$options = JHtml::_('vikrestaurants.operators', $group = 1, $login = true);
				// add empty option
				array_unshift($options, JHtml::_('select.option', 0, '--'));

				echo $vik->openControl(JText::_('VROPERATORFIELDSET1')); ?>
					<div class="vre-select-wrapper">
						<select name="id_operator" class="vre-select">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $reservation->id_operator); ?>
						</select>
					</div>
				<?php echo $vik->closeControl();
			}
			else
			{
				// in case the reservation hasn't been assigned yet, use the operator ID
				$id_operator = $reservation->id_operator ? $reservation->id_operator : $this->operator->get('id');
				?>
				<input type="hidden" name="id_operator" value="<?php echo $id_operator; ?>" />
				<?php
			}
			?>
			
			<?php echo $vik->openControl(JText::_('VREDITRESSENDMAIL')); ?>
				<div class="vre-select-wrapper">
					<select name="notifycust" class="vre-select">
						<option value="0"><?php echo JText::_('JNO'); ?></option>
						<option value="1"><?php echo JText::_('JYES'); ?></option>
					</select>
				</div>
			<?php echo $vik->closeControl(); ?>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>

	<div class="vrfront-manage-form">
		<?php echo $vik->openEmptyFieldset(); ?>

			<?php
			$fields = $this->reservation->custom_f;

			foreach ($this->customFields as $cf)
			{						
				if ($cf['type'] != 'separator')
				{
					// control ID: "vrcf" . $cf['id']
					echo $vik->openControl(JText::_($cf['name']));
				}
					
				$_val = '';

				if (!empty($fields[$cf['name']]))
				{
					$_val = $fields[$cf['name']];
				}

				$classes = array('vrcfinput');

				$inputType = 'text';

				if (VRCustomFields::isEmail($cf))
				{
					$classes[] = 'mail-field';

					$inputType = 'email';
				}
				else if (VRCustomFields::isPhoneNumber($cf))
				{
					$classes[] = 'phone-field';

					$inputType = 'tel';
				}

				if ($cf['type'] == 'text')
				{
					?>
					<input
						type="<?php echo $inputType; ?>"
						name="vrcf<?php echo $cf['id']; ?>"
						value="<?php echo $this->escape($_val); ?>"
						size="40"	
						class="<?php echo implode(' ', $classes); ?>"
						data-cfname="<?php echo $this->escape($cf['name']); ?>"
					/>
					<?php
				}
				else if ($cf['type'] == 'textarea')
				{
					?>
					<textarea name="vrcf<?php echo $cf['id']; ?>" rows="5" cols="30" class="vrtextarea" data-cfname="<?php echo $this->escape($cf['name']); ?>"><?php echo $_val; ?></textarea>
					<?php
				}
				else if ($cf['type'] == 'date')
				{
					?>
					<div class="vre-calendar-wrapper">
						<input
							type="text"
							name="vrcf<?php echo $cf['id']; ?>"
							value="<?php echo $this->escape($_val); ?>"
							size="20"
							class="vrcfinput vre-calendar calendar"
							data-cfname="<?php echo $this->escape($cf['name']); ?>"
						/>
					</div>
					<?php
				}
				else if ($cf['type'] == 'select')
				{
					$options = array();

					$answ = array_filter(explode(';;__;;', $cf['choose']));

					foreach ($answ as $aw)
					{
						$options[] = JHtml::_('select.option', $aw, $aw);
					}
					?>
					<div class="vre-select-wrapper">
						<select name="vrcf<?php echo $cf['id']; ?>" class="vr-cf-select vre-select" data-cfname="<?php echo $this->escape($cf['name']); ?>">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $_val); ?>
						</select>
					</div>
					<?php
				}
				else if ($cf['type'] == 'separator')
				{
					?>
					<div class="control-group"><strong><?php echo JText::_($cf['name']); ?></strong></div>
					<?php
				}
				else
				{
					?>
					<input
						type="checkbox"
						name="vrcf<?php echo $cf['id']; ?>"
						value="<?php echo JText::_('VRYES'); ?>"
						data-cfname="<?php echo $this->escape($cf['name']); ?>"
						<?php echo ($_val == JText::_('VRYES') ? 'checked="checked"' : ''); ?>
					/>
					<?php
				}
				
				if ($cf['type'] != 'separator')
				{
					echo $vik->closeControl(); 
				}
			}
			?>

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
		
		jQuery('.vrcfinput').keypress(function(e){
			if (e.keyCode == 13) {
				vrSaveReservation(1);
			}
		});

		validator = new VikFormValidator('#vrmanageform', 'vrinvalid');

		<?php
		if ($reservation->id == 0)
		{
			// auto-focus first input in case of creation
			?>
			jQuery('.vrcfinput').first().focus();
			<?php
		}
		?>

		jQuery('#vrdatefield:input').on('change', function() {
			// refresh times
			vrUpdateWorkingShifts('#vrdatefield', '#vrselecthour');
		});

	});

	function vrCloseQuickReservation() {
		Joomla.submitform('opreservation.cancel', document.manageresform);
	}
	
	function vrEditBill() {
		Joomla.submitform('opreservation.editbill', document.manageresform);
	}
	
	function vrSaveReservation(close) {
		if (validator.validate()) {
			var task = 'save';
			
			if (close) {
				task = 'saveclose';
			}

			Joomla.submitform('opreservation.' + task, document.manageresform);
		}
	}
	
</script>
