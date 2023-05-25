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

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

// ORDERING LINKS

foreach (array('id', 'createdon') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('rescodesorder', JText::_('VRCREATEDON'), 'createdon', $ordering['createdon'], 1, $filters, 'vrheadcolactive'.(($ordering['createdon'] == 2) ? 1 : 2)),
);

$config = VREFactory::getConfig();

$dt_format = $config->get('dateformat') . ' ' . $config->get('timeformat');

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNORESCODEORDER'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGERESCODE2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESCODE3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="30%" style="text-align: left;"><?php echo JText::_('VRMANAGERESCODE5'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="15%" style="text-align: center;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRCREATEDBY'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];	 
			?>
			<tr class="row<?php echo $kk; ?>">

				<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>"></td>
				
				<td>
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=rescodeorder.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['code']; ?></a>
						<?php
					}
					else
					{
						echo $row['code'];
					}
					?>
				</td>

				<td style="text-align: center;" class="vrrescodelink">
					<?php
					if (!empty($row['icon']))
					{
						?>
						<img src="<?php echo VREMEDIA_SMALL_URI . $row['icon']; ?>" style="max-width: 20px;" />
						<?php
					}
					?>
				</td>

				<td class="hidden-phone">
					<?php
					if (strlen($row['notes']))
					{
						echo $row['notes']; 
					}
					else
					{
						?><small><i><?php echo $row['code_notes']; ?></i></small><?php
					}
					?>
				</td>

				<td style="text-align: center;">
					<?php
					if (VikRestaurants::now() - $row['createdon'] < 86400)
					{ 
						echo VikRestaurants::formatTimestamp($dt_format, $row['createdon']); 
					}
					else
					{
						echo date($dt_format, $row['createdon']);
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo $row['user_name']; ?>
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

	<input type="hidden" name="id_order" value="<?php echo $filters['id_order']; ?>" />
	<input type="hidden" name="group" value="<?php echo $filters['group']; ?>" />

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="rescodesorder" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>

</form>
