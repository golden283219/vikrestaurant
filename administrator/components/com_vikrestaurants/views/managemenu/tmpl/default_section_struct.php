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

$section = $this->drawSection;

$vik = VREApplication::getInstance();

?>

<div class="vrmenusection" id="vrsection<?php echo $section->id; ?>">

	<h3 class="vrmenutitle" id="vrtitle<?php echo $section->id; ?>"><?php echo $section->name; ?></h3>

	<a href="javascript: void(0);" class="section-remove-link" onClick="vreRemoveSection(<?php echo $section->id; ?>, 1);">
		<i class="fas fa-trash" style="vertical-align: initial;font-size: 20px;"></i>
	</a>

	<div class="vrmenusubsection" id="vrsubsection<?php echo $section->id; ?>">
		
		<div class="control-group">
			<input type="text" name="sec_name[]" size="32" placeholder="<?php echo JText::_('VRMANAGEMENU27'); ?>" class="vrmenusectext" id="vrmenusectext<?php echo $section->id; ?>" value="<?php echo $this->escape($section->name); ?>" onchange="vreAddSectionTitle(<?php echo $section->id; ?>);"/>
		</div>

		<div class="control-group">
			<textarea class="vrmenusecarea" name="sec_desc[]" placeholder="<?php echo JText::_('VRMANAGEMENU17'); ?>"><?php echo $section->description; ?></textarea>
		</div>

		<?php
		echo $vik->openControl(JText::_('VRMANAGEMENU26'));

		$yes = $vik->initRadioElement('', '', $section->published, 'onclick="vreSectionPublishedValueChanged(\'' . $section->id . '\', 1);"');
		$no  = $vik->initRadioElement('', '', !$section->published, 'onclick="vreSectionPublishedValueChanged(\'' . $section->id . '\', 0);"');

		echo $vik->radioYesNo('vrmenusecbox' . $section->id, $yes, $no, true);

		echo $vik->closeControl();
		?>

		<input type="hidden" name="sec_publ[]" value="<?php echo ($section->published ? 1 : 0); ?>" id="vrmenusecpubhidden<?php echo $section->id; ?>" />
			
		<?php
		echo $vik->openControl(JText::_('VRMANAGEMENU32'));

		$yes = $vik->initRadioElement('', '', $section->highlight, 'onclick="vreSectionHighlightValueChanged(\'' . $section->id . '\', 1);"');
		$no  = $vik->initRadioElement('', '', !$section->highlight, 'onclick="vreSectionHighlightValueChanged(\'' . $section->id . '\', 0);"');

		echo $vik->radioYesNo('vrmenusechighlight' . $section->id, $yes, $no, true);

		echo $vik->closeControl();
		?>

		<input type="hidden" name="sec_highlight[]" value="<?php echo ($section->highlight ? 1 : 0); ?>" id="vrmenusechighlighthidden<?php echo $section->id; ?>" />

		<?php
		$help = $vik->createPopover(array(
			'title'   => JText::_('VRMANAGEMENU34'),
			'content' => JText::_('VRMANAGEMENU34_DESC'),
		));

		echo $vik->openControl(JText::_('VRMANAGEMENU34') . $help);

		$yes = $vik->initRadioElement('', '', $section->orderdishes, 'onclick="vreSectionOrderDishesValueChanged(\'' . $section->id . '\', 1);"');
		$no  = $vik->initRadioElement('', '', !$section->orderdishes, 'onclick="vreSectionOrderDishesValueChanged(\'' . $section->id . '\', 0);"');

		echo $vik->radioYesNo('vrmenusecdishes' . $section->id, $yes, $no, true);

		echo $vik->closeControl();
		?>

		<input type="hidden" name="sec_dishes[]" value="<?php echo ($section->orderdishes ? 1 : 0); ?>" id="vrmenusecdisheshidden<?php echo $section->id; ?>" />

		<div class="control-group">
			<?php echo JHtml::_('vrehtml.mediamanager.field', 'sec_image[]', $section->image, 'vre-section-media-' . $section->id); ?>
		</div>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewMenuSection". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		echo $this->onDisplayManageView('Section', $section);
		?>

		<input type="hidden" name="sec_id[]" id="vrtkentryid<?php echo $section->id; ?>" value="<?php echo $section->id; ?>" />
		<input type="hidden" name="sec_app_id[]" value="<?php echo $section->id; ?>" />
		
		<div class="vrmenuprods">
			<div id="vrsectionplistdest<?php echo $section->id; ?>"></div>
			<div class="vrmenuprodscont" id="vrmenuprodscont<?php echo $section->id; ?>">

				<?php
				foreach ($section->products as $prod)
				{
					$this->drawProduct = $prod;
					echo $this->loadTemplate('product_struct');
					unset($this->drawProduct);
				}
				?>
				
			</div>

		    <div class="vrmenuprodaddlink">
				<button type="button" class="btn btn-primary" onClick="vreShowSectionProductsDialog(<?php echo $section->id; ?>);">
					<i class="fas fa-plus-circle"></i>&nbsp;
					<?php echo JText::_('VRMANAGEMENU23'); ?>
				</button>
			</div>
		</div>
		
	</div>

</div>
