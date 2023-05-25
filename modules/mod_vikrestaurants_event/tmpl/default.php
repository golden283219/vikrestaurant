<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_event
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$images_list = array();

if (!empty($special_day['images']))
{
	$images_list = explode(';;', $special_day['images']);
}

$image_to_show = '';

if (count($images_list))
{
	$image_to_show = $images_list[rand(0, count($images_list) - 1)];
}

$time_choosen = $params->get('special_day_time');

$date_f = VikRestaurants::getDateFormat();

$now = VikRestaurants::now();

/**
 * In case the calendar is enabled, we should pre-select the best day.
 * For example, in case the start publishing of the special day is expired,
 * we should find the closest date.
 *
 * @since 1.4
 */
$bestday = max(array($special_day['start_ts'], $now));
$bestday = min(array($special_day['end_ts'], $bestday));

/**
 * Use the module ID instead the module_id parameters, which
 * is no longer available within the module settings.
 *
 * @since 1.3.1
 */
$module_id = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

?>	
<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=search&Itemid=' . $params->get('itemid', 0)); ?>" method="POST" name="eventform" id="vr-event-form<?php echo $module_id; ?>">

	<div class="vrspeventbody">
		<div class="vrspeventimageimage">
			<?php
			if (!empty($image_to_show))
			{
				?>
				<img src="<?php echo VREMEDIA_URI . $image_to_show; ?>" />
				<?php
			}
			?>
		</div>
	</div>

	<?php
	if ((bool) $params->get('enable_calendar'))
	{
		JHtml::_('vrehtml.sitescripts.calendar', '#vrdateevmod' . $module_id . ':input');
		?>
		<div class="vrspevent-calendar-div">
			<input type="text" name="date" class="vr-event-calendar" id="vrdateevmod<?php echo $module_id; ?>" value="<?php echo date($date_f, $bestday); ?>" />
		</div>
		<?php
	}
	else
	{
		?>
		<input type="hidden" name="date" value="<?php echo date($date_f, $bestday); ?>" /> 
		<?php
	}
	?>
	
	<div class="vrspeventfooter">
		<?php
		if (empty($time_choosen))
		{
			JHtml::_('vrehtml.sitescripts.updateshifts', $restaurant = 1);
			?>
			<div class="vrspeventtimediv vre-select-wrapper half-size">
				<?php
				// get available times
				$times = JHtml::_('vikrestaurants.times', $restaurant = 1, $bestday);

				$attrs = array(
					'id'    => 'vrhourevmod' . $module_id,
					'class' => 'vre-select',
				);

				// display times dropdown
				echo JHtml::_('vrehtml.site.timeselect', 'hourmin', null, $times, $attrs);
				?>
			</div>
			<?php
		}
		else
		{
			?>
			<input type="hidden" name="hourmin" value="<?php echo $time_choosen; ?>" />
			<?php
		}
		?>
		
		<div class="vrspeventpeoplediv vre-select-wrapper<?php echo $time_choosen ? '' : ' half-size'; ?>">
			<?php
			// get people options
			$options = JHtml::_('vikrestaurants.people');

			$attrs = array(
				'id'    => 'vrpeopleevmod' . $module_id,
				'class' => 'vre-select',
			);

			// display times dropdown
			echo JHtml::_('vrehtml.site.peopleselect', 'people', null, $attrs);
			?>
		</div>

		<?php
		/**
		 * Added support for safe distance disclaimer.
		 *
		 * @since 1.4
		 */
		if (VREFactory::getConfig()->getBool('safedistance'))
		{
			// ask to the customer whether all the members of the
			// group belong to the same family due to COVID-19
			// prevention measures
			?>
			<div class="vrspeventsafedist">
				<input type="checkbox" name="family" id="vrfamilyevmod<?php echo $module_id; ?>" value="1" />

				<label for="vrfamilyevmod<?php echo $module_id; ?>">
					<?php echo JText::_('VRSAFEDISTLABEL'); ?>
					<a href="javascript:void(0);" class="vrfamilyevmod-help" title="<?php echo htmlspecialchars(JText::_('VRSAFEDISTLABEL_TIP')); ?>">
						<i class="fas fa-exclamation-triangle"></i>
					</a>
				</label>
			</div>
			<?php
		}
		?>
		
		<div class="vrspeventbooknowdiv">
			<a href="javascript: void(0);" onClick="document.getElementById('vr-event-form<?php echo $module_id; ?>').submit();">
				<?php echo JText::_('VREVENTBOOKNOW'); ?>
			</a>
		</div>
	</div>

</form>

<script type="text/javascript">

	// JQUERY CALENDAR
		
	jQuery(document).ready(function() {

		// get datepciker
		var datepicker = jQuery("#vr-event-form<?php echo $module_id; ?> .vr-event-calendar:input");

		// create range of dates
		var minDate = <?php echo JHtml::_('vrehtml.sitescripts.jsdate', $special_day['start_ts']); ?>;
		var maxDate = <?php echo JHtml::_('vrehtml.sitescripts.jsdate', $special_day['end_ts']); ?>;

		var today = new Date();

		// set up min/max dates from calendar
		datepicker.datepicker('option', 'minDate', minDate > today ? minDate : today);
		datepicker.datepicker('option', 'maxDate', maxDate);

		datepicker.on('change', function() {
			// refresh times
			vrUpdateWorkingShifts(datepicker, '#vrhourevmod<?php echo $module_id; ?>');
		});

		jQuery('.vrfamilyevmod-help').tooltip();
		
	});
	
</script>
