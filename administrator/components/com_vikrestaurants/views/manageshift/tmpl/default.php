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

$shift = $this->shift;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span8">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGESHIFT1') . '*'); ?>
					<input class="required" type="text" name="name" value="<?php echo $this->escape($shift->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- DISPLAY LABEL - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $shift->showlabel, 'onClick="changeLabelStatus(1);"');
				$elem_no = $vik->initRadioElement('', JText::_('VRNO'), !$shift->showlabel, 'onClick="changeLabelStatus(0);"');
				
				echo $vik->openControl(JText::_('VRMANAGESHIFT5'));
				echo $vik->radioYesNo('showlabel', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- LABEL - Text -->
				<?php
				$control = array();
				$control['style'] = $shift->showlabel ? '' : 'display:none;';

				echo $vik->openControl(JText::_('VRMANAGESHIFT6'), 'vrlabelrow', $control); ?>
					<input type="text" name="label" value="<?php echo $shift->label; ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- FROM HOUR MIN - Form -->
				<?php
				$hours   = JHtml::_('vikrestaurants.hours');
				$minutes = JHtml::_('vikrestaurants.minutes', 5);

				$from = JHtml::_('vikrestaurants.min2time', $shift->from, false);

				echo $vik->openControl(JText::_('VRMANAGESHIFT2') . '*'); ?>
					<select name="from" id="vr-hourfrom-sel" class="short-select required">
						<?php echo JHtml::_('select.options', $hours, 'value', 'text', $from->hour); ?>
					</select>

					<select name="minfrom" id="vr-minfrom-sel" class="short-select required">
						<?php echo JHtml::_('select.options', $minutes, 'value', 'text', $from->min); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- TO HOUR MIN - Form -->
				<?php
				
				$to = JHtml::_('vikrestaurants.min2time', $shift->to, false);
				
				echo $vik->openControl(JText::_('VRMANAGESHIFT3') . '*'); ?>
					<select name="to" id="vr-hourto-sel" class="short-select required">
						<?php echo JHtml::_('select.options', $hours, 'value', 'text', $to->hour); ?>
					</select>

					<select name="minto" id="vr-minto-sel" class="short-select required">
						<?php echo JHtml::_('select.options', $minutes, 'value', 'text', $to->min); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- GROUP - Dropdown -->
				<?php
				$groups = JHtml::_('vrehtml.admin.groups', array(1, 2));

				echo $vik->openControl(JText::_('VRMANAGESHIFT4')); ?>
					<select name="group" id="vr-group-sel">
						<?php echo JHtml::_('select.options', $groups, 'value', 'text', $shift->group, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewShift". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				echo $this->onDisplayManageView();
				?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<div class="span4">
			<?php echo $vik->openEmptyFieldset(); ?>

				<!-- WEEK DAYS - Checkbox -->

				<?php
				$date = new JDate();

				for ($i = 1; $i <= 7; $i++)
				{
					$wd = $i == 7 ? 0 : $i;
					$in = in_array($wd, $shift->days);

					$yes = $vik->initRadioElement('days_' . $i . '_on', '', $in);
					$no  = $vik->initRadioElement('days_' . $i . '_off', '', !$in);

					$yes->value = $wd;
					
					echo $vik->openControl($date->dayToString($wd));
					echo $vik->radioYesNo('days[]', $yes, $no, false);
					echo $vik->closeControl();
				}
				?>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $shift->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script>

	jQuery(document).ready(function(){

		jQuery('select.short-select').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 70,
		});

		jQuery('#vr-group-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

	});
	
	function changeLabelStatus(is) {
		if (is) {
			jQuery('.vrlabelrow').show();
		} else {
			jQuery('.vrlabelrow').hide();
		}
	}

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate(vrValidateBounds)) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

	function vrValidateBounds() {

		var fromHour = jQuery('#vr-hourfrom-sel');
		var fromMin  = jQuery('#vr-minfrom-sel');

		var toHour = jQuery('#vr-hourto-sel');
		var toMin  = jQuery('#vr-minto-sel');

		if (parseInt(fromHour.val()) * 60 + parseInt(fromMin.val()) > parseInt(toHour.val()) * 60 + parseInt(toMin.val())) {

			if (fromHour.val() != toHour.val()) {
				fromHour.addClass('vrrequired');
				toHour.addClass('vrrequired');
			} else {
				fromHour.removeClass('vrrequired');
				toHour.removeClass('vrrequired');
			}

			fromMin.addClass('vrrequired');
			toMin.addClass('vrrequired');

			return false;
		}

		fromHour.removeClass('vrrequired');
		toHour.removeClass('vrrequired');

		fromMin.removeClass('vrrequired');
		toMin.removeClass('vrrequired');

		return true;
	}
	
</script>
