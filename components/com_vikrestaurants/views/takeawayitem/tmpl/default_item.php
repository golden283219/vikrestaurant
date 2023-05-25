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
 * Template file used to display the details of
 * the selected product and the form actions. 
 *
 * @since 1.8
 */

$item = $this->item;

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();

// check whether the date selection is allowed
$is_date_allowed = $config->getBool('tkallowdate');

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

// json maps
$variations_cost_map  = array();
$toppings_curr_id     = array();
$toppings_cost_map    = array();
$toppings_constraints = array();

$vik = VREApplication::getInstance();

?>

<!-- Product Head -->

<div class="vrtk-itemdet-prod-head">

	<!-- Title -->

	<div class="tk-title">

		<h2>
			<span><?php echo $item->name; ?></span>

			<!-- Attributes -->

			<?php
			if ($item->attributes)
			{
				?>
				<div class="tk-attributes">
					<?php
					foreach ($item->attributes as $attr)
					{ 
						?>
						<img src="<?php echo VREMEDIA_URI . $attr->icon; ?>" alt="<?php echo $attr->name; ?>" title="<?php echo $attr->name; ?>" />
						<?php 
					}
					?>
				</div>
				<?php
			}

			/**
			 * Added the possibility of changing the check-in date.
			 *
			 * @since 1.8
			 */
			if ($is_date_allowed)
			{
				?>
				<div class="vrtk-item-date-block">
					<?php
					// get check-in date
					$checkin = date($config->get('dateformat'), $this->cart->getCheckinTimestamp());

					if ($is_date_allowed)
					{
						// add support for datepicker events
						JHtml::_('vrehtml.sitescripts.datepicker', 'input[name="takeaway_date"]', 'takeaway');

						?>
						<input type="hidden" name="takeaway_date" value="<?php echo $checkin; ?>" />
						<?php
					}
					?>

					<i class="fas fa-calendar-alt" id="vrtk-date-picker"></i>
				</div>
				<?php
			}
			?>
		</h2>

		<?php
		// check if the menu is published for the selected date
		if (!$item->menu->isActive)
		{
			?>
			<div class="tk-subtitle-notactive">
				<?php echo $item->menu->availError; ?>
			</div>
			<?php
		}
		?> 
	</div>

</div>

<!-- Product Body -->

<div class="vrtk-itemdet-prod-body">

	<!-- Left Side -->

	<div class="tk-left">

		<!-- Image -->

		<?php
		if ($item->image && is_file(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $item->image))
		{
			JHtml::_('vrehtml.assets.fancybox');
			?>
			<div class="tk-image">
				<a href="javascript: void(0);" class="vremodal" onClick="vreOpenModalImage(<?php echo $this->escape(json_encode($item->images)); ?>);">
					<img src="<?php echo VREMEDIA_URI . $item->image; ?>" />
				</a>
			</div>
			<?php
		}
		?>

		<!-- Variations -->

		<?php
		if (count($item->options))
		{
			?>
			<div class="tk-variations">

				<div class="tk-label" id="vrtkvarlabel"><?php echo JText::_('VRTKCHOOSEVAR'); ?>*:</div>
				
				<div class="vre-select-wrapper">

					<select name="id_option" class="vre-select" id="vrtk-vars-select">

						<option value="0" data-price="<?php echo $item->totalBasePrice; ?>"><?php echo JText::_('VRTKPLEASECHOOSEOPT'); ?></option>

						<?php
						foreach ($item->options as $var)
						{ 
							$selected = '';

							if ($var->id == $this->request->idOption)
							{
								$selected = 'selected="selected"';
							}

							$variations_cost_map[$var->id] = $var->totalPrice;
							?>
							<option value="<?php echo $var->id; ?>" <?php echo $selected; ?> data-price="<?php echo $var->totalPrice; ?>">
								<?php echo $var->name . ' ' . $currency->format($var->totalPrice); ?>
							</option>
							<?php
						}
						?>

					</select>

				</div>

			</div>
			<?php
		}
		?>

		<!-- Toppings Groups -->

		<?php
		if (count($item->toppings))
		{
			?>
			<div class="tk-toppings-groups">

				<?php
				foreach ($item->toppings as $group)
				{
					?>
					<div class="tk-topping-wrapper" id="vrtkgroup<?php echo $group->id; ?>" data-id="<?php echo $group->id; ?>">

						<div class="tk-label vrtklabel<?php echo $group->id; ?>">
							<?php echo $group->description . ($group->min_toppings > 0 ? '*' : ''); ?>:
						</div>
						
						<?php
						if ($group->multiple)
						{
							?>
							<div class="tk-topping-fields-cont">
								<?php
								foreach ($group->list as $topping)
								{ 
									$checked = '';

									if (!empty($this->request->toppings[$group->id]) && in_array($topping->assoc_id, $this->request->toppings[$group->id]))
									{
										$checked = 'checked="checked"';
									}

									if (empty($toppings_constraints[$group->id]))
									{
										$toppings_constraints[$group->id] = array(
											'min' => $group->min_toppings,
											'max' => $group->max_toppings,
										);
									}
									?>

									<div class="tk-topping-field">

										<span class="tk-topping-checkbox">
											<input
												type="checkbox"
												value="<?php echo $topping->assoc_id; ?>"
												id="vrtkitem-cb<?php echo $topping->assoc_id; ?>"
												name="topping[<?php echo $group->id; ?>][]"
												data-price="<?php echo $topping->rate; ?>"
												data-group="<?php echo $group->id; ?>"
												class="vrtk-topping-checkbox<?php echo $group->id; ?>"
												<?php echo $checked; ?> 
											/>

											<label for="vrtkitem-cb<?php echo $topping->assoc_id; ?>">
												<?php
												echo $topping->name;

												/**
												 * Added description next to the topping name.
												 *
												 * @since 1.8.2
												 */
												if ($topping->description)
												{
													?>
													<i class="fas fa-info-circle hasTooltip" title="<?php echo $this->escape($topping->description); ?>"></i>
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
											<span class="tk-topping-rate">
												<?php echo $currency->format($topping->rate); ?>
											</span>
											<?php
										}
										?>

									</div>

									<?php
								}
								?>

							</div>

							<?php
						}
						else
						{
							?>
							<div class="vre-select-wrapper">

								<select name="topping[<?php echo $group->id; ?>][]" class="vre-select" data-group="<?php echo $group->id; ?>">
									
									<option value=""><?php echo JText::_('VRTKPLEASECHOOSEOPT'); ?></option>
									
									<?php
									foreach ($group->list as $topping)
									{
										$selected = '';

										if (empty($toppings_cost_map[$group->id]))
										{
											$toppings_cost_map[$group->id] = array();
											$toppings_curr_id[$group->id]  = -1;
										}

										$toppings_cost_map[$group->id][$topping->assoc_id] = $topping->rate;

										if (!empty($this->request->toppings[$group->id]) && $topping->assoc_id == $this->request->toppings[$group->id][0])
										{
											$selected = 'selected="selected"';

											$toppings_curr_id[$group->id] = $topping->assoc_id;
										}
										?>
											
										<option value="<?php echo $topping->assoc_id; ?>" <?php echo $selected; ?> data-price="<?php echo $topping->rate; ?>">
											<?php
											echo $topping->name;

											if ($topping->rate != 0)
											{
												echo ' ' . $currency->format($topping->rate);
											}
											?>
										</option>
										
										<?php
									}
									?>

								</select>

								<?php
								foreach ($group->list as $topping)
								{
									/**
									 * Added description next to the topping select.
									 *
									 * @since 1.8.2
									 */
									if ($topping->description)
									{
										?>
										<i class="fas fa-info-circle hasTooltip topping-desc-single"
											id="topping-desc-<?php echo $topping->assoc_id; ?>"
											style="<?php echo $toppings_curr_id[$group->id] == $topping->assoc_id ? '' : 'display: none;'; ?>"
											title="<?php echo $this->escape($topping->description); ?>"></i>
										<?php
									}
								}
								?>

							</div>

							<?php
						}
						?>

						<!-- end toppings group -->

					</div>
					
					<?php
				}
				?>

				<!-- end product toppings -->

			</div>

			<?php
		}
		?>

	</div>

	<!-- Right Side -->

	<div class="tk-right">

		<!-- Cart Info : price and quantity -->

		<div class="tk-cart-summary">

			<div class="tk-cart-summary-inner">
			
				<!-- Price -->

				<div class="tk-price" id="vrtk-price-box">
					<?php echo $currency->format($item->totalPrice); ?>
				</div>

				<!-- Quantity and Add button -->

				<div class="tk-add-cart">
					<div class="vrtk-additem-quantity-box" id="vrtk-item-quantity">
						<span class="quantity-actions">
							<a href="javascript: void(0);" class="vrtk-action-remove no-underline <?php echo ($this->request->quantity <= 1 ? 'disabled' : ''); ?>">
								<i class="fas fa-minus"></i>
							</a>

							<input type="text" name="quantity" value="<?php echo $this->request->quantity; ?>" size="3" onkeypress="return event.charCode >= 48 && event.charCode <= 57" />

							<a href="javascript: void(0);" class="vrtk-action-add no-underline">
								<i class="fas fa-plus"></i>
							</a>
						</span>
					</div>

					<button type="button" onClick="vrInsertTakeAwayItem();" id="vrtk-item-addbutton" <?php echo ($item->menu->isActive ? '' : 'disabled="disabled"'); ?>>
						<?php echo JText::_('VRTKADDOKBUTTON'); ?>
					</button>
				</div>

			</div>

			<!-- Order Now -->

			<div class="tk-ordernow" id="vrtk-ordernow-box" style="<?php echo ($this->cart->getCartRealLength() ? '' : 'display:none;'); ?>">
				<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeawayconfirm'); ?>">
					<?php echo JText::_('VRTAKEAWAYORDERBUTTON'); ?>
				</a>
			</div>

		</div>

		<!-- Description -->

		<?php
		if (strlen($item->description))
		{
			?>
			<div class="tk-description">
				<?php
				// prepare description to properly interpret included plugins
				$vik->onContentPrepare($item->description);

				echo $item->description->text;
				?>
			</div>
			<?php
		}
		?>

		<!-- Special Notes -->

		<div class="tk-special-notes">
			
			<div class="tk-notes-title vr-disable-selection">
				<?php echo JText::_('VRTKADDREQUEST'); ?>
			</div>

			<div class="tk-notes-field">
				<div class="tk-notes-info">
					<?php echo JText::_('VRTKADDREQUESTSUBT'); ?>
				</div>

				<textarea name="notes" maxlength="256"><?php echo $this->request->notes; ?></textarea>
			</div>

		</div>

	</div>

</div>

<?php
JText::script('VRTKADDITEMSUCC');
JText::script('VRTKADDITEMERR1');
JText::script('VRTKADDITEMERR2');
?>

<script>

	var ITEM_TOTAL_COST = <?php echo (float) $item->totalPrice; ?>;

	var VARIATIONS_COST_MAP = <?php echo json_encode($variations_cost_map); ?>;
	var VARIATIONS_CURR_ID  = <?php echo $this->request->idOption; ?>;

	var TOPPINGS_COST_MAP = <?php echo json_encode($toppings_cost_map); ?>;
	var TOPPINGS_CURR_ID  = <?php echo json_encode($toppings_curr_id); ?>;

	var TOPPINGS_CONSTRAINTS = <?php echo json_encode($toppings_constraints); ?>;

	jQuery(document).ready(function() {

		// variations

		jQuery('#vrtk-vars-select').on('change', function(){
			<?php
			if ($this->isToSubmit)
			{
				?>
				jQuery('#vrtkitemform').submit();
				<?php
			}
			else
			{
				?>
				var cost = parseFloat(jQuery(this).find('option:selected').data('price'));

				if (VARIATIONS_CURR_ID > 0 && VARIATIONS_COST_MAP.hasOwnProperty(VARIATIONS_CURR_ID))
				{
					// decrease variation from 
					ITEM_TOTAL_COST -= VARIATIONS_COST_MAP[VARIATIONS_CURR_ID];
				}
				else
				{
					// decrease price by the base cost of the item
					ITEM_TOTAL_COST -= <?php echo (float) $item->price; ?>;
				}

				ITEM_TOTAL_COST += (isNaN(cost) ? 0 : cost);
				vrUpdateItemCost();

				VARIATIONS_CURR_ID = parseInt(jQuery(this).val());
				<?php
			}
			?>
		});

		// checkbox toppings

		jQuery('.vrtk-itemdet-page .tk-topping-wrapper input[type="checkbox"]').on('change', function() {
			var p = parseFloat(jQuery(this).attr('data-price'));

			if (isNaN(p)) {
				p = 0;
			}

			// get topping container
			var toppingParent = jQuery(this).closest('.tk-topping-field');
			
			if (jQuery(this).is(':checked')) {
				// set units to 1
				var added = vrAddToppingUnits(toppingParent.find('.topping-add-unit'), 1);

				if (!added) {
					ITEM_TOTAL_COST += p;
					vrUpdateItemCost();
				}
			} else {
				// multiply topping units per -1 to decrease them
				var units = vrGetToppingUnits(this) * -1;
			
				// decrease by all the picked units
				var deleted = vrAddToppingUnits(toppingParent.find('.topping-del-unit'), units);

				if (!deleted) {
					ITEM_TOTAL_COST += p * units;
					vrUpdateItemCost();
				}
			}

			var group = jQuery('#vrtkgroup' + jQuery(this).attr('data-group'));

			// toggle status of checkboxes
			vrCheckGroupToppingsStatus(group);
		});

		// dropdown toppings

		jQuery('.vrtk-itemdet-page .tk-topping-wrapper select').on('change', function() {
			var id_group = jQuery(this).data('group');

			var cost = parseFloat(jQuery(this).find('option:selected').data('price'));

			if (TOPPINGS_CURR_ID[id_group] > 0 && TOPPINGS_COST_MAP[id_group].hasOwnProperty(TOPPINGS_CURR_ID[id_group])) {
				ITEM_TOTAL_COST -= TOPPINGS_COST_MAP[id_group][TOPPINGS_CURR_ID[id_group]];
			}

			ITEM_TOTAL_COST += (isNaN(cost) ? 0 : cost);
			vrUpdateItemCost();
			TOPPINGS_CURR_ID[id_group] = parseInt(jQuery(this).val());

			// hide all toppings description (single selection)
			jQuery('.topping-desc-single').hide();
			// show description of selected topping (if any)
			jQuery('#topping-desc-' + jQuery(this).val()).show();
		});

		// quantity

		jQuery('#vrtk-item-quantity input[name="quantity"]').on('change', function() {
			var q = vrGetItemQuantity();

			var box = jQuery(this).closest('.quantity-actions');
			
			if (q > 1) {
				jQuery(box).find('.vrtk-action-remove').removeClass('disabled');
			} else {
				jQuery(box).find('.vrtk-action-remove').addClass('disabled');
			}

			vrUpdateItemCost();
		});

		jQuery('#vrtk-item-quantity').find('.vrtk-action-remove, .vrtk-action-add').on('click', function() {
			var box   = jQuery(this).closest('.quantity-actions');
			var input = jQuery(box).find('input[name="quantity"]');

			var units = 1;

			if (jQuery(this).hasClass('vrtk-action-remove')) {
				units = -1;
			}

			var q = vrGetItemQuantity();
			
			if (q + units > 0) {
				input.val(q + units);
			}
		
			input.trigger('change');
		});

		// set initial total cost
		vrUpdateItemCost();

		<?php
		if ($is_date_allowed)
		{
			?>
			// show datepicker when the calendar icon is clicked
			jQuery('#vrtk-date-picker').on('click', function() {
				jQuery('input[name="takeaway_date"]').datepicker('show');
			});

			// submit form when the date changes
			jQuery('input[name="takeaway_date"]').on('change', function() {
				document.vrtkitemform.submit();
			});
			<?php
		}
		?>

		// register events
		jQuery('.topping-del-unit').on('click', function() {
			vrAddToppingUnits(this, -1);
		});

		jQuery('.topping-add-unit').on('click', function() {
			vrAddToppingUnits(this, 1);
		});

		// update toppings status on load
		jQuery('.tk-topping-wrapper').each(function() {
			vrCheckGroupToppingsStatus(this);
		});
	});

	function vrGetItemQuantity() {
		var quantity = parseInt(jQuery('#vrtk-item-quantity input[name="quantity"]').val());

		if (isNaN(quantity) || quantity <= 0) {
			quantity = 1;
		}

		return quantity;
	}

	function vrUpdateItemCost() {
		var q = vrGetItemQuantity();
		jQuery('#vrtk-price-box').html(Currency.getInstance().format(ITEM_TOTAL_COST * q));
	}

	function vrCheckGroupToppingsStatus(group) {
		// get group ID
		var id = parseInt(jQuery(group).data('id'));

		// check whether the groups we are fetching
		// supports the constraints
		if (!TOPPINGS_CONSTRAINTS.hasOwnProperty(id)) {
			return true;
		}

		// calculate number of picked toppings
		var checked = vrCountCheckedToppings(group);

		// fetch maximum number of selectable toppings
		var max = parseInt(TOPPINGS_CONSTRAINTS[id].max);
		
		if (checked >= max) {
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
		var units = jQuery(topping).closest('.tk-topping-checkbox')
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
		var checkbox = holder.siblings('.tk-topping-checkbox').find('input');

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
		ITEM_TOTAL_COST += p * q;

		// update total cost
		vrUpdateItemCost();

		// get topping group
		var group = holder.closest('.tk-topping-wrapper');

		// check whether all the toppings have been selected
		vrCheckGroupToppingsStatus(group);

		return true;
	}

	function vrValidateBeforeSubmit() {
		var ok = true;

		// check quantity
		var quantity = parseInt(jQuery('input[name="quantity"]').val());

		if (isNaN(quantity) || quantity <= 0) {
			ok = false;
			jQuery('input[name="quantity"]').addClass('vrrequiredfield');
		} else {
			jQuery('input[name="quantity"]').removeClass('vrrequiredfield');
		}

		// check variation
		if (jQuery('#vrtk-vars-select').length) {
			var id_var = jQuery('#vrtk-vars-select').val();

			if (isNaN(id_var) || id_var <= 0) {
				ok = false;
				jQuery('#vrtkvarlabel').addClass('vrrequired');
			} else {
				jQuery('#vrtkvarlabel').removeClass('vrrequired');
			}
		}

		// check single toppings
		jQuery('.vrtk-itemdet-page .tk-topping-wrapper select').each(function(){
			var id_group = jQuery(this).data('group');
			
			if (!jQuery(this).val().length) {
				ok = false;
				jQuery('.vrtklabel' + id_group).addClass('vrrequired');
			} else {
				jQuery('.vrtklabel' + id_group).removeClass('vrrequired');
			}
		});

		// check multiple toppings
		jQuery.each(TOPPINGS_CONSTRAINTS, function(id_group, bounds) {
			var group = jQuery('#vrtkgroup' + id_group);

			var checkedCount = vrCountCheckedToppings(group);
			
			if (checkedCount < bounds['min'] || checkedCount > bounds['max']) {
				ok = false;
				jQuery('.vrtklabel' + id_group).addClass('vrrequired');
			} else {
				jQuery('.vrtklabel' + id_group).removeClass('vrrequired');
			}
		});

		// check notes
		if (jQuery('textarea[name="notes"]').val().length > 256) {
			ok = false;
			jQuery('textarea[name="notes"]').addClass('vrrequiredfield');
		} else {
			jQuery('textarea[name="notes"]').removeClass('vrrequiredfield');
		}

		return ok;
	}

	<?php
	if ($item->menu->isActive)
	{
		?>
		function vrInsertTakeAwayItem() {
			// validate form data
			if (!vrValidateBeforeSubmit()) {
				// raise error
				ToastMessage.dispatch({
					text:   Joomla.JText._('VRTKADDITEMERR1'),
					status: 0,
				});

				return false;
			}

			// disable button to avoid adding the same product twice
			jQuery('#vrtk-item-addbutton').prop('disabled', true);

			// serialize form
			var data = jQuery('#vrtkitemform').serialize();

			// make request
			vrMakeAddCartRequest(data).then((response) => {
				// show order now button on success
				jQuery('#vrtk-ordernow-box').show();

				// enable add button again
				jQuery('#vrtk-item-addbutton').prop('disabled', false);
			}).catch((error) => {
				// enable add button again
				jQuery('#vrtk-item-addbutton').prop('disabled', false);
			});
		}
		<?php
	}
	?>

	function vrMakeAddCartRequest(data) {
		// create promise
		return new Promise((resolve, reject) => {
			// make request to add the item within the cart
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=tkreservation.addtocartajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				data,
				function(resp) {
					// try to decode JSON
					var obj = jQuery.parseJSON(resp);

					var msg = {
						status: 0,
						text:   '',
					};

					if (vrIsCartPublished()) {
						// refresh cart module in case it is published
						vrCartRefreshItems(obj.items, obj.total, obj.discount, obj.finalTotal);
					}

					// resolve promise
					resolve(obj);

					if (obj.message) {
						// use the message fetched by the controller
						msg = obj.message;
					}

					// Display the default successful message only in case there is no message text
					// and the cart is not published (or currently not visible on the screen).
					if (msg.text.length == 0 && (!vrIsCartPublished() || !vrIsCartVisibleOnScreen())) {
						msg.text   = Joomla.JText._('VRTKADDITEMSUCC');
						msg.status = 1;
					}

					if (msg.text.length) {
						// dispatch toast message
						ToastMessage.dispatch(msg);
					}
				},
				function(error) {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// reject promise
					reject(error);

					// raise error
					ToastMessage.dispatch({
						text:   error.responseText,
						status: 0,
					});
				}
			);
		});
	}

</script>
