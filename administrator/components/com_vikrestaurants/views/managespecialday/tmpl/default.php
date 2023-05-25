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
JHtml::_('vrehtml.assets.fancybox');

$specialday = $this->specialday;

if ($specialday->start_ts == -1 || $specialday->end_ts == -1)
{
	$specialday->start_ts = '';
	$specialday->end_ts	  = '';
}

$vik = VREApplication::getInstance();

$elem_yes = $vik->initRadioElement('', '', 1);
$elem_no  = $vik->initRadioElement('', '', 0);

$config = VREFactory::getConfig();

$curr_symb = $config->get('currencysymb');

?>

<form name="adminForm" id="adminForm" action="index.php" method="post">

	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('JDETAILS')); ?>
			
				<!-- GROUP - Dropdown -->
				<?php
				$groups = JHtml::_('vrehtml.admin.groups', array(1, 2));

				echo $vik->openControl(JText::_('VRMANAGESPDAY16')); ?>
					<select name="group" id="vr-group-sel">
						<?php echo JHtml::_('select.options', $groups, 'value', 'text', $specialday->group, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGESPDAY1') . '*'); ?>
					<input type="text" class="required" name="name" value="<?php echo $this->escape($specialday->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- START - Calendar -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGESPDAY2'));
				echo $vik->calendar($specialday->start_ts, 'start_ts', 'start_ts');
				echo $vik->closeControl();
				?>
				
				<!-- END - Calendar -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGESPDAY3'));
				echo $vik->calendar($specialday->end_ts, 'end_ts', 'end_ts');
				echo $vik->closeControl();
				?>

				<!-- WORKING SHIFTS - Dropdown -->
				<?php
				/**
				 * Load all the working shifts because the user might
				 * change the group while creating a new special day
				 * 
				 * @since 1.8.1
				 */
				$shifts_rs = JHtml::_('vrehtml.admin.shifts', 1);
				$shifts_tk = JHtml::_('vrehtml.admin.shifts', 2);

				// do not display field in case of continuous opening time
				if ($shifts_rs || $shifts_tk)
				{
					echo $vik->openControl(JText::_('VRMANAGESPDAY4'));
					?>
						<select name="<?php echo $specialday->group == 1 ? 'working_shifts[]' : ''; ?>" class="vrwsselect restaurant-params" multiple="multiple" style="<?php echo $specialday->group == 1 ? '' : 'display:none;'; ?>">
							<?php echo JHtml::_('select.options', $shifts_rs, 'value', 'text', $specialday->working_shifts); ?>
						</select>

						<select name="<?php echo $specialday->group == 2 ? 'working_shifts[]' : ''; ?>" class="vrwsselect takeaway-params" multiple="multiple" style="<?php echo $specialday->group == 2 ? '' : 'display:none;'; ?>">
							<?php echo JHtml::_('select.options', $shifts_tk, 'value', 'text', $specialday->working_shifts); ?>
						</select>
					<?php
					echo $vik->closeControl();
				}
				?>
				
				<!-- DAYS FILTER - Dropdown -->
				<?php echo $vik->openControl(JText::_('VRMANAGESPDAY5')); ?>
					<select name="days_filter[]" id="vrdfselect" multiple="multiple">
						<?php
						$days = JHtml::_('vikrestaurants.days');

						echo JHtml::_('select.options', $days, 'value', 'text', $specialday->days_filter);
						?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- MARK ON CALENDAR - Radio Button -->
				<?php 
				$elem_yes = $vik->initRadioElement('', $elem_yes->label, $specialday->markoncal);
				$elem_no  = $vik->initRadioElement('', $elem_no->label, !$specialday->markoncal);
				
				echo $vik->openControl(JText::_('VRMANAGESPDAY12'));
				echo $vik->radioYesNo('markoncal', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- IGNORE CLOSING DAYS - Radio Button -->
				<?php 
				$elem_yes = $vik->initRadioElement('', $elem_yes->label, $specialday->ignoreclosingdays);
				$elem_no  = $vik->initRadioElement('', $elem_no->label, !$specialday->ignoreclosingdays);
				
				echo $vik->openControl(JText::_('VRMANAGESPDAY13')); 
				echo $vik->radioYesNo('ignoreclosingdays', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- PRIORITY - Dropdown -->
				<?php 
				$options = array();
				for ($i = 1; $i <= 3; $i++)
				{
					$options[] = JHtml::_('select.option', $i, 'VRPRIORITY' . $i);
				}
				
				echo $vik->openControl(JText::_('VRMANAGESPDAY20')); ?>
					<select name="priority" id="vr-priority-sel">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $specialday->priority, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->closeFieldset(); ?>
		</div>

		<div class="span6">

			<div class="row-fluid" id="restaurant-params" style="<?php echo $specialday->group == 1 ? '' : 'display:none;'; ?>">

				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('JFIELD_PARAMS_LABEL')); ?>

						<!-- ASK FOR DEPOSIT - Select -->
						<?php
						$elements = array(
							JHtml::_('select.option', 0, 'VRCONFIGLOGINREQ1'),
							JHtml::_('select.option', 1, 'VRTKCONFIGOVERLAYOPT2'),
							JHtml::_('select.option', 2, 'VRPEOPLEALLOPT2'),
						);

						$ask = min(array(2, $specialday->askdeposit));

						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGECONFIG89'),
							'content' => JText::_('VRMANAGECONFIG89_HELP'),
						));

						echo $vik->openControl(JText::_('VRMANAGECONFIG89') . $help, 'multi-field'); ?>
							<select id="askdeposit">
								<?php echo JHtml::_('select.options', $elements, 'value', 'text', $ask, true); ?>
							</select>

							<span class="vr-askdeposit" style="<?php echo ($specialday->askdeposit > 1 ? '' : 'display: none;'); ?>">
								<input type="number" name="askdeposit" value="<?php echo $specialday->askdeposit; ?>" min="<?php echo $ask; ?>" max="9999" />
							</span>
						<?php echo $vik->closeControl(); ?>

						<!-- DEPOSIT COST - Number -->
						<?php
						$control = array();
						$control['style'] = $specialday->askdeposit ? '' : 'display:none;';

						echo $vik->openControl(JText::_('VRMANAGESPDAY6'), 'vr-deposit-child', $control); ?>
							<div class="input-prepend currency-field">
								<button type="button" class="btn"><?php echo $curr_symb; ?></button>

								<input type="number" name="depositcost" value="<?php echo $specialday->depositcost; ?>" size="6" min="0" max="99999999" step="any" />
							</div>
						<?php echo $vik->closeControl(); ?>
						
						<!-- COST PER PERSON - Radio Button -->
						<?php 
						$elem_yes = $vik->initRadioElement('', $elem_yes->label, $specialday->perpersoncost);
						$elem_no  = $vik->initRadioElement('', $elem_no->label, !$specialday->perpersoncost);
						
						echo $vik->openControl(JText::_('VRMANAGESPDAY7'), 'vr-deposit-child', $control);
						echo $vik->radioYesNo('perpersoncost', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>
						
						<!-- PEOPLE ALLOWED - Radio Button -->
						<?php 
						$options = array(
							JHtml::_('select.option', 1, 'VRPEOPLEALLOPT1'),
							JHtml::_('select.option', 2, 'VRPEOPLEALLOPT2'),
						);

						$allowed = $specialday->peopleallowed != -1 ? 2 : 1;
						
						echo $vik->openControl(JText::_('VRMANAGESPDAY21'), 'multi-field'); ?>
							<select name="peopallradio" id="vr-peopleall-select">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $allowed, true); ?>
							</select>

							<input type="number" name="peopleallowed" value="<?php echo $specialday->peopleallowed; ?>" min="0" max="9999" id="vr-people-allowed-text" 
							style="<?php echo ($allowed == 1 ? 'display:none;' : ''); ?>" />
						<?php echo $vik->closeControl(); ?>

						<!-- IMAGE - File -->
						<?php
						echo $vik->openControl(JText::_('VRMANAGESPDAY17'));
						echo JHtml::_('vrehtml.mediamanager.field', 'images[]', $specialday->images, null, array('multiple' => true));
						echo $vik->closeControl();
						?>
						
						<!-- CHOOSABLE MENUS - Radio Button -->
						<?php 
						$elem_yes = $vik->initRadioElement('', $elem_yes->label, $specialday->choosemenu, 'onclick="jQuery(\'.vr-choosemenu-child\').show();"');
						$elem_no  = $vik->initRadioElement('', $elem_no->label, !$specialday->choosemenu, 'onclick="jQuery(\'.vr-choosemenu-child\').hide();"');
						
						echo $vik->openControl(JText::_('VRMANAGESPDAY19'));
						echo $vik->radioYesNo('choosemenu', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- FREEDOM OF CHOICE - Radio Button -->
						<?php 
						$elem_yes = $vik->initRadioElement('', $elem_yes->label, $specialday->freechoose);
						$elem_no  = $vik->initRadioElement('', $elem_no->label, !$specialday->freechoose);
						
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGESPDAY23'),
							'content' => JText::_('VRMANAGESPDAY23_DESC'),
						));

						$control = array();
						$control['style'] = $specialday->choosemenu ? '' : 'display:none;';

						echo $vik->openControl(JText::_('VRMANAGESPDAY23') . $help, 'vr-choosemenu-child', $control);
						echo $vik->radioYesNo('freechoose', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- MENUS - Dropdown -->
						<?php
						$options = array();

						foreach ($this->restaurantMenus as $menu)
						{
							$key = JText::_($menu->special_day == 1 ? 'VRMANAGESPDAY14' : 'VRMANAGESPDAY15');

							if (!isset($options[$key]))
							{
								$options[$key] = array();
							}

							$options[$key][] = JHtml::_('select.option', $menu->id, $menu->name);	
						}

						$args = array(
							'id' 			=> 'vr-restaurant-menus',
							'list.attr' 	=> array('multiple' => true),
							'group.items' 	=> null,
							'list.select'	=> $specialday->group == 1 ? $specialday->menus : array(),
						);

						echo $vik->openControl(JText::_('VRMANAGESPDAY9'));
						echo JHtml::_('select.groupedList', $options, 'id_menu[]', $args);
						echo $vik->closeControl();
						?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<div class="row-fluid" id="takeaway-params" style="<?php echo $specialday->group == 2 ? '' : 'display:none;'; ?>">

				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('JFIELD_PARAMS_LABEL')); ?>

						<!-- MINIMUM COST PER ORDER - Select -->
						<?php
						$elements = array(
							JHtml::_('select.option', 0, 'VRSPDAYSERVICEOPT1'),
							JHtml::_('select.option', 1, 'VRPEOPLEALLOPT2'),
						);

						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGECONFIGTK5'),
							'content' => JText::_('VRMANAGECONFIGTK5_OVERRIDE_HELP'),
						));

						$ask = (float) $specialday->minorder ? 1 : 0;

						echo $vik->openControl(JText::_('VRMANAGECONFIGTK5') . $help, 'multi-field'); ?>
							<select id="askminorder">
								<?php echo JHtml::_('select.options', $elements, 'value', 'text', $ask, true); ?>
							</select>

							<span class="vr-askminorder" style="<?php echo ($ask ? '' : 'display: none;'); ?>">
								<input type="number" name="minorder" value="<?php echo $specialday->minorder; ?>" min="<?php echo $ask; ?>" max="999999" step="any" />
							</span>
						<?php echo $vik->closeControl(); ?>

						<!-- DELIVERY SERVICE - Select -->
						<?php 
						$options = array(
							JHtml::_('select.option', -1, 'VRSPDAYSERVICEOPT1'),
							JHtml::_('select.option', 0, 'VRSPDAYSERVICEOPT2'),
							JHtml::_('select.option', 1, 'VRSPDAYSERVICEOPT3'),
							JHtml::_('select.option', 2, 'VRSPDAYSERVICEOPT4'),
						);
						
						echo $vik->openControl(JText::_('VRMANAGESPDAY22')); ?>
							<select name="delivery_service" id="vr-service-sel">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $specialday->delivery_service, true); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- MENUS - Dropdown -->
						<?php
						$options = array();

						foreach ($this->takeawayMenus as $menu)
						{
							$key = JText::_($menu->published == 1 ? 'JPUBLISHED' : 'JUNPUBLISHED');

							if (!isset($options[$key]))
							{
								$options[$key] = array();
							}

							$options[$key][] = JHtml::_('select.option', $menu->id, $menu->name);	
						}

						$args = array(
							'id' 			=> 'vr-takeaway-menus',
							'list.attr' 	=> array('multiple' => true),
							'group.items' 	=> null,
							'list.select'	=> $specialday->group == 2 ? $specialday->menus : array(),
						);

						echo $vik->openControl(JText::_('VRMANAGESPDAY9'));
						echo JHtml::_('select.groupedList', $options, 'id_menu[]', $args);
						echo $vik->closeControl();
						?>

						<!-- DELIVERY AREAS - Dropdown -->
						<?php
						$options = array();

						foreach ($this->deliveryAreas as $area)
						{
							$key = JText::_($area->published == 1 ? 'JPUBLISHED' : 'JUNPUBLISHED');

							if (!isset($options[$key]))
							{
								$options[$key] = array();
							}

							$options[$key][] = JHtml::_('select.option', $area->id, $area->name);	
						}

						$args = array(
							'id' 			=> 'vr-takeaway-areas',
							'list.attr' 	=> array('multiple' => true),
							'group.items' 	=> null,
							'list.select'	=> $specialday->group == 2 ? $specialday->delivery_areas : array(),
						);

						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMENUTAKEAWAYDELIVERYAREAS'),
							'content' => JText::_('VRMANAGESPDAYAREAS_HELP'),
						));

						echo $vik->openControl(JText::_('VRMENUTAKEAWAYDELIVERYAREAS') . $help);
						echo JHtml::_('select.groupedList', $options, 'delivery_areas[]', $args);
						echo $vik->closeControl();
						?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<?php
			/**
			 * Trigger event to display custom HTML.
			 * In case it is needed to include any additional fields,
			 * it is possible to create a plugin and attach it to an event
			 * called "onDisplayViewSpecialDay". The event method receives the
			 * view instance as argument.
			 *
			 * @since 1.8
			 */
			$custom = $this->onDisplayManageView();

			if ($custom)
			{
				?>
				<div class="row-fluid">
					<div class="span12">
						<?php
						echo $vik->openFieldset(JText::_('VRE_CUSTOM_FIELDSET'));
						echo $custom;
						echo $vik->closeFieldset();
						?>
					</div>
				</div>
				<?php
			}
			?>

		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $specialday->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRMANAGEMENU24');
JText::script('VRMANAGEMENU25');
JText::script('VRFILTERSELECTMENU');
JText::script('VRMANAGESPDAYAREAS_SELECT');
?>

<script>

	jQuery(document).ready(function(){
		
		jQuery('.vrwsselect').select2({
			placeholder: Joomla.JText._('VRMANAGEMENU24'),
			allowClear: true,
			width: 400,
		});
		
		jQuery('#vrdfselect').select2({
			placeholder: Joomla.JText._('VRMANAGEMENU25'),
			allowClear: true,
			width: 400,
		});
		
		jQuery('#vr-restaurant-menus, #vr-takeaway-menus').select2({
			placeholder: Joomla.JText._('VRFILTERSELECTMENU'),
			allowClear: true,
			width: 400,
		});

		jQuery('#vr-takeaway-areas').select2({
			placeholder: Joomla.JText._('VRMANAGESPDAYAREAS_SELECT'),
			allowClear: true,
			width: 400,
		});

		jQuery('#vr-group-sel, #vr-priority-sel, #vr-service-sel, #vr-peopleall-select, #askdeposit, #askminorder').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#askdeposit').on('change', function() {
			var value = parseInt(jQuery(this).val());

			jQuery('input[name="askdeposit"]').attr('min', value).val(value);

			if (value > 0) {
				jQuery('.vr-deposit-child').show();
			} else {
				jQuery('.vr-deposit-child').hide();
			}

			if (value > 1) {
				jQuery('.vr-askdeposit').show();
			} else {
				jQuery('.vr-askdeposit').hide();
			}
		});

		jQuery('#askminorder').on('change', function() {
			var value = parseInt(jQuery(this).val());

			var input = jQuery('input[name="minorder"]').attr('min', value);

			if (value) {
				input.val(<?php echo $config->getFloat('mincostperorder', 1); ?>);
				jQuery('.vr-askminorder').show();
			} else {
				jQuery('.vr-askminorder').hide();
				input.val(0);
			}
		});

		jQuery('#vr-peopleall-select').on('change', function() {
			if (jQuery(this).val() == '1') {
				jQuery('#vr-people-allowed-text').hide();
				jQuery('#vr-people-allowed-text').val(-1);
			} else {
				jQuery('#vr-people-allowed-text').show();
				jQuery('#vr-people-allowed-text').val(100);
			}
		});

		jQuery('#vr-group-sel').on('change', function(){
			var group = jQuery(this).val();

			if (group == 1) {
				jQuery('#takeaway-params, .takeaway-params').hide();
				jQuery('#restaurant-params, .restaurant-params').show();

				jQuery('.vrwsselect.restaurant-params').attr('name', 'working_shifts[]');
				jQuery('.vrwsselect.takeaway-params').attr('name', '');
			} else {
				jQuery('#restaurant-params, .restaurant-params').hide();
				jQuery('#takeaway-params, .takeaway-params').show();

				jQuery('.vrwsselect.restaurant-params').attr('name', '');
				jQuery('.vrwsselect.takeaway-params').attr('name', 'working_shifts[]');
			}
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
