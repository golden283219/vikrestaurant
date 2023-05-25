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

$is_shifted = !VikRestaurants::isContinuosOpeningTime();

$date_format = VREFactory::getConfig()->get('dateformat');

// ORDERING LINKS

foreach (array('id', 'name', 'priority', 'group') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('specialdays', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('specialdays', JText::_('VRMANAGESPDAY1'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('specialdays', JText::_('VRMANAGESPDAY20'), 'priority', $ordering['priority'], 1, $filters, 'vrheadcolactive'.(($ordering['priority'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('specialdays', JText::_('VRMANAGESPDAY16'), 'group', $ordering['group'], 1, $filters, 'vrheadcolactive'.(($ordering['group'] == 2) ? 1 : 2)),
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

		<div class="btn-group pull-right">
			<button type="button" class="btn" onclick="vrOpenJModal('sdtest', null, true);">
				<?php echo JText::_('VRTESTSPECIALDAYS'); ?>
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
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOSPECIALDAY'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGESPDAY2');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGESPDAY3');?></th>

				<?php
				if ($is_shifted)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGESPDAY4');?></th>
					<?php
				}
				?>

				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGESPDAY5');?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGESPDAY10');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGESPDAY12');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo $links[3]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
			// fetch working shifts
			if (!empty($row['working_shifts']))
			{
				$_arr = explode(',', $row['working_shifts']);

				$row['working_shifts'] = array();

				foreach ($_arr as $shift_id)
				{
					// recover working shift
					$shift = JHtml::_('vikrestaurants.timeofshift', $shift_id);

					if ($shift)
					{
						// create tooltip
						$tooltip = '<i class="fas fa-stopwatch hasTooltip" title="' . $shift->fromtime . ' - ' . $shift->totime . '"></i>';

						$row['working_shifts'][] = $tooltip . ' ' . $shift->name;
					}
				}

				$row['working_shifts'] = implode(', ', $row['working_shifts']);
			}
			else
			{
				// all shifts available
				$row['working_shifts'] = JText::_('VRMANAGEMENU24');
			}

			$date = new JDate;
			
			// fetch days filter
			if (strlen($row['days_filter']))
			{
				$_df = explode(',', $row['days_filter']);

				$row['days_filter'] = array();

				foreach ($_df as $day)
				{
					// convert day core to string (abbr.)
					$row['days_filter'][] = $date->dayToString($day, true);
				}

				$row['days_filter'] = implode(', ', $row['days_filter']);
			}
			else
			{
				// all days available
				$row['days_filter'] = JText::_('VRMANAGEMENU25');
			}
			
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
						<a href="index.php?option=com_vikrestaurants&amp;task=specialday.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}

					if ($row['start_ts'] != -1 && $row['end_ts'] != -1)
					{
						?>
						<div class="mobile-only">
							<span class="badge"><?php echo date($date_format, $row['start_ts']); ?></span>
							<span class="badge"><?php echo date($date_format, $row['end_ts']); ?></span>
						</div>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($row['start_ts'] != -1)
					{
						echo date($date_format, $row['start_ts']);
					}
					else
					{
						echo '/';
					}
					?>
				</td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($row['end_ts'] != -1)
					{
						echo date($date_format, $row['end_ts']);
					}
					else
					{
						echo '/';
					}
					?>
				</td>

				<?php
				if ($is_shifted)
				{
					?>
					<td style="text-align: center;" class="hidden-phone"><?php echo $row['working_shifts']; ?></td>
					<?php
				}
				?>

				<td style="text-align: center;" class="hidden-phone"><?php echo $row['days_filter']; ?></td>

				<td style="text-align: center;">
					<a href="javascript: void(0);" onclick="SELECTED_ID=<?php echo $row['id']; ?>;vrOpenJModal('menuslist', null, true); return false;">
						<i class="fas fa-search big"></i>
					</a>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=specialday.markoncal&amp;state=<?php echo $row['markoncal'] ? 0 : 1; ?>&amp;cid[]=<?php echo $row['id']; ?>">
							<i class="fa<?php echo ($row['markoncal'] ? 's' : 'r'); ?> fa-star big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fa<?php echo ($row['markoncal'] ? 's' : 'r'); ?> fa-star big"></i>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone"><?php echo JText::_('VRPRIORITY' . $row['priority']); ?></td>

				<td style="text-align: center;" class="hidden-phone"><?php echo JText::_($row['group'] == 1 ? 'VRSHIFTGROUPOPT1' : 'VRSHIFTGROUPOPT2'); ?></td>
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
	<input type="hidden" name="view" value="specialdays" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
// special days test
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-sdtest',
	array(
		'title'       => JText::_('VRTESTSPECIALDAYS'),
		'closeButton' => true,
		'keyboard'    => false, 
		'bodyHeight'  => 80,
		'url'         => 'index.php?option=com_vikrestaurants&view=specialdaystest&tmpl=component',
	)
);

// menus list
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-menuslist',
	array(
		'title'       => JText::_('VRMANAGESPDAY10'),
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
		jQuery('#vr-group-sel').updateChosen('');
		
		document.adminForm.submit();
	}

	// modal
	
	SELECTED_ID = -1;

	function vrOpenJModal(id, url, jqmodal) {
		if (id == 'menuslist') {
			url = 'index.php?option=com_vikrestaurants&view=menuslist&tmpl=component&id=' + SELECTED_ID;
		}

		<?php echo $vik->bootOpenModalJS(); ?>
	}

</script>
