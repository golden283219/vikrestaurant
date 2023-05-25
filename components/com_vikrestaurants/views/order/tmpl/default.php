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

JHtml::_('vrehtml.sitescripts.animate');
JHtml::_('vrehtml.assets.fontawesome');

$order = $this->order;

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();

$can_cancel_order = VikRestaurants::canUserCancelOrder($order);

// check whether to display the payment form
// within the top position of this view
echo $this->displayPayment('top');
?>

<!-- ORDER SUMMARY -->
		
<div class="vrorderpagediv">

	<!-- ORDER PAYMENT -->
	
	<div class="vrorderboxcontent">

		<h3 class="vrorderheader"><?php echo JText::_('VRTKORDERTITLE1'); ?></h3>

		<div class="vrordercontentinfo">

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERNUMBER'); ?>:</span>
				<span class="orderinfo-value"><?php echo $order->id; ?></span>
			</div>

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERKEY'); ?>:</span>
				<span class="orderinfo-value"><?php echo $order->sid; ?></span>
			</div>

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERSTATUS'); ?>:</span>
				<span class="orderinfo-value vrreservationstatus<?php echo strtolower($order->status); ?>">
					<?php echo JText::_('VRRESERVATIONSTATUS' . $order->status); ?>
				</span>
			</div>
			
			<?php
			if ($order->payment)
			{
				?>
				<br clear="all"/>

				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRORDERPAYMENT'); ?>:</span>
					<span class="orderinfo-value"><?php echo $order->payment->name . ($order->pay_charge != 0 ? ' (' . $currency->format($order->pay_charge) . ')' : ''); ?></span>
				</div>
				<?php
			}
			
			if ($order->total_to_pay > 0)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRTKORDERTOTALTOPAY'); ?>:</span>
					<span class="orderinfo-value"><?php echo $currency->format($order->total_to_pay); ?></span>
				</div>
				<?php
			}

			if ($order->taxes > 0)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRTKORDERTOTALNETPRICE'); ?>:</span>
					<span class="orderinfo-value"><?php echo $currency->format($order->total_net); ?></span>
				</div>

				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRTKORDERTAXES'); ?>:</span>
					<span class="orderinfo-value"><?php echo $currency->format($order->taxes); ?></span>
				</div>
				<?php
			}

			if ($order->tot_paid > 0)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRORDERDEPOSIT'); ?>:</span>
					<span class="orderinfo-value"><?php echo $currency->format($order->tot_paid); ?></span>
				</div>
				<?php
			}
			
			if ($order->coupon)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRORDERCOUPON'); ?>:</span>
					<span class="orderinfo-value">
						<?php 
						echo $order->coupon->code;

						if ($order->coupon->amount > 0)
						{
							if ($order->coupon->type == 1)
							{
								echo ' ' . $order->coupon->amount . '%';
							}
							else
							{
								echo ' ' . $currency->format($order->coupon->amount);
							}
						}
						?>
					</span>
				</div>
				<?php
			}
			?>

		</div>

	</div>

	<!-- ORDER DETAILS -->
	
	<div class="vrorderboxcontent">

		<h3 class="vrorderheader"><?php echo JText::_('VRORDERTITLE2'); ?></h3>

		<div class="vrordercontentinfo">

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERDATETIME'); ?>:</span>
				<span class="orderinfo-value">
					<?php echo $order->checkin_lc1; ?>
				</span>
			</div>

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRTKORDERDELIVERYSERVICE'); ?>:</span>
				<span class="orderinfo-value">
					<?php
					echo JText::_($order->delivery_service ? 'VRTKORDERDELIVERYOPTION': 'VRTKORDERPICKUPOPTION');
					
					if ($order->delivery_charge != 0)
					{
						echo ' (' . ($order->delivery_charge > 0 ? '+' : '') . $currency->format($order->delivery_charge) . ')';
					}
					?>
				</span>
			</div>
			
			<br clear="all"/>
			
			<?php
			foreach ($order->fields as $key => $val)
			{
				if (!empty($val))
				{
					?>
					<div class="vrorderinfo">
						<span class="orderinfo-label"><?php echo JText::_($key); ?>:</span>
						<span class="orderinfo-value"><?php echo nl2br($val); ?></span>
					</div>
					<?php
				}
			}
			?>

		</div>

	</div>
	
	<?php
	if (count($order->items))
	{
		?>
		<!-- ORDER CART -->

		<div class="vrorderboxcontent">

			<h3 class="vrorderheader"><?php echo JText::_('VRTKORDERTITLE3'); ?></h3>

			<div class="vrordercontentinfo">

				<?php
				foreach ($order->items as $item)
				{
					?>
					<div class="vrtk-order-food">

						<div class="vrtk-order-food-details">

							<div class="vrtk-order-food-details-left">
								<span class="vrtk-order-food-details-name"><?php echo $item->name; ?></span>
							</div>

							<div class="vrtk-order-food-details-right">
								<span class="vrtk-order-food-details-quantity">x<?php echo $item->quantity; ?></span>

								<span class="vrtk-order-food-details-price"><?php echo $currency->format($item->price); ?></span>
							</div>

						</div>

						<?php
						if ($item->toppings)
						{
							?>
							<div class="vrtk-order-food-middle">
								<?php
								foreach ($item->toppings as $group)
								{
									?>
									<div class="vrtk-order-food-group">
										<span class="vrtk-order-food-group-title"><?php echo $group->title; ?>:</span>
										
										<span class="vrtk-order-food-group-toppings">
											<?php echo $group->str; ?>
										</span>
									</div>
									<?php
								}
								?>
							</div>
							<?php
						}
						
						if (!empty($item->notes))
						{
							?>
							<div class="vrtk-order-food-notes">
								<?php echo $item->notes; ?>
							</div>
							<?php
						}
						?>

					</div>
					<?php
				}
				?>

			</div>

			<div class="vrorder-grand-total">

				<?php
				if ($order->total_to_pay > 0)
				{
					?>
					<div class="grand-total-row">
						<span class="label"><?php echo JText::_('VRTKCARTTOTALNET'); ?></span>
						<span class="amount"><?php echo $currency->format($order->total_net); ?></span>
					</div>
					<?php

					// delivery charge
					if ($order->delivery_charge != 0)
					{
						?>
						<div class="grand-total-row">
							<span class="label"><?php echo JText::_('VRTKCARTTOTALSERVICE'); ?></span>
							<span class="amount"><?php echo $currency->format($order->delivery_charge); ?></span>
						</div>
						<?php
					}

					// payment charge
					if ($order->pay_charge != 0)
					{
						?>
						<div class="grand-total-row">
							<span class="label"><?php echo JText::_('VRTKCARTTOTALPAYCHARGE'); ?></span>
							<span class="amount"><?php echo $currency->format($order->pay_charge); ?></span>
						</div>
						<?php
					}

					// taxes
					if ($order->taxes > 0)
					{
						?>
						<div class="grand-total-row red">
							<span class="label"><?php echo JText::_('VRTKCARTTOTALTAXES'); ?></span>
							<span class="amount"><?php echo $currency->format($order->taxes); ?></span>
						</div>
						<?php
					}

					// discount
					if ($order->discount_val > 0)
					{
						?>
						<div class="grand-total-row red">
							<span class="label"><?php echo JText::_('VRTKCARTTOTALDISCOUNT'); ?></span>
							<span class="amount"><?php echo $currency->format($order->discount_val * -1); ?></span>
						</div>
						<?php
					}

					// gratuity
					if ($order->tip_amount > 0)
					{
						?>
						<div class="grand-total-row">
							<span class="label"><?php echo JText::_('VRTKCARTTOTALTIP'); ?></span>
							<span class="amount"><?php echo $currency->format($order->tip_amount); ?></span>
						</div>
						<?php
					}

					// grand total
					if ($order->total_net != $order->total_to_pay)
					{
						?>
						<div class="grand-total-row grand-total">
							<span class="label"><?php echo JText::_('VRTKCARTTOTALPRICE'); ?></span>
							<span class="amount"><?php echo $currency->format($order->total_to_pay); ?></span>
						</div>
						<?php
					}
				}
				?>

			</div>

		</div>

		<?php
	}
	?>
	
</div>

<?php
if ($can_cancel_order)
{
	// load cancellation form by using a sub-template
	echo $this->loadTemplate('cancellation');
}

// check whether to display the payment form
// within the bottom position of this view
echo $this->displayPayment('bottom');
