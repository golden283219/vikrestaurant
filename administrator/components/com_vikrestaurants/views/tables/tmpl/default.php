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

// ordering links
foreach (array('t.id', 't.name', 't.published', 'r.ordering') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('tables', JText::_('VRMANAGEMENUSPRODUCT1'), 't.id', $ordering['t.id'], 1, $filters, 'vrheadcolactive'.(($ordering['t.id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tables', JText::_('VRMANAGETABLE1'), 't.name', $ordering['t.name'], 1, $filters, 'vrheadcolactive'.(($ordering['t.name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tables', JText::_('VRMANAGEROOM3'), 't.published', $ordering['t.published'], 1, $filters, 'vrheadcolactive'.(($ordering['t.published'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tables', JText::_('VRMANAGETABLE4'), 'r.ordering', $ordering['r.ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['r.ordering'] == 2) ? 1 : 2)),
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
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<div class="btn-group pull-left">
			<?php
			$options = array(
				JHtml::_('select.option', 0, JText::_('VRMAPSCHOOSEROOM')),
			);

			$options = array_merge($options, $this->rooms);
			?>
			<select name="id_room" id="vr-rooms-sel" class="<?php echo ($filters['id_room'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['id_room']); ?>
			</select>
		</div>

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

	</div>

<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOTABLE'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="12%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGETABLE2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGETABLE3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGETABLE12'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="8%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="12%" style="text-align: center;"><?php echo $links[3]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		
		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>
				
				<td class="hidden-phone"><?php echo $row['id']; ?></td>

				<td>
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=table.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}
					?>

					<span class="badge mobile-only pull-right"><?php echo $row['min_capacity'] . '-' . $row['max_capacity']; ?></span>
				</td>

				<td style="text-align: center;" class="hidden-phone"><?php echo $row['min_capacity']; ?></td>

				<td style="text-align: center;" class="hidden-phone"><?php echo $row['max_capacity']; ?></td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=table.changeshared&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['multi_res'] ? 0 : 1; ?>">
							<i class="fas fa-<?php echo $row['multi_res'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-<?php echo $row['multi_res'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=table.publish&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['published'] ? 0 : 1; ?>">
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

				<td style="text-align: center;">
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=room.edit&amp;cid[]=<?php echo $row['id_room']; ?>"><?php echo $row['room_name']; ?></a></td>
						<?php
					}
					else
					{
						echo $row['room_name'];
					}
					?>
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
	<input type="hidden" name="view" value="tables" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-rooms-sel').updateChosen(0);
		jQuery('#vr-status-sel').updateChosen(-1);
		
		document.adminForm.submit();
	}

</script>
