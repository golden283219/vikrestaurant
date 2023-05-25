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

// ORDERING LINKS

$orderDir = null;

foreach (array('id', 'code', 'type', 'ordering') as $c)
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
	OrderingManager::getLinkColumnOrder('rescodes', JText::_('VRMANAGERESCODE1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('rescodes', JText::_('VRMANAGERESCODE2'), 'code', $ordering['code'], 1, $filters, 'vrheadcolactive'.(($ordering['code'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('rescodes', JText::_('VRMANAGERESCODE4'), 'type', $ordering['type'], 1, $filters, 'vrheadcolactive'.(($ordering['type'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('rescodes', '<i class="fas fa-sort"></i>', 'ordering', $ordering['ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['ordering'] == 2) ? 1 : 2)),
);

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=rescode.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'rescodesList', 'adminForm', $orderDir, $saveOrderingUrl, array('type' => $filters['type']));
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

		<div class="btn-group pull-right">
			<select name="type" id="vr-type-sel" onchange="document.adminForm.submit();">
				<?php
				$options = JHtml::_('vrehtml.admin.groups', array(1, 2));

				/**
				 * Added support for "Food" group.
				 *
				 * @since 1.8
				 */
				$options[] = JHtml::_('select.option', 3, 'VRCONFIGFIELDSETFOOD');

				echo JHtml::_('select.options', $options, 'value', 'text', $filters['type'], true);
				?>
			</select>
		</div>
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNORESCODE'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="rescodesList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="40%" style="text-align: left;"><?php echo JText::_('VRMANAGERESCODE5'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGECUSTOMF11'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESCODE3'); ?></th>
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
				
				<td><?php echo $row['id']; ?></td>
				
				<td>
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=rescode.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['code']; ?></a>
						<?php
					}
					else
					{
						echo $row['code'];
					}
					?>
				</td>

				<td class="hidden-phone">
					<?php echo $row['notes']; ?>
				</td>

				<td class="hidden-phone">
					<?php
					if ($row['rule'])
					{
						try
						{
							// get rule instance
							$rule = ResCodesHandler::getRule($row['rule']);
							?>
							<span class="hasTooltip" title="<?php echo $this->escape($rule->getDescription()); ?>">
								<?php echo $rule->getName(); ?>
							</span>
							<?php
						}
						catch (Exception $e)
						{
							// rule not found
							?>
							<span class="hasTooltip" title="<?php echo $this->escape($e->getMessage()); ?>">
								<em><?php echo $row['rule']; ?></em>
								<i class="fas fa-exclamation-circle no"></i>
							</span>
							<?php
						}
					}
					?>
				</td>

				<td style="text-align: center;" class="vrrescodelink">
					<?php
					if (!empty($row['icon']))
					{
						?>
						<img src="<?php echo VREMEDIA_URI . $row['icon']; ?>" />
						<?php
					}
					?>
				</td>
				
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
	<input type="hidden" name="view" value="rescodes" />
	
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		
		document.adminForm.submit();
	}
	
</script>
