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
 * @var  ineteger            $index        The current key of the item in cart.
 * @var  VREDishesItem       $item         The item instance to insert/update.
 * @var  VREOrderRestaurant  $reservation  The reservation instance.
 */
extract($displayData);

$currency = VREFactory::getCurrency();

?>

<form id="dish-overlay-item-form">

	<!-- ITEM DETAILS -->
	
	<div class="dish-overlay-item-details">

		<?php
		if ($item->image)
		{
			?>
			<!-- ITEM IMAGE -->

			<div class="dish-overlay-item-image" style="background-image: url(<?php echo VREMEDIA_URI . $item->image; ?>);">
				
			</div>
			<?php
		}
		?>

		<!-- ITEM TEXTS -->

		<div class="dish-overlay-item-text">

			<!-- ITEM NAME -->
			
			<div class="dish-overlay-item-name">
				<?php echo $item->getName(); ?>
			</div>

			<?php
			$desc = $item->getDescription();

			if ($desc)
			{
				?>
				<!-- ITEM DESCRIPTION -->

				<div class="dish-overlay-item-description">
					<?php echo $desc; ?>
				</div>
				<?php
			}
			?>

		</div>

	</div>

	<!-- ADDITIONAL NOTES -->

	<div class="vrtk-additem-notes-box">

		<div class="vrtk-additem-notes-title vr-disable-selection">
			<?php echo JText::_('VRTKADDREQUEST'); ?>
		</div>

		<div class="vrtk-additem-notes-field" style="<?php echo $item->getAdditionalNotes() ? '' : 'display: none;'; ?>">
			<div class="vrtk-additem-notes-info">
				<?php echo JText::_('VRTKADDREQUESTSUBT'); ?>
			</div>

			<textarea name="notes" maxlength="256"><?php echo $item->getAdditionalNotes(); ?></textarea>
		</div>

	</div>

	<?php
	// get a list of variations
	$variations = $item->getVariations();

	if ($variations)
	{
		// get selected variation
		$selected = $item->getVariation();
		?>
		<!-- ITEM VARIATIONS -->

		<div class="vrtk-additem-variations-box">

			<p><?php echo JText::_('VRTKCHOOSEVAR'); ?></p>

			<ul>
				<?php
				foreach ($variations as $var)
				{
					?>
					<li>
						<input
							type="radio"
							name="id_product_option"
							value="<?php echo $var->id; ?>"
							id="vre-item-var-<?php echo $var->id; ?>"
							data-cost="<?php echo $var->price; ?>"
							<?php echo $selected && $var->id == $selected->id ? 'checked="checked"' : ''; ?>
						/>

						<label for="vre-item-var-<?php echo $var->id; ?>">
							<?php echo $var->name; ?>
						</label>

						<?php
						if ($var->price)
						{
							?>
							<span class="var-charge">
								+&nbsp;<?php echo $currency->format($var->price); ?>
							</span>
							<?php
						}
						?>
					</li>
					<?php
				}
				?>
			</ul>

		</div>
		<?php
	}
	?>

	<!-- ITEM QUANTITY -->

	<div class="vrtk-additem-quantity-box">

		<div class="vrtk-additem-quantity-box-inner">

			<span class="quantity-actions">
				<a href="javascript: void(0);" data-role="unit.remove" class="vrtk-action-remove <?php echo ($item->getQuantity() <= 1 ? 'disabled' : ''); ?>">
					<i class="fas fa-minus"></i>
				</a>

				<input type="text" name="quantity" value="<?php echo $item->getQuantity(); ?>" size="4" id="vrtk-quantity-input" onkeypress="return (event.keyCode >= 48 && event.keyCode <= 57) || event.keyCode == 13;" />

				<a href="javascript: void(0);" data-role="unit.add" class="vrtk-action-add">
					<i class="fas fa-plus"></i>
				</a>
			</span>

		</div>

	</div>

	<!-- ACTIONS BAR -->

	<div class="dish-item-overlay-footer">

		<button type="button" class="btn" data-role="close">
			<?php echo JText::_('VRTKADDCANCELBUTTON'); ?>
		</button>

		<button type="button" class="btn" data-role="save">
			<?php echo JText::sprintf('VRTKADDTOTALBUTTON', $currency->format($item->getTotalCost())); ?>
		</button>

	</div>

	<input type="hidden" name="id" value="<?php echo $item->id_assoc; ?>" />
	<input type="hidden" name="index" value="<?php echo $index; ?>" />
	<input type="hidden" name="ordnum" value="<?php echo $reservation->id; ?>" />
	<input type="hidden" name="ordkey" value="<?php echo $reservation->sid; ?>" />

</form>

<script>

	// Item notes

	jQuery('.vrtk-additem-notes-title').on('click', function() {
		if (!jQuery('.vrtk-additem-notes-field').is(':visible')) {
			jQuery('.vrtk-additem-notes-field').slideDown();
		} else {
			jQuery('.vrtk-additem-notes-field').slideUp();
		}
	});

	// Item variations

	jQuery('#dish-overlay-item-form input[name="id_product_option"]').on('change', function() {
		// trigger quantity change to update total
		jQuery('#vrtk-quantity-input').trigger('change');
	});

	// Item quantity

	jQuery('#vrtk-quantity-input').on('change', function() {
		// get quantity
		var q = parseInt(jQuery(this).val());
		
		if (q > 1) {
			// allow (-) button again
			jQuery('#dish-overlay-item-form .vrtk-action-remove').removeClass('disabled');
		} else {
			// disable (-) button
			jQuery('#dish-overlay-item-form .vrtk-action-remove').addClass('disabled');
		}

		// get total cost per unit
		var total = <?php echo $item->getPrice(); ?>;

		// get selected variation
		var opt = jQuery('#dish-overlay-item-form input[name="id_product_option"]:checked');

		if (opt.length) {
			// increase base cost by the variation charge
			total += parseFloat(opt.data('cost'));
		}

		// multiply by the number of selected units
		total *= q;

		// fetch total text
		var text = Joomla.JText._('VRTKADDTOTALBUTTON').replace(/%s/, Currency.getInstance().format(total));
		// update button text
		jQuery('#dish-overlay-item-form .dish-item-overlay-footer button[data-role="save"]').text(text);
	});

	jQuery('.quantity-actions *[data-role]').on('click', function() {
		// get quantity input
		var input = jQuery('#vrtk-quantity-input');

		// get current quantity
		var q = parseInt(input.val());

		// fetch units to add/decrease
		var units = jQuery(this).data('role') == 'unit.add' ? 1 : -1;
		
		if (q + units > 0) {
			// update only in case the quantity is higher than 0
			input.val(q + units);

			// update quantity
			input.trigger('change');
		}
	});

	// Actions

	jQuery('#dish-overlay-item-form .dish-item-overlay-footer button[data-role="close"]').on('click', function() {
		vrCloseDishOverlay();
	});

	jQuery('#dish-overlay-item-form .dish-item-overlay-footer button[data-role="save"]').on('click', function() {
		// serialize form data
		var data = jQuery('#dish-overlay-item-form').serialize();

		var btn = jQuery(this);

		// disable button during the request
		btn.prop('disabled', true);

		// save dish into the cart
		vrAddDishToCart(data).then((resp) => {
			// close overlay on success
			vrCloseDishOverlay();
		}).catch((error) => {
			// enable button again
			btn.prop('disabled', false);
		});
	});

</script>
