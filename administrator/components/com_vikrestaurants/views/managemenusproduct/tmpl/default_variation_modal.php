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

?>

<div class="inspector-form" id="inspector-option-form">

	<div class="inspector-fieldset">
		<h3><?php echo JText::_('VRMAPDETAILSBUTTON'); ?></h3>
	
		<!-- OPTION NAME - Text -->
		<?php echo $vik->openControl(JText::_('VRMANAGETKMENU4') . '*'); ?>
			<input type="text" id="option_name" value="" class="field required" size="40" />
		<?php echo $vik->closeControl(); ?>

		<!-- OPTION INC PRICE - Number -->
		<?php
		$help = $vik->createPopover(array(
			'title'     => JText::_('VRMANAGETKMENU5'),
			'content'   => JText::_('VRE_PRODUCT_INC_PRICE_SHORT'),
			'placement' => 'top',
		));

		echo $vik->openControl(JText::_('VRMANAGETKMENU5') . $help); ?>
			<div class="input-prepend currency-field">
				<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

				<input type="number" id="option_inc_price" class="field" value="0.0" min="-999999" max="999999" step="any" />
			</div>
		<?php echo $vik->closeControl(); ?>

	</div>

	<input type="hidden" id="option_id" class="field" value="" />

</div>

<script>

	var optionValidator = new VikFormValidator('#inspector-option-form');

	function fillProductOptionForm(data) {
		// update name
		if (data.name === undefined) {
			data.name = '';
		}

		jQuery('#option_name').val(data.name);

		optionValidator.unsetInvalid(jQuery('#option_name'));

		// update price
		if (data.inc_price === undefined) {
			data.inc_price = 0.0;
		}

		jQuery('#option_inc_price').val(data.inc_price);
		
		// update ID
		jQuery('#option_id').val(data.id);
	}

	function getProductOptionData() {
		var data = {};

		// set ID
		data.id = jQuery('#option_id').val();

		// set name
		data.name = jQuery('#option_name').val();

		// set price
		data.inc_price = jQuery('#option_inc_price').val();

		return data;
	}

</script>
