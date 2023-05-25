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

$separator = $this->separator;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>
				
				<!-- TITLE - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKTOPPINGSEP1') . '*'); ?>
					<input type="text" name="title" class="required" value="<?php echo $this->escape($separator->title); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewTktopseparator". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				echo $this->onDisplayManageView();
				?>
			
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $separator->id; ?>" />
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
