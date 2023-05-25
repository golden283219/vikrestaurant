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

JHtml::_('formbehavior.chosen');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

// ORDERING LINKS

foreach (array('r.id', 'r.timestamp', 'r.rating') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('reviews', JText::_('VRMANAGEREVIEW1'), 'r.id', $ordering['r.id'], 1, $filters, 'vrheadcolactive'.(($ordering['r.id'] == 2) ? 1 : 2) ),
	OrderingManager::getLinkColumnOrder('reviews', JText::_('VRMANAGEREVIEW4'), 'r.timestamp', $ordering['r.timestamp'], 1, $filters, 'vrheadcolactive'.(($ordering['r.timestamp'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('reviews', JText::_('VRMANAGEREVIEW5'), 'r.rating', $ordering['r.rating'], 1, $filters, 'vrheadcolactive'.(($ordering['r.rating'] == 2) ? 1 : 2)),
);

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');

$has_filters = $this->hasFilters();

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<div class="btn-toolbar vr-btn-toolbar" style="height:32px;">
		<div class="btn-group pull-left input-append">
			<input type="text" name="keysearch" id="vrkeysearch" size="32" 
				value="<?php echo $filters['keysearch']; ?>" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />

			<button type="submit" class="btn">
				<i class="icon-search"></i>
			</button>
		</div>

		<div class="btn-group pull-left hidden-phone">
			<button type="button" class="btn <?php echo ($has_filters ? 'btn-primary' : ''); ?>" onclick="vrToggleSearchToolsButton(this);">
				<?php echo JText::_('JSEARCH_TOOLS'); ?>&nbsp;<i class="fas fa-caret-<?php echo ($has_filters ? 'up' : 'down'); ?>" id="vr-tools-caret"></i>
			</button>
		</div>
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>

		<div class="btn-group pull-right hidden-phone" style="display:none;">
			<a href="index.php?option=com_vikrestaurants&amp;view=roomclosures" class="btn" id="closures-link">
				<i class="icon-calendar"></i>
				<?php echo JText::_('VRMANAGECLOSURES'); ?>
			</a>
		</div>
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<?php
		$options = array(
			JHtml::_('select.option', -1, 'JOPTION_SELECT_PUBLISHED'),
			JHtml::_('select.option', 1, 'JPUBLISHED'),
			JHtml::_('select.option', 0, 'JUNPUBLISHED'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="status" id="vr-status-sel" class="<?php echo ($filters['status'] != -1 ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['status'], true); ?>
			</select>
		</div>

		<?php
		$options = array(
			JHtml::_('select.option', -1, 'VRE_FILTER_SELECT_TYPE'),
			JHtml::_('select.option', 1, 'VRMANAGEREVIEW12'),
			JHtml::_('select.option', 0, 'VRMANAGEREVIEW13'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="verified" id="vr-verified-sel" class="<?php echo ($filters['verified'] != -1 ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['verified'], true); ?>
			</select>
		</div>

		<?php
		$options = array(
			JHtml::_('select.option', 0, JText::_('VRE_FILTER_SELECT_RATING')),
		);

		for ($i = 5; $i >= 1; $i--)
		{
			$options[] = JHtml::_('select.option', $i, $i . ' ' . JText::_($i > 1 ? 'VRSTARS' : 'VRSTAR'));
		}

		?>
		<div class="btn-group pull-left">
			<select name="stars" id="vr-stars-sel" class="<?php echo ($filters['stars'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['stars']); ?>
			</select>
		</div>

	</div>

<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOREVIEW'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGEREVIEW2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGEREVIEW3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEREVIEW6'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEREVIEW7'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEREVIEW12'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEREVIEW9'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEREVIEW8'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
			$country = explode('-', $row['langtag']);
			$country = $country[1];
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>
				
				<td style="text-align: left;" class="hidden-phone"><?php echo $row['id']; ?></td>
				
				<td style="text-align: left;">
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=review.edit&amp;cid[]=<?php echo $row['id']; ?>">
							<?php echo $row['title']; ?>
						</a>
						<?php
					}
					else
					{
						echo $row['title'];
					}
					?>

					<div class="mobile-only">
						<?php echo $row['name']; ?>
					</div>

					<div class="mobile-only">
						<?php echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC2'), $row['timestamp']); ?>
					</div>
				</td>
				
				<td style="text-align: left;" class="hidden-phone"><?php echo $row['name']; ?></td>
				
				<td style="text-align: left;" class="hidden-phone">
					<?php echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC2'), $row['timestamp']); ?>
				</td>
				
				<td style="text-align: center;">
					<?php
					for ($j = 1; $j <= $row['rating']; $j++)
					{
						?>
						<i class="fas fa-star big review-star"></i>
						<?php
					}
					?>

					<div class="mobile-only">
						<?php echo $row['takeaway_product_name']; ?>
					</div>
				</td>
				
				<td style="text-align: center;" class="hidden-phone"><?php echo $row['takeaway_product_name']; ?></td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php
					if($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=review.<?php echo ($row['published'] ? 'un' : ''); ?>publish&amp;cid[]=<?php echo $row['id']; ?>">
							<i class="fas fa-<?php echo $row['published'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-<?php echo $row['published'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=review.verified&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['verified'] == 1 ? 0 : 1; ?>" />
							<i class="fas fa-<?php echo $row['verified'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-<?php echo $row['verified'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<a href="javascript: void(0);" onclick="SELECTED_REVIEW = <?php echo $row['id']; ?>;vrOpenJModal('review', null, true);">
							<i class="fas fa-comment-dots big"></i>
						</a>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<img src="<?php echo VREASSETS_URI . 'css/flags/' . strtolower($country) . '.png'; ?>" />
				</td>

			</tr>
			<?php
			$kk = 1 - $kk;
		}		
		?>
	</table>
	<?php
}
?>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="reviews" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
// display products selection modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-review',
	array(
		'title'       => JText::_('VRE_REVIEW_CARD_TITLE'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'         => '',
	)
);
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-sel').updateChosen(-1);
		jQuery('#vr-verified-sel').updateChosen(-1);
		jQuery('#vr-stars-sel').updateChosen(0);
		
		document.adminForm.submit();
	}

	var SELECTED_REVIEW = null;

	function vrOpenJModal(id, url, jqmodal) {
		url = 'index.php?option=com_vikrestaurants&view=managereview&tmpl=component&layout=modal&cid[]=' + SELECTED_REVIEW;

		<?php echo $vik->bootOpenModalJS(); ?>
	}

</script>
