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
 * Template file used to display a summary of the ordered products.
 *
 * @since 1.8
 */

$cart = $this->cart;

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();

$show_taxes = $config->getBool('tkshowtaxes');
$use_taxes  = $config->getUint('tkusetaxes');

// get total cost
$total_cost = $cart->getTotalCost();
// get total discount
$total_discount = $cart->getTotalDiscount();

// fetch total net
if ($show_taxes)
{
	// display total NET without taxes
	$total_net = $cart->getRealTotalNet($use_taxes);
}
else
{
	// display difference between discount and grand total
	$total_net = $total_cost - $cart->getTotalDiscount();
}

$grand_total = $cart->getRealTotalCost($use_taxes);

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<div id="vrtkconfcartitemsdiv" class="vrtkconfcartitemsdiv">

	<!-- CART ITEMS -->

	<div id="vrtkconfitemcontainer">

		<?php
		foreach ($cart->getItemsList() as $k => $item)
		{
			?>
			<div id="vrtk-conf-itemrow<?php echo $k; ?>" class="vrtkconfcartoneitemrow">

				<div class="vrtk-confcart-item-main">

					<div class="vrtkconfcartleftrow">
						<div class="vrtkconfcart-item-name">
							<span class="vrtkconfcartenamesp"><?php echo $item->getItemName(); ?></span>
							
							<?php
							if (strlen($item->getVariationName()))
							{
								?>
								<span class="vrtkconfcartonamesp">-&nbsp;<?php echo $item->getVariationName(); ?></span>
								<?php
							}
							?>
						</div>
					</div>
					
					<div class="vrtkconfcartrightrow">
						<span class="vrtkconfcartquantitysp">
							<?php echo JText::_('VRTKCARTQUANTITYSUFFIX') . $item->getQuantity(); ?>
						</span>
						
						<span class="vrtkconfcartpricesp">
							<?php
							$item_total_price = $item->getTotalCost();
							
							if ($item_total_price > 0)
							{
								echo $currency->format($item_total_price);
							}
							else
							{
								echo JText::_('VRFREE');
							}
							?>
						</span>

						<?php
						if ($item->getPrice() != $item->getOriginalPrice())
						{
							?>
							<span class="vrtkconfcartpricesp-full">
								<s><?php echo $currency->format($item->getTotalCostNoDiscount()); ?></s>
							</span>
							<?php
						}
						
						if ($item->canBeRemoved())
						{
							?>
							<span class="vrtkconfcartremovesp">
								<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=tkreservation.removefromcart&index=' . $k . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="vrtkconfcartremovelink">
									<i class="fas fa-minus-circle"></i>
								</a>
							</span>
							<?php
						}
						?>
					</div>

				</div>

				<div class="vrtk-confcart-item-details">
					<?php
					if (count($item->getToppingsGroupsList()))
					{
						?>
						<div class="vrtk-confcart-item-toppings">
							<?php
							foreach ($item->getToppingsGroupsList() as $t_group)
							{
								$toppings = $t_group->getToppingsList();

								/**
								 * Before displaying the group title, make sure
								 * the customer selected at least a topping.
								 * Otherwise an empty label would be shown.
								 *
								 * @since 1.7.4
								 */
								if (count($toppings))
								{
									?>
									<div class="vrtk-confcart-topping">
										<?php
										echo $t_group->getTitle() . ': ';
										
										foreach ($toppings as $index => $topping)
										{
											if ($index > 0)
											{
												echo ', ';
											}

											echo $topping->getName();

											/**
											 * Include picked units when higher than 1.
											 *
											 * @since 1.8.2
											 */
											if ($topping->getUnits() > 1)
											{
												echo ' x' . $topping->getUnits();
											}
										}
										?>
									</div>
									<?php
								}
							}
							?>
						</div>
						<?php
					}
					
					if (strlen($item->getAdditionalNotes()))
					{
						?>
						<div class="vrtk-confcart-notes"><?php echo $item->getAdditionalNotes(); ?></div>
						<?php
					}
					?>
				</div>

			</div>
			<?php
		}
		?>

	</div>

	<!-- TOTAL NET -->

	<div class="vrtk-confcart-fullcost-details net">

		<span class="fullcost-label">
			<?php echo JText::_('VRTKCARTTOTALNET'); ?>
		</span>

		<div class="fullcost-amount" id="vrtkconfcartfullcost">
			<!-- TOTAL NET STRIKETHROUGH, DISCOUNT IN USE -->
			<?php
			if ($total_discount > 0)
			{
				?>
				<s><?php echo $currency->format($total_net + $total_discount); ?></s>
				<?php
			}
			?>

			<span id="vrtkconfcartnet">
				<?php echo $currency->format($total_net); ?>
			</span>
		</div>
	</div>
	
	<!-- DELIVERY COST -->

	<div class="vrtk-confcart-fullcost-details service">

		<span class="fullcost-label">
			<?php echo JText::_('VRTKCARTTOTALSERVICE'); ?>
		</span>

		<div class="fullcost-amount" id="vrtkconfcartservice">
			<!-- filled via JS -->
		</div>

	</div>

	<!-- DISCOUNT VALUE -->

	<?php
	if ($total_discount > 0)
	{
		?>
		<div class="vrtk-confcart-fullcost-details discount">
			
			<span class="fullcost-label">
				<?php echo JText::_('VRTKCARTTOTALDISCOUNT'); ?>
			</span>

			<div class="fullcost-amount" id="vrtkconfcartdiscount">
				<?php echo $currency->format($total_discount); ?>
			</div>

		</div>
		<?php
	}
	?>
	
	<!-- TAXES -->

	<?php
	if ($show_taxes)
	{
		?>
		<div class="vrtk-confcart-fullcost-details taxes">
			
			<span class="fullcost-label">
				<?php echo JText::_('VRTKCARTTOTALTAXES'); ?>
			</span>

			<div class="fullcost-amount" id="vrtkconfcarttaxes">
				<?php echo $currency->format($cart->getRealTotalTaxes($use_taxes)); ?>
			</div>

		</div>
		<?php
	}
	?>

	<!-- GRATUITY -->

	<?php
	$gratuity = 0;

	if ($config->getBool('tkenablegratuity'))
	{
		$def_gratuity = explode(':', $config->get('tkdefgratuity', ''));
		?>
		<div class="vrtk-confcart-fullcost-details gratuity">

			<span class="fullcost-label">
				<?php echo JText::_('VRTKCARTTOTALTIP'); ?>
			</span>

			<div class="fullcost-amount" id="vrtkconfcartgratuity">
				<?php
				$gratuity = (float) $def_gratuity[0];

				if ($def_gratuity[1] == 1)
				{
					$gratuity = $total_net * $gratuity / 100;
				}

				echo $currency->format($gratuity);
				?>
			</div>

			<div class="gratuity-inline-form">
				<input type="number" value="<?php echo $def_gratuity[0]; ?>" min="0" step="any" max="9999" id="vrtk-gratuity-amount" />
				
				<div class="vre-select-wrapper">
					<select id="vrtk-gratuity-percentot" class="vre-select">
						<option value="1" <?php echo $def_gratuity[1] == 1 ? 'selected="selected"' : ''; ?>>%</option>
						<option value="2" <?php echo $def_gratuity[1] == 2 ? 'selected="selected"' : ''; ?>><?php echo $config->get('currencysymb', ''); ?></option>
					</select>
				</div>
			</div>

		</div>
		<?php
	}
	?>
	
	<!-- GRAND TOTAL -->

	<div class="vrtk-confcart-fullcost-details grand-total">
		
		<span class="fullcost-label">
			<?php echo JText::_('VRTKCARTTOTALPRICE'); ?>
		</span>
		
		<div class="fullcost-amount" id="vrtkconfcartprice">
			<?php echo $currency->format($grand_total); ?>
		</div>

	</div>
	
</div>

<script>

	var TK_GRAND_TOTAL = <?php echo $grand_total; ?>;
	var TK_TOTAL_NET   = <?php echo $total_net; ?>;
	var TK_TOTAL_TAXES = <?php echo $show_taxes ? $cart->getRealTotalTaxes($use_taxes) : 0; ?>;
	var TK_BASE_TOTAL  = <?php echo $total_cost; ?>;

	jQuery(document).ready(function() {

		// gratuity

		jQuery('#vrtk-gratuity-amount, #vrtk-gratuity-percentot').on('change', function() {
			// calculate gratuity
			var gratuity = vrGetGratuity();
			// update tip label
			jQuery('#vrtkconfcartgratuity').text(Currency.getInstance().format(gratuity));

			// refresh total cost
			vrRefreshGrandTotal();
		});

	});

	function vrRefreshGrandTotal() {
		var ch = 0;

		// fetch delivery charge
		if (vrIsDelivery()) {
			ch = TK_DELIVERY_COST + TK_DELIVERY_SURCHARGE;
		} else {
			ch = TK_PICKUP_COST;
		}

		calculateServiceCharge(ch);
	}

	function calculateServiceCharge(ch) {
		var use_taxes = parseInt(<?php echo $use_taxes; ?>);
		var tax_ratio = parseFloat(<?php echo $config->getFloat('tktaxesratio'); ?>);
		var show_tax  = parseInt(<?php echo $show_taxes ? 1 : 0; ?>);

		var base = ch;
		var net  = TK_TOTAL_NET;
		var tax  = 0;

		if (show_tax) {
			if (base > 0) {

				if (use_taxes == 0) {
					// included
					tax  = (base - (base * 100 / (100 + tax_ratio))).roundTo(2);
					base = (base - tax).roundTo(2);
				} else {
					// excluded
					tax  = base * tax_ratio / 100;
				}

			} else if (base < 0) {

				// we have a discount, get proportional net
				net = (TK_TOTAL_NET * (TK_GRAND_TOTAL + base) / TK_GRAND_TOTAL + Math.abs(base)).roundTo(2);

				tax = (TK_TOTAL_TAXES * (TK_GRAND_TOTAL + base) / TK_GRAND_TOTAL).roundTo(2) - TK_TOTAL_TAXES;
			}

			tax += TK_TOTAL_TAXES;
		}

		// get gratuity
		var gratuity = vrGetGratuity();

		var currency = Currency.getInstance();

		// net
		jQuery('#vrtkconfcartnet').text(currency.format(net));
		// grand total
		jQuery('#vrtkconfcartprice').text(currency.format(net + tax + base + gratuity));
		// service cost
		jQuery('#vrtkconfcartservice').text(currency.format(base));
		// taxes
		jQuery('#vrtkconfcarttaxes').text(currency.format(tax));
	}

	function vrGetGratuity() {
		var amount = parseFloat(jQuery('#vrtk-gratuity-amount').val());
		var type   = parseInt(jQuery('#vrtk-gratuity-percentot').val());

		if (isNaN(amount)) {
			// make sure gratuity is enabled
			return 0;
		}

		if (type == 1) {
			amount = TK_TOTAL_NET * amount / 100;
		}

		return amount.roundTo(2);
	}

</script>
