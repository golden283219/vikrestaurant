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
			<?php echo $vik->openFieldset(JText::_('VRCUSTFIELD')); ?>
			
				<!-- LANGUAGE - Dropdown -->
				<?php
				$elements = JHtml::_('contentlanguage.existing');
				
				echo $vik->openControl(JText::_('VRMANAGELANG4')); ?>
					<select name="tag" id="vre-lang-sel">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', isset($this->struct->tag) ? $this->struct->tag : null); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- NAME - Text -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGELANG2'));

				if ($this->struct->type != 'separator')
				{
					?>
					<input type="text" name="name" value="<?php echo $this->escape((isset($this->struct->lang_name) ? $this->struct->lang_name : '')); ?>" size="48" />
					<?php
				}
				else
				{
					?>
					<textarea name="name" class="full-width" style="height:150px;resize:vertical;"><?php echo (isset($this->struct->lang_name) ? $this->struct->lang_name : ''); ?></textarea>
					<?php
				}

				echo $vik->closeControl(); ?>
				
				<!-- CHOOSE - Mixed -->
				<?php
				if (VRCustomFields::isSelect($this->struct))
				{
					if ($this->struct->choose)
					{
						$options = explode(';;__;;', $this->struct->choose);
					}
					else
					{
						$options = array();
					}

					if (!empty($this->struct->lang_choose))
					{
						$lang_options = explode(';;__;;', $this->struct->lang_choose);
					}
					else
					{
						$lang_options = array();
					}

					echo $vik->openControl(JText::_('VRCUSTOMFTYPEOPTION4'));
					foreach ($options as $i => $opt)
					{
						?>
						<div style="margin-bottom: 10px;">
							<input type="text" name="choose[]" value="<?php echo $this->escape((!empty($lang_options[$i]) ? $lang_options[$i] : '')); ?>" size="40" />
						</div>
						<?php
					}
					echo $vik->closeControl();
				}
				else if (VRCustomFields::isCheckbox($this->struct))
				{
					echo $vik->openControl(JText::_('VRMANAGECUSTOMF5'));
					?><input type="text" name="poplink" value="<?php echo (isset($this->struct->lang_poplink) ? $this->struct->lang_poplink : ''); ?>" size="48" /><?php
					echo $vik->closeControl();
				}
				else if (VRCustomFields::isSeparator($this->struct))
				{
					echo $vik->openControl(JText::_('VRSUFFIXCLASS'));
					?><input type="text" name="choose" value="<?php echo (isset($this->struct->lang_choose) ? $this->struct->lang_choose : ''); ?>" size="48" /><?php
					echo $vik->closeControl();
				}
				else if (VRCustomFields::isPhoneNumber($this->struct))
				{
					echo $vik->openControl(JText::_('VRMANAGECUSTOMF10'));

					$options = array(
						JHtml::_('select.option', '', ''),
					);

					$options = array_merge($options, JHtml::_('vrehtml.admin.countries'));

					?>
					<select name="choose" id="vr-countrylang-sel">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', (!empty($this->struct->lang_choose) ? $this->struct->lang_choose : '')); ?>
					</select>
					<?php
					echo $vik->closeControl();
				}
				?>
				
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
				<?php
				echo $vik->openControl(JText::_('VRMANAGELANG2'));
				if ($this->struct->type != 'separator')
				{
					?>
					<input type="text" value="<?php echo $this->escape(JText::_($this->struct->name)); ?>" size="48" readonly tabindex="-1" />
					<?php
				}
				else
				{
					?>
					<textarea class="full-width" style="height:150px;resize:vertical;" readonly tabindex="-1"><?php echo JText::_($this->struct->name); ?></textarea>
					<?php
				}
				echo $vik->closeControl();
				?>

				<!-- CHOOSE - Mixed -->
				<?php
				if (VRCustomFields::isSelect($this->struct))
				{
					if ($this->struct->choose)
					{
						$options = explode(';;__;;', $this->struct->choose);
					}
					else
					{
						$options = array();
					}

					echo $vik->openControl(JText::_('VRCUSTOMFTYPEOPTION4'));
					foreach ($options as $opt)
					{
						?>
						<div style="margin-bottom: 10px;">
							<input type="text" value="<?php echo $this->escape($opt); ?>" size="48" readonly tabindex="-1" />
						</div>
						<?php
					}
					echo $vik->closeControl();
				}
				else if (VRCustomFields::isCheckbox($this->struct))
				{
					echo $vik->openControl(JText::_('VRMANAGECUSTOMF5'));
					?><input type="text" value="<?php echo $this->struct->poplink; ?>" size="48" readonly tabindex="-1" /><?php
					echo $vik->closeControl();
				}
				else if (VRCustomFields::isSeparator($this->struct))
				{
					echo $vik->openControl(JText::_('VRSUFFIXCLASS'));
					?><input type="text" value="<?php echo $this->escape($this->struct->choose); ?>" size="48" readonly tabindex="-1" /><?php
					echo $vik->closeControl();
				}
				else if (VRCustomFields::isPhoneNumber($this->struct))
				{
					echo $vik->openControl(JText::_('VRMANAGECUSTOMF10'));
					?><input type="text" value="<?php echo $this->escape($this->struct->choose); ?>" size="48" readonly tabindex="-1" /><?php
					echo $vik->closeControl();
				}
				?>
				
			<?php echo $vik->closeFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id_customf" value="<?php echo $this->struct->id_customf; ?>" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_SAVE_TRX_DEF_LANG');
JText::script('VRE_FILTER_SELECT_COUNTRY');
?>

<script>

	jQuery(document).ready(function() {
		jQuery('#vr-countrylang-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_COUNTRY'),
			allowClear: true,
			width: 300,
		});
	});
	
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
