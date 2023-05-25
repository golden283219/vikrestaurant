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

$config = VREFactory::getConfig();

$currency = VREFactory::getCurrency();

?>

<h3><?php echo JText::_('VRMANAGERESERVATION20'); ?></h3>

<div class="order-fields">

	<!-- Total Net -->

	<div class="order-field total-net">

		<label><?php echo JText::_('VRMANAGETKORDDISC2'); ?></label>

		<div class="order-field-value">
			<b><?php echo $currency->format($this->order->total_net); ?></b>
		</div>

	</div>

	<?php
	if ($this->order->delivery_charge)
	{
		?>
		<!-- Delivery Charge -->

		<div class="order-field delivery-charge">

			<label><?php echo JText::_('VRMANAGETKRES31'); ?></label>

			<div class="order-field-value">
				<b><?php echo $currency->format($this->order->delivery_charge); ?></b>
			</div>

		</div>
		<?php
	}
	?>

	<?php
	if ($this->order->taxes)
	{
		?>
		<!-- Total Taxes -->

		<div class="order-field total-tax">

			<label><?php echo JText::_('VRTKCARTOPTION2'); ?></label>

			<div class="order-field-value">
				<b><?php echo $currency->format($this->order->taxes); ?></b>
			</div>

		</div>
		<?php
	}
	?>

	<?php
	if ($this->order->discount_val > 0)
	{
		?>
		<!-- Discount -->

		<div class="order-field total-discount">

			<label><?php echo JText::_('VRINVDISCOUNTVAL'); ?></label>

			<div class="order-field-value">
				<b><?php echo $currency->format($this->order->discount_val * -1); ?></b>
			</div>

		</div>
		<?php
	}
	?>

	<?php
	if ($this->order->tip_amount > 0)
	{
		?>
		<!-- Tip -->

		<div class="order-field total-tax">

			<label><?php echo JText::_('VRINVTIP'); ?></label>

			<div class="order-field-value">
				<b><?php echo $currency->format($this->order->tip_amount); ?></b>
			</div>

		</div>
		<?php
	}
	?>

	<!-- Paid -->

	<div class="order-field total-paid">

		<label>
			<?php
			if ($this->order->payment)
			{
				?><i class="<?php echo $this->order->payment->fontIcon; ?> hasTooltip" title="<?php echo $this->escape($this->order->payment->name); ?>" style="margin-right:4px;"></i><?php
			}

			echo JText::_('VRORDERPAID');
			?>
		</label>

		<div class="order-field-value">
			<b><?php echo $currency->format($this->order->tot_paid); ?></b>
		</div>

	</div>

	<?php
	if ($this->order->total_to_pay > $this->order->tot_paid)
	{
		$due = $this->order->total_to_pay - $this->order->tot_paid;
		?>
		<!-- Due -->

		<div class="order-field total-due">

			<label><?php echo JText::_('VRORDERINVDUE'); ?></label>

			<div class="order-field-value">
				<b><?php echo $currency->format($due); ?></b>
			</div>

		</div>
		<?php
	}
	?>

	<!-- Total Cost -->

	<div class="order-field total-cost">

		<label><?php echo JText::_('VRMANAGETKORDDISC1'); ?></label>

		<div class="order-field-value">
			<b><?php echo $currency->format($this->order->total_to_pay); ?></b>
		</div>

	</div>

</div>

<?php
if ($this->order->coupon)
{
	?>
	<!-- coupon code -->

	<div class="coupon-box">

		<span class="coupon-code">
			<i class="fas fa-ticket-alt hasTooltip" title="<?php echo $this->escape(JText::_('VRMANAGERESERVATION8')); ?>"></i>
			<b><?php echo $this->order->coupon->code; ?></b>
		</span>

		<span class="coupon-amount">
			<?php
			if ($this->order->coupon->type == 1)
			{
				echo $this->order->coupon->amount . '%';
			}
			else
			{
				echo $currency->format($this->order->coupon->amount);
			}
			?>
		</span>

	</div>
	<?php
}

if ($this->order->invoice)
{
	?>
	<hr />

	<!-- Invoice -->

	<div class="invoice-record">

		<!-- Invoice Number -->

		<div class="invoice-id">
			<b><?php echo $this->order->invoice->number; ?></b>
		</div>

		<!-- Invoice Creation Date -->

		<div class="invoice-date">
			<?php echo JHtml::_('date', JDate::getInstance($this->order->invoice->createdon), JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?>
		</div>

		<!-- Invoice File -->

		<div class="invoice-download">
			<?php
			if (is_file($this->order->invoice->path))
			{
				?>
				<a href="<?php echo $this->order->invoice->uri; ?>" target="_blank">
					<i class="fas fa-file-pdf"></i>
				</a>
				<?php
			}
			else
			{
				?><i class="fas fa-file-pdf"></i><?php
			}
			?>
		</div>

	</div>
	<?php
}

if ($this->order->history)
{
	?>
	<hr />

	<h3><?php echo JText::_('VRORDERSTATUSES'); ?></h3>

	<!-- Order Status Codes History -->

	<div class="order-status-history">

		<?php
		foreach ($this->order->history as $status)
		{
			?>
			<div class="order-status-block">

				<?php
				if ($status->icon)
				{
					?>
					<div class="code-icon">
						<img src="<?php echo $status->iconURL; ?>" />
					</div>
					<?php
				}
				?>

				<div class="code-text">
					<div class="code-title">
						<div class="code-name">
							<?php echo $status->code; ?>
						</div>

						<div class="code-date">
							<?php echo VikRestaurants::formatTimestamp($config->get('dateformat') . ' ' . $config->get('timeformat'), $status->createdon); ?>
						</div>
					</div>

					<?php
					if ($status->notes || $status->codeNotes)
					{
						?>
						<div class="code-notes">
							<?php
							echo $status->notes ? $status->notes : '<em>' . $status->codeNotes . '</em>';
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
	<?php
}
