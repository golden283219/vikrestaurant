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

foreach (array('id', 'name', 'ordering') as $c)
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
	OrderingManager::getLinkColumnOrder('menus', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('menus', JText::_('VRMANAGEMENU1'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('menus', '<i class="fas fa-sort"></i>', 'ordering', $ordering['ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['ordering'] == 2) ? 1 : 2)),
);

$has_filters = $this->hasFilters();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=menu.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'restaurantmenusList', 'adminForm', $orderDir, $saveOrderingUrl);
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
			<select name="status" id="vr-status-select" class="<?php echo ($filters['status'] != -1 ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['status'], true); ?>
			</select>
		</div>

	</div>

<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOMENU'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="restaurantmenusList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGEMENU3');?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGEMENU4');?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU26');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU31');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU2');?></th>

				<?php
				if ($multi_lang)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU33');?></th>
					<?php
				}
				?>

				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU14');?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU18');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[2]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
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

			// fetch working shifts
			if ($row['special_day'])
			{
				// working shifts not available in case of special day
				$row['working_shifts'] = '/';
			}
			else if (!empty($row['working_shifts']))
			{
				$_arr = explode(',', $row['working_shifts']);

				$row['working_shifts'] = array();

				foreach ($_arr as $shift_id)
				{
					// recover working shift
					$shift = JHtml::_('vikrestaurants.timeofshift', $shift_id);

					// create tooltip
					$tooltip = '<i class="fas fa-stopwatch hasTooltip" title="' . $shift->fromtime . ' - ' . $shift->totime . '"></i>';

					$row['working_shifts'][] = $tooltip . ' ' . $shift->name;
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
			if ($row['special_day'])
			{
				// days not available in case of special day
				$row['days_filter'] = '/';
			}
			else if (strlen($row['days_filter']))
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
						<a href="index.php?option=com_vikrestaurants&amp;task=menu.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}
					?>
				</td>

				<td class="hidden-phone"><?php echo $row['working_shifts']; ?></td>

				<td class="hidden-phone"><?php echo $row['days_filter']; ?></td>

				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=menu.<?php echo $row['published'] ? 'un' : ''; ?>publish&amp;cid[]=<?php echo $row['id']; ?>">
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
					<i class="fas fa-<?php echo $row['choosable'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<i class="fas fa-<?php echo $row['special_day'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>	
				</td>

				<?php
				if ($multi_lang)
				{
					?>
					<td style="text-align: center;">
						<a href="index.php?option=com_vikrestaurants&amp;view=langmenus&amp;id_menu=<?php echo $row['id']; ?>">
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
					<a href="javascript: void(0);" onclick="SELECTED_MENU = '<?php echo $row['id']; ?>';vrOpenJModal('sneakmenu', null, true); return false;">
						<i class="fas fa-search big"></i>
					</a>
				</td>

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
	<input type="hidden" name="view" value="menus" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-sneakmenu',
	array(
		'title'       => JText::_('VRMANAGEMENU14'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});

	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-select').updateChosen(-1);

		document.adminForm.submit();
	}

	// JMODAL

	var SELECTED_MENU = -1;
	
	function vrOpenJModal(id, url, jqmodal) {
		if (id == 'sneakmenu') {
			url = 'index.php?option=com_vikrestaurants&view=sneakmenu&tmpl=component&id=' + SELECTED_MENU;
		}

		<?php echo $vik->bootOpenModalJS(); ?>
	}

</script>
