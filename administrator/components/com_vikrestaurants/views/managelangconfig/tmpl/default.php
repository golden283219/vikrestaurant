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

switch ($this->struct->param)
{
	case 'symbpos':
		$input   = 'select';
		$options = array(
			JHtml::_('select.option', '1', JText::_('VRCONFIGSYMBPOSITION1')),
			JHtml::_('select.option', '2', JText::_('VRCONFIGSYMBPOSITION2')),
		);
		break;

	case 'currdecimalsep':
	case 'currthousandssep':
		$input = 'textshort';
		break;

	case 'tknote':
		$input = 'editor';
		break;

	default:
		$input = 'text';
}

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>

		<!-- TRANSLATION -->

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRE_CONFIG_SETTING')); ?>
			
				<!-- LANGUAGE - Dropdown -->
				<?php
				$elements = JHtml::_('contentlanguage.existing');
				
				echo $vik->openControl(JText::_('VRMANAGELANG4')); ?>
					<select name="tag" id="vre-lang-sel">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', isset($this->struct->tag) ? $this->struct->tag : null); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- SETTING - Text -->
				<?php
				if ($input != 'editor')
				{
					echo $vik->openControl(JText::_('VRE_CONFIG_SETTING'));
				}

				$setting = isset($this->struct->lang_setting) ? $this->struct->lang_setting : '';

				if ($input == 'editor')
				{
					echo $vik->getEditor()->display('setting', $setting, 400, 200, 70, 20);
				}
				else if ($input == 'select')
				{
					?>
					<select name="setting" id="setting-dropdown">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $setting); ?>
					</select>
					<?php
				}
				else if ($input == 'textshort')
				{
					?>
					<input type="text" name="setting" value="<?php echo $this->escape($setting); ?>" size="12" />
					<?php
				}
				else
				{
					?>
					<input type="text" name="setting" value="<?php echo $this->escape($setting); ?>" size="48" />
					<?php
				}

				if ($input != 'editor')
				{
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

				<!-- SETTING - Text -->
				<?php
				echo $vik->openControl(JText::_('VRE_CONFIG_SETTING'));

				if ($input == 'select')
				{
					foreach ($options as $opt)
					{
						if ($opt->value == $this->struct->setting)
						{
							// overwrite setting
							$this->struct->setting = $opt->text;
							// let the default input is used to display the option
						}
					}
				}

				if ($input == 'editor')
				{
					?>
					<textarea class="full-width" style="height:300px;resize:vertical;" readonly tabindex="-1"><?php echo $this->struct->setting; ?></textarea>
					<?php
				}
				else if ($input == 'textshort')
				{
					?>
					<input type="text" value="<?php echo $this->escape($this->struct->setting); ?>" size="12" readonly tabindex="-1" />
					<?php
				}
				else
				{
					?>
					<input type="text" value="<?php echo $this->escape($this->struct->setting); ?>" size="48" readonly tabindex="-1" />
					<?php
				}

				echo $vik->closeControl();
				?>
				
			<?php echo $vik->closeFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="param" value="<?php echo $this->struct->param; ?>" />	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_SAVE_TRX_DEF_LANG');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#setting-dropdown').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 250,
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
