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

$vik = VREApplication::getInstance();

$editor = $vik->getEditor();

?>

<div class="row-fluid">
	
	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('VRTKMENUFIELDSET1')); ?>
		
			<!-- TITLE - Text -->
			<?php echo $vik->openControl(JText::_('VRMANAGETKMENU1') . '*'); ?>
				<input type="text" name="title" class="required" value="<?php echo $this->escape($menu->title); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>

			<!-- ALIAS - Text -->
			<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
				<input type="text" name="alias" value="<?php echo $this->escape($menu->alias); ?>" size="40" />
			<?php echo $vik->closeControl(); ?>
			
			<!-- PUBLISHED - Radio Button -->
			<?php
			$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $menu->published);
			$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$menu->published);
			
			echo $vik->openControl(JText::_('VRMANAGETKMENU12'));
			echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
			echo $vik->closeControl();
			?>

			<!-- START PUBLISHING - Calendar -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGETKMENU23'));
			echo $vik->calendar($menu->publish_up == -1 ? '' : $menu->publish_up, 'publish_up', 'vr-start-pub', null, array('showTime' => true));
			echo $vik->closeControl();
			?>

			<!-- FINISH PUBLISHING - Calendar -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGETKMENU24'));
			echo $vik->calendar($menu->publish_down == -1 ? '' : $menu->publish_down, 'publish_down', 'vr-finish-pub', null, array('showTime' => true));
			echo $vik->closeControl();
			?>

			<!-- TAXES TYPE - Dropdown -->
			<?php
			$options = array(
				JHtml::_('select.option', 0, 'VRTKMENUTAXESOPT1'),
				JHtml::_('select.option', 1, 'VRTKMENUTAXESOPT2'),
			);

			echo $vik->openControl(JText::_('VRMANAGETKMENU22')); ?>
				<select name="taxes_type" id="vrtk-taxestype-sel">
					<?php echo JHtml::_('select.options', $options, 'value', 'text', $menu->taxes_type, true); ?>
				</select>

				<div class="input-append currency-field" id="vrtk-taxes-amount" style="<?php echo ($menu->taxes_type ? '' : 'display: none;'); ?>">
					<input type="number" name="taxes_amount" value="<?php echo $menu->taxes_amount; ?>" min="0" max="100" step="any" />
					
					<button type="button" class="btn">%</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<?php
			/**
			 * Trigger event to display custom HTML.
			 * In case it is needed to include any additional fields,
			 * it is possible to create a plugin and attach it to an event
			 * called "onDisplayViewTkmenu". The event method receives the
			 * view instance as argument.
			 *
			 * @since 1.8
			 */
			echo $this->onDisplayManageView();
			?>
		
		<?php echo $vik->closeFieldset(); ?>
	</div>

	<div class="span6">
		<?php
		echo $vik->openFieldset(JText::_('VRMANAGETKMENU2'));
		echo $editor->display('description', $menu->description, 400, 200, 20, 20);
		echo $vik->closeFieldset();
		?>
	</div>

</div>

<script>

	jQuery(document).ready(function() {

		jQuery('#vrtk-taxestype-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150,
		});

		jQuery('#vrtk-taxestype-sel').on('change', function() {
			if (jQuery(this).val() == 1) {
				jQuery('#vrtk-taxes-amount').show();
			} else {
				jQuery('#vrtk-taxes-amount').hide();
			}
		});

	});

</script>
