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

JHtml::_('vrehtml.assets.currency');

$entries = $this->menu->entries;

$currency = VREFactory::getCurrency();

$deliveryLayout = new JLayoutFile('blocks.card');
?>

<div class="vr-delivery-locations-container vre-cards-container">

	<?php
	foreach ($entries as $i => $entry)
	{
		?>
		<div class="delivery-fieldset vre-card-fieldset" id="entry-fieldset-<?php echo $i; ?>">

			<?php
			$displayData = array();

			// fetch card ID
			$displayData['id'] = 'entry-card-' . $i;

			// fetch card class
			if ($entry->published)
			{
				$displayData['class'] = 'published';
			}

			// fetch image
			if ($entry->image)
			{
				$displayData['image'] = VREMEDIA_URI . $entry->image[0];
			}
			else
			{
				$displayData['image'] = VREASSETS_ADMIN_URI . 'images/entry-placeholder.png';
			}

			if ($entry->ready)
			{
				$icon = 'far fa-snowflake';
			}
			else
			{
				$icon = 'fas fa-stopwatch';
			}

			// fetch badge
			$displayData['badge'] = '<i class="' . $icon . '"></i>';

			// fetch primary text
			$displayData['primary']  = $entry->name;

			// fetch secondary text
			$displayData['secondary'] = '<span class="badge badge-info entry-cost">' . $currency->format($entry->price) . '</span>';

			if ($entry->options)
			{
				$displayData['secondary'] .= '<span class="badge badge-info entry-vars">' . JText::plural('VRE_N_VARIATIONS', count($entry->options)) . '</span>';
			}

			// fetch edit button
			$displayData['edit'] = 'openEntryCard(' . $i . ');';

			// render layout
			echo $deliveryLayout->render($displayData);
			?>
			
			<input type="hidden" name="entry_id[]" value="<?php echo $entry->id; ?>" />
			<input type="hidden" name="entry_tmp_id[]" value="<?php echo $i; ?>" />
			<input type="hidden" name="entry_name[]" value="<?php echo $this->escape($entry->name); ?>" />
			<input type="hidden" name="entry_alias[]" value="<?php echo $this->escape($entry->alias); ?>" />
			<input type="hidden" name="entry_description[]" value="<?php echo $this->escape($entry->description); ?>" />
			<input type="hidden" name="entry_price[]" value="<?php echo $entry->price; ?>" />
			<input type="hidden" name="entry_published[]" value="<?php echo $entry->published; ?>" />
			<input type="hidden" name="entry_ready[]" value="<?php echo $entry->ready; ?>" />
			<input type="hidden" name="entry_image[]" value="<?php echo $this->escape(json_encode($entry->image)); ?>" />
			<input type="hidden" name="entry_attributes[]" value="<?php echo $this->escape(json_encode($entry->attributes)); ?>" />

			<?php
			foreach ($entry->options as $option)
			{
				?>
				<input type="hidden" name="option_id[<?php echo $i; ?>][]" value="<?php echo $option->id; ?>" />
				<input type="hidden" name="option_name[<?php echo $i; ?>][]" value="<?php echo $this->escape($option->name); ?>" />
				<input type="hidden" name="option_price[<?php echo $i; ?>][]" value="<?php echo $option->price; ?>" />
				<?php
			}
			?>

		</div>
		<?php
	}
	?>

	<div class="delivery-fieldset vre-card-fieldset" id="add-delivery-location">
		<div class="vre-card">
			<i class="fas fa-plus"></i>
		</div>
	</div>

</div>

<div style="display:none;" id="entry-struct">
	
	<?php
	// create menu entry structure for new items
	$displayData = array();
	$displayData['id']        = 'entry-card-{id}';
	$displayData['image']     = VREASSETS_ADMIN_URI . 'images/entry-placeholder.png';
	$displayData['badge']     = '<i class="fas fa-stopwatch"></i>';
	$displayData['primary']   = '';
	$displayData['secondary'] = '';
	$displayData['edit']      = true;

	echo $deliveryLayout->render($displayData);
	?>

</div>

<?php
JText::script('VRSYSTEMCONFIRMATIONMSG');
JText::script('VRE_ADD_PRODUCT');
JText::script('VRE_EDIT_PRODUCT');
JText::script('VRMANAGETKENTRYFIELDSET2');
JText::script('VRE_N_VARIATIONS');
JText::script('VRE_N_VARIATIONS_1');
?>

<script>

	var ENTRIES_COUNT  = <?php echo count($entries); ?>;
	var SELECTED_INDEX = null;

	jQuery(document).ready(function() {

		jQuery('.vr-delivery-locations-container').sortable({
			// exclude "add" boxs
			items: '.delivery-fieldset:not(#add-delivery-location)',
			// hide "add" box when sorting starts
			start: function() {
				jQuery('#add-delivery-location').hide();
			},
			// show "add" box again when sorting stops
			stop: function() {
				jQuery('#add-delivery-location').show();
			},
		});

		jQuery('#add-delivery-location').on('click', function() {
			openEntryCard();
		});

		// fill the form before showing the inspector
		jQuery('#menu-entry-inspector').on('inspector.show', function() {
			var data = {};

			// in case the INDEX is a number, extract the menu entry data
			if (SELECTED_INDEX !== undefined && SELECTED_INDEX !== null) {
				var fieldset = jQuery('#entry-fieldset-' + SELECTED_INDEX);
				
				fieldset.find('input[type="hidden"][name^="entry_"]')
					.each(function() {
						var name  = jQuery(this).attr('name').match(/^entry_([a-z0-9_]+)\[\]$/i);
						var value = jQuery(this).val();

						if (name && name.length) {
							data[name[1]] = value;
						}
					});

				data.options = [];

				var opt_id    = fieldset.find('input[name^="option_id"]');
				var opt_name  = fieldset.find('input[name^="option_name"]');
				var opt_price = fieldset.find('input[name^="option_price"]');

				for (var i = 0; i < opt_id.length; i++) {
					data.options.push({
						id:    jQuery(opt_id[i]).val(),
						name:  jQuery(opt_name[i]).val(),
						price: jQuery(opt_price[i]).val(),
					});
				}
			}

			// fill the form with the retrieved data
			fillMenuEntryForm(data);
		});

		jQuery('#menu-entry-save').on('click', function() {
			// validate form
			if (!entryValidator.validate()) {
				return false;
			}

			// get updated entry data
			var data = getEntryData();

			var index = SELECTED_INDEX;

			if (index === undefined || index === null) {
				index = ENTRIES_COUNT;

				addEntryCard(data);

				// update entries count
				jQuery('#tkmenu_entries_tab_badge').attr('data-count', jQuery('.delivery-fieldset[id^="entry-fieldset-"]').length);
			} else {
				// inject data within the form
				for (var k in data) {
					if (data.hasOwnProperty(k) && k != 'attributes') {
						jQuery('#entry-fieldset-' + index)
							.find('input[name="entry_' + k + '[]"]').val(data[k]);
					}
				}

				// update attributes
				var attributes = JSON.stringify(data.attributes);

				jQuery('#entry-fieldset-' + index)
					.find('input[name="entry_attributes[]"]').val(attributes);

				// remove all entry options before re-adding them
				jQuery('#entry-fieldset-' + index)
					.find('input[name^="option_"]').remove();

				// update options
				addEntryOptions(index, data.options);

				// register deleted options
				for (var i = 0; i < data.deletedOptions.length; i++) {
					var oid = data.deletedOptions[i];

					jQuery('#adminForm').append(
						'<input type="hidden" name="delete_option[]" value="' + oid + '" />\n'
					);
				}
			}

			// refresh details shown in card
			refreshEntryCard(index, data);

			// dismiss inspector
			jQuery('#menu-entry-inspector').inspector('dismiss');
		});

		jQuery('#menu-entry-delete').on('click', function() {
			var r = confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'));

			if (!r) {
				return false;
			}

			jQuery('#menu-entry-inspector').inspector('dismiss');

			var index = SELECTED_INDEX;

			var fieldset = jQuery('#entry-fieldset-' + SELECTED_INDEX);
			var id       = parseInt(fieldset.find('input[name="entry_id[]"]').val());
			var tmp_id   = parseInt(fieldset.find('input[name="entry_tmp_id[]"]').val());

			if (!isNaN(id) && id > 0) {
				jQuery('#adminForm').append('<input type="hidden" name="delete_entry[]" value="' + id + '" />');
			}

			// NOTE: do not need to delete options too because they will be removed
			// in cascade while erasing the parent menu entry.

			fieldset.remove();

			// update entries count
			jQuery('#tkmenu_entries_tab_badge').attr('data-count', jQuery('.delivery-fieldset[id^="entry-fieldset-"]').length);
		});

	});

	function openEntryCard(index) {
		SELECTED_INDEX = index;

		var title;

		if (typeof index === 'undefined') {
			title = Joomla.JText._('VRE_ADD_PRODUCT');
			jQuery('#menu-entry-delete').hide();
		} else {
			title = Joomla.JText._('VRE_EDIT_PRODUCT');
			jQuery('#menu-entry-delete').show();
		}
		
		// open inspector
		vreOpenInspector('menu-entry-inspector', {title: title});
	}

	function addEntryCard(data) {
		var index = ENTRIES_COUNT;

		var html = jQuery('#entry-struct').clone().html();

		html = html.replace(/{id}/, index);

		jQuery(
			'<div class="delivery-fieldset vre-card-fieldset" id="entry-fieldset-' + index + '">' + html + '</div>'
		).insertBefore('#add-delivery-location');

		jQuery('#entry-card-' + index).vrecard('edit', 'openEntryCard(' + index + ')');

		jQuery('#entry-fieldset-' + index).append(
			'<input type="hidden" name="entry_id[]" value="0" />\n'+
			'<input type="hidden" name="entry_tmp_id[]" value="' + index + '" />\n'+
			'<input type="hidden" name="entry_name[]" value="' + data.name + '" />\n'+
			'<input type="hidden" name="entry_alias[]" value="' + data.alias + '" />\n'+
			'<input type="hidden" name="entry_description[]" value="' + data.description + '" />\n'+
			'<input type="hidden" name="entry_price[]" value="' + data.price + '" />\n'+
			'<input type="hidden" name="entry_published[]" value="' + data.published + '" />\n'+
			'<input type="hidden" name="entry_ready[]" value="' + data.ready + '" />\n'+
			'<input type="hidden" name="entry_image[]" value="' + data.image.replace(/\"/g, '&quot;') + '" />\n'+
			'<input type="hidden" name="entry_attributes[]" value="' + JSON.stringify(data.attributes).replace(/\"/g, '&quot;') + '" />\n'
		);

		addEntryOptions(index, data.options);

		ENTRIES_COUNT++;
	}

	function addEntryOptions(index, options) {
		for (var i = 0; i < options.length; i++) {
			var option = options[i];

			jQuery('#entry-fieldset-' + index).append(
				'<input type="hidden" name="option_id[' + index + '][]" value="' + option.id + '" />\n'+
				'<input type="hidden" name="option_name[' + index + '][]" value="' + option.name + '" />\n'+
				'<input type="hidden" name="option_price[' + index + '][]" value="' + option.price + '" />\n'
			);
		}
	}

	function refreshEntryCard(index, data) {
		var card = jQuery('#entry-card-' + index);

		// update badge
		var icon;

		if (parseInt(data.ready) == 1) {
			icon = 'far fa-snowflake';
		} else {
			icon = 'fas fa-stopwatch';
		}

		if (parseInt(data.published) == 1) {
			card.addClass('published');
		} else {
			card.removeClass('published');
		}

		card.vrecard('badge', '<i class="' + icon + '"></i>');

		// update primary text
		card.vrecard('primary', data.name);

		// update secondary text
		var secondary = '<span class="badge badge-info entry-cost">' + Currency.getInstance().format(data.price) + '</span>';

		if (data.options.length) {
			var vars_badge_text;

			if (data.options.length == 1) {
				vars_badge_text = Joomla.JText._('VRE_N_VARIATIONS_1');
			} else {
				vars_badge_text = Joomla.JText._('VRE_N_VARIATIONS').replace(/%d/, data.options.length);
			}

			secondary += '<span class="badge badge-info entry-vars">' + vars_badge_text + '</span>';
		}

		card.vrecard('secondary', secondary);

		// update image
		var image = '<?php echo VREASSETS_ADMIN_URI; ?>images/entry-placeholder.png';

		if (data.image) {
			var imageJSON;

			try {
				// try to parse JSON image
				imageJSON = JSON.parse(data.image);
				imageJSON = imageJSON.shift();
			} catch (err) {
				// not a JSON, use plain text
				imageJSON = data.image;
			}

			if (imageJSON) {
				image = '<?php echo VREMEDIA_URI; ?>' + imageJSON;
			}
		}

		card.vrecard('image', image);
	}

</script>
