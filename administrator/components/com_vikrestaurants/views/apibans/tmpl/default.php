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
JHtml::_('vrehtml.assets.fontawesome');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

$config = VREFactory::getConfig();

$max_fail = $config->getUint('apimaxfail');

foreach (array('id', 'last_update', 'fail_count') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('apibans', JText::_('VRMANAGEAPIUSER1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('apibans', JText::_('VRMANAGEAPIUSER18'), 'last_update', $ordering['last_update'], 1, $filters, 'vrheadcolactive'.(($ordering['last_update'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('apibans', JText::_('VRMANAGEAPIUSER19'), 'fail_count', $ordering['fail_count'], 1, $filters, 'vrheadcolactive'.(($ordering['fail_count'] == 2) ? 1 : 2)),
);

$dt_format = $config->get('dateformat') . ' ' . $config->get('timeformat');

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

		<?php
		$options = array(
			JHtml::_('select.option', 1, 'VRAPIBANOPT1'),
			JHtml::_('select.option', 2, 'VRAPIBANOPT2'),
		);
		?>
		<div class="btn-group pull-right">
			<select name="type" id="vr-type-sel" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['type'], true); ?>
			</select>
		</div>
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOAPIBAN'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGEAPIUSER17'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="20%" style="text-align: center;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;">&nbsp;</th>
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
				
				<td><?php echo $row['ip']; ?></td>
				
				<td style="text-align: center;">
					<span class="hasTooltip" title="<?php echo date($dt_format, $row['last_update']); ?>">
						<?php echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC2'), $row['last_update']); ?>
					</span>
				</td>

				<td style="text-align: center;<?php echo ($row['fail_count'] >= $max_fail ? 'color:#900;' : ''); ?>">
					<?php
					if ($row['fail_count'] == 0)
					{
						$badge = 'success';
					}
					else if ($row['fail_count'] >= $max_fail)
					{
						$badge = 'important';
					}
					else
					{
						$badge = 'warning';
					}
					?>
					<span class="badge badge-<?php echo $badge; ?>">
						<?php echo $row['fail_count'] . ' / ' . $max_fail; ?>
					</span>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($row['fail_count'] < $max_fail)
					{
						?>
						<span style="color: #090;">
							<i class="fas fa-check-circle"></i>
							<b style="text-transform: uppercase;margin-left: 2px;">
								<?php echo JText::_('VRMANAGEAPIUSER6'); ?>
							</b>
						</span>
						<?php
					}
					else
					{
						?>
						<span style="color: #900;">
							<i class="fas fa-ban"></i>
							<b style="text-transform: uppercase;margin-left: 2px;">
								<?php echo JText::_('VRMANAGEAPIUSER20'); ?>
							</b>
						</span>
						<?php
					}
					?>
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
	<input type="hidden" name="view" value="apibans" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-type-sel').updateChosen(1);
		
		document.adminForm.submit();
	}
	
</script>
