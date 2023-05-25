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

$item = $this->item;

$user = JFactory::getUser();

// leave review restrictions
$review_leave_mode = VREFactory::getConfig()->getUint('revleavemode');

?>

<div class="rv-reviews-quickstats">

	<div class="rv-top">

		<div class="rv-average-stars">
			<?php
			/**
			 * Displays the rating stars.
			 * It is possible to change the $image argument to false
			 * to use FontAwesome 4 instead of the images.
			 * For FontAwesome 5, $image have to be set to "5.0".
			 */
			echo JHtml::_('vikrestaurants.rating', $this->reviewsStats->halfRating, $image = true);
			?>
		</div>

		<div class="rv-count-reviews">
			<?php echo JText::sprintf('VRREVIEWSCOUNT', $this->reviewsStats->count); ?>
		</div>

		<?php
		if ($this->canLeaveReview)
		{
			?>
			<div class="rv-submit-review">
				<button type="button" class="vr-review-btn" style="<?php echo ($this->submitReview ? 'display: none;' : ''); ?>" onClick="vrDisplayPostReview(this);">
					<?php echo JText::_('VRREVIEWLEAVEBUTTON'); ?>
				</button>
			</div>
			<?php
		}
		else
		{
			$str = "";

			if ($review_leave_mode == 1 && $user->guest)
			{
				$str = JText::_('VRREVIEWLEAVENOTICE1');
			}
			else if ($review_leave_mode == 2 && !VikRestaurants::isVerifiedTakeAwayReview($item->id))
			{
				$str = JText::_('VRREVIEWLEAVENOTICE2'); 
			}

			if (!empty($str))
			{
				?>
				<div class="rv-submit-review info-message"><?php echo $str; ?></div>
				<?php
			}
		}
		?>

	</div>

	<div class="rv-average-ratings">
		<?php
		echo JText::sprintf(
			'VRREVIEWSAVG', 
			floatval(number_format($this->reviewsStats->rating, 2))
		);
		?>
	</div>

</div>
