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

JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen');
JHtml::_('bootstrap.tooltip', '.hasTooltip');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

$multi_lang = VikRestaurants::isMultilanguage();

// ORDERING LINKS

$orderDir = null;

foreach (array('id', 'name', 'published', 'ordering') as $c)
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
	OrderingManager::getLinkColumnOrder('rooms', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('rooms', JText::_('VRMANAGEROOM1'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('rooms', JText::_('VRMANAGEROOM3'), 'published', $ordering['published'], 1, $filters, 'vrheadcolactive'.(($ordering['published'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('rooms', '<i class="fas fa-sort"></i>', 'ordering', $ordering['ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['ordering'] == 2) ? 1 : 2)),
);

$has_filters = $this->hasFilters();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=room.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'roomsList', 'adminForm', $orderDir, $saveOrderingUrl);
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
			JHtml::_('select.option', 1, 'VRROOMSTATUSACTIVE'),
			JHtml::_('select.option', 0, 'VRROOMSTATUSCLOSED'),
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
	echo $vik->alert(JText::_('VRNOROOM'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="roomsList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="30%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEROOM10'); ?></th>

				<?php
				if ($multi_lang)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU33');?></th>
					<?php
				}
				?>
				
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEROOM4'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[3]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
			if ($row['is_closed'] || !$row['published'])
			{
				$status_class = "vrreservationstatusremoved"; 
				$status_name  = "VRROOMSTATUSCLOSED";
			}
			else
			{
				$status_class = "vrreservationstatusconfirmed"; 
				$status_name  = "VRROOMSTATUSACTIVE";
			}
			
			$icon_type = 1;

			if (empty($row['image']))
			{
				// image not uploaded
				$icon_type = 2;
			}
			
			if (!file_exists(VREMEDIA . DIRECTORY_SEPARATOR . $row['image']))
			{
				// image not found
				$icon_type = 0;
			}
			
			$img_title = JText::_('VRIMAGESTATUS' . $icon_type);
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
							<a href="index.php?option=com_vikrestaurants&amp;task=room.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
							<?php
						}
						else
						{
							echo $row['name'];
						}
						?>
					</div>

					<div class="btn-group">
						<a href="index.php?option=com_vikrestaurants&amp;task=map.edit&amp;selectedroom=<?php echo $row['id']; ?>" class="btn btn-mini hasTooltip" title="<?php echo JText::_('VRE_DESIGN_MAP'); ?>">
							<i class="fas fa-paint-brush"></i>
						</a>

						<?php
						if ($row['tables_count'])
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;view=tables&amp;id_room=<?php echo $row['id']; ?>" class="btn btn-mini">
								<i class="fas fa-filter"></i>
								<span class="hidden-phone"><?php echo JText::plural('VRE_DISPLAY_N_TABLES', $row['tables_count']); ?></span>
							</a>
							<?php
						}
						?>

						<a href="index.php?option=com_vikrestaurants&amp;task=table.add&amp;id_room=<?php echo $row['id']; ?>" class="btn btn-mini">
							<i class="fas fa-plus-circle"></i>
							<span class="hidden-phone"><?php echo JText::_('VRE_ADD_TABLE'); ?></span>
						</a>
					</div>
				</td>

				<td style="text-align: center;">
					<?php
					if($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=room.publish&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['published'] == 1 ? 0 : 1; ?>" />
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

				<td style="text-align: center;" class="<?php echo $status_class; ?>">
					<?php echo JText::_($status_name); ?>
				</td>

				<?php
				if ($multi_lang)
				{
					?>
					<td style="text-align: center;">
						<a href="index.php?option=com_vikrestaurants&amp;view=langrooms&amp;id_room=<?php echo $row['id']; ?>">
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
				
				<td style="text-align: center;">
					<?php
					if ($icon_type == 1)
					{
						?>
						<a href="<?php echo VREMEDIA_URI . $row['image']; ?>" class="modal" target="_blank">
							<i class="fas fa-image ok big-2x" title="<?php echo $img_title ?>"></i>
						</a>
						<?php
					}
					else if ($icon_type == 0)
					{
						?>
						<i class="fas fa-eye-slash no big-2x" title="<?php echo $img_title ?>"></i>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-image no big-2x" title="<?php echo $img_title ?>"></i>
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
	<input type="hidden" name="view" value="rooms" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-sel').updateChosen(-1);
		
		document.adminForm.submit();
	}

	Joomla.submitbutton = function(task) {
		if (task == 'roomclosures') {
			// extract HREF from link in order to use the correct platform URL
			document.location.href = jQuery('#closures-link').attr('href');
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

</script>
