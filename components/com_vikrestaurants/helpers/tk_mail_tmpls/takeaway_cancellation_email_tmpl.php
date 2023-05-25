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
 * VikRestaurants - Take-Away Cancellation E-Mail Template
 *
 * @var object  $order  It is possible to use this variable to 
 * 						access the details of the order.
 *
 * @see the bottom of the page to check the available TAGS to use.
 */

?>

<style>
	@media print {
		.no-printable {
			display: none;
		}
	}
</style>

<div style="background:#f6f6f6; color: #666; width: 100%; padding: 10px 0; table-layout: fixed;" class="vreBackground">
	<div style="max-width: 600px; margin:0 auto; background: #fff;" class="vreBody">

		<!--[if (gte mso 9)|(IE)]>
		<table width="800" align="center">
		<tr>
		<td>
		<![endif]-->

		<table align="center" style="border-collapse: separate; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif;">
			
			<!-- TOP BOX [logo and cancellation content] -->

			<tr>
				<td style="padding: 20px 25px 0; text-align: center;">
					<div style="display: inline-block; width: 200px; margin-bottom: 20px;">{logo}</div>
					<div style="margin: 10px auto 20px;">{cancellation_content}</div>
				</td>
			</tr>

			<!-- CANCELLATION REASON -->

			<?php
			if ($order->cancellation_reason)
			{
				?>
				<tr>
					<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-collapse: separate; font-size: 13px; padding: 10px;">
							<tr>
								<td>
									<div>{cancellation_reason}</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			?>

			<!-- ORDER LINK -->

			<tr class="no-printable">
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 15px; font-size: 14px;">
						<tr>
							<td style="line-height: 1.4em;">
								<div>
									<a href="{order_link}" target="_blank" style="word-break: break-word;">{order_link}</a>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- ORDER DETAILS -->

			<tr>
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 10px auto 0; font-size: 14px; border-top: 2px solid #ddd;">
						<tr>
							<td style="line-height: 1.4em; text-align: left;">
								<div style="display: inline-block; width: 100%; padding: 10px 25px; box-sizing: border-box;">
									<div style="float:left; display: inline-block;">
										{order_number} - {order_key}
									</div>
									<div style="float:right; display: inline-block; text-transform: uppercase; font-weight: bold; color: #df0202;">
										<?php echo JText::_('VRRESERVATIONSTATUSCANCELLED'); ?>
									</div>
								</div>
								<div style="padding: 10px 25px;display: inline-block;width: 100%;box-sizing: border-box;">
									<div style="float:left; display: inline-block;">{order_date_time}</div>
									<div style="float:right; display: inline-block;">{order_total_cost}</div>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- CUSTOMER DETAILS -->

			<tr>
				<td style="padding: 0; text-align: center; border-top: 2px solid #ddd;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 10px auto 0; padding: 0; font-size: 14px;">
						<tr>
							<td style="padding: 0; line-height: 1.4em; text-align: left;">
								<div style="padding: 10px 25px 0;"><strong><?php echo JText::_('VRPERSONALDETAILS'); ?></strong></div>
								<div style="padding: 10px 25px;">
								<?php
								foreach ($order->fields as $label => $value)
								{
									?>
									<div style="padding: 2px 0;">
										<div style="display: inline-block; width: 180px; vertical-align: top;"><?php echo JText::_($label); ?>:</div>
										<div style="display: inline-block;"><?php echo nl2br($value); ?></div>
									</div>
									<?php
								}
								?>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

		</table>

		<!--[if (gte mso 9)|(IE)]>
		</td>
		</tr>
		</table>
		<![endif]-->

	</div>
</div>

<?php
/**
 * @var string|null  {logo}                  The logo image of your company.
 * @var string|null  {company_name}          The name of the company.
 * @var integer      {order_number}          The unique ID of the order.
 * @var string       {order_key}             The serial key of the order.
 * @var string       {order_date_time}       The checkin date and time of the order.
 * @var string       {order_total_cost}      The formatted total cost of the order.
 * @var string|null  {cancellation_content}  The content specified in the language file at VRORDERCANCELLEDCONTENT.
 * @var string       {cancellation_reason}   The cancellation reason specified by the customer (according to the configuration).
 * @var string       {order_link}            The direct url to the details page of the order.
 */
