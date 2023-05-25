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

$input = JFactory::getApplication()->input;

$user = JFactory::getUser();

$config = VREFactory::getConfig();

$vik = VREApplication::getInstance();

// comment restrictions
$is_comment_required = $config->getBool('revcommentreq');
$min_comment_length  = $config->getUint('revminlength');
$max_comment_length  = $config->getUint('revmaxlength');

$itemid = $input->get('Itemid', null, 'uint');

?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=submit_review' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" id="vrrevformpost" name="vrrevformpost">
	
	<div class="vr-new-review-wrapper" id="vr-new-review" style="<?php echo ($this->submitReview ? '' : 'display: none;'); ?>">

		<?php
		if ($user->guest)
		{
			?>
			<div class="rv-new-top">
			
				<div class="rv-new-field">

					<div class="rv-new-field-label"><?php echo JText::_('VRREVIEWSFIELDUSERNAME'); ?>*</div>
					
					<div class="rv-new-field-value">
						<input type="text" name="review_user_name" size="32" id="vrreviewusername" class="required" maxlength="128" value="<?php echo $this->data->name; ?>" />
					</div>

				</div>

				<div class="rv-new-field">

					<div class="rv-new-field-label"><?php echo JText::_('VRREVIEWSFIELDUSERMAIL'); ?>*</div>
					
					<div class="rv-new-field-value">
						<input type="email" name="review_user_mail" size="32" id="vrreviewusermail" class="required" maxlength="128" value="<?php echo $this->data->email; ?>" />
					</div>

				</div>

			</div>
			<?php
		}
		?>

		<div class="rv-new-top">
			
			<div class="rv-new-field">

				<div class="rv-new-field-label"><?php echo JText::_('VRREVIEWSFIELDTITLE'); ?>*</div>
				
				<div class="rv-new-field-value">
					<input type="text" name="review_title" size="32" id="vrreviewtitle" class="required" maxlength="64" value="<?php echo $this->data->title; ?>" />
				</div>

			</div>

			<div class="rv-new-field">

				<div class="rv-new-field-label"><?php echo JText::_('VRREVIEWSFIELDRATING'); ?>*</div>
				
				<div class="rv-new-field-value">
					<?php
					for ($i = 1; $i <= 5; $i++)
					{
						?>
						<div class="vr-ratingstar-box rating-nostar" data-id="<?php echo $i; ?>"></div>
						<?php
					}
					?>

					<div id="vr-newrating-desc"><?php echo JText::_('VRREVIEWSTARDESC0'); ?></div>
					
					<input type="hidden" name="review_rating" id="vrreviewrating" class="required" value="" />
				</div>

			</div>

		</div>

		<div class="rv-new-middle">

			<div class="rv-new-field">

				<div class="rv-new-field-label"><?php echo JText::_('VRREVIEWSFIELDCOMMENT') . ($is_comment_required ? '*' : ''); ?></div>
				
				<div class="rv-new-field-value">
					<textarea
						name="review_comment"
						id="vrreviewcomment"
						class="<?php echo ($is_comment_required ? 'required' : ''); ?>"
						maxlength="<?php echo $max_comment_length; ?>"
					><?php echo $this->data->comment; ?></textarea>

					<div class="rv-new-charsleft">
						<span><?php echo JText::_('VRREVIEWSCHARSLEFT'); ?>&nbsp;</span>
						<span id="vrcommentchars"><?php echo $max_comment_length; ?></span>
					</div>

					<?php
					if ($min_comment_length > 0)
					{
						?>
						<div class="rv-new-minchars">
							<span><?php echo JText::_('VRREVIEWSMINCHARS'); ?>&nbsp;</span>
							<span id="vrcommentminchars"><?php echo $min_comment_length; ?></span>
						</div>
						<?php
					}
					?>
				</div>

			</div>

		</div>

		<?php
		// check if global captcha is configured
		if ($vik->isGlobalCaptcha())
		{
			// display reCaptcha plugin
			?>
			<div class="rv-new-footer">

				<div class="rv-new-field">

					<div class="rv-new-field-label">&nbsp;</div>

					<div class="rv-new-field-value">
						<?php echo $vik->reCaptcha(); ?>
					</div>

				</div>

			</div>
			<?php
		}
		?>

		<div class="rv-new-submit">
			<button type="submit" class="vr-review-btn" onClick="return validateReviewOnSubmit();">
				<?php echo JText::_('VRREVIEWSUBMITBUTTON'); ?>
			</button>
		</div>

	</div>

	<?php
	foreach ($this->request as $k => $v)
	{ 
		if (!empty($v))
		{
			?>
			<input type="hidden" name="request[<?php echo $k; ?>]" value="<?php echo $v; ?>" />
			<?php
		}
	}
	?>

	<input type="hidden" name="id_tk_prod" value="<?php echo $item->id; ?>" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="task" value="submit_review" />

	<?php echo JHtml::_('form.token'); ?>

</form>

<?php
for ($i = 0; $i <= 5; $i++)
{
	JText::script('VRREVIEWSTARDESC' . $i);
}
?>

<script>

	var TO_RATE = true;

	var MAX_COMMENT_LENGTH      = <?php echo $max_comment_length; ?>;
	var MIN_COMMENT_LENGTH      = <?php echo $min_comment_length; ?>;
	var REVIEW_COMMENT_REQUIRED = <?php echo $is_comment_required ? 1 : 0; ?>;

	var STAR_DESC_MAP = [
		Joomla.JText._('VRREVIEWSTARDESC1'),
		Joomla.JText._('VRREVIEWSTARDESC2'),
		Joomla.JText._('VRREVIEWSTARDESC3'),
		Joomla.JText._('VRREVIEWSTARDESC4'),
		Joomla.JText._('VRREVIEWSTARDESC5'),
	];

	jQuery(document).ready(function() {

		jQuery('.vr-ratingstar-box').on('click', function() {
			var id = jQuery(this).data('id');
			
			jQuery('.vr-ratingstar-box').removeClass('rating-nostar rating-hoverstar rating-yesstar');
			
			if (TO_RATE) {
				jQuery(this).addClass('rating-yesstar');

				jQuery(this).siblings('.vr-ratingstar-box').each(function() {
					if (jQuery(this).data('id') < id) {
						jQuery(this).addClass('rating-yesstar');
					} else {
						jQuery(this).addClass('rating-nostar');
					}
				});
				
				jQuery('#vrreviewrating').val(id);
				jQuery('#vr-newrating-desc').text(Joomla.JText._('VRREVIEWSTARDESC0'));
			} else {
				jQuery(this).addClass('rating-hoverstar');
				
				jQuery(this).siblings('.vr-ratingstar-box').each(function() {
					if (jQuery(this).data('id') < id) {
						jQuery(this).addClass('rating-hoverstar');
					} else {
						jQuery(this).addClass('rating-nostar');
					}
				});
				
				jQuery('#vrreviewrating').val('');
				jQuery('#vr-newrating-desc').text(STAR_DESC_MAP[id - 1]);
			}
			
			TO_RATE = !TO_RATE
		});
		
		jQuery('.vr-ratingstar-box').hover(function() {
			var id = jQuery(this).data('id');
			
			if (TO_RATE) {
				jQuery('.vr-ratingstar-box').removeClass('rating-nostar rating-hoverstar rating-yesstar');
				
				jQuery(this).addClass('rating-hoverstar');

				jQuery(this).siblings('.vr-ratingstar-box').each(function() {
					if (jQuery(this).data('id') < id) {
						jQuery(this).addClass('rating-hoverstar');
					} else {
						jQuery(this).addClass('rating-nostar');
					}
				});

				jQuery('#vr-newrating-desc').text(STAR_DESC_MAP[id - 1]);
			}
			
		}, function(){
			
		});

		jQuery('#vrreviewcomment').on('keyup', function(e) {
			jQuery('#vrcommentchars').text((MAX_COMMENT_LENGTH - jQuery(this).val().length));       
		});

		<?php
		if ($this->data->rating)
		{
			// auto-set stored rating
			?>
			TO_RATE = true;
			jQuery('.vr-ratingstar-box[data-id="<?php echo $this->data->rating; ?>"]').trigger('click');
			<?php
		}
		?>
	});

	function vrDisplayPostReview(btn) {
		jQuery(btn).remove();
		jQuery('#vr-new-review').fadeIn();
	}

	var vrLeaveReviewValidator = new VikFormValidator('#vrrevformpost', 'vrinvalid');

	/**
	 * Overwrite getLabel method to properly access the
	 * label by using our custom layout.
	 *
	 * @param 	mixed 	input  The input element.
	 *
	 * @param 	mixed 	The label of the input.
	 */
	vrLeaveReviewValidator.getLabel = function(input) {
		return jQuery(input).parent().prev();
	}

	// validate comment length
	vrLeaveReviewValidator.addCallback(function() {
		// get comment field
		var commentInput = jQuery('#vrreviewcomment');
		// get comment length
		var length = commentInput.val().length;

		if (length && (length < MIN_COMMENT_LENGTH || length > MAX_COMMENT_LENGTH)) {
			vrLeaveReviewValidator.setInvalid(commentInput);
			return false;
		}

		vrLeaveReviewValidator.unsetInvalid(commentInput);
		return true;
	});

	// validate selected rating
	vrLeaveReviewValidator.addCallback(function() {
		// get rating field
		var ratingInput = jQuery('#vrreviewrating');
		// get selected rating
		var rating = parseInt(ratingInput.val());

		if (isNaN(rating) || rating < 1 || rating > 5) {
			vrLeaveReviewValidator.setInvalid(ratingInput);
			return false;
		}

		vrLeaveReviewValidator.unsetInvalid(ratingInput);
		return true;
	});

	function validateReviewOnSubmit() {
		// use the validator object
		return vrLeaveReviewValidator.validate();
	}

</script>
