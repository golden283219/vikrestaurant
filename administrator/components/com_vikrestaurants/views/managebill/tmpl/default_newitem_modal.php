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

<div class="inspector-form" id="inspector-newprod-form">

	<!-- PRODUCT NAME - Text -->

	<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT2') . '*'); ?>
		<input type="text" name="product_name" value="" size="32" class="required" />
	<?php echo $vik->closeControl() ?>

	<!-- PRODUCT PRICE - Number -->

	<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT4')); ?>
		<div class="input-prepend currency-field">
			<button type="button" class="btn"><?php echo VREFactory::getCurrency()->getSymbol(); ?></button>
			
			<input type="number" name="product_price" value="0" size="4" min="0" step="any" />
		</div>
	<?php echo $vik->closeControl(); ?>

	<!-- PRODUCT QUANTITY - Number -->

	<?php echo $vik->openControl(JText::_('VRMANAGETKRES20')); ?>
		<input type="number" name="product_quantity" value="1" size="4" min="1" step="1" style="text-align: right;" />
	<?php echo $vik->closeControl(); ?>

	<!-- PRODUCT NOTES - Textarea -->

	<?php echo $vik->openControl(JText::_('VRMANAGETKRESTITLE4')); ?>
		<textarea name="product_notes" maxlength="128"></textarea>
	<?php echo $vik->closeControl(); ?>

</div>

<script>

	var newProdValidator = new VikFormValidator('#inspector-newprod-form');

	function clearHiddenProductForm() {
		var form = jQuery('#inspector-newprod-form');

		form.find('input[name="product_name"]').val('');
		form.find('input[name="product_price"]').val(0);
		form.find('input[name="product_quantity"]').val(1);
		form.find('textarea[name="product_notes"]').val('');

		newProdValidator.unsetInvalid(form.find('input[name="product_name"]'));
	}
	
	function getHiddenProductData() {
		var data = {};

		jQuery('#inspector-newprod-form')
			.find('input,textarea')
				.filter('[name^="product_"]')
					.each(function() {
						var name  = jQuery(this).attr('name').replace(/^product_/, '');
						var value = jQuery(this).val();

						data[name] = value;						
					});

		return data;
	}

</script>
