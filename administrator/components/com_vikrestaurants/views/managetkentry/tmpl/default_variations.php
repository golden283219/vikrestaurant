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

$deliveryLayout = new JLayoutFile('blocks.card');

?>
	
<div class="span12">
	<?php echo $vik->openFieldset(JText::_('VRMANAGETKENTRYFIELDSET2')); ?>
			
		<div class="vr-delivery-locations-container vre-cards-container" id="variations-card-container">

			<?php
			foreach ($this->entry->variations as $i => $option)
			{
				?>
				<div class="delivery-fieldset vre-card-fieldset up-to-2" id="option-fieldset-<?php echo $i; ?>">

					<?php
					$displayData = array();

					// fetch card ID
					$displayData['id'] = 'option-card-' . $i;

					// fetch card class
					if ($option->published)
					{
						$displayData['class'] = 'published';
					}

					// fetch primary text
					$displayData['primary']  = $option->name;

					// fetch secondary text
					$displayData['secondary'] = '<span class="badge badge-info option-cost">' . $currency->format($option->inc_price) . '</span>';

					if ($option->published)
					{
						$icon = 'check-circle';
					}
					else
					{
						$icon = 'dot-circle';
					}

					// fetch badge
					$displayData['badge'] = '<i class="fas fa-' . $icon . '"></i>';

					// fetch edit button
					$displayData['edit'] = 'openOptionCard(' . $i . ');';

					// render layout
					echo $deliveryLayout->render($displayData);
					?>
					
					<input type="hidden" name="option_id[]" value="<?php echo $option->id; ?>" />
					<input type="hidden" name="option_name[]" value="<?php echo $this->escape($option->name); ?>" />
					<input type="hidden" name="option_alias[]" value="<?php echo $this->escape($option->alias); ?>" />
					<input type="hidden" name="option_inc_price[]" value="<?php echo $option->inc_price; ?>" />
					<input type="hidden" name="option_published[]" value="<?php echo $option->published; ?>" />
					<input type="hidden" name="option_stock_enabled[]" value="<?php echo $option->stock_enabled; ?>" />
					<input type="hidden" name="option_items_in_stock[]" value="<?php echo $option->items_in_stock; ?>" />
					<input type="hidden" name="option_notify_below[]" value="<?php echo $option->notify_below; ?>" />

				</div>
				<?php
			}
			?>

			<div class="delivery-fieldset vre-card-fieldset up-to-2" id="add-entry-option">
				<div class="vre-card">
					<i class="fas fa-plus"></i>
				</div>
			</div>

		</div>

		<div style="display:none;" id="option-struct">
			
			<?php
			// create entry option structure for new items
			$displayData = array();
			$displayData['id']        = 'option-card-{id}';
			$displayData['primary']   = '';
			$displayData['secondary'] = '';
			$displayData['badge']     = '<i class="fas fa-check-circle"></i>';
			$displayData['edit']      = true;

			echo $deliveryLayout->render($displayData);
			?>

		</div>

	<?php echo $vik->closeFieldset(); ?>
</div>

<?php
JText::script('VRE_ADD_VARIATION');
JText::script('VRE_EDIT_VARIATION');
?>

<script>

	var OPTIONS_COUNT   = <?php echo count($this->entry->variations); ?>;
	var SELECTED_OPTION = null;

	jQuery(document).ready(function() {

		jQuery('#variations-card-container').sortable({
			// exclude "add" boxs
			items: '.delivery-fieldset:not(#add-entry-option)',
			// hide "add" box when sorting starts
			start: function() {
				jQuery('#add-entry-option').hide();
			},
			// show "add" box again when sorting stops
			stop: function() {
				jQuery('#add-entry-option').show();
			},
		});

		jQuery('#add-entry-option').on('click', function() {
			openOptionCard();
		});

		// fill the form before showing the inspector
		jQuery('#entry-option-inspector').on('inspector.show', function() {
			var data = {};

			// in case the INDEX is a number, extract the entry option data
			if (SELECTED_OPTION !== undefined && SELECTED_OPTION !== null) {
				var fieldset = jQuery('#option-fieldset-' + SELECTED_OPTION);
				
				fieldset.find('input[type="hidden"][name^="option_"]')
					.each(function() {
						var name  = jQuery(this).attr('name').match(/^option_([a-z0-9_]+)\[\]$/i);
						var value = jQuery(this).val();

						if (name && name.length) {
							data[name[1]] = value;
						}
					});
			}

			// fill the form with the retrieved data
			fillEntryOptionForm(data);
		});

		jQuery('#entry-option-save').on('click', function() {
			// validate form
			if (!optionValidator.validate()) {
				return false;
			}

			// get updated entry option data
			var data = getEntryOptionData();

			var index = SELECTED_OPTION;

			if (index === undefined || index === null) {
				index = OPTIONS_COUNT;

				addOptionCard(data);
			} else {
				// inject data within the form
				for (var k in data) {
					if (data.hasOwnProperty(k)) {
						jQuery('#option-fieldset-' + index)
							.find('input[name="option_' + k + '[]"]').val(data[k]);
					}
				}
			}

			// refresh details shown in card
			refreshOptionCard(index, data);

			// dismiss inspector
			jQuery('#entry-option-inspector').inspector('dismiss');
		});

		jQuery('#entry-option-delete').on('click', function() {
			var r = confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'));

			if (!r) {
				return false;
			}

			jQuery('#entry-option-inspector').inspector('dismiss');

			var index = SELECTED_OPTION;

			var fieldset = jQuery('#option-fieldset-' + SELECTED_OPTION);
			var id       = parseInt(fieldset.find('input[name="option_id[]"]').val());

			if (!isNaN(id) && id > 0) {
				jQuery('#adminForm').append('<input type="hidden" name="delete_option[]" value="' + id + '" />');
			}

			fieldset.remove();
		});

	});

	function openOptionCard(index) {
		SELECTED_OPTION = index;

		var title;

		if (typeof index === 'undefined') {
			title = Joomla.JText._('VRE_ADD_VARIATION');
			jQuery('#entry-option-delete').hide();
		} else {
			title = Joomla.JText._('VRE_EDIT_VARIATION');
			jQuery('#entry-option-delete').show();
		}
		
		// open inspector
		vreOpenInspector('entry-option-inspector', {title: title});
	}

	function addOptionCard(data) {
		var index = OPTIONS_COUNT;

		var html = jQuery('#option-struct').clone().html();

		html = html.replace(/{id}/, index);

		jQuery(
			'<div class="delivery-fieldset vre-card-fieldset up-to-2" id="option-fieldset-' + index + '">' + html + '</div>'
		).insertBefore('#add-entry-option');

		jQuery('#option-card-' + index).vrecard('edit', 'openOptionCard(' + index + ')');

		jQuery('#option-fieldset-' + index).append(
			'<input type="hidden" name="option_id[]" value="0" />\n'+
			'<input type="hidden" name="option_name[]" value="' + data.name.replace(/"/g, '&quot;') + '" />\n'+
			'<input type="hidden" name="option_alias[]" value="' + data.alias + '" />\n'+
			'<input type="hidden" name="option_inc_price[]" value="' + data.inc_price + '" />\n'+
			'<input type="hidden" name="option_published[]" value="' + data.published + '" />\n'+
			'<input type="hidden" name="option_stock_enabled[]" value="' + data.stock_enabled + '" />\n'+
			'<input type="hidden" name="option_items_in_stock[]" value="' + data.items_in_stock + '" />\n'+
			'<input type="hidden" name="option_notify_below[]" value="' + data.notify_below + '" />\n'
		);

		OPTIONS_COUNT++;
	}

	function refreshOptionCard(index, data) {
		var card = jQuery('#option-card-' + index);

		// update badge
		var icon;

		if (parseInt(data.published) == 1) {
			icon = 'check-circle';
		} else {
			icon = 'dot-circle';
		}

		if (parseInt(data.published) == 1) {
			card.addClass('published');
		} else {
			card.removeClass('published');
		}

		card.vrecard('badge', '<i class="fas fa-' + icon + '"></i>');

		// update primary text
		card.vrecard('primary', data.name);

		// update secondary text
		var secondary = '<span class="badge badge-info option-cost">' + Currency.getInstance().format(data.inc_price) + '</span>';
		card.vrecard('secondary', secondary);
	}

</script>
