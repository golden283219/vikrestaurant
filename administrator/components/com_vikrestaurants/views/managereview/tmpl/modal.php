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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');

$review  = $this->review;
$product = $this->product;

?>

<div class="vre-review-card">

	<!-- product details -->
	
	<div class="product-slide-left">

		<?php
		if (!empty($product->img_path) && is_file(VREMEDIA . DIRECTORY_SEPARATOR . $product->img_path))
		{
			?><img src="<?php echo VREMEDIA_URI . $product->img_path; ?>" /><?php
		}
		?>

		<div class="product-name">
			<h3 style="word-wrap: break-word;"><?php echo $product->name; ?></h3>
		</div>

		<?php
		if ($product->description)
		{
			?>
			<div class="product-desc">
				<small><?php echo $product->description; ?></small>
			</div>
			<?php
		}
		?>

		<!-- spacer to avoid overlapping menu title with description -->
		<div style="margin-bottom: 20px;">&nbsp;</div>

		<div class="product-menu"><?php echo $product->menuTitle; ?></div>

	</div>

	<!-- review details -->

	<div class="review-slide-right">
		
		<!-- review top: review title, user details and rating -->

		<div class="review-card-top">
			
			<?php
			if (!empty($review->customerImage) && is_file(VRECUSTOMERS_AVATAR . DIRECTORY_SEPARATOR . $review->customerImage))
			{
				$avatar = VRECUSTOMERS_AVATAR_URI . $review->customerImage;
			}
			else
			{
				$avatar = VREASSETS_URI . 'css/images/default-profile.png';
			}
			?>
			<div class="review-user-image">
				<img src="<?php echo $avatar; ?>" class="vr-customer-image" style="margin-right:20px;" />
			</div>

			<div class="review-details">

				<h3 class="review-title" style="margin-top: 0;word-wrap: break-word;"><?php echo $review->title; ?></h3>

				<div class="user-name">
					<span style="margin-right:2px;"><?php echo $review->name; ?></span>

					<?php $title = JText::_($review->verified ? 'VRMANAGEREVIEW12' : 'VRMANAGEREVIEW13'); ?>
					<i class="fas fa-<?php echo $review->verified ? 'check-circle ok' : 'dot-circle no'; ?> medium hasTooltip" title="<?php echo $title; ?>"></i>
				</div>

				<div class="user-email"><?php echo $review->email; ?></div>
				
			</div>

			<div class="review-vote">
				<div class="review-rating">
					<?php
					for ($i = 1; $i <= $review->rating; $i++)
					{
						?><i class="fas fa-star review-star"></i><?php
					}
					for ($i = $review->rating; $i < 5; $i++)
					{
						?><i class="far fa-star review-star"></i><?php
					}
					?>
				</div>

				<div class="review-date">
					<?php echo JHtml::_('date', JDate::getInstance($review->timestamp), JText::_('DATE_FORMAT_LC2'), date_default_timezone_get()); ?>
				</div>
			</div>

		</div>

		<?php
		if ($review->comment)
		{
			?>
			<!-- review comment -->

			<div class="review-card-content">
				
				<?php echo $review->comment; ?>

			</div>
			<?php
		}
		?>

		<!-- review bottom: IP and status -->

		<div class="review-card-bottom">
			
			<div class="user-ip">
				<?php list($region, $country) = explode('-', $review->langtag); ?>
				<img src="<?php echo VREASSETS_URI . 'css/flags/' . strtolower($country) . '.png'; ?>" title="<?php echo $review->langtag; ?>" class="hasTooltip" />
				<span style="margin-left:5px;vertical-align:middle;"><?php echo $review->ipaddr; ?></span>
			</div>

			<div class="review-status">
				<i class="fas fa-<?php echo $review->published ? 'check-circle ok' : 'dot-circle no'; ?> big-2x"></i>
				<span style="margin-left:5px;vertical-align:middle;"><?php echo JText::_($review->published ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?></span>
			</div>

		</div>

	</div>

</div>
