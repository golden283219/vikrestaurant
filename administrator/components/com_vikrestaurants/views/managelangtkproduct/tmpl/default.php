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

JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.scripts.selectflags', '#vre-lang-sel');

$vik = VREApplication::getInstance();

$deflang = VikRestaurants::getDefaultLanguage();

// always use default tab while creating a new record
$active_tab = $this->struct->id_lang ? $this->getActiveTab('managelangtkproduct_details') : 'managelangtkproduct_details';

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->bootStartTabSet('managelangtkproduct', array('active' => $active_tab, 'cookie' => $this->getCookieTab()->name)); ?>

		<?php echo $vik->bootAddTab('managelangtkproduct', 'managelangtkproduct_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<div class="row-fluid">

				<!-- TRANSLATION -->

				<div class="span6">

					<div class="row-fluid">

						<!-- DETAILS -->

						<div class="span12">
							<?php echo $vik->openFieldset(JText::_('VRMANAGETKSTOCK1')); ?>
							
								<!-- LANGUAGE - Dropdown -->
								<?php
								$elements = JHtml::_('contentlanguage.existing');
								
								echo $vik->openControl(JText::_('VRMANAGELANG4')); ?>
									<select name="tag" id="vre-lang-sel">
										<?php echo JHtml::_('select.options', $elements, 'value', 'text', $this->struct->tag); ?>
									</select>
								<?php echo $vik->closeControl(); ?>
								
								<!-- NAME - Text -->
								<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
									<div class="input-append translation-hint">
										<input type="text" name="name" value="<?php echo $this->escape($this->struct->lang_name); ?>" data-id="entry-<?php echo $this->struct->id; ?>" size="48" />

										<button type="button" class="btn"><i class="fas fa-globe-americas"></i></button>
									</div>
								<?php echo $vik->closeControl(); ?>

								<!-- ALIAS - Text -->
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
							<input type="text" value="<?php echo $this->escape($this->struct->name); ?>" data-link="entry-<?php echo $this->struct->id; ?>" size="48" readonly tabindex="-1" />
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
		if ($this->struct->options)
		{
			echo $vik->bootAddTab('managelangtkproduct', 'managelangtkproduct_options', JText::_('VRMANAGETKENTRYFIELDSET2'));

			foreach ($this->struct->options as $option)
			{
				?>
				<div class="row-fluid">

					<!-- TRANSLATION -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRMANAGETKSTOCK2')); ?>

							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
								<div class="input-append translation-hint">
									<input type="text" name="option_name[]" value="<?php echo $this->escape($option->lang_name); ?>" data-id="option-<?php echo $option->id; ?>" size="48" />

									<button type="button" class="btn"><i class="fas fa-globe-americas"></i></button>
								</div>
							<?php echo $vik->closeControl(); ?>

							<!-- ALIAS - Text -->
							<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
								<input type="text" name="option_alias[]" value="<?php echo $this->escape($option->lang_alias); ?>" size="48" />
							<?php echo $vik->closeControl(); ?>

							<input type="hidden" name="option_lang_id[]" value="<?php echo $option->id_lang; ?>" />

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<!-- ORIGINAL -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRE_LANG_ORIGINAL')); ?>

							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
								<input type="text" value="<?php echo $this->escape($option->name); ?>" data-link="option-<?php echo $option->id; ?>" size="48" readonly tabindex="-1" />
							<?php echo $vik->closeControl(); ?>

							<!-- ALIAS - Text -->
							<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
								<input type="text" value="<?php echo $this->escape($option->alias); ?>" size="48" readonly tabindex="-1" />
							<?php echo $vik->closeControl(); ?>

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<input type="hidden" name="option_id[]" value="<?php echo $option->id; ?>" />

				</div>
				<?php
			}

			echo $vik->bootEndTab();
		}
		?>

		<?php
		if ($this->struct->groups)
		{
			echo $vik->bootAddTab('managelangtkproduct', 'managelangtkproduct_groups', JText::_('VRMENUTAKEAWAYTOPPINGS'));

			foreach ($this->struct->groups as $group)
			{
				?>
				<div class="row-fluid">

					<!-- TRANSLATION -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRTOPPING')); ?>

							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
								<div class="input-append translation-hint">
									<input type="text" name="group_name[]" value="<?php echo $this->escape($group->lang_name); ?>" data-id="group-<?php echo $group->id; ?>" size="48" />

									<button type="button" class="btn"><i class="fas fa-globe-americas"></i></button>
								</div>
							<?php echo $vik->closeControl(); ?>

							<!-- DESCRIPTION - Textarea -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
								<div class="translation-hint textarea">
									<textarea name="group_description[]" class="full-width" style="height:60px;resize:vertical;" data-id="group-desc-<?php echo $group->id; ?>" maxlength="128"><?php echo $this->escape($group->lang_description); ?></textarea>

									<a href="javascript:void(0);"><i class="fas fa-globe-americas"></i></a>
								</div>
							<?php echo $vik->closeControl(); ?>

							<input type="hidden" name="group_lang_id[]" value="<?php echo $group->id_lang; ?>" />

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<!-- ORIGINAL -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRE_LANG_ORIGINAL')); ?>

							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG2')); ?>
								<input type="text" value="<?php echo $this->escape($group->name); ?>" data-link="group-<?php echo $group->id; ?>" size="48" readonly tabindex="-1" />
							<?php echo $vik->closeControl(); ?>

							<!-- DESCRIPTION - Textarea -->
							<?php echo $vik->openControl(JText::_('VRMANAGELANG3')); ?>
								<textarea class="full-width" style="height:80px;resize:vertical;" data-link="group-desc-<?php echo $group->id; ?>" readonly tabindex="-1"><?php echo $group->description; ?></textarea>
							<?php echo $vik->closeControl(); ?>

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<input type="hidden" name="group_id[]" value="<?php echo $group->id; ?>" />

				</div>
				<?php
			}

			echo $vik->bootEndTab();
		}
		?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id_entry" value="<?php echo $this->struct->id; ?>" />	
	<input type="hidden" name="id_takeaway_menu" value="<?php echo $this->idMenu; ?>" />    
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
/**
 * Display utils scripts to enhance the translations management.
 *
 * @since 1.8
 */
echo JLayoutHelper::render('script.langhint');

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
