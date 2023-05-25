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

foreach (array('id', 'name', 'charge', 'ordering') as $c)
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
	OrderingManager::getLinkColumnOrder('tkareas', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkareas', JText::_('VRMANAGETKAREA1'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkareas', JText::_('VRMANAGETKAREA4'), 'charge', $ordering['charge'], 1, $filters, 'vrheadcolactive'.(($ordering['charge'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkareas', '<i class="fas fa-sort"></i>', 'ordering', $ordering['ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['ordering'] == 2) ? 1 : 2)),
);

$has_filters = $this->hasFilters();

$currency = VREFactory::getCurrency();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=tkarea.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'areasList', 'adminForm', $orderDir, $saveOrderingUrl);
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
			<button type="button" id="mapareas" class="btn" onclick="vrOpenJModal(this.id, null, true); return false;">
				<i class="fas fa-map-marker-alt" style="margin-right: 4px;"></i>&nbsp;<?php echo JText::_('VRTKMAPDELIVERYAREAS'); ?>
			</button>
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
		$options = array(
			JHtml::_('select.option', 0, 'VRE_FILTER_SELECT_TYPE'),
			JHtml::_('select.option', 1, 'VRTKAREATYPE1'),
			JHtml::_('select.option', 2, 'VRTKAREATYPE2'),
			JHtml::_('select.option', 3, 'VRTKAREATYPE3'),
			JHtml::_('select.option', 4, 'VRTKAREATYPE4'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="type" id="vr-type-select" class="<?php echo ($filters['type'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['type'], true); ?>
			</select>
		</div>

	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOTKAREA'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="areasList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGETKAREA2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGETKAREA3'); ?></th>
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
						<a href="index.php?option=com_vikrestaurants&amp;task=tkarea.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone"><?php echo JText::_('VRTKAREATYPE' . $row['type']); ?></td>

				<td style="text-align: center;"><?php echo $currency->format($row['charge']); ?></td>

				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=tkarea.<?php echo ($row['published'] ? 'un' : ''); ?>publish&amp;cid[]=<?php echo $row['id']; ?>">
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
	<input type="hidden" name="view" value="tkareas" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<!-- JQUERY MODALS -->

<?php
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-mapareas',
	array(
		'title'       => JText::_('VRTKMAPDELIVERYAREAS'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => 'index.php?option=com_vikrestaurants&view=tkmapareas&tmpl=component',
	)
);
?>

<script>

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-select').updateChosen('');
		jQuery('#vr-type-select').updateChosen(0);

		document.adminForm.submit();
	}

	// JMODAL
	
	function vrOpenJModal(id, url, jqmodal) {
		<?php echo $vik->bootOpenModalJS(); ?>
	}
	
</script>
