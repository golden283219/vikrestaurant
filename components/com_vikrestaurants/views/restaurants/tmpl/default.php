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

// refresh working shifts every time the date changes
JHtml::_('vrehtml.sitescripts.updateshifts', $restaurant = 1);
JHtml::_('vrehtml.sitescripts.datepicker', '#vrcalendar:input');
JHtml::_('vrehtml.sitescripts.animate');
JHtml::_('vrehtml.assets.fontawesome');

$args = $this->args;

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

// display step bar using the view sub-template
echo $this->loadTemplate('stepbar');
?>

<!-- reservation form -->

<div class="vrreservationform" id="vrsearchform" >

	<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=search' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post">
		
		<fieldset class="vrformfieldset">
			<legend><?php echo JText::_('VRMAKEARESERVATION'); ?></legend>

			<div class="vrsearchinputdiv">
				<label class="vrsearchinputlabel" for="vrcalendar">
					<?php echo JText::_('VRDATE'); ?>
				</label>
				
				<div class="vrsearchentryinput vre-calendar-wrapper">
					<input class="vrsearchdate vre-calendar" type="text" value="<?php echo $args['date']; ?>" id="vrcalendar" name="date" size="20" />
				</div>
			</div>

			<div class="vrsearchinputdiv">
				<label class="vrsearchinputlabel" for="vrhour">
					<?php echo JText::_('VRTIME'); ?>
				</label>

				<div class="vrsearchentryselect vre-select-wrapper">
					<?php
					// get available times
					$times = JHtml::_('vikrestaurants.times', $restaurant = 1, $args['date']);

					$attrs = array(
						'id'    => 'vrhour',
						'class' => 'vre-select',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $args['hourmin'], $times, $attrs);
					?>
				</div>
			</div>

			<div class="vrsearchinputdiv">
				<label class="vrsearchinputlabel" for="vrpeople">
					<?php echo JText::_('VRPEOPLE'); ?>
				</label>

				<div class="vrsearchentryselectsmall vre-select-wrapper">
					<?php
					// get people options
					$options = JHtml::_('vikrestaurants.people');

					$attrs = array(
						'id'    => 'vrpeople',
						'class' => 'vre-select',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.peopleselect', 'people', $args['people'], $attrs);
					?>
				</div>
			</div>

			<div class="vrsearchinputdiv">
				<?php
				if (VREFactory::getConfig()->getBool('safedistance'))
				{
					// ask to the customer whether all the members of the
					// group belong to the same family due to COVID-19
					// prevention measures
					?>
					<div class="vre-family-check">
						<input type="checkbox" name="family" id="vrfamily" value="1" <?php echo $this->family ? 'checked="checked"' : ''; ?> />

						<label for="vrfamily">
							<?php echo JText::_('VRSAFEDISTLABEL'); ?>
							<a href="javascript:void(0);" class="vrfamily-help" title="<?php echo $this->escape(JText::_('VRSAFEDISTLABEL_TIP')); ?>">
								<i class="fas fa-exclamation-triangle"></i>
							</a>
						</label>
					</div>
					<?php
				}
				?>

				<button type="submit" class="vrsearchsubmit">
					<?php echo JText::_('VRFINDATABLE'); ?>
				</button>
			</div>
			
			<input type="hidden" name="option" value="com_vikrestaurants" />
			<input type="hidden" name="view" value="search" />
		</fieldset>

	</form>

</div>

<script>

	jQuery(document).ready(function() {

		jQuery('#vrcalendar:input').on('change', function() {
			// refresh times
			vrUpdateWorkingShifts('#vrcalendar', '#vrhour');
		});

		jQuery('.vrfamily-help').tooltip();
		
	});

</script>
