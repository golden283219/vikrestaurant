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
 * VikRestaurants - Restaurant Administrator E-Mail Template
 *
 * @var object  $reservation  It is possible to use this variable to 
 * 							  access the details of the reservation.
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
			
			<!-- TOP BOX [company logo and name] -->

			<tr>
				<td style="padding: 0 25px;">
					<p style="display: inline-block; float: left; max-width: 150px;">{logo}</p>
					<h3 style="display: inline-block; float: right;">{company_name}</h3>
				</td>
			</tr>

			<!-- ORDER NUMBER BOX -->

			<tr>
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 10px auto 0; padding: 15px 15px 5px; font-size: 18px; font-weight: bold;">
						<tr>
							<td style="padding: 0 10px; line-height: 1.4em; text-align: center;">
								<div>
									<?php echo JText::_('VRORDERNUMBER'); ?>: {order_number}
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- ORDER KEY AND STATUS -->

			<tr>
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 10px auto 0; padding: 10px 25px; font-size: 14px; border-top: 2px solid #ddd;">
						<tr>
							<td style="line-height: 1.4em; text-align: left;">
								<div style="float:left; display:inline-block;">
									{order_key}
								</div>
								<div style="float:right; display:inline-block;">
									<span style="text-transform:uppercase; font-weight:bold; color:{order_status_color}">
										{order_status}
									</span>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- CHECK-IN DATE AND PEOPLE -->

			<tr>
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 0 auto 0; padding: 10px 25px; font-size: 14px;">
						<tr>
							<td style="line-height: 1.4em; text-align: left;">
								<div style="float:left; display:inline-block;">
									{order_date_time}
								</div>
								<div style="float:right; display:inline-block;">
									{order_people}
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- TOTAL COST AND PAYMENT GATEWAY -->

			<?php
			if ($reservation->deposit > 0)
			{
				?>
				<tr>
					<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 0 auto 0; padding: 10px 25px; font-size: 14px;">
							<tr>
								<td style="line-height: 1.4em; text-align: left;">
									<div style="float:left; display:inline-block;">
										{order_payment}
									</div>

									<div style="float:right; display:inline-block;">{order_deposit}</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			?>

			<!-- COUPON CODE -->

			<?php
			if (!empty($reservation->coupon))
			{
				?>
				<tr>
					<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 0 auto 0; padding: 10px 25px; font-size: 14px;">
							<tr>
								<td style="line-height: 1.4em; text-align: left;">
									<div style="float:left; display:inline-block;">
										<?php echo JText::_('VRORDERCOUPON'); ?>
									</div>
									<div style="float:right; display:inline-block;">
										{order_coupon_code}
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			?>

			<!-- SELECTED MENUS -->

			<?php
			if (count($reservation->menus))
			{
				?>
				<tr>
					<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 0; font-size: 14px; border-top: 2px solid #ddd;">
							<tr>
								<td style="padding: 0; line-height: 1.4em; text-align: left;">
									<div style="padding: 10px 25px 0;"><strong><?php echo JText::_('VRORDERTITLE3'); ?></strong></div>
									<div style="padding: 10px 25px;">
									<?php
									foreach ($reservation->menus as $menu)
									{
										?>
										<div style="padding: 2px 0;display: inline-block;width: 100%;">
											<div style="float: left;"><?php echo $menu->name; ?></div>
											<div style="float: right;">x<?php echo $menu->quantity; ?></div>
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
				<?php
			}
			?>

			<!-- CUSTOMER DETAILS -->

			<tr>
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 0; font-size: 14px; border-top: 2px solid #ddd;">
						<tr>
							<td style="padding: 0; line-height: 1.4em; text-align: left;">
								<div style="padding: 10px 25px 0;"><strong><?php echo JText::_('VRPERSONALDETAILS'); ?></strong></div>
								<div style="padding: 10px 25px;">
								<?php
								foreach ($reservation->fields as $label => $value)
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

			<!-- ORDER LINK -->

			<tr class="no-printable">
				<td style="padding: 0; text-align: center; border-top: 2px solid #ddd;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 0; font-size: 14px;">
						<tr>
							<td style="padding: 0; line-height: 1.4em; text-align: left;">
								<div style="padding: 10px 25px 0;"><strong><?php echo JText::_('VRORDERLINK'); ?></strong></div>
								<div style="padding: 10px 25px;">
									<a href="{order_link}" target="_blank" style="word-break: break-word;">{order_link}</a>
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- CONFIRMATION LINK -->
			<?php
			if ($reservation->status == 'PENDING')
			{
				?>
				<tr class="no-printable">
					<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 5px auto 0; padding: 0; font-size: 14px;">
							<tr>
								<td style="padding: 0; line-height: 1.4em; text-align: left;">
									<div style="padding: 0px 25px 0;"><strong><?php echo JText::_('VRCONFIRMATIONLINK'); ?></strong></div>
									<div style="padding: 10px 25px;">
										<a href="{confirmation_link}" target="_blank" style="word-break: break-word;">{confirmation_link}</a>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			?>

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
 * @var string|null  {logo}                 The logo image of your company. Null if not specified.
 * @var integer      {order_number}         The unique ID of the reservation.
 * @var string       {order_key}            The serial key of the reservation.
 * @var string       {order_date_time}      The checkin date and time of the reservation.
 * @var string       {order_people}         The party size of the reservaion.
 * @var string       {order_status}         The status of the order [CONFIRMED, PENDING, REMOVED or CANCELLED].
 * @var string       {order_status_color}   The color related to the selected status.
 * @var string|null  {order_payment}        The name of the payment processor selected (*), otherwise NULL.
 * @var string|null  {order_payment_notes}  The notes of the payment processor selected, otherwise NULL.
 * @var string       {order_deposit}        The formatted deposit to leave for the reservation.
 * @var string       {order_coupon_code}    The coupon code used for the order.
 * @var string       {order_room}           The room in which the reservation was placed.
 * @var string       {order_room_table}     The table of the room in which the reservation was placed.
 * @var string       {order_link}           The direct url to the page of the order.
 * @var string       {confirmation_link}	The direct url to confirm the order. Available only if the status of the order is PENDING.
 * @var string|null  {company_name}         The name of the company.
 * @var string|null  {user_name}            The name of the user account.
 * @var string|null  {user_username}        The username of the user account.
 * @var string|null  {user_email}           The e-mail address of the user account.
 */
