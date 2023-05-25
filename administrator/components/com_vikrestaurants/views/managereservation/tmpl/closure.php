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

JHtml::_('vrehtml.scripts.updateshifts', 1, '_vrUpdateWorkingShifts');

$reservation = $this->reservation;

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

$editor = $vik->getEditor();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<div class="row-fluid">
	
		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRMANAGERESERVATIONTITLE1')); ?>
			
				<!-- DATE - Calendar -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGERESERVATION13') . '*');

				$attributes = array();
				$attributes['class'] 	= 'required';
				$attributes['onChange'] = "vrUpdateWorkingShifts();";

				echo $vik->calendar($reservation->date, 'date', 'vrdatefilter', null, $attributes);

				echo $vik->closeControl();
				?>

				<!-- TIME - Dropdown -->
				<?php
				// calculate available times
				$times = JHtml::_('vikrestaurants.times', 1, $reservation->date);

				$attrs = array(
					'id'    => 'vr-hour-sel',
					'class' => 'required',
				);

				echo $vik->openControl(JText::_('VRMANAGERESERVATION14') . '*');
				echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $reservation->hourmin, $times, $attrs);
				echo $vik->closeControl();
				?>

				<!-- STAY TIME - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION25')); ?>
					<input type="number" name="stay_time" value="<?php echo $reservation->stay_time; ?>" min="15" max="9999" step="5" />
					&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?>
				<?php echo $vik->closeControl(); ?>

				<!-- TABLE - Dropdown -->
				<?php echo $vik->openControl(JText::_('VRMANAGERESERVATION5') . '*'); ?>
					
					<select name="id_table" id="vr-table-sel" class="required">
						<option class="placeholder"></option>
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

				<?php echo $vik->closeControl(); ?>

				<!-- RE-OPEN - Checkbox -->
				<?php
				$yes = $vik->initRadioElement('', JText::_('JYES'), false, 'onclick="reopenValueChanged(1);"');
				$no  = $vik->initRadioElement('', JText::_('JNO'), true, 'onclick="reopenValueChanged(0);"');

				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGERESERVATION26'),
					'content' => JText::_('VRMANAGERESERVATION26_HELP'),
				));

				echo $vik->openControl(JText::_('VRMANAGERESERVATION26') . $help);
				echo $vik->radioYesNo('reopen', $yes, $no);
				echo $vik->closeControl();
				?>

			<?php echo $vik->closeFieldset(); ?>
		</div>

		<!-- NOTES -->
		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRMANAGERESERVATIONTITLE3')); ?>
				<div class="control-group">
					<?php echo $editor->display('notes', $reservation->notes, 400, 200, 70, 20); ?>
				</div>
			<?php echo $vik->closeFieldset(); ?>
		</div>

	</div>
	
	<input type="hidden" name="from" value="<?php echo $this->returnTask; ?>" />
	<input type="hidden" name="id" value="<?php echo $reservation->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_FILTER_SELECT_TABLE');
?>

<script>

	var IS_AJAX_CALLING = false;
	
	jQuery(document).ready(function() {

		jQuery('#vr-hour-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-table-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_TABLE'),
			allowClear: false,
			width: 200,
			formatResult: formatTablesSelect,
			formatSelection: formatTablesSelect,
			escapeMarkup: function(m) { return m; },
		});

		// update working shifts
		// @deprecated (the event is triggered directly from the calendar)
		jQuery('#vrdatefilter').on('change', function() {
			vrUpdateWorkingShifts();
		});

		// do not submit the form in case we have any pending requests
		validator.addCallback(function() {
			if (IS_AJAX_CALLING) {
				/**
				 * @todo 	Should we prompt an alert?
				 * 			e.g. "Please wait for the request completion."
				 */

				return false;
			}

			return true;
		});

	});

	function vrUpdateWorkingShifts() {
		// making an AJAX request
		IS_AJAX_CALLING = true;

		_vrUpdateWorkingShifts(
			'#vrdatefilter',
			'#vr-hour-sel',
			function(resp) {
				// request has finished
				IS_AJAX_CALLING = false;
			},
			function(error) {
				// request has finished
				IS_AJAX_CALLING = false;
			}
		);
	}

	function formatTablesSelect(opt) {
		if (!opt.id) {
			// optgroup
			return opt.text;
		}

		var html = opt.text;

		if (jQuery(opt.element).data('shared') == '1') {
			html = '<i class="fas fa-users" style=""></i> ' + html;
		}

		return html;
	}

	function reopenValueChanged(is) {
		// enable/disable other options depending on the "RE-OPEN" checkbox status
		jQuery('input,select')
			.not('input[type="hidden"]')
			.not('input[name="reopen"]')
			.prop('disabled', is ? true : false);
	}
	
	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate() || jQuery('input[name="reopen"]').is(':checked')) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}
	
</script>
