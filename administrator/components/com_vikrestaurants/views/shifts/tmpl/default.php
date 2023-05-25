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

$time_format = VikRestaurants::getTimeFormat(true);

// ORDERING LINKS

foreach (array('id', 'name', 'from', 'group') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('shifts', JText::_('VRMANAGERESCODE1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('shifts', JText::_('VRMANAGESHIFT1'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('shifts', JText::_('VRMANAGESHIFT2'), 'from', $ordering['from'], 1, $filters, 'vrheadcolactive'.(($ordering['from'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('shifts', JText::_('VRMANAGESHIFT4'), 'group', $ordering['group'], 1, $filters, 'vrheadcolactive'.(($ordering['group'] == 2) ? 1 : 2)),
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
			<select name="group" id="vr-group-sel" class="<?php echo ($filters['group'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php
				$options = JHtml::_('vrehtml.admin.groups', array(1, 2), true);

				echo JHtml::_('select.options', $options, 'value', 'text', $filters['group'], true);
				?>
			</select>
		</div>

	</div>

<?php
if (VikRestaurants::isContinuosOpeningTime())
{
	echo $vik->alert(JText::sprintf('VRSHIFTSIGNOREDWARNING', 'index.php?option=com_vikrestaurants&amp;task=shift.switchmode'), 'warning', true);
}

if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOSHIFT'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGESHIFT3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGESHIFT5'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[3]; ?></th>
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
						<a href="index.php?option=com_vikrestaurants&amp;task=shift.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}

					if (strlen($row['label']) && $row['name'] != $row['label'])
					{
						?><div class="hidden-phone"><small><?php echo $row['label']; ?></small></div><?php
					}
					?>

					<div class="badge mobile-only">
						<?php
						echo JHtml::_('vikrestaurants.min2time', $row['from']) . ' - ' . JHtml::_('vikrestaurants.min2time', $row['to']);
						?>
					</div>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo JHtml::_('vikrestaurants.min2time', $row['from']); ?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo JHtml::_('vikrestaurants.min2time', $row['to']); ?>
				</td>

				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=shift.showlabel&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['showlabel'] == 1 ? 0 : 1; ?>" />
							<i class="fas fa-<?php echo $row['showlabel'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-<?php echo $row['showlabel'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;">
					<?php echo JText::_($row['group'] == 1 ? 'VRSHIFTGROUPOPT1' : 'VRSHIFTGROUPOPT2'); ?>
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
	<input type="hidden" name="view" value="shifts" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-group-sel').updateChosen('');
		
		document.adminForm.submit();
	}
	
</script>
