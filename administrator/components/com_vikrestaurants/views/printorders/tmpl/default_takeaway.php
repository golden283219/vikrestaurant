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

$order = $this->orderDetails;

$config = VREFactory::getConfig();

$date_format = $config->get('dateformat');
$time_format = $config->get('timeformat');

$currency = VREFactory::getCurrency();

?>

<div class="vr-print-order-wrapper">

	<!-- HEAD -->

	<div class="tk-print-box">

		<div class="tk-field">
			<span class="tk-label"><?php echo JText::_('VRORDERNUMBER') . ':'; ?></span>
			<span class="tk-value"><?php echo $order->id . ' - ' . $order->sid; ?></span>
		</div>

		<div class="tk-field">
			<span class="tk-label"><?php echo JText::_('VRORDERSTATUS') . ':'; ?></span>
			<span class="tk-value order-<?php echo strtolower($order->status); ?>">
				<?php echo strtoupper(JText::_('VRRESERVATIONSTATUS' . $order->status)); ?>
			</span>
		</div>

		<div class="tk-field">
			<span class="tk-label"><?php echo JText::_('VRORDERDATETIME') . ':'; ?></span>
			<span class="tk-value"><?php echo date($date_format . ' ' . $time_format, $order->checkin_ts); ?></span>
		</div>

		<div class="tk-field">
			<span class="tk-label"><?php echo JText::_('VRTKORDERDELIVERYSERVICE') . ':'; ?></span>
			<span class="tk-value"><?php echo JText::_($order->delivery_service ? 'VRTKORDERDELIVERYOPTION' : 'VRTKORDERPICKUPOPTION'); ?></span>
		</div>

		<?php
		if (!empty($order->payment_name))
		{
			?>
			<div class="tk-field">
				<span class="tk-label"><?php echo JText::_('VRORDERPAYMENT') . ':'; ?></span>
				<span class="tk-value"><?php echo $order->payment_name; ?></span>
			</div>
			<?php
		}

		if ($order->total_to_pay > 0)
		{
			?>
			<div class="tk-field">
				<span class="tk-label"><?php echo JText::_('VRTKORDERTOTALTOPAY') . ':'; ?></span>
				<span class="tk-value">
					<?php echo $currency->format($order->total_to_pay); ?>
				</span>
			</div>
			<?php
		}

		if ($order->coupon)
		{ 
			?>
			<div class="tk-field">
				<span class="tk-label"><?php echo JText::_('VRORDERCOUPON') . ':'; ?></span>
				<span class="tk-value">
					<?php echo $order->coupon->code . ' : ' . ($order->coupon->type == 1 ? $order->coupon->amount . '%' : $currency->format($order->coupon->amount)); ?>
				</span>
			</div>
			<?php
		}
		?>
		
	</div>

	<!-- CUSTOMER DETAILS -->

	<?php
	if ($order->hasFields)
	{
		?>
		<div class="tk-print-box">
			<?php
			foreach ($order->fields as $k => $v)
			{ 
				if (strlen($v))
				{
					?>
					<div class="tk-field">
						<span class="tk-label"><?php echo JText::_($k) . ':'; ?></span>
						<span class="tk-value"><?php echo nl2br($v); ?></span>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
	}
	?>

	<!-- ITEMS -->

	<?php
	if (count($order->items))
	{
		?>
		<div class="tk-print-box">
			<?php
			foreach ($order->items as $item)
			{
				?>
				<div class="tk-item">
					<div class="tk-details">
						<span class="name">
							<span class="quantity"><?php echo $item->quantity; ?>x</span>
							<?php echo $item->name; ?>
						</span>
						<span class="price"><?php echo $currency->format($item->price); ?></span>
					</div>

					<?php
					if (count($item->toppings))
					{
						?>
						<div class="tk-toppings-cont">
							<?php
							foreach ($item->toppings as $group)
							{
								?>
								<div class="tk-toppings-group">
									<span class="title"><?php echo $group->title; ?>:&nbsp;</span>
									<span class="toppings"><?php echo $group->str; ?></span>
								</div>
								<?php
							}
							?>
						</div>
						<?php
					}

					if (strlen($item->notes))
					{
						?>
						<div class="tk-notes"><?php echo $item->notes; ?></div>
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
	?>

	<!-- ORDER TOTAL -->

	<?php
	if ($order->total_to_pay > 0)
	{
		?>
		<div class="tk-print-box">
			
			<!-- TOTAL NET -->
			<div class="tk-total-row">
				<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALNET'); ?></span>
				<span class="tk-amount"><?php echo $currency->format($order->total_net); ?></span>
			</div>

			<?php
			if ($order->delivery_charge != 0)
			{
				?>
				<div class="tk-total-row">
					<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALSERVICE'); ?></span>
					<span class="tk-amount"><?php echo $currency->format($order->delivery_charge); ?></span>
				</div>
				<?php
			}
		
			if ($order->pay_charge != 0)
			{
				?>
				<div class="tk-total-row">
					<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALPAYCHARGE'); ?></span>
					<span class="tk-amount"><?php echo $currency->format($order->pay_charge); ?></span>
				</div>
				<?php
			}
		
			if ($order->taxes != 0)
			{
				?>
				<div class="tk-total-row">
					<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALTAXES'); ?></span>
					<span class="tk-amount"><?php echo $currency->format($order->taxes); ?></span>
				</div>
				<?php
			}
			
			if ($order->tip_amount > 0)
			{
				?>
				<!-- TIP -->
				<div class="tk-total-row">
					<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALTIP'); ?></span>
					<span class="tk-amount"><?php echo $currency->format($order->tip_amount); ?></span>
				</div>
				<?php
			}

			if ($order->discount_val > 0)
			{
				?>
				<!-- DISCOUNT -->
				<div class="tk-total-row">
					<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALDISCOUNT'); ?></span>
					<span class="tk-amount"><?php echo $currency->format($order->discount_val * -1); ?></span>
				</div>
				<?php
			}
			?>

			<!-- GRAND TOTAL -->
			<div class="tk-total-row">
				<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALPRICE'); ?></span>
				<span class="tk-amount"><?php echo $currency->format($order->total_to_pay); ?></span>
			</div>

		</div>
		<?php
	}
	?>

</div>
