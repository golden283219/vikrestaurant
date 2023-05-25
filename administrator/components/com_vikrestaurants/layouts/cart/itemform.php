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

/**
 * Layout variables
 * -----------------
 * @var  object  $product  The product details.
 * @var  object  $item     The selected item data.
 */
extract($displayData);

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

$toppings_json  = array();
$vars_json      = array();
$item_var_price = 0;

// fetch remaining items in stock
if (isset($product->stock))
{
	// use product stock by default
	$stock = $product->stock;

	foreach ($product->variations as $var)
	{
		if ($var->id == $item->id_product_option)
		{
			// overwrite with variation stock
			$stock = $var->stock;
		}
	}
}
else
{
	$stock = null;
}

?>

<div class="inspector-form" id="inspector-entry-form">

	<!-- Product details -->

	<div class="inspector-fieldset">
		<h3><?php echo JText::_('VRMAPDETAILSBUTTON'); ?></h3>
	
		<!-- ENTRY NAME - Text -->

		<?php
		if (!is_null($stock))
		{
			// fetch HELP text to use
			$help = $product->variations ? 'VRMANAGETKCARTSTOCK_VAR_HELP' : 'VRMANAGETKCARTSTOCK_HELP';

			$help = '<i class="fas fa-question-circle vr-quest-popover hasTooltip" title="' . $this->escape(JText::_($help)) . '"></i>';
		}
		else
		{
			$help = '';
		}

		echo $vik->openControl(JText::_('VRMANAGETKMENU1') . $help);

		if (!is_null($stock))
		{
			?>
			<div class="input-append">
				<input type="text" id="entry_name" value="<?php echo $product->name; ?>" class="field" readonly />

				<button type="button" class="btn">
					<i class="fas fa-archive"></i>
					<span id="entry_stock"><?php echo $stock; ?></span>
				</button>
			</div>
			<?php
		}
		else
		{
			?>
			<input type="text" id="entry_name" value="<?php echo $product->name; ?>" class="field" size="40" readonly />
			<?php
		}

		echo $vik->closeControl();
		?>

		<!-- GROUP VARIATION - Select -->

		<?php
		if ($product->variations)
		{
			$variations = array();

			foreach ($product->variations as $var)
			{
				$name = $var->name;

				if ($var->inc_price != 0)
				{
					$name .= ' : ' . $currency->format($var->inc_price);
				}

				if ($var->id == $item->id_product_option)
				{
					// keep price of the selected variation
					$item_var_price = $var->inc_price;
				}

				// create VARIATION lookup
				$vars_json[$var->id] = array();
				$vars_json[$var->id]['price'] = $var->inc_price;

				if (isset($var->stock))
				{
					$vars_json[$var->id]['stock'] = $var->stock;
				}

				$variations[] = JHtml::_('select.option', $var->id, $name);
			}

			echo $vik->openControl(JText::_('VRMANAGETKENTRYFIELDSET2') . '*'); ?>
				<select id="entry_variation" class="field required">
					<?php echo JHtml::_('select.options', $variations, 'value', 'text', $item->id_product_option); ?>
				</select>
			<?php echo $vik->closeControl();
		}
		else
		{
			?><input type="hidden" id="entry_variation" value="0" class="field" /><?php
		}
		?>

		<!-- PRICE - Number -->

		<?php echo $vik->openControl(JText::_('VRMANAGETKMENU5')); ?>
			<div class="input-prepend currency-field">
				<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

				<input type="number" id="entry_price" class="field" value="<?php echo $item->price; ?>" min="-999999" max="999999" step="any" />
			</div>
		<?php echo $vik->closeControl(); ?>

		<!-- QUANTITY - Number -->

		<?php echo $vik->openControl(JText::_('VRMANAGETKRES20')); ?>
			<input type="number" id="entry_quantity" value="<?php echo $item->quantity; ?>" min="1" step="1" style="text-align: right;" />
		<?php echo $vik->closeControl(); ?>

	</div>

	<?php
	if ($product->groups)
	{
		?>
		<!-- Product Toppings -->

		<div class="inspector-fieldset">
			<h3><?php echo JText::_('VRMENUTAKEAWAYTOPPINGS'); ?></h3>
			
			<?php
			// iterate topping groups
			foreach ($product->groups as $group)
			{
				$required = $group->min_toppings > 0;
				$suitable = in_array($group->id_variation, array(0, $item->id_product_option));

				$control = array(
					'data-id'        => $group->id,
					'data-variation' => $group->id_variation,
					'style'          => $suitable ? '' : 'display:none;',
				);

				$badges = '';

				if ($group->multiple)
				{
					if ($group->max_toppings)
					{
						$badges .= '<span class="badge badge-important pull-right" style="margin-left:4px;">' . JText::sprintf('VRE_MAX_N', $group->max_toppings) . '</span>';
					}

					if ($group->min_toppings)
					{
						$badges .= '<span class="badge badge-warning pull-right" style="margin-left:4px;">' . JText::sprintf('VRE_MIN_N', $group->min_toppings) . '</span>';
					}
				}

				echo $vik->openControl($group->description . ($required ? '*' : '') . $badges, 'toppings-control', $control);
				
				if (!$group->multiple)
				{
					// single-selection
					?>
					<select class="entry_group_toppings required" data-id="<?php echo $group->id; ?>">
						<?php
						$toppings_json[$group->id] = 0;

						foreach ($group->toppings as $topping)
						{
							$selected = '';

							// check if the topping has been selected
							if (isset($item->toppingGroupsRel[$group->id][$topping->id]))
							{
								$selected = ' selected="selected"';

								if ($suitable)
								{
									// increase map price with selected topping only if suitable
									$toppings_json[$group->id] = $topping->rate;
								}
							}

							?>
							<option value="<?php echo $topping->id; ?>" data-rate="<?php echo $topping->rate; ?>"<?php echo $selected; ?>>
								<?php
								echo $topping->name;

								if ($topping->rate != 0)
								{
									echo ' : ' . $currency->format($topping->rate);
								}
								?>
							</option>
							<?php
						}
						?>
					</select>
					<?php
				}
				else
				{
					// multi-selection
					?>
					<div class="toppings-group<?php echo $group->use_quantity ? ' use-quantity' : ''; ?>">
						<?php
						foreach ($group->toppings as $topping)
						{
							$checked = '';

							// check if the topping has been checked
							if (isset($item->toppingGroupsRel[$group->id][$topping->id]))
							{
								$checked = 'checked="checked"';

								// use specified number of units
								$units = $item->toppingGroupsRel[$group->id][$topping->id];
							}
							else
							{
								$units = 0;
							}

							?>
							<span>
								<input type="checkbox" value="<?php echo $topping->id; ?>" data-rate="<?php echo $topping->rate; ?>" id="topping-<?php echo $group->id; ?>-<?php echo $topping->id; ?>" <?php echo $checked; ?>/>
								<label for="topping-<?php echo $group->id; ?>-<?php echo $topping->id; ?>"><?php echo $topping->name; ?></label>
								
								<?php
								if ($group->use_quantity)
								{
									?>
									<span class="topping-quantity pull-right" data-units="<?php echo $units; ?>">
										<a href="javascript: void(0);" class="topping-del-unit<?php echo $units > 1 ? '' : ' disabled'; ?>">
											<i class="fas fa-minus-circle medium-big"></i>
										</a>

										<span class="topping-units"><?php echo $units; ?></span>

										<a href="javascript: void(0);" class="topping-add-unit<?php echo $units > 0 ? '' : ' disabled'; ?>">
											<i class="fas fa-plus-circle medium-big"></i>
										</a>
									</span>
									<?php
								}

								if ($topping->rate != 0)
								{
									?>
									<span class="badge badge-info pull-right"><?php echo $currency->format($topping->rate); ?></span>
									<?php
								}
								?>
							</span>
							<?php
						}
						?>
					</div>
					<?php
				}
				
				echo $vik->closeControl();
			}
			?>

		</div>
		<?php
	}
	?>

	<!-- Product Notes -->

	<div class="inspector-fieldset">
		<h3><?php echo JText::_('VRMANAGETKRESTITLE4'); ?></h3>

		<textarea id="entry_notes" maxlength="128" style="height:100px;"><?php echo $item->notes; ?></textarea>
	</div>

	<input type="hidden" id="entry_id" class="field" value="<?php echo $product->id; ?>" />
	<input type="hidden" id="entry_index" class="field" value="<?php echo $item->id; ?>" />

</div>

<script>

	// auto-render form dropdowns with Select2
	jQuery('#inspector-entry-form select').each(function() {
		var data = {
			placeholder: '--',
			allowClear: jQuery(this).find('option').first().val() ? false : true,
			width: '100%',
		};

		if (jQuery(this).find('option').length <= 4) {
			// turn off search in case the select doesn't have more than 4 options
			data.minimumResultsForSearch = -1;
		}

		jQuery(this).select2(data);
	});

	// create tooltip
	jQuery('#inspector-entry-form .hasTooltip').tooltip({container: 'body'});

	var BASE_VAR_PRICE  = <?php echo $item_var_price; ?>;
	var VARIATIONS_JSON = <?php echo json_encode($vars_json); ?>

	// handle variation change
	jQuery('#entry_variation').on('change', function() {
		// get selected variation ID
		var id = jQuery(this).val();

		// iterate toppings groups and toggle them according
		// to the variation that has been selected
		jQuery('.toppings-control').each(function() {
			// get group variation ID
			var id_variation = parseInt(jQuery(this).data('variation'));

			// show toppings group only if available for all variations or if
			// the selected variation matches the one specified for the item
			if (id_variation == 0 || id_variation == id) {
				jQuery(this).show();
			} else {
				jQuery(this).hide();

				// when a group of checkboxes is no more suitable for the selected variation,
				// turn off all the checked toppings and trigger the "change" event to unset
				// their cost from the product price
				jQuery(this).find('.toppings-group input[type="checkbox"]:checked').prop('checked', false).trigger('change');
			}

			// trigger toppings change to refresh price in case the group is
			// no more suitable for the selected variation and vice-versa
			jQuery(this).find('select.entry_group_toppings').trigger('change');
		});

		// get variation price
		var cost = parseFloat(VARIATIONS_JSON[id].price);

		// increase price by the rate of the selected variation
		// and decrease by the rate of the previous one
		updatePrice(cost - BASE_VAR_PRICE);

		// update variation price
		BASE_VAR_PRICE = cost;

		// update stock if supported
		if (VARIATIONS_JSON[id].hasOwnProperty('stock')) {
			jQuery('#entry_stock').text(VARIATIONS_JSON[id].stock);
		}
	});

	// keep relation of current toppings costs
	var TOPPINGS_MAP = <?php echo json_encode($toppings_json); ?>;

	jQuery('.entry_group_toppings').on('change', function() {
		// get topping price
		var rate = parseFloat(jQuery(this).find('option:selected').data('rate'));
		var prev = 0;
		var id   = jQuery(this).data('id');

		// get toppings group ID variation
		var group_var = jQuery(this).closest('*[data-variation]').data('variation');
		// get selected variation
		var sel_var = jQuery('#entry_variation').val();

		// unset topping rate in case the group is not
		// suitable for the selected variation
		if (group_var != 0 && group_var != sel_var) {
			rate = 0;
		}

		// get previous topping rate
		if (TOPPINGS_MAP.hasOwnProperty(id)) {
			// decrease rate by the cost of the previously selected topping
			prev = parseFloat(TOPPINGS_MAP[id]);
		}

		// update map with new rate
		TOPPINGS_MAP[id] = rate;

		// increase price by the rate of the selected topping
		// and decrease by the rate of the previous one
		updatePrice(rate - prev);
	});

	jQuery('.toppings-group input[type="checkbox"]').on('change', function() {
		// get topping price
		var rate = parseFloat(jQuery(this).data('rate'));
		// set units to specify
		var units = 1

		if (jQuery(this).is(':checked') == false) {
			// find current units
			units = getToppingUnits(this);

			// topping unchecked, decrease rate instead
			rate *= units * -1;

			// unset units
			units *= -1;
		}

		// update picked units
		addToppingUnits(this, units);

		// increase/decrease price by the rate of the checked/unchecked topping
		updatePrice(rate);
	});

	jQuery('.topping-del-unit').on('click', function() {
		if (jQuery(this).hasClass('disabled')) {
			return false;
		}

		// find topping
		var topping = jQuery(this).closest('.topping-quantity').siblings('input[type="checkbox"]');

		// decrease units by one
		addToppingUnits(topping, -1);

		// get topping price
		var rate = parseFloat(jQuery(topping).data('rate'));

		// decrease price by the rate of the topping
		updatePrice(rate * -1);
	});

	jQuery('.topping-add-unit').on('click', function() {
		if (jQuery(this).hasClass('disabled')) {
			return false;
		}

		// find topping
		var topping = jQuery(this).closest('.topping-quantity').siblings('input[type="checkbox"]');

		// increase units by one
		addToppingUnits(topping, 1);

		// get topping price
		var rate = parseFloat(jQuery(topping).data('rate'));

		// increase price by the rate of the topping
		updatePrice(rate);
	});

	function getToppingUnits(topping) {
		var unitsBox = jQuery(topping).siblings('.topping-quantity');

		if (unitsBox.length) {
			return parseInt(unitsBox.attr('data-units'));	
		}
		
		return 1;
	}

	function addToppingUnits(topping, units) {
		// find units box
		var unitsBox = jQuery(topping).siblings('.topping-quantity');

		if (unitsBox.length == 0) {
			// the topping doesn't support the units selection
			return false;
		}

		// increase/decrease units by the specified amount
		units = getToppingUnits(topping) + units;

		// update picked units
		unitsBox.attr('data-units', units);
		unitsBox.find('.topping-units').text(units);

		if (units <= 1) {
			unitsBox.find('.topping-del-unit').addClass('disabled');
		} else {
			unitsBox.find('.topping-del-unit').removeClass('disabled');
		}

		if (units <= 0) {
			unitsBox.find('.topping-add-unit').addClass('disabled');
		} else {
			unitsBox.find('.topping-add-unit').removeClass('disabled');
		}

		return true;
	}

	function updatePrice(add) {
		// get current price
		var price = parseFloat(jQuery('#entry_price').val());

		// increase price
		price += add;

		// round price to avoid sum/diff errors
		price = price.roundTo(2);

		// update price and make sure the cost is not lower than 0
		jQuery('#entry_price').val(Math.max(0, price));
	}

	function getProductData() {
		var data = {};

		// get cart item ID
		data.id = parseInt(jQuery('#entry_index').val());

		// get product ID
		data.id_product = parseInt(jQuery('#entry_id').val());

		// get product variation ID
		data.id_option = parseInt(jQuery('#entry_variation').val());

		// get price
		data.price = parseFloat(jQuery('#entry_price').val());

		// get quantity
		data.quantity = parseInt(jQuery('#entry_quantity').val());

		if (jQuery('#entry_stock').length) {
			// get stock
			data.stock = parseInt(jQuery('#entry_stock').text());

			if (data.id > 0) {
				// increase stock by the original quantity in case of update
				data.stock += <?php echo $item->quantity; ?>;
			}
		}

		// get notes
		data.notes = jQuery('#entry_notes').val();

		// get toppings groups
		data.groups = [];

		// iterate controls
		jQuery('.toppings-control').each(function() {
			// consider only the groups with matching variation ID
			var id_var = jQuery(this).data('variation');

			if (id_var == 0 || id_var == data.id_option) {
				// fetch group
				var group = {
					id: parseInt(jQuery(this).data('id')),
					toppings: [],
					units: {},
				};

				if (jQuery(this).find('select').length) {
					// get topping from select
					var id_topping = parseInt(jQuery(this).find('select').val());

					// add only if not empty
					if (!isNaN(id_topping) && id_topping > 0) {
						group.toppings.push(id_topping);
					}
				} else {
					// get toppings from checked inputs
					jQuery(this).find('input:checked').each(function() {
						// get topping ID
						var id_topping = parseInt(jQuery(this).val());

						// register topping
						group.toppings.push(id_topping);

						// look for the topping units
						var units = getToppingUnits(this);

						// register units
						group.units[id_topping] = units;
					});
				}

				if (group.toppings.length) {
					data.groups.push(group);
				}
			}
		});

		return data;
	}

</script>
