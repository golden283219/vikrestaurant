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

foreach (array('t.id', 't.name', 't.price', 't.ordering', 's.ordering') as $c)
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
	OrderingManager::getLinkColumnOrder('tktoppings', JText::_('VRMANAGEMENUSPRODUCT1'), 't.id', $ordering['t.id'], 1, $filters, 'vrheadcolactive'.(($ordering['t.id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktoppings', JText::_('VRMANAGETKTOPPING1'), 't.name', $ordering['t.name'], 1, $filters, 'vrheadcolactive'.(($ordering['t.name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktoppings', JText::_('VRMANAGETKTOPPING2'), 't.price', $ordering['t.price'], 1, $filters, 'vrheadcolactive'.(($ordering['t.price'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktoppings', JText::_('VRMANAGETKTOPPING5'), 's.ordering', $ordering['s.ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['s.ordering'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tktoppings', '<i class="fas fa-sort"></i>', 't.ordering', $ordering['t.ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['t.ordering'] == 2) ? 1 : 2)),
);

$has_filters = $this->hasFilters();

$currency = VREFactory::getCurrency();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['t.ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=tktopping.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'toppingsList', 'adminForm', $orderDir, $saveOrderingUrl);
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

		<div class="btn-group pull-right hidden-phone">
			<a href="index.php?option=com_vikrestaurants&amp;view=tktopseparators" class="btn">
				<?php echo JText::_('VRTKGOTOTOPPINGSEP'); ?>
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

		<?php
		if ($this->separators)
		{
			$options = array(
				JHtml::_('select.option', 0, JText::_('VRE_FILTER_SELECT_SEPARATOR')),
			);

			$options = array_merge($options, $this->separators);
			?>
			<div class="btn-group pull-left">
				<select name="id_separator" id="vr-separator-select" class="<?php echo ($filters['id_separator'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['id_separator']); ?>
				</select>
			</div>
			<?php
		}
		?>

	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOTKTOPPING'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="toppingsList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGETKTOPPING3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo $links[3]; ?></th>
				
				<?php
				if ($multi_lang)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU33');?></th>
					<?php
				}
				?>

				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[4]; ?></th>
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
						<a href="index.php?option=com_vikrestaurants&amp;task=tktopping.edit&amp;cid[]=<?php echo $row['id']; ?>">
							<?php echo $row['name']; ?>
						</a>
						<?php
					}
					else
					{
						echo $row['name'];
					}
					?>
				</td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php echo $currency->format($row['price']); ?>
				</td>
				
				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=tktopping.<?php echo ($row['published'] ? 'un' : ''); ?>publish&amp;cid[]=<?php echo $row['id']; ?>">
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
					<?php echo (empty($row['separator']) ? '/' : $row['separator']); ?>
				</td>

				<?php
				if ($multi_lang)
				{
					?>
					<td style="text-align: center;">
						<a href="index.php?option=com_vikrestaurants&amp;view=langtktoppings&amp;id_topping=<?php echo $row['id']; ?>">
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
	<input type="hidden" name="view" value="tktoppings" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});

	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-select').updateChosen('');
		jQuery('#vr-separator-select').updateChosen(0);

		document.adminForm.submit();
	}
	
</script>
