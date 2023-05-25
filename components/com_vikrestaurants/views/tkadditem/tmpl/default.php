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

JHtml::_('vrehtml.assets.fontawesome');

$currency = VREFactory::getCurrency();

$toppings_map_costs = array();

?>

<form id="vrtk-additem-form">

	<div class="vrtk-additem-container">
		
		<!-- ITEM QUANTITY -->

		<div class="vrtk-additem-quantity-box" id="vrtk-additem-quantity">
			<span class="quantity-label"><?php echo JText::_('VRTKADDQUANTITY'); ?>:</span>

			<span class="quantity-actions">
				<a href="javascript: void(0);" class="vrtk-action-remove no-underline <?php echo ($this->item->quantity <= 1 ? 'disabled' : ''); ?>">
					<i class="fas fa-minus"></i>
				</a>

				<input type="text" name="quantity" value="<?php echo $this->item->quantity; ?>" size="4" onkeypress="return event.charCode >= 48 && event.charCode <= 57" />

				<a href="javascript: void(0);" class="vrtk-action-add no-underline">
					<i class="fas fa-plus"></i>
				</a>
			</span>
		</div>
		
		<div class="vrtk-additem-middle">
		
			<!-- ADDITIONAL NOTES -->

			<div class="vrtk-additem-notes-box">
				<div class="vrtk-additem-notes-title vr-disable-selection">
					<?php echo JText::_('VRTKADDREQUEST'); ?>
				</div>

				<div class="vrtk-additem-notes-field" style="display: none;">
					<div class="vrtk-additem-notes-info">
						<?php echo JText::_('VRTKADDREQUESTSUBT'); ?>
					</div>

					<textarea name="notes" maxlength="256"><?php echo $this->item->notes; ?></textarea>
				</div>
			</div>
			
			<!-- TOTAL COST -->
			<div class="vrtk-additem-tcost-box">
				<?php echo $currency->format($this->item->price * $this->item->quantity); ?>
			</div>
		
		</div>
		
		<!-- TOPPINGS GROUPS CONTAINER -->
		
		<div class="vrtk-additem-groups-loading" style="display: none;text-align: center;">
			<img id="img-loading" src="<?php echo VREASSETS_URI . 'css/images/hor-loader.gif'; ?>" />
		</div>
		
		<div class="vrtk-additem-groups-container" style="visibility: hidden;">
			
			<?php
			foreach ($this->item->toppings as $group)
			{ 
				?>
				<div class="vrtk-additem-group-box" id="vrtkgroup<?php echo $group->id; ?>" data-multiple="<?php echo $group->multiple; ?>" data-min-toppings="<?php echo $group->min_toppings; ?>" data-max-toppings="<?php echo $group->max_toppings; ?>">

					<div class="vrtk-additem-group-title">
						<?php echo $group->description; ?>
					</div>
					
					<div class="vrtk-additem-group-fields">
						<?php
						foreach ($group->list as $topping)
						{ 
							?>
							<div class="vrtk-additem-group-topping vrtk-group-<?php echo ($group->multiple ? 'multiple' : 'single') . ($group->use_quantity ? ' use-quantity' : ''); ?>">
								
								<?php
								if ($group->multiple)
								{
									?>
									<span class="vrtk-additem-topping-field">
										<input
											type="checkbox"
											value="<?php echo $topping->assoc_id; ?>"
											id="vrtk-cb<?php echo $topping->assoc_id; ?>"
											name="topping[<?php echo $group->id; ?>][]"
											class="vre-topping-checkbox"
											data-price="<?php echo $topping->rate; ?>"
											data-group="<?php echo $group->id; ?>"
											<?php echo ($topping->checked ? 'checked="checked"' : ''); ?>
										/>

										<label for="vrtk-cb<?php echo $topping->assoc_id; ?>">
											<?php
											echo $topping->name;

											if ($topping->description)
											{
												?>
												<i class="fas fa-info-circle topping-desc" title="<?php echo $this->escape($topping->description); ?>"></i>
												<?php
											}
											?>
										</label>
									</span>

									<?php
									if ($group->use_quantity)
									{
										?>
										<span class="vrtk-additem-topping-units" data-units="<?php echo $topping->units; ?>">
											<a href="javascript: void(0);" class="topping-del-unit no-underline">
												<i class="fas fa-minus-circle"></i>
											</a>

											<span class="topping-units"><?php echo $topping->units; ?></span>

											<a href="javascript: void(0);" class="topping-add-unit no-underline">
												<i class="fas fa-plus-circle"></i>
											</a>
										</span>

										<input type="hidden" name="topping_units[<?php echo $group->id; ?>][<?php echo $topping->assoc_id; ?>]" value="<?php echo $topping->units; ?>" />
										<?php
									}

									if ($topping->rate != 0)
									{
										?>
										<span class="vrtk-additem-topping-price">
											<?php echo $currency->format($topping->rate); ?>
										</span>
										<?php
									}
								}
								else
								{
									?>
									<span class="vrtk-additem-topping-field">
										<input
											type="radio"
											value="<?php echo $topping->assoc_id; ?>"
											id="vrtk-rb<?php echo $topping->assoc_id; ?>"
											name="topping[<?php echo $group->id; ?>][]" 
											class="vre-topping-radio"
											data-price="<?php echo $topping->rate; ?>"
											data-group="<?php echo $group->id; ?>"
											<?php echo ($topping->checked ? 'checked="checked"' : ''); ?>
										/>

										<label for="vrtk-rb<?php echo $topping->assoc_id; ?>">
											<?php
											echo $topping->name;

											if ($topping->description)
											{
												?>
												<i class="fas fa-info-circle topping-desc" title="<?php echo $this->escape($topping->description); ?>"></i>
												<?php
											}
											?>
										</label>
									</span>

									<?php
									if ($topping->rate != 0)
									{
										?>
										<span class="vrtk-additem-topping-price">
											<?php echo $currency->format($topping->rate); ?>
										</span>
										<?php
									}
									
									if ($topping->checked)
									{
										$toppings_map_costs[$group->id] = $topping->rate; 
									}
									
								}
								?>

							</div>

							<?php
						}
						?>
					</div>

				</div>
				
				<?php
			}
			?>
			
		</div>
		
		<div class="vrtk-additem-bottom dish-item-overlay-footer">
			
			<!-- CANCEL BUTTON -->

			<button type="button" id="vrtk-cartcancel-button" class="vrtk-additem-cancel-button" data-role="close">
				<?php echo JText::_("VRTKADDCANCELBUTTON"); ?>
			</button>

			<!-- ADD TO CART BUTTON -->

			<button type="button" id="vrtk-addtocart-button" class="vrtk-additem-success-button" data-role="save">
				<?php echo JText::_($this->item->cartIndex >= 0 ? 'VRSAVE' : 'VRTKADDOKBUTTON'); ?>
			</button>
			
		</div>
		
	</div>
	
	<input type="hidden" name="item_index" value="<?php echo $this->item->cartIndex; ?>" />
	<input type="hidden" name="id_entry" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="id_option" value="<?php echo $this->item->oid; ?>" />

</form>

<script>

	setTimeout(function() {
		if (jQuery('.vrtk-additem-groups-container').is(':visible') == false) {
			jQuery('.vrtk-additem-groups-loading').show();
		}
	}, 750);

	jQuery('.topping-desc').tooltip();
	
	// ITEM QUANTITY

	jQuery('#vrtk-additem-quantity input[name="quantity"]').on('change', function() {
		var q = vrGetAddItemQuantity();

		var box = jQuery(this).closest('.quantity-actions');
		
		if (q > 1) {
			jQuery(box).find('.vrtk-action-remove').removeClass('disabled');
		} else {
			jQuery(box).find('.vrtk-action-remove').addClass('disabled');
		}

		vrIncreaseEntryPrice();
	});

	jQuery('#vrtk-additem-quantity').find('.vrtk-action-remove, .vrtk-action-add').on('click', function() {
		var box   = jQuery(this).closest('.quantity-actions');
		var input = jQuery(box).find('input[name="quantity"]');

		var units = 1;

		if (jQuery(this).hasClass('vrtk-action-remove')) {
			units = -1;
		}

		var q = vrGetAddItemQuantity();
		
		if (q + units > 0) {
			input.val(q + units);
		}
	
		input.trigger('change');
	});

	function vrGetAddItemQuantity() {
		var quantity = parseInt(jQuery('#vrtk-additem-quantity input[name="quantity"]').val());

		if (isNaN(quantity) || quantity <= 0) {
			quantity = 1;
		}

		return quantity;
	}
	
	// ADDITIONAL NOTES
	
	jQuery('.vrtk-additem-notes-title').on('click', function() {
		if (!jQuery('.vrtk-additem-notes-field').is(':visible')) {
			jQuery('.vrtk-additem-notes-field').slideDown();
		} else {
			jQuery('.vrtk-additem-notes-field').slideUp();
		}
	});
	
	// GROUPS 
	
	jQuery(function() {
		var cont  = jQuery('.vrtk-additem-groups-container');
		var bound = cont.offset().left + cont.width() / 2;
		
		var _float, _pos;

		// recalculate position of the blocks in order to be properly displayed
		jQuery('.vrtk-additem-group-box').each(function() {
			var _float = jQuery(this).css('float');
			var _pos   = jQuery(this).offset().left + jQuery(this).width();
			
			if (_pos < bound && _float == 'right') {
				jQuery(this).css('float', 'left');
			} else if (_pos >= bound && _float == 'left') {
				jQuery(this).css('float', 'right');   
			}
		});
		
		// remove loading box
		jQuery('.vrtk-additem-groups-loading').remove();
		// DO NOT use display:none because the position wouldn't
		// be properly calculated
		cont.css('visibility', 'visible');

		// register events
		jQuery('.topping-del-unit').on('click', function() {
			vrAddToppingUnits(this, -1);
		});

		jQuery('.topping-add-unit').on('click', function() {
			vrAddToppingUnits(this, 1);
		});
	});
	
	// TOPPINGS
	
	var ENTRY_TOTAL_COST   = <?php echo ($this->item->price); ?>;
	var TOPPINGS_MAP_COSTS = <?php echo json_encode($toppings_map_costs); ?>;

	function vrCheckGroupToppingsStatus(group) {
		// calculate number of picked toppings
		var checked = vrCountCheckedToppings(group);
		// fetch maximum number of selectable toppings
		var max = parseInt(jQuery(group).attr('data-max-toppings'));
		
		if (checked == max) {
			jQuery(group).find('input[name^="topping["]:not(:checked)').prop('disabled', true);

			// disable add units button (if supported)
			jQuery(group).find('.topping-add-unit').addClass('disabled');

			return true;
		}

		jQuery(group).find('input[name^="topping["]:not(:checked)').prop('disabled', false);

		// enable add units button (if supported)
		jQuery(group).find('.topping-add-unit').removeClass('disabled');

		return false;
	}

	function vrCountCheckedToppings(group) {
		var count = 0;

		jQuery(group).find('input[name^="topping["]:checked').each(function() {
			count += vrGetToppingUnits(this);
		});

		return count;
	}

	function vrGetToppingUnits(topping) {
		var units = jQuery(topping).closest('.vrtk-additem-topping-field')
			.siblings('[data-units]');

		if (units.length == 0) {
			return 1;
		}

		return parseInt(units.attr('data-units'));
	}

	function vrAddToppingUnits(btn, q) {
		if (jQuery(btn).length == 0 || jQuery(btn).hasClass('disabled')) {
			// do not go ahead in case of disabled button
			return false;
		}

		// get units holder
		var holder = jQuery(btn).closest('[data-units]');

		// get selected units plus the specified ones
		var units = parseInt(holder.attr('data-units')) + q;

		if (units < 0) {
			// cannot decrease further units
			return true;
		}

		// update units
		holder.attr('data-units', units);
		holder.siblings('input[name^="topping_units"]').val(units);
		holder.find('.topping-units').text(units);

		// get related topping checkbox
		var checkbox = holder.siblings('.vrtk-additem-topping-field').find('input');

		if (units == 0) {
			if (checkbox.is(':checked')) {
				// uncheck checkbox
				checkbox.prop('checked', false);
			}

			// do not disable delete button in order to avoid
			// strange behaviors with other events
		}

		// get topping price
		var p = parseFloat(jQuery(checkbox).attr('data-price'));

		// increase product price
		vrIncreaseEntryPrice(p * q);

		// get topping group
		var group = holder.closest('.vrtk-additem-group-box');

		// check whether all the toppings have been selected
		vrCheckGroupToppingsStatus(group);

		return true;
	}
	
	jQuery('.vrtk-additem-group-box').each(function() {
		if (jQuery(this).attr('data-multiple') == 1) {
			// toggle status of checkboxes
			vrCheckGroupToppingsStatus(this);
		}
	});

	jQuery('.vre-topping-checkbox').on('change', function() {
		var p = parseFloat(jQuery(this).attr('data-price'));

		// get topping container
		var toppingParent = jQuery(this).closest('.vrtk-additem-group-topping');
		
		if (jQuery(this).is(':checked')) {
			// set units to 1
			var added = vrAddToppingUnits(toppingParent.find('.topping-add-unit'), 1);

			if (!added) {
				vrIncreaseEntryPrice(p);
			}
		} else {
			// multiply topping units per -1 to decrease them
			var units = vrGetToppingUnits(this) * -1;
		
			// decrease by all the picked units
			var deleted = vrAddToppingUnits(toppingParent.find('.topping-del-unit'), units);

			if (!deleted) {
				vrIncreaseEntryPrice(p * units);
			}
		}

		var group = jQuery('#vrtkgroup'+jQuery(this).attr('data-group'));

		// toggle status of checkboxes
		vrCheckGroupToppingsStatus(group);
	});
	
	jQuery('.vre-topping-radio').on('change', function(e) {
		var id_group = jQuery(this).attr('data-group');

		var price = parseFloat(jQuery(this).attr('data-price'));
		var total = price;

		if (TOPPINGS_MAP_COSTS.hasOwnProperty(id_group)) {
			// decrease by the previously selected topping
			total -= parseFloat(TOPPINGS_MAP_COSTS[id_group]);
		}

		// register selected topping price
		TOPPINGS_MAP_COSTS[id_group] = price;
		
		vrIncreaseEntryPrice(total);
	});
	
	function vrIncreaseEntryPrice(p) {
		if (p) {
			ENTRY_TOTAL_COST += p;
		}

		var total = ENTRY_TOTAL_COST * vrGetAddItemQuantity();

		jQuery('.vrtk-additem-tcost-box').text(Currency.getInstance().format(total));
	}
	
	function vrAllGroupsChecked() {
		var min_toppings, sel_toppings;
		var ok = true;

		jQuery('.vrtk-additem-group-box').each(function() {
			min_toppings = parseInt(jQuery(this).attr('data-min-toppings'));
			max_toppings = parseInt(jQuery(this).attr('data-max-toppings'));
			sel_toppings = vrCountCheckedToppings(this);

			if ((min_toppings > 0 && sel_toppings < min_toppings)
				|| sel_toppings > max_toppings) {
				ok = false;
				jQuery(this).addClass('vrrequiredfield');
			} else {
				jQuery(this).removeClass('vrrequiredfield');
			}
		});
		
		return ok;
	}
	
	// MODAL BUTTONS
	
	jQuery('#vrtk-addtocart-button').on('click', function() {
		if (!vrAllGroupsChecked()) {
			return false;
		}
		
		vrPostTakeAwayItem();
	});
	
	jQuery('#vrtk-cartcancel-button').on('click', function() {
		vrCloseOverlay('vrnewitemoverlay');
	});
	
	function vrPostTakeAwayItem() {
		// serialize form
		var data = jQuery('#vrtk-additem-form').serialize();

		// make request
		vrMakeAddCartRequest(data).then((response) => {
			// auto-close overlay on success
			vrCloseOverlay('vrnewitemoverlay');
		}).catch((error) => {
			// do nothing here
		});
	}
	
</script>
