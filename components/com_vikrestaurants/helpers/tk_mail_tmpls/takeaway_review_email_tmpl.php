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
 * VikRestaurants - Take-Away Review E-Mail Template
 *
 * @var object  $review  It is possible to use this variable to 
 * 						 access the details of the review.
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
			
			<!-- TOP BOX [logo and review content] -->

			<tr>
				<td style="padding: 20px 25px 0; text-align: center;">
					<div style="display: inline-block; width: 200px; margin-bottom: 20px;">{logo}</div>
					<div style="margin: 10px auto; line-height: 20px; font-size: 14px;">{review_content}</div>
				</td>
			</tr>

			<!-- PRODUCT DETAILS -->

			<tr>
				<td style="padding: 0; text-align: center;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 10px; font-size: 14px; border-top: 2px solid #ddd;">
						<tr>
							<?php
							if ($review->productImage)
							{
								?>
								<td width="30%" style="vertical-align: top;padding-right: 10px;">
									<img src="{review_product_image}" alt="{review_product_menu} - {review_product_name}" style="max-width:100%;" />
								</td>
								<?php
							}
							?>

							<td style="vertical-align: top;text-align: left;" width="<?php echo $review->productImage ? '70%' : 'auto'; ?>">
								<div><strong>{review_product_menu} - {review_product_name}</strong></div>
								<div><small>{review_product_desc}</small></div>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- REVIEW DETAILS -->

			<tr>
				<td style="padding: 15px 10px;">
					<table width="100%" style="border-collapse: separate; border-spacing: 0; padding: 10px; font-size: 14px; border: 1px solid #ddd;">
						<tr>
							<td>
								<div style="display: inline-block; width: 100%;">
									<span style="float: left;">{review_rating}</span>

									<span style="float: right; line-height: 26px; font-size: smaller;">{review_verified}</span>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: left; padding-top: 10px;">
								<div style="font-weight: bold; font-size: 16px; margin-bottom: 10px;">{review_title}</div>
								<?php
								if ($review->comment)
								{
									?>
									<div>{review_comment}</div>
									<?php
								}
								else
								{
									?>
									<small><em><?php echo JText::_('VRREVIEWNOCOMMENT'); ?></em></small>
									<?php
								}
								?>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- CONFIRMATION LINK -->

			<?php
			if (!$review->published)
			{
				?>
				<tr class="no-printable">
					<td style="padding: 0; text-align: center;">
						<table width="100%" style="border-collapse: separate; border-spacing: 0; margin: 5px auto 0; padding: 0; font-size: 14px;">
							<tr>
								<td style="padding: 0; line-height: 1.4em; text-align: left;">
									<div style="padding: 0px 10px 0;"><strong><?php echo JText::_('VRCONFIRMATIONLINK'); ?></strong></div>
									<div style="padding: 10px;">
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
 * @var string|null  {logo}                  The logo image of your company.
 * @var string|null  {company_name}          The name of the company.
 * @var string       {review_content}        The content specified in the language file at VRREVIEWCONTENT.
 * @var string       {review_product_menu}   The menu name of the reviewed product.
 * @var string       {review_product_name}   The name of the reviewed product.
 * @var string|null  {review_product_desc}   The description of the reviewed product.
 * @var string|null  {review_product_image}  The image URI of the reviewed product.
 * @var string       {review_title}          The title of the review left.
 * @var string|null  {review_comment}        The comment of the review left.
 * @var string       {review_rating}         The stars (images) related to the rating left.
 * @var string|null  {review_verified}       The "VERIFIED" text in case the review was left by a trusted customer.
 * @var string       {confirmation_link}	 The direct url to the details page of the order.
 * @var string|null  {user_name}             The name of the user account.
 * @var string|null  {user_username}         The username of the user account.
 * @var string|null  {user_email}            The e-mail address of the user account.
 */
