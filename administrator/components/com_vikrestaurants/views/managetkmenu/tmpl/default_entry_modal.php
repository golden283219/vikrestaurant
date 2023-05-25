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

$currency = VREFactory::getCurrency();

$attr_icons = array();

foreach ($this->attributes as $attr)
{
	$attr_icons[$attr->id] = $attr->icon;
}

?>

<div class="inspector-form" id="inspector-entry-form">

	<?php echo $vik->bootStartTabSet('tkentry', array('active' => 'tkentry_details')); ?>

		<?php echo $vik->bootAddTab('tkentry', 'tkentry_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<div class="inspector-fieldset">
			
				<!-- ENTRY NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKMENU4') . '*'); ?>
					<input type="text" id="entry_name" value="" class="field required" size="40" />
				<?php echo $vik->closeControl(); ?>

				<!-- ENTRY ALIAS - Text -->
				<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
					<input type="text" id="entry_alias" value="" class="field" size="40" />
				<?php echo $vik->closeControl(); ?>

				<!-- ENTRY ATTRIBUTES - Dropdown -->
				<?php
				$elements = array(
					JHtml::_('select.option', '', ''),
				);

				foreach ($this->attributes as $attr)
				{
					$elements[] = JHtml::_('select.option', $attr->id, $attr->name);	
				}
				
				echo $vik->openControl(JText::_('VRMANAGETKMENU18')); ?>
					<select id="entry_attributes" class="field" multiple>
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', array()); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- ENTRY PRICE - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKMENU5')); ?>
					<div class="input-prepend currency-field">
						<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

						<input type="number" class="field" id="entry_price" value="0" min="0" max="99999999" step="any" />
					</div>
				<?php echo $vik->closeControl(); ?>

				<!-- ENTRY IMAGE - Number -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGETKMENU16'));
				echo JHtml::_('vrehtml.mediamanager.field', 'entry_image', null, 'entry_image', array('multiple' => true));
				echo $vik->closeControl();
				?>

				<!-- ENTRY STATUS - Checkbox -->
				<?php
				$yes = $vik->initRadioElement('', JText::_('JYES'), true);
				$no  = $vik->initRadioElement('', JText::_('JNO'), false);

				echo $vik->openControl(JText::_('VRMANAGETKMENU12'));
				echo $vik->radioYesNo('entry_published', $yes, $no, false);
				echo $vik->closeControl();
				?>

				<!-- ENTRY PREPARATION - Checkbox -->
				<?php
				$yes = $vik->initRadioElement('', JText::_('JYES'), false);
				$no  = $vik->initRadioElement('', JText::_('JNO'), true);

				$help = $vik->createPopover(array(
					'title'     => JText::_('VRMANAGETKMENU9'),
					'content'   => JText::_('VRMANAGETKMENU9_HELP'),
					'placement' => 'top',
				));

				echo $vik->openControl(JText::_('VRMANAGETKMENU9') . $help);
				echo $vik->radioYesNo('entry_ready', $yes, $no, false);
				echo $vik->closeControl();
				?>

				<!-- ENTRY DESCRIPTION - Textarea -->
				<?php echo $vik->openControl(JText::_('VRMANAGETKMENU2')); ?>
					<textarea id="entry_description" class="field"></textarea>
				<?php echo $vik->closeControl(); ?>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<?php echo $vik->bootAddTab('tkentry', 'tkentry_variations', JText::_('VRMANAGETKENTRYFIELDSET2')); ?>

			<div class="inspector-fieldset">

				<div class="control-group">
					<div class="vrtk-entry-variations">

					</div>

					<div class="vrtk-entry-addvar">
						<button type="button" class="btn" onClick="addNewVariation();">
							<?php echo JText::_('VRMANAGETKMENUADDVAR'); ?>
						</button>
					</div>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>

	<input type="hidden" id="entry_id" class="field" value="" />

</div>

<?php
JText::script('VRTKNOATTR');
?>

<script>

	var entryValidator = new VikFormValidator('#inspector-entry-form');

	var ATTRIBUTES_LOOKUP = <?php echo json_encode($attr_icons); ?>;
	var NAVIGATION_POOL   = {};

	var VARIATIONS_COUNT = 1;

	jQuery(document).ready(function() {

		jQuery('#entry_attributes').select2({
			placeholder: Joomla.JText._('VRTKNOATTR'),
			allowClear: true,
			width: 'resolve',
			formatResult: formatAttributeOption,
			formatSelection: formatAttributeOption,
			escapeMarkup: function(m) { return m; },
		});

		jQuery('.vrtk-entry-variations').sortable({
			axis:   'y',
			cursor: 'move',
			handle: '.manual-sort-handle',
			revert: false,
		});

		// keep track of active tabs
		jQuery('#tkentryTabs a[href^="#tkentry_"]').on('click', function() {
			var id = parseInt(jQuery('#entry_id').val());

			if (id) {
				var href = jQuery(this).attr('href');

				NAVIGATION_POOL[id] = href;
			}
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

	function fillMenuEntryForm(data) {
		// update name
		if (data.name === undefined) {
			data.name = '';
		}

		jQuery('#entry_name').val(data.name);

		entryValidator.unsetInvalid(jQuery('#entry_name'));

		// update alias
		if (data.alias === undefined) {
			data.alias = '';
		}

		jQuery('#entry_alias').val(data.alias);

		// update price
		if (data.price === undefined) {
			data.price = 0;
		}

		jQuery('#entry_price').val(data.price);

		// update attributes
		if (!data.attributes) {
			data.attributes = [];
		} else if (!Array.isArray(data.attributes)) {
			data.attributes = data.attributes.length ? JSON.parse(data.attributes) : [];
		}

		jQuery('#entry_attributes').select2('val', data.attributes);

		// update image
		if (data.image === undefined) {
			data.image = null;
		}

		try {
			// try to parse JSON image
			data.image = JSON.parse(data.image);
		} catch (err) {
			// not a JSON, do nothing
		}

		jQuery('#entry_image').mediamanager('val', data.image);

		// update status
		var statusInput = jQuery('input[name="entry_published"]');

		if (data.published === undefined) {
			data.published = true;
		} else if (('' + data.published).match(/^[\d]+$/)) {
			data.published = parseInt(data.published);
		}

		if (statusInput.attr('type') == 'checkbox') {
			statusInput.prop('checked', data.published ? true : false);
		} else {
			statusInput.val(data.published ? 1 : 0);
		}

		// update ready
		var readyInput = jQuery('input[name="entry_ready"]');

		if (data.ready === undefined) {
			data.ready = false;
		} else if (('' + data.ready).match(/^[\d]+$/)) {
			data.ready = parseInt(data.ready);
		}

		if (readyInput.attr('type') == 'checkbox') {
			readyInput.prop('checked', data.ready ? true : false);
		} else {
			readyInput.val(data.ready ? 1 : 0);
		}
		
		// update description
		if (data.description === undefined) {
			data.description = '';
		}

		jQuery('#entry_description').val(data.description);

		// update ID
		if (data.id === undefined) {
			data.id = 0;
		}

		jQuery('#entry_id').val(data.id);

		// clear variations list before re-filling it
		jQuery('.vrtk-entry-variations').html('');

		if (!data.options) {
			data.options = [];
		}

		console.log(data.options);

		// add variations
		for (var i = 0; i < data.options.length; i++) {
			addNewVariation(data.options[i]);
		}

		// set active tab
		if (data.id && NAVIGATION_POOL.hasOwnProperty(data.id)) {
			jQuery('a[href="' + NAVIGATION_POOL[data.id] + '"]').trigger('click');
		} else {
			// fallback to default details tab
			jQuery('a[href="#tkentry_details"]').trigger('click');
		}
	}

	function getEntryData() {
		var data = {};

		// set ID
		data.id = jQuery('#entry_id').val();

		// set name
		data.name = jQuery('#entry_name').val();

		// set alias
		data.alias = jQuery('#entry_alias').val();

		// set description
		data.description = jQuery('#entry_description').val();

		// set price
		data.price = jQuery('#entry_price').val();

		// set image
		data.image = jQuery('#entry_image').mediamanager('val');

		// make sure we got an array
		if (Array.isArray(data.image)) {
			// JSON encode the images
			data.image = JSON.stringify(data.image);
		}

		// set status
		if (jQuery('input[name="entry_published"]').attr('type') == 'checkbox') {
			data.published = jQuery('input[name="entry_published"]').is(':checked') ? 1 : 0;
		} else {
			data.published = parseInt(jQuery('input[name="entry_published"]').val());
		}

		// set ready
		if (jQuery('input[name="entry_ready"]').attr('type') == 'checkbox') {
			data.ready = jQuery('input[name="entry_ready"]').is(':checked') ? 1 : 0;
		} else {
			data.ready = parseInt(jQuery('input[name="entry_ready"]').val());
		}

		// set attributes
		data.attributes = jQuery('#entry_attributes').val();

		if (!data.attributes) {
			data.attributes = [];
		}

		// set variations
		data.options = [];
		
		var opt_id    = jQuery('#inspector-entry-form .entry_option_id');
		var opt_name  = jQuery('#inspector-entry-form .entry_option_name');
		var opt_price = jQuery('#inspector-entry-form .entry_option_price');

		for (var i = 0; i < opt_id.length; i++) {
			data.options.push({
				id:    jQuery(opt_id[i]).val(),
				name:  jQuery(opt_name[i]).val(),
				price: jQuery(opt_price[i]).val(),
			});
		}

		// retrieve deleted options
		data.deletedOptions = [];

		jQuery('.entry_option_delete').each(function() {
			data.deletedOptions.push(jQuery(this).val());
		});

		return data;
	}

	function addNewVariation(option) {
		var _html =	jQuery('#variation-struct').clone().html();

		if (option === undefined) {
			option = {};
		}

		var index = VARIATIONS_COUNT++;

		option.id    = option.id    ? option.id    : 0;
		option.name  = option.name  ? option.name  : '';
		option.price = option.price ? option.price : 0.0;

		_html = _html.replace(/{__var_index__}/g, index);
		_html = _html.replace(/{__var_id__}/g, option.id);
		_html = _html.replace(/{__var_name__}/g, option.name);
		_html = _html.replace(/{__var_price__}/g, option.price);

		jQuery('.vrtk-entry-variations').append(_html);

		jQuery('#vrtkoptdiv' + index + ' .hasTooltip').tooltip();
	}

	function removeVariation(var_id) {
		var option = jQuery('#vrtkoptdiv' + var_id);
		var id     = parseInt(option.find('.entry_option_id').first().val());
		
		option.remove();
		
		if (!isNaN(id) && id > 0) {
			jQuery('.vrtk-entry-variations').append(
				'<input type="hidden" class="entry_option_delete" value="' + id + '" />\n'
			);
		}
	}

</script>
