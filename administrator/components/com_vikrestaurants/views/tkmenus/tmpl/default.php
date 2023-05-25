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
JHtml::_('bootstrap.tooltip', '.hasTooltip');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

$multi_lang = VikRestaurants::isMultilanguage();

// ORDERING LINKS

$orderDir = null;

foreach (array('m.id', 'm.title', 'm.ordering') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
	else
	{
		$orderDir = $ordering[$c] == 2 ? 'asc' : 'desc';
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('tkmenus', JText::_('VRMANAGEMENUSPRODUCT1'), 'm.id', $ordering['m.id'], 1, $filters, 'vrheadcolactive'.(($ordering['m.id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkmenus', JText::_('VRMANAGETKMENU1'), 'm.title', $ordering['m.title'], 1, $filters, 'vrheadcolactive'.(($ordering['m.title'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkmenus', '<i class="fas fa-sort"></i>', 'm.ordering', $ordering['m.ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['m.ordering'] == 2) ? 1 : 2)),
);

$has_filters = $this->hasFilters();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['m.ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=tkmenu.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'tkmenusList', 'adminForm', $orderDir, $saveOrderingUrl);
}

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

		<div class="btn-group pull-right">
			<?php
			if (VikRestaurants::isTakeAwayStockEnabled())
			{
				?>
				<a href="index.php?option=com_vikrestaurants&amp;view=tkmenustocks" class="btn">
					<i class="fas fa-archive" style="margin-right: 4px;"></i>&nbsp;<?php echo JText::_('VRMANAGETKMENUSTOCKS'); ?>
				</a>
				<?php
			}
			?>
			<a href="index.php?option=com_vikrestaurants&amp;view=tkmenuattr" class="btn">
				<i class="fas fa-leaf" style="margin-right: 4px;"></i>&nbsp;<?php echo JText::_('VRMANAGETKMENUATTRIBUTES'); ?>
			</a>
		</div>
	
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<?php
		$options = array(
			JHtml::_('select.option', '', 'JOPTION_SELECT_PUBLISHED'),
			JHtml::_('select.option', 1, 'JPUBLISHED'),
			JHtml::_('select.option', 0, 'JUNPUBLISHED'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="status" id="vr-status-select" class="<?php echo (strlen($filters['status']) ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['status'], true); ?>
			</select>
		</div>

	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOTKMENU'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="tkmenusList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="35%" style="text-align: left;"><?php echo JText::_('VRMANAGETKMENU2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGETKMENU12'); ?></th>

				<?php
				if ($multi_lang)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU33'); ?></th>
					<?php
				}
				?>

				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[2]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];

			$desc = strip_tags($row['description']);
			
			if (strlen($desc) > 150)
			{
				$desc = mb_substr($desc, 0, 128, 'UTF-8') . "...";
			} 
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>

				<td class="hidden-phone"><?php echo $row['id']; ?></td>

				<td>
					<div class="vr-primary-text">
						<?php
						if ($canEdit)
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;task=tkmenu.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a>
							<?php
						}
						else
						{
							echo $row['title'];
						}
						?>
					</div>

					<div class="btn-group">
						<?php
						if ($row['entries_count'])
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;view=tkproducts&amp;id_takeaway_menu=<?php echo $row['id']; ?>" class="btn btn-mini">
								<i class="fas fa-filter"></i>
								<span class="hidden-phone"><?php echo JText::plural('VRE_DISPLAY_N_PRODUCTS', $row['entries_count']); ?></span>
							</a>
							<?php
						}
						?>

						<a href="index.php?option=com_vikrestaurants&amp;task=tkentry.add&amp;id_takeaway_menu=<?php echo $row['id']; ?>" class="btn btn-mini">
							<i class="fas fa-plus-circle"></i>
							<span class="hidden-phone"><?php echo JText::_('VRE_ADD_PRODUCT'); ?></span>
						</a>
					</div>
				</td>

				<td class="hidden-phone"><?php echo $desc; ?></td>

				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=tkmenu.<?php echo ($row['published'] ? 'un' : ''); ?>publish&amp;cid[]=<?php echo $row['id']; ?>">
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

				<?php
				if ($multi_lang)
				{
					?>
					<td style="text-align: center;">
						<a href="index.php?option=com_vikrestaurants&amp;view=langtkmenus&amp;id_menu=<?php echo $row['id']; ?>">
							<?php
							foreach ($row['languages'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</td>
					<?php
				}
				?>

				<td class="order nowrap center hidden-phone">
					<?php echo JHtml::_('vrehtml.admin.sorthandle', $row['ordering'], $canEditState, $canOrder); ?>
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
	<input type="hidden" name="view" value="tkmenus" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script>

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-select').updateChosen('');

		document.adminForm.submit();
	}
	
</script>
