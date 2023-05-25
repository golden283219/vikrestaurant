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

$entry = $this->entry;

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

$attr_icons = array();

foreach ($this->attributes as $attr)
{
	$attr_icons[$attr->id] = $attr->icon;
}

$editor = $vik->getEditor();

?>

<div class="row-fluid">	

	<!-- LEFT BOX -->

	<div class="span6">

		<!-- PRODUCT DETAILS -->

		<div class="row-fluid">
			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRMANAGETKSTOCK1')); ?>
				
					<!-- NAME - Text -->
					<?php echo $vik->openControl(JText::_('VRMANAGETKMENU4') . '*'); ?>
						<input type="text" name="name" class="required" value="<?php echo $this->escape($entry->name); ?>" size="30" />
					<?php echo $vik->closeControl(); ?>

					<!-- ALIAS - Text -->
					<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
						<input type="text" name="alias" value="<?php echo $this->escape($entry->alias); ?>" size="30" />
					<?php echo $vik->closeControl(); ?>
					
					<!-- PRICE - Number -->
					<?php echo $vik->openControl(JText::_('VRMANAGETKMENU5')); ?>
						<div class="input-prepend currency-field">
							<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

							<input type="number" name="price" value="<?php echo $entry->price; ?>" min="0" max="99999999" step="any" />
						</div>
					<?php echo $vik->closeControl(); ?>
					
					<!-- ATTRIBUTES - Dropdown -->
					<?php
					$elements = array(
						JHtml::_('select.option', '', ''),
					);

					foreach ($this->attributes as $attr)
					{
						$elements[] = JHtml::_('select.option', $attr->id, $attr->name);
					}
					
					echo $vik->openControl(JText::_('VRMANAGETKMENU18')); ?>
						<select name="attributes[]" id="vrtk-attributes-select" multiple style="width:100%">
							<?php echo JHtml::_('select.options', $elements, 'value', 'text', $entry->attributes); ?>
						</select>
					<?php echo $vik->closeControl(); ?>
					
					<!-- IMAGE - File -->
					<?php
					echo $vik->openControl(JText::_('VRMANAGETKMENU16'));
					echo JHtml::_('vrehtml.mediamanager.field', 'img_path', $entry->img_path, null, array('multiple' => true));
					echo $vik->closeControl();
					?>
					
					<!-- PUBLISHED - Radio Button -->
					<?php
					$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $entry->published);
					$elem_no  = $vik->initRadioElement('', JText::_('JNO'), !$entry->published);
					
					echo $vik->openControl(JText::_('VRMANAGETKMENU12'));
					echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
					echo $vik->closeControl();
					?>

					<!-- NO PREPARATION - Radio Button -->
					<?php
					$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $entry->ready);
					$elem_no  = $vik->initRadioElement('', JText::_('JNO'), !$entry->ready);

					$help = $vik->createPopover(array(
						'title'   => JText::_('VRMANAGETKMENU9'),
						'content' => JText::_('VRMANAGETKMENU9_HELP'),
					));
					
					echo $vik->openControl(JText::_('VRMANAGETKMENU9') . $help);
					echo $vik->radioYesNo('ready', $elem_yes, $elem_no, false);
					echo $vik->closeControl();
					?>

					<?php
					if (VikRestaurants::isTakeAwayStockEnabled())
					{
						?>

						<!-- ITEMS IN STOCK - Number -->
						<?php
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGETKSTOCK3'),
							'content' => JText::_('VRMANAGETKSTOCK3_HELP'),
						));

						echo $vik->openControl(JText::_('VRMANAGETKSTOCK3') . $help); ?>
							<input type="number" name="items_in_stock" value="<?php echo $entry->items_in_stock; ?>" size="6" min="0" max="999999" step="1" />
						<?php echo $vik->closeControl(); ?>

						<!-- NOTIFY BELOW - Number -->
						<?php
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGETKSTOCK4'),
							'content' => JText::_('VRMANAGETKSTOCK4_HELP'),
						));

						echo $vik->openControl(JText::_('VRMANAGETKSTOCK4') . $help); ?>
							<input type="number" name="notify_below" value="<?php echo $entry->notify_below; ?>" size="6" min="0" max="999999" step="1" />
						<?php echo $vik->closeControl(); ?>

						<?php
					}
					?>
					
					<!-- MENU PARENT - Dropdown -->
					<?php echo $vik->openControl(JText::_('VRMANAGETKMENU15')); ?>
						<select name="id_takeaway_menu" id="vrtk-menus-select">
							<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.takeawaymenus'), 'value', 'text', $entry->id_takeaway_menu); ?>
						</select>
					<?php echo $vik->closeControl(); ?>
				
				<?php echo $vik->closeFieldset(); ?>
			</div>
		</div>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewTkentry". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			?>
			<div class="row-fluid">
				<div class="span12">
					<?php
					echo $vik->openFieldset(JText::_('VRE_CUSTOM_FIELDSET'));
					echo $custom;
					echo $vik->closeFieldset();
					?>
				</div>
			</div>
			<?php
		}
		?>

		<!-- VARIATIONS -->

		<div class="row-fluid">
			<?php echo $this->loadTemplate('variations'); ?>
		</div>

	</div>

	<!-- DESCRIPTION -->

	<div class="span6">
		<?php
		echo $vik->openFieldset(JText::_('VRMANAGETKMENU2'));
		echo $editor->display('description', $entry->description, 400, 200, 70, 20 );
		echo $vik->closeFieldset();
		?>
	</div>

</div>

<?php
JText::script('VRTKNOATTR');
?>

<script>

	var ATTRIBUTES_LOOKUP = <?php echo json_encode($attr_icons); ?>;

	jQuery(document).ready(function() {

		jQuery('#vrtk-attributes-select').select2({
			placeholder: Joomla.JText._('VRTKNOATTR'),
			allowClear: true,
			width: 'resolve',
			formatResult: formatAttributeOption,
			formatSelection: formatAttributeOption,
			escapeMarkup: function(m) { return m; },
		});
		
		jQuery('#vrtk-menus-select').select2({
			allowClear: false,
			width: 300,
		});

	});
	
	function formatAttributeOption(attr) {
		if (!attr.id) {
			// optgroup
			return attr.text;
		}

		if (!ATTRIBUTES_LOOKUP.hasOwnProperty(attr.id)) {
			// unsupported icon
			return attr.text;
		}

		return '<img class="vr-opt-tkattr" src="<?php echo VREMEDIA_URI; ?>' + ATTRIBUTES_LOOKUP[attr.id] + '" /> ' + attr.text;
	}

</script>
