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

	<?php echo $vik->bootStartTabSet('managelangmenu', array('active' => $this->getActiveTab('managelangmenu_details'), 'cookie' => $this->getCookieTab()->name)); ?>

		<?php echo $vik->bootAddTab('managelangmenu', 'managelangmenu_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<div class="row-fluid">

				<!-- TRANSLATION -->

				<div class="span6">

					<div class="row-fluid">

						<!-- DETAILS -->

						<div class="span12">
							<?php echo $vik->openFieldset(JText::_('VRE_MENU')); ?>
					
								<!-- LANGUAGE - Dropdown -->
								<?php
								$elements = JHtml::_('contentlanguage.existing');
								
								echo $vik->openControl(JText::_('VRMANAGELANG4')); ?>
									<select name="tag" id="vre-lang-sel">
										<?php echo JHtml::_('select.options', $elements, 'value', 'text', $this->struct->tag); ?>
									</select>
								<?php echo $vik->closeControl(); ?>
								
								<!-- MENU NAME - Text -->
								<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
									<input type="text" name="name" value="<?php echo $this->escape($this->struct->lang_name); ?>" size="48" />
								<?php echo $vik->closeControl(); ?>

								<!-- MENU ALIAS - Text -->
								<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
									<input type="text" name="alias" value="<?php echo $this->escape($this->struct->lang_alias); ?>" size="48" />
								<?php echo $vik->closeControl(); ?>
							
								<input type="hidden" name="id" value="<?php echo $this->struct->id_lang; ?>" />
							
							<?php echo $vik->closeFieldset(); ?>
						</div>

						<!-- DESCRIPTION -->

						<div class="span12">
							<?php
							echo $vik->openFieldset(JText::_('VRMANAGELANG3'));
							echo $vik->getEditor()->display('description', $this->struct->lang_description, 400, 200, 70, 20);
							echo $vik->closeFieldset();
							?>
						</div>

					</div>

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

						<!-- ALIAS - Text -->
						<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
							<input type="text" value="<?php echo $this->escape($this->struct->alias); ?>" size="48" readonly tabindex="-1" />
						<?php echo $vik->closeControl(); ?>
						
						<!-- DESCRIPTION - Textarea -->
						<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
							<textarea class="full-width" style="height:150px;resize:vertical;" readonly tabindex="-1"><?php echo $this->struct->description; ?></textarea>
						<?php echo $vik->closeControl(); ?>
						
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>
		
		<?php
		if ($this->struct->sections)
		{
			echo $vik->bootAddTab('managelangmenu', 'managelangmenu_sections', JText::_('VRMANAGEMENU20'));

			foreach ($this->struct->sections as $section)
			{
				?>
				<div class="row-fluid">

					<!-- TRANSLATION -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRE_SECTION')); ?>

							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
								<input type="text" name="section_name[]" value="<?php echo $this->escape($section->lang_name); ?>" size="48" />
							<?php echo $vik->closeControl(); ?>

							<!-- DESCRIPTION - Textarea -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
								<textarea name="section_description[]" class="full-width" style="height:150px;resize:vertical;"><?php echo $section->lang_description; ?></textarea>
							<?php echo $vik->closeControl(); ?>

							<input type="hidden" name="section_lang_id[]" value="<?php echo $section->id_lang; ?>" />

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<!-- ORIGINAL -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRE_LANG_ORIGINAL')); ?>

							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
								<input type="text" value="<?php echo $this->escape($section->name); ?>" size="48" readonly tabindex="-1" />
							<?php echo $vik->closeControl(); ?>

							<!-- DESCRIPTION - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
								<textarea class="full-width" style="height:150px;resize:vertical;" readonly tabindex="-1"><?php echo $section->description; ?></textarea>
							<?php echo $vik->closeControl(); ?>

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<input type="hidden" name="section_id[]" value="<?php echo $section->id; ?>" />

				</div>
				<?php
			}

			echo $vik->bootEndTab();
		}
		?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id_menu" value="<?php echo $this->struct->id; ?>" />
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
