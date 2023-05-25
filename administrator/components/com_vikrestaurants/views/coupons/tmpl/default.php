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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

$date_format = VREFactory::getConfig()->get('dateformat');

$currency = VREFactory::getCurrency();

// ORDERING LINKS

foreach (array('id', 'code', 'group') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('coupons', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('coupons', JText::_('VRMANAGECOUPON1'), 'code', $ordering['code'], 1, $filters, 'vrheadcolactive'.(($ordering['code'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('coupons', JText::_('VRMANAGECOUPON10'), 'group', $ordering['group'], 1, $filters, 'vrheadcolactive'.(($ordering['group'] == 2) ? 1 : 2))
);

$min_value_head = strlen($filters['group']) ? ($filters['group'] == 0 ? '8' : '9') : '7';

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
			<select name="group" id="vr-group-sel" class="<?php echo (strlen($filters['group']) ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php
				$options = JHtml::_('vrehtml.admin.groups', null, true);

				echo JHtml::_('select.options', $options, 'value', 'text', $filters['group'], true);
				?>
			</select>
		</div>

		<div class="btn-group pull-left">
			<select name="type" id="vr-type-sel" class="<?php echo ($filters['type'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php
				$options = array(
					JHtml::_('select.option', 0, 'VRE_FILTER_SELECT_TYPE'),
					JHtml::_('select.option', 1, 'VRCOUPONTYPEOPTION1'),
					JHtml::_('select.option', 2, 'VRCOUPONTYPEOPTION2'),
				);

				echo JHtml::_('select.options', $options, 'value', 'text', $filters['type'], true);
				?>
			</select>
		</div>

	</div>

<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOCOUPON'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="8%" style="text-align: left;"><?php echo JText::_('VRMANAGECOUPON2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGECOUPON4'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGECOUPON11'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGECOUPON' . $min_value_head); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
			$ds_de = null;

			if (!empty($row['datevalid']))
			{
				$ds_de = explode('-', $row['datevalid']);
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
						<a href="index.php?option=com_vikrestaurants&amp;task=coupon.edit&amp;cid[]=<?php echo $row['id']; ?>">
							<?php echo $row['code']; ?>
						</a>
						<?php
					}
					else
					{
						echo $row['code'];
					}
					?>
				</td>

				<td class="hidden-phone">
					<?php echo JText::_('VRCOUPONTYPEOPTION' . $row['type']); ?>
				</td>

				<td style="text-align: center;">
					<?php
					if ((float) $row['value'])
					{
						if ($row['percentot'] == 1)
						{
							echo $row['value'] . '%';
						}
						else
						{
							echo $currency->format($row['value']);
						}
					}
					else
					{
						echo '/';
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($ds_de)
					{
						echo date($date_format, $ds_de[0]) . ' - ' . date($date_format, $ds_de[1]);
					}
					else
					{
						echo '/';
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php 
					if ($row['group'] == 0)
					{
						
						echo JText::plural('VRE_N_PEOPLE', $row['minvalue']);
					}
					else
					{
						echo (float) $row['minvalue'] ? $currency->format($row['minvalue']) : '/';
					}
					?>
				</td>

				<td style="text-align: center;">
					<?php echo JText::_($row['group'] == 0 ? 'VRMANAGECONFIGTITLE1' : 'VRMANAGECONFIGTITLE2'); ?>
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
	<input type="hidden" name="view" value="coupons" />
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
		jQuery('#vr-type-sel').updateChosen(0);
		
		document.adminForm.submit();
	}

</script>
