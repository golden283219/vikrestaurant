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

$varLayout = new JLayoutFile('blocks.card');

?>
	
<div class="span12">
	<?php echo $vik->openFieldset(JText::_('VRMENUPRODFIELDSET2')); ?>
			
		<div class="vr-delivery-locations-container vre-cards-container" id="variations-card-container">

			<?php
			foreach ($this->product->variations as $i => $option)
			{
				?>
				<div class="delivery-fieldset vre-card-fieldset up-to-2" id="option-fieldset-<?php echo $i; ?>">

					<?php
					$displayData = array();

					// fetch card ID
					$displayData['id'] = 'option-card-' . $i;

					// fetch primary text
					$displayData['primary']  = $option->name;

					// fetch secondary text
					$displayData['secondary'] = '<span class="badge badge-info option-cost">' . $currency->format($option->inc_price) . '</span>';

					// fetch edit button
					$displayData['edit'] = 'openOptionCard(' . $i . ');';

					// render layout
					echo $varLayout->render($displayData);
					?>
					
					<input type="hidden" name="option_id[]" value="<?php echo $option->id; ?>" />
					<input type="hidden" name="option_name[]" value="<?php echo $this->escape($option->name); ?>" />
					<input type="hidden" name="option_inc_price[]" value="<?php echo $option->inc_price; ?>" />

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
			// create product option structure for new items
			$displayData = array();
			$displayData['id']        = 'option-card-{id}';
			$displayData['primary']   = '';
			$displayData['secondary'] = '';
			$displayData['edit']      = true;

			echo $varLayout->render($displayData);
			?>

		</div>

	<?php echo $vik->closeFieldset(); ?>
</div>

<?php
JText::script('VRE_ADD_VARIATION');
JText::script('VRE_EDIT_VARIATION');
?>

<script>

	var OPTIONS_COUNT   = <?php echo count($this->product->variations); ?>;
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
		jQuery('#product-option-inspector').on('inspector.show', function() {
			var data = {};

			// in case the INDEX is a number, extract the product option data
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
			fillProductOptionForm(data);
		});

		jQuery('#product-option-save').on('click', function() {
			// validate form
			if (!optionValidator.validate()) {
				return false;
			}

			// get updated product option data
			var data = getProductOptionData();

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
			jQuery('#product-option-inspector').inspector('dismiss');
		});

		jQuery('#product-option-delete').on('click', function() {
			var r = confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'));

			if (!r) {
				return false;
			}

			jQuery('#product-option-inspector').inspector('dismiss');

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
			jQuery('#product-option-delete').hide();
		} else {
			title = Joomla.JText._('VRE_EDIT_VARIATION');
			jQuery('#product-option-delete').show();
		}
		
		// open inspector
		vreOpenInspector('product-option-inspector', {title: title});
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
			'<input type="hidden" name="option_inc_price[]" value="' + data.inc_price + '" />\n'
		);

		OPTIONS_COUNT++;
	}

	function refreshOptionCard(index, data) {
		var card = jQuery('#option-card-' + index);

		// update primary text
		card.vrecard('primary', data.name);

		// update secondary text
		var secondary = '<span class="badge badge-info option-cost">' + Currency.getInstance().format(data.inc_price) + '</span>';
		card.vrecard('secondary', secondary);
	}

</script>
