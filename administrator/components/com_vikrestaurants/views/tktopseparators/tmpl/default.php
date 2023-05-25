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

JHtml::_('bootstrap.tooltip', '.hasTooltip');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

// ORDERING LINKS

$orderDir = null;

foreach (array('s.id', 's.title', 's.ordering', 'num_items') as $c)
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
	OrderingManager::getLinkColumnOrder('tktopseparators', JText::_('VRMANAGEMENUSPRODUCT1'), 's.id', $ordering['s.id'], 1, $filters, 'vrheadcolactive'.(($ordering['s.id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktopseparators', JText::_('VRMANAGETKTOPPINGSEP1'), 's.title', $ordering['s.title'], 1, $filters, 'vrheadcolactive'.(($ordering['s.title'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktopseparators', JText::_('VRMENUTAKEAWAYTOPPINGS'), 'num_items', $ordering['num_items'], 1, $filters, 'vrheadcolactive'.(($ordering['num_items'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktopseparators', '<i class="fas fa-sort"></i>', 's.ordering', $ordering['s.ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['s.ordering'] == 2) ? 1 : 2)),
);

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['s.ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=tktopseparator.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'separatorsList', 'adminForm', $orderDir, $saveOrderingUrl);
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
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>

		<div class="btn-group pull-right hidden-phone">
			<a href="index.php?option=com_vikrestaurants&amp;view=tktoppings" class="btn">
				<?php echo JText::_('VRTKGOTOTOPPING'); ?>
			</a>
		</div>
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOTKTOPPINGSEP'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="separatorsList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="40%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[3]; ?></th>
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
						<a href="index.php?option=com_vikrestaurants&amp;task=tktopseparator.edit&amp;cid[]=<?php echo $row['id']; ?>">
							<?php echo $row['title']; ?>
						</a>
						<?php
					}
					else
					{
						echo $row['title'];
					}
					?>
				</td>
				
				<td style="text-align: center;"><?php echo $row['num_items']; ?></td>

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
	<input type="hidden" name="view" value="tktopseparators" />
	
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		
		document.adminForm.submit();
	}
	
</script>
