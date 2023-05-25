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

		<!-- OPTION ALIAS - Text -->
		<?php echo $vik->openControl(JText::_('JFIELD_ALIAS_LABEL')); ?>
			<input type="text" id="option_alias" value="" class="field" size="40" />
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

		<!-- PUBLISHED - Checkbox -->
		<?php
		$yes = $vik->initRadioElement('', JText::_('JYES'), true);
		$no  = $vik->initRadioElement('', JText::_('JNO'), false);

		echo $vik->openControl(JText::_('VRMANAGETKMENU12') . $help);
		echo $vik->radioYesNo('option_published', $yes, $no, false);
		echo $vik->closeControl();
		?>

	</div>

	<?php if (VikRestaurants::isTakeAwayStockEnabled()) { ?>

		<div class="inspector-fieldset">
			<h3><?php echo JText::_('VRMANAGECONFIGTKSECTION2'); ?></h3>

			<!-- STOCK ENABLED - Checkbox -->
			<?php
			$help = $vik->createPopover(array(
				'title'     => JText::_('VRMANAGETKSTOCK5'),
				'content'   => JText::_('VRMANAGETKSTOCK5_HELP'),
				'placement' => 'top',	
			));

			$yes = $vik->initRadioElement('', JText::_('JYES'), true, 'onclick="enableOptionStocks(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), false, 'onclick="enableOptionStocks(0);"');

			echo $vik->openControl(JText::_('VRMANAGETKSTOCK5') . $help);
			echo $vik->radioYesNo('option_stock_enabled', $yes, $no, false);
			echo $vik->closeControl();
			?>

			<!-- ITEMS IN STOCK - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'     => JText::_('VRMANAGETKSTOCK3'),
				'content'   => JText::_('VRMANAGETKSTOCK3_HELP'),
				'placement' => 'top',
			));

			echo $vik->openControl(JText::_('VRMANAGETKSTOCK3') . $help); ?>
				<input type="number" id="option_items_in_stock" class="field" value="0" min="0" max="999999" step="1" />
			<?php echo $vik->closeControl(); ?>

			<!-- NOTIFY BELOW - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'     => JText::_('VRMANAGETKSTOCK4'),
				'content'   => JText::_('VRMANAGETKSTOCK4_HELP'),
				'placement' => 'top',
			));

			echo $vik->openControl(JText::_('VRMANAGETKSTOCK4') . $help); ?>
				<input type="number" id="option_notify_below" class="field" value="0" min="0" max="999999" step="1" />
			<?php echo $vik->closeControl(); ?>

		</div>

	<?php } ?>

	<input type="hidden" id="option_id" class="field" value="" />

</div>

<script>

	var optionValidator = new VikFormValidator('#inspector-option-form');

	function fillEntryOptionForm(data) {
		// update name
		if (data.name === undefined) {
			data.name = '';
		}

		jQuery('#option_name').val(data.name);

		optionValidator.unsetInvalid(jQuery('#option_name'));

		// update alias
		if (data.alias === undefined) {
			data.alias = '';
		}

		jQuery('#option_alias').val(data.alias);

		// update price
		if (data.inc_price === undefined) {
			data.inc_price = 0.0;
		}

		jQuery('#option_inc_price').val(data.inc_price);

		// update published
		var pubInput = jQuery('input[name="option_published"]');

		if (data.published === undefined) {
			data.published = true;
		} else if (('' + data.published).match(/^[\d]+$/)) {
			data.published = parseInt(data.published);
		}

		if (pubInput.attr('type') == 'checkbox') {
			pubInput.prop('checked', data.published ? true : false);
		} else {
			pubInput.val(data.published ? 1 : 0);
		}

		// update stock enabled
		var stockInput = jQuery('input[name="option_stock_enabled"]');

		if (data.stock_enabled === undefined) {
			data.stock_enabled = true;
		} else if (('' + data.stock_enabled).match(/^[\d]+$/)) {
			data.stock_enabled = parseInt(data.stock_enabled);
		}

		if (stockInput.attr('type') == 'checkbox') {
			stockInput.prop('checked', data.stock_enabled ? true : false);
		} else {
			stockInput.val(data.stock_enabled ? 1 : 0);
		}

		// toggle "stocks enabled" status
		enableOptionStocks(data.stock_enabled ? 1 : 0);

		// update items in stock
		if (data.items_in_stock === undefined) {
			data.items_in_stock = 9999;
		}

		jQuery('#option_items_in_stock').val(data.items_in_stock);

		// update notify below
		if (data.notify_below === undefined) {
			data.notify_below = 5;
		}

		jQuery('#option_notify_below').val(data.notify_below);
		
		// update ID
		jQuery('#option_id').val(data.id);
	}

	function getEntryOptionData() {
		var data = {};

		// set ID
		data.id = jQuery('#option_id').val();

		// set name
		data.name = jQuery('#option_name').val();

		// set alias
		data.alias = jQuery('#option_alias').val();

		// set price
		data.inc_price = jQuery('#option_inc_price').val();

		// set published
		if (jQuery('input[name="option_published"]').attr('type') == 'checkbox') {
			data.published = jQuery('input[name="option_published"]').is(':checked') ? 1 : 0;
		} else {
			data.published = parseInt(jQuery('input[name="option_published"]').val());
		}

		// set stock enabled
		if (jQuery('input[name="option_stock_enabled"]').attr('type') == 'checkbox') {
			data.stock_enabled = jQuery('input[name="option_stock_enabled"]').is(':checked') ? 1 : 0;
		} else {
			data.stock_enabled = parseInt(jQuery('input[name="option_stock_enabled"]').val());
		}

		// set items in stock
		data.items_in_stock = jQuery('#option_items_in_stock').val();

		// set notify below
		data.notify_below = jQuery('#option_notify_below').val();

		return data;
	}

	function enableOptionStocks(is) {
		jQuery('#option_items_in_stock, #option_notify_below').prop('readonly', is ? false : true);
	}

</script>
