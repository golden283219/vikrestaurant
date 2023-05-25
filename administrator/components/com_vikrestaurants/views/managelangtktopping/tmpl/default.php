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
JHtml::_('vrehtml.scripts.selectflags', '#vre-lang-sel');

$vik = VREApplication::getInstance();

$deflang = VikRestaurants::getDefaultLanguage();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>

		<!-- TRANSLATION -->

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRTOPPING')); ?>
			
				<!-- LANGUAGE - Dropdown -->
				<?php
				$elements = JHtml::_('contentlanguage.existing');
				
				echo $vik->openControl(JText::_('VRMANAGELANG4')); ?>
					<select name="tag" id="vre-lang-sel">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', isset($this->struct->tag) ? $this->struct->tag : null); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
					<input type="text" name="name" value="<?php echo $this->escape(isset($this->struct->lang_name) ? $this->struct->lang_name : ''); ?>" size="48" />
				<?php echo $vik->closeControl(); ?>

				<!-- DESCRIPTION - Textarea -->
				<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
					<textarea name="description" class="full-width" style="height:150px;resize:vertical;"><?php echo isset($this->struct->lang_description) ? $this->struct->lang_description : ''; ?></textarea>
				<?php echo $vik->closeControl(); ?>
				
				<input type="hidden" name="id" value="<?php echo isset($this->struct->id_lang) ? $this->struct->id_lang : 0; ?>" />
				
			<?php echo $vik->closeFieldset(); ?>
		</div>

		<!-- ORIGINAL -->

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRE_LANG_ORIGINAL')); ?>
			
				<!-- LANGUAGE - HTML -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGELANG4'));
				echo JHtml::_('vrehtml.site.flag', $deflang);
				echo $vik->closeControl();
				?>
				
				<!-- NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
					<input type="text" value="<?php echo $this->escape($this->struct->name); ?>" size="48" readonly tabindex="-1" />
				<?php echo $vik->closeControl(); ?>

				<!-- DESCRIPTION - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
					<textarea class="full-width" style="height:150px;resize:vertical;" readonly tabindex="-1"><?php echo $this->struct->description; ?></textarea>
				<?php echo $vik->closeControl(); ?>
				
			<?php echo $vik->closeFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id_topping" value="<?php echo $this->struct->id_topping; ?>"/>	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_SAVE_TRX_DEF_LANG');
?>

<script>

	Joomla.submitbutton = function(task) {
		var selected_lang = jQuery('#vre-lang-sel').val();

		if (task.indexOf('save') !== -1 && selected_lang == '<?php echo $deflang; ?>') {
			// saving translation with default language, ask for confirmation
			var r = confirm(Joomla.JText._('VRE_SAVE_TRX_DEF_LANG').replace(/%s/, selected_lang));

			if (!r) {
				return false;
			}
		}

		Joomla.submitform(task, document.adminForm);
	}

</script>
