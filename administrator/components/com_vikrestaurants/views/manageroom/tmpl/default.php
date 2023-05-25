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

$room = $this->room;

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRMANAGETABLE4'), 'form-horizontal'); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGEROOM1') . '*'); ?>
					<input class="required" type="text" name="name" value="<?php echo $this->escape($room->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- PUBLISHED - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $room->published == 1);
				$elem_no = $vik->initRadioElement('', JText::_('VRNO'), $room->published == 0);
				
				echo $vik->openControl(JText::_('VRMANAGEROOM3'));
				echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- IMAGE - File -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGEROOM4'));
				echo JHtml::_('vrehtml.mediamanager.field', 'image', $room->image);
				echo $vik->closeControl();
				?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewRoom". The event method receives the
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
			echo $vik->openFieldset(JText::_('VRMANAGEROOM2'), 'form-horizontal');
			echo $editor->display('description', $room->description, 400, 200, 70, 20);
			echo $vik->closeFieldset();
			?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $room->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">
	
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
