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

JHtml::_('vrehtml.sitescripts.animate');

$item = $this->item;

$input = JFactory::getApplication()->input;

$user = JFactory::getUser();

// leave review restrictions
$can_leave_review = VikRestaurants::canLeaveTakeAwayReview($item->id);
$submit_rev       = $input->get('submit_rev', 0, 'uint');

// get list of reviews
$reviews = $this->reviewsHandler->getReviews($item->id);

// get reviews statistics
$reviewsStats = $this->reviewsHandler->getAverageRatio($item->id);

// get reviews count
$ratingsCount = $this->reviewsHandler->getRatingsCount($item->id);

$itemid = $input->get('Itemid', null, 'uint');

?>

<!-- display "breadcrumb" -->

<div class="vrtk-itemdet-category">

	<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeaway' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
		<?php echo JText::_('VRTAKEAWAYALLMENUS'); ?>
	</a>

	<span class="arrow-separator">&raquo;</span>

	<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeaway&takeaway_menu=' . $item->menu->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
		<?php echo $item->menu->title; ?>
	</a>

	<span class="arrow-separator">&raquo;</span>

	<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeawayitem&takeaway_item=' . $item->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
		<?php echo $item->name; ?>
	</a>

</div>

<!-- reviews list -->

<?php
if ($reviews !== false)
{ 
	?>
	<div class="vr-reviews-quickwrapper">

		<h3><?php echo JText::_('VRREVIEWSTITLE'); ?></h3>

		<?php
		if ($reviewsStats !== null)
		{
			// prepare sub-template data
			$this->reviewsStats   = $reviewsStats;
			$this->canLeaveReview = $can_leave_review;
			$this->submitReview   = $submit_rev;

			// display head information by using a sub-template
			echo $this->loadTemplate('head');
		}

		if ($can_leave_review)
		{
			// prepare sub-template data
			$this->submitReview = $submit_rev;

			// display form to leave a review by using a sub-template
			echo $this->loadTemplate('form');
		}
		
		// display overall ratings
		if ($ratingsCount !== null && $ratingsCount->count > 0)
		{
			?>
			<div class="vr-reviews-counts">
				<?php
				for ($i = 5; $i > 0; $i--)
				{ 
					$ratio = round($ratingsCount->ratings[$i] / $ratingsCount->count * 100);
					?>
					<div class="rv-rating-count-box">

						<div class="rv-rating-title"><?php echo JText::_('VRREVIEWSTAR' . $i); ?></div>

						<div class="rv-rating-progress">
							<div class="rv-rating-bar" data-width="<?php echo $ratio; ?>%"></div>
						</div>

						<div class="rv-rating-ratio"><?php echo $ratio; ?>%</div>

					</div>
					<?php
				}
				?>
			</div>
			<?php
		}

		// prepare sub-template data
		$this->reviews = $reviews;

		// display reviews list by using a sub-template
		echo $this->loadTemplate('reviews');
		?>

	</div>

	<?php
}
?>

<script type="text/javascript">

	var RV_BOUNDS = {};
	var W_BOUNDS  = {};

	var TIMER_START = null;

	jQuery(document).ready(function() {

		RV_BOUNDS.y      = jQuery('.vr-reviews-counts').offset().top;
		RV_BOUNDS.height = jQuery('.vr-reviews-counts').height();

		TIMER_START = new Date().getTime();

		jQuery(window).on('scroll', __debounce(
			reviewsScrollControl, 250
		));

		// fire scroll
		jQuery(window).trigger('scroll');

	});

	function reviewsScrollControl() {
		W_BOUNDS.y      = jQuery(window).scrollTop();
		W_BOUNDS.height = jQuery(window).height();

		if (W_BOUNDS.y <= RV_BOUNDS.y && RV_BOUNDS.y + RV_BOUNDS.height <= W_BOUNDS.y + W_BOUNDS.height) {
			jQuery(window).off('scroll');

			var delay = 1250;
			var diff  = new Date().getTime() - TIMER_START;

			setTimeout(function() {
				jQuery('.vr-reviews-counts .rv-rating-progress .rv-rating-bar').each(function() {
					jQuery(this).css('width', jQuery(this).data('width'));
				});
			}, Math.max(0, delay - diff));
		}
	}

</script>
