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
 * @var  mixed    $review   Either an array or an object containing the review details.
 */
extract($displayData);

$review = (array) $review;
?>

<div class="review-block">

	<div class="rv-top">

		<div class="rv-head-up">

			<div class="rv-rating">
				<?php
				/**
				 * Displays the rating stars.
				 * It is possible to change the $image argument to false
				 * to use FontAwesome 4 instead of the images.
				 * For FontAwesome 5, $image have to be set to "5.0".
				 */
				echo JHtml::_('vikrestaurants.rating', $review['rating'], $image = true);
				?>
			</div>

			<div class="rv-title"><?php echo $review['title']; ?></div>

			<?php
			if ($review['langtag'])
			{
				list($regional, $country) = explode('-', $review['langtag']);
				?>
				<div class="rv-lang">
					<img src="<?php echo VREASSETS_URI . 'css/flags/' . strtolower($country) . '.png'; ?>" />
				</div>
				<?php
			}
			?>

		</div>

		<div class="rv-head-down">
			<?php
			// get relative date time (e.g. 2 days ago)
			$dt = strtolower(VikRestaurants::formatTimestamp('', $review['timestamp']));

			if (!$dt)
			{
				// obtain formatted date (e.g. on 9 Dec 2020)
				$dt = JText::sprintf(
					'VRDFWHEN',
					JHtml::_('date', $review['timestamp'], JText::_('DATE_FORMAT_LC3'), date_default_timezone_get())
				);
			}

			echo JText::sprintf(
				!empty($review['comment']) ? 'VRREVIEWSUBHEAD' : 'VRREVIEWSUBHEAD2', 
				'<strong>' . $review['name'] . '</strong>', 
				$dt
			);

			if ($review['verified'])
			{
				?>
				<div class="rv-verified"><?php echo JText::_('VRREVIEWVERIFIED'); ?></div>
				<?php
			}
			?>
		</div>

	</div>

	<?php
	if (!empty($review['comment']))
	{
		?>
		<div class="rv-middle">
			<?php echo nl2br($review['comment']); ?>
		</div>
		<?php
	}
	?>

</div>
