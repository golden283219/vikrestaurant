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
 * @var  mixed      $order    The order details.
 * @var  JRegistry  $args     The event arguments.
 * @var  boolean    $logo     True to show the logo, false otherwise.
 * @var  boolean    $company  True to show the restaurant name, false otherwise.
 * @var  boolean    $details  True to show the order details, false otherwise.
 * @var  boolean    $items    True to show the ordered items, false otherwise.
 * @var  boolean    $total    True to show the total lines, false otherwise.
 * @var  boolean    $billing  True to show the billing details, false otherwise.
 */
extract($displayData);

$config = VREFactory::getConfig();

$currency = VREFactory::getCurrency();

?>

<div style="padding: 5px 10px;">

	<table align="center" style="margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif;">

		<?php
		if ($logo && $config->get('companylogo'))
		{
			?>
			<!-- logo -->

			<tr>
				<td style="padding: 0 0 10px; text-align: center;">
					<img src="<?php echo VREMEDIA_SMALL_URI . $config->get('companylogo'); ?>" />
				</td>
			</tr>
			<?php
		}

		if ($company)
		{
			?>
			<!-- restaurant name -->

			<tr>	
				<td style="padding: 0 0 10px; text-align: center;">
					<?php echo $config->get('restname'); ?>
				</td>
			</tr>
			<?php
		}

		if ($details)
		{
			?>
			<!-- order details -->

			<tr>
				<td style="padding: 10px 0 10px; text-align: center; border-top: 1px solid #ddd;">
					<table style="width: 100%; border-spacing: 0;">

						<!-- order number -->

						<tr>
							<td style="text-align: left;">
								<?php echo JText::_('VRORDERNUMBER') . ':'; ?>
							</td>

							<td style="text-align: right;">
								<?php echo $order->id . ' - ' . $order->sid; ?>
							</td>
						</tr>

						<!-- order status -->

						<tr>
							<td style="text-align: left;">
								<?php echo JText::_('VRORDERSTATUS') . ':'; ?>
							</td>
							
							<td style="text-align: right;">
								<?php echo strtoupper(JText::_('VRRESERVATIONSTATUS' . $order->status)); ?>
							</td>
						</tr>

						<!-- check-in -->

						<tr>
							<td style="text-align: left;">
								<?php echo JText::_('VRORDERDATETIME') . ':'; ?>
							</td>

							<td style="text-align: right;">
								<?php
								echo date(
									$config->get('dateformat') . ' ' . $config->get('timeformat'),
									$order->checkin_ts
								);
								?>
							</td>
						</tr>

						<!-- service -->

						<tr>
							<td style="text-align: left;">
								<?php echo JText::_('VRTKORDERDELIVERYSERVICE') . ':'; ?>
							</td>

							<td style="text-align: right;">
								<?php echo JText::_($order->delivery_service ? 'VRTKORDERDELIVERYOPTION' : 'VRTKORDERPICKUPOPTION'); ?>
							</td>
						</tr>

						<?php
						if (!empty($order->payment_name))
						{
							?>
							<!-- payment -->

							<tr>
								<td style="text-align: left;">
									<?php echo JText::_('VRORDERPAYMENT') . ':'; ?>
								</td>
								
								<td style="text-align: right;">
									<?php echo $order->payment_name; ?>
								</td>
							</tr>
							<?php
						}

						if ($order->total_to_pay > 0)
						{
							?>
							<!-- total -->

							<tr>
								<td style="text-align: left;">
									<?php echo JText::_('VRTKORDERTOTALTOPAY') . ':'; ?>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($order->total_to_pay); ?>
								</td>
							</tr>
							<?php
						}

						if ($order->coupon)
						{ 
							?>
							<!-- coupon -->

							<tr>
								<td style="text-align: left;">
									<?php echo JText::_('VRORDERCOUPON') . ':'; ?>
								</td>

								<td style="text-align: right;">
									<?php echo $order->coupon->code . ' : ' . ($order->coupon->type == 1 ? $order->coupon->amount . '%' : $currency->format($order->coupon->amount)); ?>
								</td>
							</tr>
							<?php
						}
						?>

					</table>
				</td>
			</tr>
			<?php
		}
		
		if ($items && count($order->items))
		{
			?>
			<!-- order items -->

			<tr>
				<td style="padding: 10px 0 10px; text-align: center; border-top: 1px solid #ddd;">
					<table style="width: 100%; border-spacing: 0;">

						<?php
						foreach ($order->items as $item)
						{
							?>
							<!-- item -->

							<tr>
								<td style="text-align: left;">
									<span><?php echo $item->quantity; ?>x</span>
									<span><?php echo $item->name; ?></span>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($item->price); ?>
								</td>
							</tr>

							<?php
							if (strlen($item->notes))
							{
								?>
								<!-- notes -->

								<tr>
									<td style="padding: 0 0 0 30px; text-align: left; font-size: 90%; font-style: italic;" colspan="2">
										<?php echo $item->notes; ?>
									</td>
								</tr>
								<?php
							}

							foreach ($item->toppings as $group)
							{
								?>
								<!-- topping -->

								<tr>
									<td style="padding: 0 0 0 30px; text-align: left; font-size: 90%;" colspan="2">
										<span><?php echo $group->title; ?>:&nbsp;</span>
										<span><?php echo $group->str; ?></span>
									</td>
								</tr>
								<?php
							}
						}
						?>

					</table>
				</td>
			</tr>
			<?php
		}
	
		if ($total && $order->total_to_pay > 0)
		{
			?>
			<!-- total -->

			<tr>
				<td style="padding: 10px 0 10px; text-align: center; border-top: 1px solid #ddd;">
					<table style="width: 100%; border-spacing: 0;">

						<!-- grand total -->

						<tr>
							<td style="text-align: left;">
								<?php echo JText::_('VRTKCARTTOTALPRICE'); ?>
							</td>

							<td style="text-align: right;">
								<?php echo $currency->format($order->total_to_pay); ?>
							</td>
						</tr>

						<!-- total net -->

						<tr style="font-size: 90%;">
							<td style="text-align: left;">
								<?php echo JText::_('VRTKCARTTOTALNET'); ?>
							</td>

							<td style="text-align: right;">
								<?php echo $currency->format($order->total_net); ?>
							</td>
						</tr>

						<?php
						if ($order->delivery_charge != 0)
						{
							?>
							<!-- delivery charge -->

							<tr style="font-size: 90%;">
								<td style="text-align: left;">
									<?php echo JText::_('VRTKCARTDELIVERYCHARGE'); ?>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($order->delivery_charge); ?>
								</td>
							</tr>
							<?php
						}
					
						if ($order->pay_charge != 0)
						{
							?>
							<!-- payment charge  -->

							<tr style="font-size: 90%;">
								<td style="text-align: left;">
									<?php echo JText::_('VRTKCARTTOTALPAYCHARGE'); ?>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($order->pay_charge); ?>
								</td>
							</tr>
							<?php
						}
					
						if ($order->taxes != 0)
						{
							?>
							<!-- taxes  -->

							<tr style="font-size: 90%;">
								<td style="text-align: left;">
									<?php echo JText::_('VRTKCARTTOTALTAXES'); ?>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($order->taxes); ?>
								</td>
							</tr>
							<?php
						}
						
						if ($order->tip_amount > 0)
						{
							?>
							<!-- tip -->

							<tr style="font-size: 90%;">
								<td style="text-align: left;">
									<?php echo JText::_('VRTKCARTTOTALTIP'); ?>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($order->tip_amount); ?>
								</td>
							</tr>
							<?php
						}

						if ($order->discount_val > 0)
						{
							?>
							<!-- discount -->

							<tr style="font-size: 90%;">
								<td style="text-align: left;">
									<?php echo rtrim(JText::_('VRTKCARTTOTALDISCOUNT'), ':'); ?>
								</td>

								<td style="text-align: right;">
									<?php echo $currency->format($order->discount_val * -1); ?>
								</td>
							</tr>
							<?php
						}
						?>

					</table>
				</td>
			</tr>
			<?php
		}

		if ($billing && $order->hasFields)
		{
			?>
			<!-- customer -->
			
			<tr>
				<td style="padding: 10px 0 10px; text-align: center; border-top: 1px solid #ddd;">
					<table style="width: 100%; border-spacing: 0;">
						<?php
						foreach ($order->fields as $k => $v)
						{ 
							if (strlen($v))
							{
								?>
								<tr>
									<td style="text-align: left; width: 30%;"><?php echo JText::_($k) . ':'; ?></td>
									<td style="text-align: left;"><?php echo nl2br($v); ?></td>
								</tr>
								<?php
							}
						}
						?>
					</table>
				</td>
			</tr>
			<?php
		}
		?>

	</table>

</div>