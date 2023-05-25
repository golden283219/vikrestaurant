<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_search
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Use the module ID to support multiple instances.
 *
 * @since 1.4.2
 */
$module_id = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);

/**
 * Use VikRestaurants scripts to handle default search events.
 *
 * @since 1.5
 */
JHtml::_('vrehtml.sitescripts.updateshifts', $restaurant = 1);
JHtml::_('vrehtml.sitescripts.datepicker', '#vrcalendarmod' . $module_id . ':input');

$sel = $last_values;

/**
 * Get Itemid by checking the new property name.
 *
 * @since 1.4.1
 */
$itemid = (int) $params->get('itemid', 0);

if (!$itemid)
{
	$itemid = JFactory::getApplication()->input->getUint('Itemid', 0);
}

$itemid = $itemid ? '&Itemid=' . $itemid : '';

?>

<div class="moduletablevikre">
	
	<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=search' . $itemid); ?>" method="post" id="vr-search-form-<?php echo $module_id; ?>">
		
		<fieldset class="vrformfieldsetmod">
			
			<div class="vrsearchinputdivmod">
				<label class="vrsearchinputlabelmod" for="vrcalendarmod<?php echo $module_id; ?>">
					<?php echo JText::_('VRDATE'); ?>
				</label>
				
				<div class="vrsearchentryinputmod vrmod-search-wrappercal">
					<span class="vrmod-search-iconcal"></span>
					<input class="vrsearchdatemod" type="text" value="<?php echo $sel['date']; ?>" id="vrcalendarmod<?php echo $module_id; ?>" name="date" size="20"/>
				</div>
			</div>

			<div class="vrsearchinputdivmod">
				<label class="vrsearchinputlabelmod" for="vrhourmod<?php echo $module_id; ?>">
					<?php echo JText::_('VRTIME'); ?>
				</label>
				
				<div class="vrsearchentryselectmod vre-select-wrapper">
					<?php
					// get available times
					$times = JHtml::_('vikrestaurants.times', $restaurant = 1, $sel['date']);

					$attrs = array(
						'id'    => 'vrhourmod' . $module_id,
						'class' => 'vrsearchhourmod vre-select',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $sel['hourmin'], $times, $attrs);
					?>
				</div>
			</div>

			<div class="vrsearchinputdivmod">
				<label class="vrsearchinputlabelmod" for="vrpeoplemod<?php echo $module_id; ?>">
					<?php echo JText::_('VRPEOPLE'); ?>
				</label>
				
				<div class="vrsearchentryselectmod vre-select-wrapper">
					<?php
					// get people options
					$options = JHtml::_('vikrestaurants.people');

					$attrs = array(
						'id'    => 'vrpeoplemod' . $module_id,
						'class' => 'vrsearchpeoplemod vre-select',
					);

					// display times dropdown
					echo JHtml::_('vrehtml.site.peopleselect', 'people', $sel['people'], $attrs);
					?>
				</div>
			</div>

			<?php
			/**
			 * Added support for safe distance disclaimer.
			 *
			 * @since 1.5
			 */
			if (VREFactory::getConfig()->getBool('safedistance'))
			{
				// ask to the customer whether all the members of the
				// group belong to the same family due to COVID-19
				// prevention measures
				?>
				<div class="vrsearchinputdivmod checkbox-wrapper">
					<input type="checkbox" name="family" id="vrfamilymod<?php echo $module_id; ?>" value="1" <?php echo $sel['family'] ? 'checked="checked"' : ''; ?> />

					<label for="vrfamilymod<?php echo $module_id; ?>">
						<?php echo JText::_('VRSAFEDISTLABEL'); ?>
						<a href="javascript:void(0);" class="vrfamilymod-help" title="<?php echo htmlspecialchars(JText::_('VRSAFEDISTLABEL_TIP')); ?>">
							<i class="fas fa-exclamation-triangle"></i>
						</a>
					</label>
				</div>
				<?php
			}
			?>

			<div class="vrsearchinputdivmod">
				<button type="submit" class="vrsearchsubmitmod">
					<?php echo JText::_('VRFINDATABLE'); ?>
				</button>
			</div>
			
			<input type="hidden" name="option" value="com_vikrestaurants" />
			<input type="hidden" name="view" value="search" />
		</fieldset>

	</form>

</div>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('#vrcalendarmod<?php echo $module_id; ?>:input').on('change', function() {
			// refresh times
			vrUpdateWorkingShifts('#vrcalendarmod<?php echo $module_id; ?>', '#vrhourmod<?php echo $module_id; ?>');
		});

		jQuery('#vr-search-form-<?php echo $module_id; ?>').on('submit', function() {
			if (jQuery('#vrhourmod<?php echo $module_id; ?>').prop('disabled') === true) {
				<?php
				/**
				 * Prevent form submit while hourmin dropdown is empty.
				 * The system is still retriving the available times.
				 *
				 * @since 1.4.2
				 */
				?>
				return false;
			}
		});

		jQuery('.vrfamilymod-help').tooltip();

	});

</script>
