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

$reservation = $this->reservation;

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();

$can_cancel_order = VikRestaurants::canUserCancelOrder($reservation);

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

// check whether to display the payment form
// within the top position of this view
echo $this->displayPayment('top');
?>

<!-- RESERVATION SUMMARY -->
		
<div class="vrorderpagediv">

	<!-- RESERVATION PAYMENT -->
	
	<div class="vrorderboxcontent">

		<h3 class="vrorderheader"><?php echo JText::_('VRORDERTITLE1'); ?></h3>

		<div class="vrordercontentinfo">

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERNUMBER'); ?>:</span>
				<span class="orderinfo-value"><?php echo $reservation->id; ?></span>
			</div>

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERKEY'); ?>:</span>
				<span class="orderinfo-value"><?php echo $reservation->sid; ?></span>
			</div>

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERSTATUS'); ?>:</span>
				<span class="orderinfo-value vrreservationstatus<?php echo strtolower($reservation->status); ?>">
					<?php echo JText::_('VRRESERVATIONSTATUS' . $reservation->status); ?>
				</span>
			</div>
			
			<?php
			if ($reservation->payment)
			{
				?>
				<br clear="all"/>

				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRORDERPAYMENT'); ?>:</span>
					<span class="orderinfo-value"><?php echo $reservation->payment->name . ($reservation->pay_charge != 0 ? ' (' . $currency->format($reservation->pay_charge) . ')' : ''); ?></span>
				</div>
				<?php
			}

			$deposit = max(array($reservation->bill_value, $reservation->deposit));

			if ($deposit > 0)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_($reservation->bill_value ? 'VREORDERFOOD_BILL_AMOUNT' : 'VRORDERRESERVATIONCOST'); ?>:</span>
					<span class="orderinfo-value"><?php echo $currency->format($deposit); ?></span>
				</div>

				<?php
				if ($reservation->tot_paid > 0)
				{
					?>
					<div class="vrorderinfo">
						<span class="orderinfo-label"><?php echo JText::_('VRORDERDEPOSIT'); ?>:</span>
						<span class="orderinfo-value"><?php echo $currency->format($reservation->tot_paid); ?></span>
					</div>
					<?php
				}
			}

			if ($reservation->coupon)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRORDERCOUPON'); ?>:</span>
					<span class="orderinfo-value">
						<?php 
						echo $reservation->coupon->code;

						if ($reservation->coupon->amount > 0)
						{
							if ($reservation->coupon->type == 1)
							{
								echo ' ' . $reservation->coupon->amount . '%';
							}
							else
							{
								echo ' ' . $currency->format($reservation->coupon->amount);
							}
						}
						?>
					</span>
				</div>
				<?php
			}

			// check if the customer is allowed to order the dishes online
			if ($config->getUint('orderfood'))
			{
				?>
				<div id="vr-order-dishes-button">
					<?php
					/**
					 * Check if the customer can currently order food.
					 *
					 * @since 1.8.1  Still allow access to order view in case the bill has been closed
					 * 				 and the payment method haven't been yet selected.
					 */
					if (VikRestaurants::canUserOrderFood($reservation, $errmsg) || (VikRestaurants::hasPayment(1) && $reservation->bill_closed && $reservation->id_payment <= 0))
					{
						?>
						<button type="button" class="btn">
							<i class="fas fa-shopping-basket"></i>
							<span><?php echo JText::_('VREORDERFOOD'); ?></span>
						</button>
						<?php
					}
					else
					{
						?>
						<button type="button" class="btn disabled" title="<?php echo $this->escape($errmsg); ?>" disabled>
							<i class="fas fa-shopping-basket"></i>
							<span><?php echo JText::_('VREORDERFOOD'); ?></span>
						</button>
						<?php
					}
					?>
				</div>

				<script>
					jQuery(function() {
						// get button to start ordering the dishes
						var btn = jQuery('#vr-order-dishes-button').find('button');

						if (btn.prop('disabled') == false) {
							btn.on('click', function() {
								document.location.href = '<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=orderdishes&ordnum=' . $reservation->id . '&ordkey=' . $reservation->sid . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
							});
						}
					});
				</script>
				<?php
			}
			?>

		</div>

	</div>

	<!-- RESERVATION DETAILS -->
	
	<div class="vrorderboxcontent">

		<h3 class="vrorderheader"><?php echo JText::_('VRORDERTITLE2'); ?></h3>

		<div class="vrordercontentinfo">

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERDATETIME'); ?>:</span>
				<span class="orderinfo-value">
					<?php echo JHtml::_('date', $reservation->checkin_ts, JText::_('DATE_FORMAT_LC1') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?>
				</span>
			</div>

			<div class="vrorderinfo">
				<span class="orderinfo-label"><?php echo JText::_('VRORDERPEOPLE'); ?>:</span>
				<span class="orderinfo-value"><?php echo $reservation->people; ?></span>
			</div>

			<?php
			if ($config->getUint('reservationreq') != 2)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRROOM'); ?>:</span>
					<span class="orderinfo-value"><?php echo $reservation->room->name; ?></span>
				</div>
				<?php
			}
			
			if ($config->getUint('reservationreq') == 0)
			{
				?>
				<div class="vrorderinfo">
					<span class="orderinfo-label"><?php echo JText::_('VRTABLE'); ?>:</span>
					<span class="orderinfo-value"><?php echo $reservation->table_name; ?></span>
				</div>
				<?php
			}
			?>
			
			<br clear="all"/>
			
			<?php
			foreach ($reservation->fields as $key => $val)
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
	if (count($reservation->menus))
	{
		?>
		<!-- RESERVATION MENUS -->

		<div class="vrorderboxcontent">

			<h3 class="vrorderheader"><?php echo JText::_('VRORDERTITLE3'); ?></h3>

			<div class="vrordercontentinfo">

				<?php
				foreach ($reservation->menus as $m)
				{
					?>
					<div class="vrtk-order-food">

						<div class="vrtk-order-food-details">

							<div class="vrtk-order-food-details-left">
								<span class="vrtk-order-food-details-name"><?php echo $m->name; ?></span>
							</div>

							<div class="vrtk-order-food-details-right">
								<span class="vrtk-order-food-details-quantity">x<?php echo $m->quantity; ?></span>
							</div>

						</div>

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

<?php
if ($can_cancel_order)
{
	// load cancellation form by using a sub-template
	echo $this->loadTemplate('cancellation');
}

// check whether to display the payment form
// within the bottom position of this view
echo $this->displayPayment('bottom');
