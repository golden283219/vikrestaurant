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

$config = VREFactory::getConfig();

$itemid = $input->get('Itemid', null, 'uint');

?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=revslist' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" name="vrrevsform" id="vrrevsform">

	<div class="vr-reviews-quicklist">

		<div class="vr-reviews-toolbar">

			<div class="rv-toolbar-field left">

				<div class="rv-toolbar-field-label"><?php echo JText::_('VRREVIEWSSORTBY'); ?></div>
				
				<div class="vre-select-wrapper rv-toolbar-field-value">
					<select name="sortby" class="vre-select" id="vrsortby">
						
						<option value="1" <?php echo ($this->request->sortby == '1' ? 'selected="selected"' : ''); ?>>
							<?php echo JText::_('VRREVIEWSORTBY1'); ?>
						</option>
						
						<option value="2" <?php echo ($this->request->sortby == '2' ? 'selected="selected"' : ''); ?>>
							<?php echo JText::_('VRREVIEWSORTBY2'); ?>
						</option>
						
						<option value="3" <?php echo ($this->request->sortby == '3' ? 'selected="selected"' : ''); ?>>
							<?php echo JText::_('VRREVIEWSORTBY3'); ?>
						</option>
					
					</select>
				</div>

			</div>

			<div class="rv-toolbar-field right">
				
				<div class="rv-toolbar-field-label"><?php echo JText::_('VRREVIEWSFILTERBY'); ?></div>
				
				<div class="vre-select-wrapper rv-toolbar-field-value">
					<select name="filterstar" class="vre-select" id="vrfilterstar">
						
						<option value="0" <?php echo ($this->request->filterstar == '0' ? 'selected="selected"' : ''); ?>>
							<?php echo JText::_('VRREVIEWFILTERSTAR0'); ?>
						</option>
						
						<?php
						for( $i = 5; $i > 0; $i--)
						{
							?>
							<option value="<?php echo $i; ?>" <?php echo ($this->request->filterstar == $i ? 'selected="selected"' : ''); ?>>
								<?php echo JText::_('VRREVIEWFILTERSTAR' . $i); ?>
							</option>
							<?php
						}
						?>

					</select>
				</div>

				<?php
				if ($config->getBool('revlangfilter'))
				{
					$all_langs = VikRestaurants::getKnownLanguages();
					?>
					<div class="vre-select-wrapper rv-toolbar-field-value">
						<select name="filterlang" class="vre-select" id="vrfilterlang">
							
							<option value="" <?php echo ($this->request->filterlang == '' ? 'selected="selected"' : ''); ?>>
								<?php echo JText::_('VRREVIEWSLANGSALL'); ?>
							</option>
							
							<?php
							foreach ($all_langs as $langtag)
							{
								?>
								<option value="<?php echo $langtag; ?>" <?php echo ($this->request->filterlang == $langtag ? 'selected="selected"' : ''); ?>>
									<?php echo $langtag; ?>
								</option>
								<?php
							}
							?>

						</select>
					</div>
					<?php
				}
				?>
			</div>

		</div>

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
	
	<div class="vr-list-pagination">
		<?php echo $this->reviewsHandler->getNavigationHTML($this->request); ?>
	</div>
	
	<input type="hidden" name="id_tk_prod" value="<?php echo $this->request->id_tk_prod; ?>" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="revslist" />

</form>

<script>

	jQuery(document).ready(function() {

		jQuery('#vrrevsform select').on('change', function(){
			jQuery('#vrrevsform').submit();
		});

	});

</script>
