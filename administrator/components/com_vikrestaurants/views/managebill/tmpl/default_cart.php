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

<div id="empty-cart" style="<?php echo $this->bill->products ? 'display:none;' : ''; ?>">
	<?php echo $vik->alert(JText::_('VREMPTYCART')); ?>
</div>

<div class="vrtk-order-cart">

	<div class="order-cart-items">
		<?php
		foreach ($this->bill->products as $item)
		{
			?>
			<div
				id="vrtk-order-cart-item<?php echo $item->id_assoc; ?>"
				class="vrtk-order-cart-item"
				data-index="<?php echo $item->id_assoc; ?>"
				data-option="<?php echo $item->id_option; ?>"
				data-price="<?php echo $item->price; ?>"
				data-quantity="<?php echo $item->quantity; ?>"
				data-notes="<?php echo $this->escape($item->notes); ?>"
			>

				<div class="cart-item-name">
					<a href="javascript: void(0);" onClick="openProductCard(<?php echo $item->id; ?>, <?php echo $item->id_assoc; ?>);">
						<?php echo $item->name; ?>
					</a>
				</div>

				<div class="cart-item-note">
					<?php
					if ($item->notes)
					{
						?>
						<a href="javascript:void(0);" class="no-underline">
							<i class="fas fa-sticky-note big hasTooltip" title="<?php echo nl2br($this->escape($item->notes)); ?>"></i>
						</a>
						<?php
					}
					?>
				</div>

				<div class="cart-item-quantity">x<?php echo $item->quantity; ?></div>

				<div class="cart-item-price"><?php echo $currency->format($item->price); ?></div>
				
				<div class="cart-item-remove">
					<a href="javascript: void(0);" class="no-underline" onClick="removeProductFromCart(<?php echo $item->id_assoc; ?>);">
						<i class="fas fa-minus-circle big"></i>
					</a>
				</div>

			</div>
			<?php
		}
		?>
	</div>

	<div class="order-cart-summary" style="<?php echo $this->bill->products ? '' : 'display:none;'; ?>">

		<?php
		if ($this->bill->discount > 0 || $this->bill->tip > 0)
		{
			// calculate NET amount
			$net = $this->bill->value + $this->bill->discount - $this->bill->tip;
			?>
			<!-- NET -->
			<div class="vrtk-order-amount-line total-net">
				<span class="amount-legend"><?php echo JText::_('VRMANAGETKORDDISC2'); ?></span>
				<span class="amount-value"><?php echo $currency->format($net); ?></span>
			</div>
			<?php
			if ($this->bill->discount > 0)
			{
				if ($this->bill->coupon)
				{
					$help = '<i class="fas fa-question-circle hasTooltip" title="' . $this->escape($this->bill->coupon->code) . '" style="margin-right: 4px;"></i>';
				}
				else
				{
					$help = '';
				}

				?>
				<!-- DISCOUNT -->
				<div class="vrtk-order-amount-line total-discount">
					<span class="amount-legend"><?php echo $help . JText::_('VRDISCOUNT'); ?></span>
					<span class="amount-value"><?php echo $currency->format($this->bill->discount * -1); ?></span>
				</div>
				<?php
			}

			if ($this->bill->tip > 0)
			{
				?>
				<!-- TIP -->
				<div class="vrtk-order-amount-line total-tip">
					<span class="amount-legend"><?php echo JText::_('VRTIP'); ?></span>
					<span class="amount-value"><?php echo $currency->format($this->bill->tip); ?></span>
				</div>
				<?php
			}
		}
		
		if (!$this->bill->closed)
		{
			$due = max(array(0, $this->bill->value - $this->bill->deposit));
			?>
			<!-- DUE -->
			<div class="vrtk-order-amount-line total-due" style="<?php echo $due > 0 ? '' : 'display:none;'; ?>">
				<span class="amount-legend"><?php echo JText::_('VRORDERINVDUE'); ?></span>
				<span class="amount-value"><?php echo $currency->format($due); ?></span>
			</div>
			<?php
		}
		?>

		<!-- GRAND TOTAL -->
		<div class="vrtk-order-amount-line grand-total">
			<span class="amount-legend"><?php echo JText::_('VRMANAGETKORDDISC1'); ?></span>
			<span class="amount-value"><?php echo $currency->format($this->bill->value); ?></span>
		</div>
	</div>

</div>

<script type="text/javascript">

	function vrCartPushItem(item, total) {
		var html = '<div id="vrtk-order-cart-item' + item.id + '" class="vrtk-order-cart-item">\n'+
			'<div class="cart-item-name">\n'+
				'<a href="javascript: void(0);" onClick="openProductCard(' + item.id_product + ', ' + item.id + ');">' + item.name + '</a>\n'+
			'</div>\n'+
			'<div class="cart-item-note">{notes}</div>\n'+
			'<div class="cart-item-quantity">x' + item.quantity + '</div>\n'+
			'<div class="cart-item-price">' + Currency.getInstance().format(item.price) + '</div>\n'+
			'<div class="cart-item-remove">\n'+
				'<a href="javascript: void(0);" class="no-underline" onClick="removeProductFromCart(' + item.id + ');">\n'+
					'<i class="fas fa-minus-circle big"></i>\n'+
				'</a>\n'+
			'</div>\n'+
		'</div>\n';

		var notes = '';

		if (item.notes) {
			notes = '<a href="javascript:void(0);" class="no-underline">\n'+ 
				'<i class="fas fa-sticky-note big hasTooltip"></i>\n'+
			'</a>\n';
		}

		html = html.replace('{notes}', notes);
		
		if (jQuery('#vrtk-order-cart-item' + item.id).length == 0) {
			jQuery('.vrtk-order-cart .order-cart-items').append(html);
		} else {
			jQuery('#vrtk-order-cart-item' + item.id).replaceWith(html);
		}

		// update data attributes
		jQuery('#vrtk-order-cart-item' + item.id)
			.attr('data-index', item.id)
			.attr('data-option', item.id_product_option)
			.attr('data-price', item.price)
			.attr('data-quantity', item.quantity)
			.attr('data-notes', item.notes);

		if (item.notes) {
			// update notes by using jQuery to avoid EOF errors
			jQuery('#vrtk-order-cart-item' + item.id)
				.find('.cart-item-note i')
					.attr('title', item.notes.replace(/[\n\r]/g, '<br />'));
		}

		// re-create tooltips
		jQuery('#vrtk-order-cart-item' + item.id).find('.hasTooltip').tooltip();
		
		// update total cost
		vrCartUpdateTotalCost(total);

		// always hide "empty cart" message when adding a new product
		jQuery('#empty-cart').hide();
		// show the total amount too
		jQuery('.order-cart-summary').show();
	}

	function vrCartRemoveItem(id, total) {
		// remove item from cart
		jQuery('#vrtk-order-cart-item' + id).remove();
		// update total cost
		vrCartUpdateTotalCost(total);

		if (jQuery('.vrtk-order-cart .vrtk-order-cart-item').length == 0) {
			// show "empty cart" message again when there are no more products in cart
			jQuery('#empty-cart').show();
			// hide the total amount too
			jQuery('.order-cart-summary').hide();
		}
	}

	function vrCartUpdateTotalCost(total) {
		jQuery('#vrtk-total-text').val(total.toFixed(2));

		// update the grand total too
		jQuery('.order-cart-summary .grand-total .amount-value').html(Currency.getInstance().format(total));

		// calculate "due" amount
		var due = Math.max(0, parseFloat(total) - parseFloat(jQuery('input[name="deposit"]').val()));

		// update "due" amount
		jQuery('.order-cart-summary .total-due .amount-value').html(Currency.getInstance().format(due));

		if (due > 0) {
			// show remaining balance
			jQuery('.order-cart-summary .total-due').show();
		} else {
			// hide remaining balance (already paid)
			jQuery('.order-cart-summary .total-due').hide();
		}
	}

</script>
