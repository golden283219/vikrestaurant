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

$menu = $this->menu;

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

$vik = VREApplication::getInstance();

$shifts = JHtml::_('vrehtml.admin.shifts', $restaurantGroup = 1);

?>

<div class="row-fluid">
	
	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('JDETAILS'), 'form-horizontal'); ?>
			
			<!-- NAME - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGEMENU1') . '*'); ?>
				<input type="text" name="name" id="vrnametitle" class="required" value="<?php echo $this->escape($menu->name); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>

			<!-- ALIAS - Text -->
			<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
				<input type="text" name="alias" value="<?php echo $this->escape($menu->alias); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>

			<!-- COST - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRTKCARTOPTION3'),
				'content' => JText::_('VRE_MENU_COST_HELP'),
			));

			echo $vik->openControl(JText::_('VRTKCARTOPTION3') . $help); ?>
				<div class="input-prepend currency-field">
					<button type="button" class="btn"><?php echo VREFactory::getCurrency()->getSymbol(); ?></button>
				
					<input type="number" name="cost" value="<?php echo $menu->cost; ?>" min="0" step="any" />
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<!-- PUBLISHED - Radio Button -->
			<?php
			$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $menu->published == 1);
			$elem_no  = $vik->initRadioElement('', JText::_('JNO'), $menu->published == 0);
			
			echo $vik->openControl(JText::_('VRMANAGEMENU26'));
			echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
			echo $vik->closeControl();
			?>
			
			<!-- CHOOSABLE - Radio Button -->
			<?php
			$elem_yes = $vik->initRadioElement('', $elem_yes->label, $menu->choosable == 1);
			$elem_no  = $vik->initRadioElement('', $elem_no->label, $menu->choosable == 0);
			
			echo $vik->openControl(JText::_('VRMANAGEMENU31'));
			echo $vik->radioYesNo('choosable', $elem_yes, $elem_no, false);
			echo $vik->closeControl();
			?>
			
			<!-- SPECIAL DAY - Radio Button -->
			<?php
			$elem_yes = $vik->initRadioElement('', $elem_yes->label, $menu->special_day == 1, 'onClick="specialDayChanged(1);"');
			$elem_no  = $vik->initRadioElement('', $elem_no->label, $menu->special_day == 0, 'onClick="specialDayChanged(0);"');
			
			echo $vik->openControl(JText::_('VRMANAGEMENU2'));
			echo $vik->radioYesNo('special_day', $elem_yes, $elem_no, false);
			echo $vik->closeControl();

			$control = array();
			$control['style'] = $menu->special_day ? 'display:none;' : '';
			?>
			
			<!-- WORKING SHIFTS - Dropdown -->
			<?php
			if (count($shifts))
			{
				echo $vik->openControl(JText::_('VRMANAGEMENU3'), 'vrspdaychild', $control);
				?>
					<select name="working_shifts[]" id="vrwsselect" multiple="multiple">
						<?php echo JHtml::_('select.options', $shifts, 'value', 'text', $menu->working_shifts); ?>
					</select>
				<?php
				echo $vik->closeControl();
			}
			?>
			
			<!-- DAYS FILTER - Dropdown -->
			<?php echo $vik->openControl(JText::_('VRMANAGEMENU4'), 'vrspdaychild', $control); ?>
				<select name="days_filter[]" id="vrdfselect" multiple="multiple">
					<?php
					$days = JHtml::_('vikrestaurants.days');

					echo JHtml::_('select.options', $days, 'value', 'text', $menu->days_filter);
					?>
				</select>
			<?php echo $vik->closeControl(); ?>
			
			<!-- IMAGE - File -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGEMENU18'));
			echo JHtml::_('vrehtml.mediamanager.field', 'image', $menu->image);
			echo $vik->closeControl();
			?>
			
		<?php echo $vik->closeFieldset(); ?>
	</div>
	
	<div class="span6">
		<?php
		echo $vik->openFieldset(JText::_('VRMANAGEMENU17'), 'form-horizontal');
		echo $editor->display('description', $menu->description, 400, 200, 70, 20);
		echo $vik->closeFieldset();
		?>
	</div>

</div>

<?php
JText::script('VRMANAGEMENU24');
JText::script('VRMANAGEMENU25');
?>

<script>
	
	jQuery(document).ready(function() { 
		
		jQuery("#vrwsselect").select2({
			placeholder: Joomla.JText._('VRMANAGEMENU24'),
			allowClear: true,
			width: 400,
		});
		
		jQuery("#vrdfselect").select2({
			placeholder: Joomla.JText._('VRMANAGEMENU25'),
			allowClear: true,
			width: 400,
		});
		
	});

	function specialDayChanged(is) {
		if (is) {
			jQuery('.vrspdaychild').hide();
		} else {
			jQuery('.vrspdaychild').show();
		}
	}
	
</script>
