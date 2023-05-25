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

$groups = $this->entry->groups;

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

$deliveryLayout = new JLayoutFile('blocks.card');

?>
	
<div class="vr-delivery-locations-container vre-cards-container" id="groups-card-container">

	<?php
	foreach ($groups as $i => $group)
	{
		?>
		<div class="delivery-fieldset vre-card-fieldset" id="group-fieldset-<?php echo $i; ?>">

			<?php
			$displayData = array();

			// fetch card ID
			$displayData['id'] = 'group-card-' . $i;

			if ($group->multiple)
			{
				$icon = 'check-circle';
			}
			else
			{
				$icon = 'dot-circle';
			}

			// fetch badge
			$displayData['badge'] = '<i class="fas fa-' . $icon . '"></i>';

			// fetch primary text
			$displayData['primary']  = $group->title;

			// fetch secondary text
			$text = JText::plural('VRE_N_TOPPINGS', count($group->toppings));
			$displayData['secondary'] = '<span class="badge badge-' . (count($group->toppings) ? 'info' : 'important') . ' group-toppings">' . $text . '</span>';

			if ($group->id_variation)
			{
				// search selected variation
				for ($j = 0, $varname = null; $j < count($this->entry->variations) && !$varname; $j++)
				{
					if ($this->entry->variations[$j]->id == $group->id_variation)
					{
						$varname = $this->entry->variations[$j]->name;
					}
				}

				if ($varname)
				{
					// display variation badge
					$displayData['secondary'] .= '<span class="badge badge-warning group-variation">' . $varname . '</span>';
				}
			}

			// fetch edit button
			$displayData['edit'] = 'openGroupCard(' . $i . ');';

			// render layout
			echo $deliveryLayout->render($displayData);
			?>
			
			<input type="hidden" name="group_id[]" value="<?php echo $group->id; ?>" />
			<input type="hidden" name="group_tmp_id[]" value="<?php echo $i; ?>" />
			<input type="hidden" name="group_id_variation[]" value="<?php echo $group->id_variation; ?>" />
			<input type="hidden" name="group_title[]" value="<?php echo $this->escape($group->title); ?>" />
			<input type="hidden" name="group_description[]" value="<?php echo $this->escape($group->description); ?>" />
			<input type="hidden" name="group_multiple[]" value="<?php echo $group->multiple; ?>" />
			<input type="hidden" name="group_min_toppings[]" value="<?php echo $group->min_toppings; ?>" />
			<input type="hidden" name="group_max_toppings[]" value="<?php echo $group->max_toppings; ?>" />
			<input type="hidden" name="group_use_quantity[]" value="<?php echo $group->use_quantity; ?>" />

			<?php
			foreach ($group->toppings as $topping)
			{
				?>
				<input type="hidden" name="topping_id[<?php echo $i; ?>][]" value="<?php echo $topping->id; ?>" />
				<input type="hidden" name="topping_id_assoc[<?php echo $i; ?>][]" value="<?php echo $topping->id_assoc; ?>" />
				<input type="hidden" name="topping_rate[<?php echo $i; ?>][]" value="<?php echo $topping->rate; ?>" />
				<?php
			}
			?>

		</div>
		<?php
	}
	?>

	<div class="delivery-fieldset vre-card-fieldset no-image" id="add-delivery-location">
		<div class="vre-card">
			<i class="fas fa-plus"></i>
		</div>
	</div>

</div>

<div style="display:none;" id="group-struct">
	
	<?php
	// create entry group structure for new items
	$displayData = array();
	$displayData['id']        = 'group-card-{id}';
	$displayData['badge']     = '<i class="fas fa-check-circle"></i>';
	$displayData['primary']   = '';
	$displayData['secondary'] = '';
	$displayData['edit']      = true;

	echo $deliveryLayout->render($displayData);
	?>

</div>

<?php
JText::script('VRSYSTEMCONFIRMATIONMSG');
JText::script('VRE_ADD_TOPPING_GROUP');
JText::script('VRE_EDIT_TOPPING_GROUP');
JText::script('VRE_N_TOPPINGS_0');
JText::script('VRE_N_TOPPINGS_1');
JText::script('VRE_N_TOPPINGS');
?>

<script>

	var GROUPS_COUNT   = <?php echo count($groups); ?>;
	var SELECTED_INDEX = null;

	jQuery(document).ready(function() {

		jQuery('#groups-card-container').sortable({
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
			openGroupCard();
		});

		// fill the form before showing the inspector
		jQuery('#entry-group-inspector').on('inspector.show', function() {
			var data = {};

			// in case the INDEX is a number, extract the entry group data
			if (SELECTED_INDEX !== undefined && SELECTED_INDEX !== null) {
				var fieldset = jQuery('#group-fieldset-' + SELECTED_INDEX);
				
				fieldset.find('input[type="hidden"][name^="group_"]')
					.each(function() {
						var name  = jQuery(this).attr('name').match(/^group_([a-z0-9_]+)\[\]$/i);
						var value = jQuery(this).val();

						if (name && name.length) {
							data[name[1]] = value;
						}
					});

				data.toppings = [];

				var tp_id    = fieldset.find('input[name^="topping_id["]');
				var tp_assoc = fieldset.find('input[name^="topping_id_assoc["]');
				var tp_rate  = fieldset.find('input[name^="topping_rate["]');

				for (var i = 0; i < tp_id.length; i++) {
					data.toppings.push({
						id:       jQuery(tp_id[i]).val(),
						id_assoc: jQuery(tp_assoc[i]).val(),
						rate:     jQuery(tp_rate[i]).val(),
					});
				}
			}

			// fill the form with the retrieved data
			fillEntryGroupForm(data);
		});

		jQuery('#entry-group-save').on('click', function() {
			// validate form
			if (!groupValidator.validate()) {
				return false;
			}

			// get updated entry group data
			var data = getEntryGroupData();

			var index = SELECTED_INDEX;

			if (index === undefined || index === null) {
				index = GROUPS_COUNT;

				addGroupCard(data);

				// update toppings badge
				jQuery('#tkentry_toppings_tab_badge').attr('data-count', jQuery('.delivery-fieldset[id^="group-fieldset-"]').length);
			} else {
				// inject data within the form
				for (var k in data) {
					if (data.hasOwnProperty(k) && k != 'attributes') {
						jQuery('#group-fieldset-' + index)
							.find('input[name="group_' + k + '[]"]').val(data[k]);
					}
				}

				// remove all group toppings before re-adding them
				jQuery('#group-fieldset-' + index)
					.find('input[name^="topping_"]').remove();

				// update group toppings
				addGroupToppings(index, data.toppings);
			}

			// refresh details shown in card
			refreshGroupCard(index, data);

			// dismiss inspector
			jQuery('#entry-group-inspector').inspector('dismiss');
		});

		jQuery('#entry-group-delete').on('click', function() {
			var r = confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'));

			if (!r) {
				return false;
			}

			jQuery('#entry-group-inspector').inspector('dismiss');

			var index = SELECTED_INDEX;

			var fieldset = jQuery('#group-fieldset-' + SELECTED_INDEX);
			var id       = parseInt(fieldset.find('input[name="group_id[]"]').val());
			var tmp_id   = parseInt(fieldset.find('input[name="group_tmp_id[]"]').val());

			if (!isNaN(id) && id > 0) {
				jQuery('#adminForm').append('<input type="hidden" name="delete_group[]" value="' + id + '" />');
			}

			// NOTE: do not need to delete toppings too because they will be removed
			// in cascade while erasing the parent entry group.

			fieldset.remove();

			// update toppings count
			jQuery('#tkentry_toppings_tab_badge').attr('data-count', jQuery('.delivery-fieldset[id^="group-fieldset-"]').length);
		});

	});

	function openGroupCard(index) {
		SELECTED_INDEX = index;

		var title;

		if (typeof index === 'undefined') {
			title = Joomla.JText._('VRE_ADD_TOPPING_GROUP');
			jQuery('#entry-group-delete').hide();
		} else {
			title = Joomla.JText._('VRE_EDIT_TOPPING_GROUP');
			jQuery('#entry-group-delete').show();
		}
		
		// open inspector
		vreOpenInspector('entry-group-inspector', {title: title});
	}

	function addGroupCard(data) {
		var index = GROUPS_COUNT;

		var html = jQuery('#group-struct').clone().html();

		html = html.replace(/{id}/, index);

		jQuery(
			'<div class="delivery-fieldset vre-card-fieldset" id="group-fieldset-' + index + '">' + html + '</div>'
		).insertBefore('#add-delivery-location');

		jQuery('#group-card-' + index).vrecard('edit', 'openGroupCard(' + index + ')');

		jQuery('#group-fieldset-' + index).append(
			'<input type="hidden" name="group_id[]" value="0" />\n'+
			'<input type="hidden" name="group_tmp_id[]" value="' + index + '" />\n'+
			'<input type="hidden" name="group_id_variation[]" value="' + data.id_variation + '" />\n'+
			'<input type="hidden" name="group_title[]" value="' + data.title.replace(/"/g, '&quot;') + '" />\n'+
			'<input type="hidden" name="group_description[]" value="' + data.description.replace(/"/g, '&quot;') + '" />\n'+
			'<input type="hidden" name="group_multiple[]" value="' + data.multiple + '" />\n'+
			'<input type="hidden" name="group_min_toppings[]" value="' + data.min_toppings + '" />\n'+
			'<input type="hidden" name="group_max_toppings[]" value="' + data.max_toppings + '" />\n'+
			'<input type="hidden" name="group_use_quantity[]" value="' + data.use_quantity + '" />\n'
		);

		addGroupToppings(index, data.toppings);

		GROUPS_COUNT++;
	}

	function addGroupToppings(index, toppings) {
		for (var i = 0; i < toppings.length; i++) {
			var topping = toppings[i];

			jQuery('#group-fieldset-' + index).append(
				'<input type="hidden" name="topping_id[' + index + '][]" value="' + topping.id + '" />\n'+
				'<input type="hidden" name="topping_id_assoc[' + index + '][]" value="' + topping.id_assoc + '" />\n'+
				'<input type="hidden" name="topping_rate[' + index + '][]" value="' + topping.rate + '" />\n'
			);
		}
	}

	function refreshGroupCard(index, data) {
		var card = jQuery('#group-card-' + index);

		// update badge
		var icon;

		if (parseInt(data.multiple) == 1) {
			icon = 'check-circle';
		} else {
			icon = 'dot-circle';
		}

		card.vrecard('badge', '<i class="fas fa-' + icon + '"></i>');

		// update primary text
		card.vrecard('primary', data.title);

		// update secondary text
		var secondary = '';
		var text;

		if (data.toppings.length == 1) {
			text = Joomla.JText._('VRE_N_TOPPINGS_1');
		} else if (data.toppings.length > 1) {
			text = Joomla.JText._('VRE_N_TOPPINGS').replace(/%d/, data.toppings.length);
		} else {
			text = Joomla.JText._('VRE_N_TOPPINGS_0');
		}

		secondary += '<span class="badge badge-' + (data.toppings.length ? 'info' : 'important') + ' group-toppings">' + text + '</span>';

		if (parseInt(data.id_variation)) {
			var variations = <?php echo json_encode($this->entry->variations); ?>;

			for (var i = 0, varname = null; i < variations.length && !varname; i++) {
				if (variations[i].id == data.id_variation) {
					varname = variations[i].name;
				}
			}

			if (varname) {
				secondary += '<span class="badge badge-warning group-variation">' + varname + '</span>';
			}
		}

		card.vrecard('secondary', secondary);
	}

</script>
