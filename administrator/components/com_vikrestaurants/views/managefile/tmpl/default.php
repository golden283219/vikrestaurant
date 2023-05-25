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

$vik = VREApplication::getInstance();

$code_mirror = $vik->getCodeMirror('content', $this->content);

$data = array(
	'name'   => basename($this->file),
	'path'   => $this->file,
	'base64' => base64_encode($this->file),
);

?>

<form action="index.php" method="POST" name="adminForm" id="adminForm">

	<?php
	if ($this->blank)
	{
		?>
		<div class="btn-toolbar vr-btn-toolbar" style="display:none;">
			<div class="btn-group pull-left">
				<button type="button" class="btn btn-success" name="tmplSaveButton" onclick="fileSaveButtonPressed(this);">
					<i class="icon-apply"></i>&nbsp;<?php echo JText::_('VRSAVE'); ?>
				</button>

				<button type="button" class="btn btn-success" name="tmplSaveCopyButton" onclick="fileSaveAsCopyButtonPressed(this);">
					<i class="icon-apply"></i>&nbsp;<?php echo JText::_('VRSAVE'); ?>
				</button>
			</div>
		</div>
		<?php
	}
	?>
	
	<div class="managefile-wrapper" style="padding:0 10px;">

		<h3><?php echo basename($this->file); ?></h3>
		
		<div class="vr-file-box">
			<?php echo $code_mirror; ?>
		</div>
	
	</div>
	
	<input type="hidden" name="file" value="<?php echo base64_encode($this->file); ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />

	<?php
	if ($this->blank)
	{
		?><input type="hidden" name="tmpl" value="component" /><?php
	}
	?>
	
</form>

<?php
JText::script('VREXPORTRES1');
?>

<script>
	
	function fileSaveButtonPressed(button) {
		if (jQuery(button).prop('disabled')) {
			// button already submitted
			return false;
		}

		// disable button
		jQuery(button).prop('disabled', true);

		Joomla.submitform('file.save', document.adminForm);
	}

	function fileSaveAsCopyButtonPressed(button) {
		if (jQuery(button).prop('disabled')) {
			// button already submitted
			return false;
		}

		// disable button
		jQuery(button).prop('disabled', true);

		// ask for the new name
		var name = prompt(Joomla.JText._('VREXPORTRES1'), 'file.php');

		if (!name) {
			// invalid name
			return false;
		}

		if (!name.match(/\.php$/i)) {
			// append ".php" if not specified
			name += '.php';
		}

		jQuery('#adminForm').append('<input type="hidden" name="dir" value="<?php echo base64_encode(dirname($this->file)); ?>" />');
		jQuery('#adminForm').append('<input type="hidden" name="filename" value="' + name + '" />');

		Joomla.submitform('file.savecopy', document.adminForm);
	}

	<?php
	if ($this->blank)
	{
		?>
		// transfer submit buttons instances to parent for being clicked
		window.parent.modalFileSaveButton     = document.adminForm.tmplSaveButton;
		window.parent.modalFileSaveCopyButton = document.adminForm.tmplSaveCopyButton;

		// transfer saved file path to parent
		window.parent.modalSavedFile = <?php echo json_encode($data); ?>;
		<?php
	}
	else
	{
		?>
		Joomla.submitbutton = function(task) {
			if (task == 'file.savecopy') {
				fileSaveAsCopyButtonPressed(null);
			} else {
				Joomla.submitform(task, document.adminForm);
			}
		}
		<?php
	}
	?>
	
</script>
