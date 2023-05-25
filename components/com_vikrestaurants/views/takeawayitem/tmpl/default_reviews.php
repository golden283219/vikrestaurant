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
 * Template file used to display a short list of reviews
 * left for the selected product.
 *
 * @since 1.8
 */

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<div class="vr-reviews-quickwrapper">

	<h3><?php echo JText::_('VRREVIEWSTITLE'); ?></h3>

	<?php
	if ($this->reviewsStats !== null)
	{
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
				// checks whether the current user is allowed to leave a review for this product
				if (VikRestaurants::canLeaveTakeAwayReview($this->item->id))
				{
					?>
					<div class="rv-submit-review">
						<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=revslist&id_tk_prod=' . $this->item->id . '&submit_rev=1' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="vr-review-btn">
							<?php echo JText::_('VRREVIEWLEAVEBUTTON'); ?>
						</a>
					</div>
					<?php
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

			<?php
			if ($this->reviewsStats->count > 0)
			{
				?>
				<div class="rv-see-all">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=revslist&id_tk_prod=' . $this->item->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="vr-review-btn">
						<?php echo JText::sprintf('VRREVIEWSEEALLBUTTON', $this->reviewsStats->count); ?>
					</a>
				</div>
				<?php
			}
			?>

		</div>
		<?php
	}
	?>

	<div class="vr-reviews-quicklist">

		<?php
		if (!count($this->reviews))
		{
			?>
			<div class="no-review"><?php echo JText::_('VRREVIEWSNOLEFT'); ?></div>
			<?php
		}
		else
		{
			// load layout used to display each review block
			$layout = new JLayoutFile('blocks.review');

			/**
			 * The preview of the ratings displays a short list
			 * of the most rated reviews. In case of same rating,
			 * the most recent will be shown first.
			 */
			foreach ($this->reviews as $review)
			{
				/**
				 * The review block is displayed from the layout below:
				 * /components/com_vikrestaurants/layouts/blocks/review.php
				 *
				 * @since 1.8
				 */
				echo $layout->render(array('review' => $review));
			}
		}
		?>

	</div>

</div>
