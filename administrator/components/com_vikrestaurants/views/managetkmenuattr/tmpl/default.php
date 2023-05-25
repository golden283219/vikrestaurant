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

$attribute = $this->attribute;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKMENUATTR1') . ':'); ?>
					<input type="text" name="name" class="required" value="<?php echo $this->escape($attribute->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- PUBLISHED - Number -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $attribute->published);
				$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$attribute->published);
				
				echo $vik->openControl(JText::_('VRMANAGETKMENUATTR3'));
				echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- ICON - File -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGETKMENUATTR4') . '*');
				echo JHtml::_('vrehtml.mediamanager.field', 'icon', $attribute->icon, null, array('class' => 'required'));
				echo $vik->closeControl();
				?>
				
				<!-- DESCRIPTION - Textarea -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKMENUATTR2')); ?>
					<textarea name="description" style="width: calc(100% - 14px);height:120px;resize:vertical;" maxlength="512"><?php echo $attribute->description; ?></textarea>
				<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewTkmenuattr". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			?>
			<div class="span6">
				<?php
				echo $vik->openEmptyFieldset();
				echo $custom;
				echo $vik->closeEmptyFieldset();
				?>
			</div>
			<?php
		}
		?>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $attribute->id; ?>" />
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
