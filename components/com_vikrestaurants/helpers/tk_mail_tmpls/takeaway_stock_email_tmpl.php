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
 * VikRestaurants - Take-Away Stock E-Mail Template
 *
 * @var object  $items  It is possible to use this variable to 
 * 						iterate all the items with low stocks.
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
			
			<!-- TOP BOX [logo and stocks content] -->

			<tr>
				<td style="padding: 20px 25px 0; text-align: center;">
					<div style="display: inline-block; width: 200px; margin-bottom: 20px;">{logo}</div>
					<div style="margin: 10px auto; line-height: 20px; font-size: 14px;">{stocks_content}</div>
				</td>
			</tr>

			<!-- PRODUCTS LIST -->

			<tr>
				<td style="padding: 0; text-align: left;">
					<?php
					foreach ($items as $menu)
					{
						?>
						<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 10px; font-size: 14px; border-top: 2px solid #ddd;">
							<tr>
								<td style="font-size: 16px; font-weight: bold; padding-bottom: 10px;">
									<?php echo $menu->title; ?>
								</td>
							</tr>

							<?php
							foreach ($menu->list as $item)
							{
								?>
								<tr>
									<td style="padding: 3px 20px 3px 20px;">
										<div style="width: 100%; display: inline-block;">
											<span style="float: left;">
												<?php echo $item->name; ?>
											</span>

											<span style="float: right; font-weight: bold; font-size: smaller; text-transform: uppercase; color: #900;">
												<?php echo JText::sprintf('VRTKADMINLOWSTOCKREMAINING', $item->remaining); ?>
											</span>
										</div>
									</td>
								</tr>
								<?php
							}
							?>
						</table>
						<?php
					}
					?>
				</td>
			</tr>

			<!-- STOCKS HELP -->

			<tr>
				<td style="padding: 20px 25px; text-align: center; border-top: 2px solid #ddd;">
					<div style="margin: 10px auto; line-height: 20px; font-size: 13px;">{stocks_help}</div>
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
 * @var string|null  {logo}            The logo image of your company.
 * @var string|null  {company_name}    The name of the company.
 * @var string       {stocks_content}  The content specified in the language file at VRTKADMINLOWSTOCKCONTENT.
 * @var string       {stocks_help}     The help text specified in the language file at VRTKADMINLOWSTOCKHELP.
 */
