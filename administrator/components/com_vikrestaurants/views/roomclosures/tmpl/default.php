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

$config = VREFactory::getConfig();

$dt_format = $config->get('dateformat') . ' ' . $config->get('timeformat');

// ordering links
foreach (array('c.id', 'r.ordering', 'c.start_ts') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('roomclosures', JText::_('VRMANAGEMENUSPRODUCT1'), 'c.id', $ordering['c.id'], 1, $filters, 'vrheadcolactive'.(($ordering['c.id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('roomclosures', JText::_('VRMANAGEROOMCLOSURE1'), 'r.ordering', $ordering['r.ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['r.ordering'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('roomclosures', JText::_('VRMANAGEROOMCLOSURE2'), 'c.start_ts', $ordering['c.start_ts'], 1, $filters, 'vrheadcolactive'.(($ordering['c.start_ts'] == 2) ? 1 : 2)),
);

$now = VikRestaurants::now();

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">
	
	<div class="btn-toolbar" style="height:32px;">
		<div class="btn-group pull-left">
			<?php
			$options = array(
				JHtml::_('select.option', 0, JText::_('VRMAPSCHOOSEROOM')),
			);

			$options = array_merge($options, $this->rooms);
			?>
			<select name="id_room" id="vr-rooms-sel" class="<?php echo ($filters['id_room'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['id_room']); ?>
			</select>
		</div>

		<div class="btn-group pull-right vr-toolbar-setfont hidden-phone">
			<?php
			$attr = array(
				'onChange' => 'document.adminForm.submit();',
			);

			echo $vik->calendar($filters['date'], 'date', 'vr-date-filter', null, $attr);
			?>
		</div>
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOROOMCLOSURES'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEROOMCLOSURE3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGEROOMCLOSURE4'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEROOMCLOSURE5'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
			if ($row['start_ts'] <= $now && $now < $row['end_ts'])
			{
				$status = 'confirmed';
			}
			else if ($row['end_ts'] <= $now)
			{
				$status = 'removed';
			}
			else
			{
				$status = 'pending';
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
						<a href="index.php?option=com_vikrestaurants&amp;task=roomclosure.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}
					?>

					<div class="mobile-only">
						<span class="badge badge-important"><?php echo date($config->get('dateformat'), $row['start_ts']); ?></span>
						<span class="badge badge-important"><?php echo date($config->get('dateformat'), $row['end_ts']); ?></span>
					</div>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo date($dt_format, $row['start_ts']); ?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo date($dt_format, $row['end_ts']); ?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo VikRestaurants::minutesToStr(($row['end_ts'] - $row['start_ts']) / 60); ?>
				</td>

				<td style="text-align: center;">
					<span class="vrreservationstatus<?php echo $status; ?>">
						<?php echo JText::_('VRROOMCLOSURESTATUS' . strtoupper($status)); ?>
					</span>
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
	<input type="hidden" name="view" value="roomclosures" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script>
	
	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');
		
	});
	
</script>
