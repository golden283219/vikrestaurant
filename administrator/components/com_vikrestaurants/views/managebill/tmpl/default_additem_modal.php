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

?>

<div class="inspector-form" id="inspector-addprod-form">

	<!-- PRODUCT NAME - Text -->

	<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT2')); ?>
		<input type="text" name="product_name" value="" readonly />
	<?php echo $vik->closeControl(); ?>

	<!-- PRODUCT VARIATION - Select -->

	<?php echo $vik->openControl(JText::_('VRTKCARTOPTION5') . '*', 'product-var-control'); ?>
		<select name="product_id_option"></select>
	<?php echo $vik->closeControl(); ?>

	<!-- PRODUCT PRICE - Number -->

	<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT4')); ?>
		<div class="input-prepend currency-field">
			<button type="button" class="btn"><?php echo VREFactory::getCurrency()->getSymbol(); ?></button>

			<input type="number" name="product_price" data-price="" value="0" min="0" step="any" />
		</div>
	<?php echo $vik->closeControl(); ?>

	<!-- PRODUCT QUANTITY - Number -->

	<?php echo $vik->openControl(JText::_('VRMANAGETKRES20')); ?>
		<input type="number" name="product_quantity" value="1" min="1" step="1" style="text-align: right;" />
	<?php echo $vik->closeControl(); ?>

	<!-- PRODUCT NOTES - Textarea -->

	<?php echo $vik->openControl(JText::_('VRMANAGETKRESTITLE4')); ?>
		<textarea name="product_notes" maxlength="128" style="width: 80%;height:100px;"></textarea>
	<?php echo $vik->closeControl(); ?>

	<input type="hidden" name="product_index" value="" />
	<input type="hidden" name="product_id" value="" />

</div>

<script>

	var addProdValidator = new VikFormValidator('#inspector-addprod-form');

	jQuery(document).ready(function() {
		jQuery('#inspector-addprod-form select').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 'auto',
		});

		jQuery('select[name="product_id_option"]').on('change', function() {
			var input = jQuery('#inspector-addprod-form input[name="product_price"]');

			var def_price = parseFloat(input.attr('data-price'));
			var inc_price = parseFloat(jQuery(this).find('option:selected').attr('data-price'));

			// update price based on default cost + variation cost
			input.val(def_price + inc_price);
		});
	});

	function fillProductForm(data) {
		if (!data.quantity) {
			data.quantity = 1;
		}

		if (!data.index) {
			data.index = 0;
		}

		if (!data.variations) {
			data.variations = [];
		}

		// build variations dropdown
		var html = '';

		for (var i = 0; i < data.variations.length; i++) {
			var v = data.variations[i];

			html += '<option value="' + v.id + '" data-price="' + v.price + '">' + v.name + '</option>';

			if (data.id_option == v.id) {
				// calculate base cost by subtracting the variation cost
				data.price -= parseFloat(v.price);
			}
		}

		var variationSelect = jQuery('select[name="product_id_option"]');

		variationSelect.html(html);

		jQuery('#inspector-addprod-form')
			.find('input,textarea')
				.filter('[name^="product_"]')
					.each(function() {
						var key = jQuery(this).attr('name').replace(/^product_/, '');

						if (!data.hasOwnProperty(key)) {
							data[key] = '';
						}

						jQuery(this).val(data[key]);

						if (key == 'price') {
							jQuery(this).attr('data-price', data[key]);
						}

						addProdValidator.unsetInvalid(this);
					});

		if (data.variations.length) {
			// get first option
			var id_var = data.id_option ? data.id_option : variationSelect.first().val();

			// select first variation available and update the price
			variationSelect.select2('val', id_var).trigger('change');

			// make variation select required
			addProdValidator.registerFields(variationSelect);

			jQuery('.product-var-control').show();
		} else {
			// make variation select optional
			addProdValidator.unregisterFields(variationSelect);

			jQuery('.product-var-control').hide();
		}

		addProdValidator.unsetInvalid(variationSelect);
	}
	
	function getProductData() {
		var data = {};

		jQuery('#inspector-addprod-form')
			.find('input,textarea,select')
				.filter('[name^="product_"]')
					.each(function() {
						var name  = jQuery(this).attr('name').replace(/^product_/, '');
						var value = jQuery(this).val();

						data[name] = value;						
					});

		return data;
	}

</script>
