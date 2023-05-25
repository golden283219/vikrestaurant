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

<div class="vrtk-order-cart">

	<div class="order-cart-summary">

		<!-- NET -->
		<div class="vrtk-order-amount-line total-net">
			<span class="amount-legend"><?php echo JText::_('VRMANAGETKORDDISC2'); ?></span>
			<span class="amount-value"><?php echo $currency->format($this->order->total_net); ?></span>
		</div>

		<?php
		if ($this->order->discount_val > 0)
		{
			if ($this->order->coupon)
			{
				$help = '<i class="fas fa-question-circle hasTooltip" title="' . $this->escape($this->order->coupon->code) . '" style="margin-right: 4px;"></i>';
			}
			else
			{
				$help = '';
			}

			?>
			<!-- DISCOUNT -->
			<div class="vrtk-order-amount-line total-discount">
				<span class="amount-legend"><?php echo $help . JText::_('VRDISCOUNT'); ?></span>
				<span class="amount-value"><?php echo $currency->format($this->order->discount_val * -1); ?></span>
			</div>
			<?php
		}

		if ((float) $this->order->delivery_charge)
		{
			?>
			<!-- DELIVERY CHARGE -->
			<div class="vrtk-order-amount-line delivery-charge">
				<span class="amount-legend"><?php echo JText::_('VRINVDELIVERYCHARGE'); ?></span>
				<span class="amount-value"><?php echo $currency->format($this->order->delivery_charge); ?></span>
			</div>
			<?php
		}

		if ($this->order->taxes > 0)
		{
			?>
			<!-- TAXES -->
			<div class="vrtk-order-amount-line total-tax">
				<span class="amount-legend"><?php echo JText::_('VRINVTAXES'); ?></span>
				<span class="amount-value"><?php echo $currency->format($this->order->taxes); ?></span>
			</div>
			<?php
		}

		if ($this->order->tip_amount > 0)
		{
			?>
			<!-- TIP -->
			<div class="vrtk-order-amount-line total-tip">
				<span class="amount-legend"><?php echo JText::_('VRTIP'); ?></span>
				<span class="amount-value"><?php echo $currency->format($this->order->tip_amount); ?></span>
			</div>
			<?php
		}
		
		if ($this->order->total_to_pay > $this->order->tot_paid)
		{
			?>
			<!-- DUE -->
			<div class="vrtk-order-amount-line total-due">
				<span class="amount-legend"><?php echo JText::_('VRORDERINVDUE'); ?></span>
				<span class="amount-value"><?php echo $currency->format($this->order->total_to_pay - $this->order->tot_paid); ?></span>
			</div>
			<?php
		}
		?>

		<!-- GRAND TOTAL -->
		<div class="vrtk-order-amount-line grand-total">
			<span class="amount-legend"><?php echo JText::_('VRMANAGETKORDDISC1'); ?></span>
			<span class="amount-value"><?php echo $currency->format($this->order->total_to_pay); ?></span>
		</div>
	</div>

</div>
