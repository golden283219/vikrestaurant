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
 * @var  object  $order  The order details.
 */
extract($displayData);

$total_rows = 7;

if ($order->discount_val <= 0)
{
	// no discount, hide row
	$total_rows--;
}

if ($order->tip_amount <= 0)
{
	// no tip, hide row
	$total_rows--;
}

if ($order->pay_charge <= 0)
{
	// no payment charge, hide row
	$total_rows--;
}

if ($order->delivery_charge <= 0)
{
	// no delivery charge, hide row
	$total_rows--;
}

$currency = VREFactory::getCurrency();

?>

<table width="100%"  border="0">
	
	<tr>
		<td>
			<table width="100%"  border="0" cellspacing="5" cellpadding="5">
				<tr>
					<td width="70%">{company_logo}<br/><br />{company_info}</td>

					<td width="30%"align="right" valign="bottom">
						<table width="100%" border="0" cellpadding="1" cellspacing="1">
							<tr>
								<td align="right" bgcolor="#FFFFFF"><strong><?php echo JText::_('VRINVNUM'); ?> {invoice_number}{invoice_suffix}</strong></td>
							</tr>

							<tr>
								<td align="right" bgcolor="#FFFFFF"><strong><?php echo JText::_('VRINVDATE'); ?> {invoice_date}</strong></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td>
			<table width="100%"  border="0" cellspacing="1" cellpadding="2">
				<tr bgcolor="#E1E1E1" style="background-color: #E1E1E1;">
					<td width="60%"><strong><?php echo JText::_('VRINVITEMDESC'); ?></strong></td>
					<td width="10%" align="right"><strong><?php echo JText::_('VRINVITEMQUANTITY'); ?></strong></td>
					<td width="30%" align="right"><strong><?php echo JText::_('VRINVITEMPRICE'); ?></strong></td>
				</tr>
				
				<?php
				foreach ($order->items as $item)
				{
					?>	
					<tr>
						<td width="60%"><?php echo $item->name; ?></td>
						<td width="10%" align="right">x<?php echo $item->quantity; ?></td>
						<td width="30%" align="right"><?php echo $currency->format($item->price); ?></td>
					</tr>
					<?php
				}
				?>

				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>

				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td>
			<table width="100%" border="0" cellspacing="1" cellpadding="2">
				<tr bgcolor="#E1E1E1">
					<td width="70%" colspan="2" rowspan="<?php echo $total_rows; ?>" valign="top">
						<strong><?php echo JText::_('VRINVCUSTINFO'); ?></strong><br/>{customer_info}<br/>{billing_info}
					</td>

					<td width="30%" align="left">
						<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
							<td align="left"><strong><?php echo JText::_('VRINVTOTAL'); ?></strong></td>
							<td align="right">{invoice_totalnet}</td>
						</tr></table>
					</td>
				</tr>

				<?php
				if ($order->delivery_charge > 0)
				{
					?>
					<tr bgcolor="#E1E1E1">
						<td width="30%" align="left">
							<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
								<td align="left"><strong><?php echo JText::_('VRINVDELIVERYCHARGE'); ?></strong></td>
								<td align="right">{invoice_deliverycharge}</td>
							</tr></table>
						</td>
					</tr>
					<?php
				}
				
				if ($order->pay_charge > 0)
				{
					?>
					<tr bgcolor="#E1E1E1" color="#900">
						<td width="30%" align="left">
							<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
								<td align="left"><strong><?php echo JText::_('VRINVPAYCHARGE'); ?></strong></td>
								<td align="right">{invoice_paycharge}</td>
							</tr></table>
						</td>
					</tr>
					<?php
				}
				?>
				
				<tr bgcolor="#E1E1E1">
					<td width="30%" align="left">
						<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
							<td align="left"><strong><?php echo JText::_('VRINVTAXES'); ?></strong></td>
							<td align="right">{invoice_totaltax}</td>
						</tr></table>
					</td>
				</tr>

				<?php
				if ($order->tip_amount > 0)
				{
					?>
					<tr bgcolor="#E1E1E1">
						<td width="30%" align="left">
							<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
								<td align="left"><strong><?php echo JText::_('VRINVTIP'); ?></strong></td>
								<td align="right">{invoice_totaltip}</td>
							</tr></table>
						</td>
					</tr>
					<?php
				}
				
				if ($order->discount_val > 0)
				{
					?>
					<tr bgcolor="#E1E1E1" color="#900">
						<td width="30%" align="left">
							<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
								<td align="left"><strong><?php echo JText::_('VRINVDISCOUNTVAL'); ?></strong></td>
								<td align="right">{invoice_discountval}</td>
							</tr></table>
						</td>
					</tr>
					<?php
				}
				?>

				<tr bgcolor="#E1E1E1">
					<td width="30%" align="left" valign="top">
						<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>
							<td align="left"><strong><?php echo JText::_('VRINVGRANDTOTAL'); ?></strong></td>
							<td align="right">{invoice_grandtotal}</td>
						</tr></table>
					</td>
				</tr>
			</table>
		</td>
	</tr>

</table>