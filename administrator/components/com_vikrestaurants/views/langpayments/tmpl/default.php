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

$vik = VREApplication::getInstance();

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
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
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo JText::_('VRMANAGELANG1');?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGELANG2');?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="30%" style="text-align: left;"><?php echo JText::_('VRMANAGEPAYMENT11');?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="30%" style="text-align: left;"><?php echo JText::_('VRMANAGEPAYMENT7');?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGELANG4');?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];

			$name = $row['name'] ? $row['name'] : JText::_('JOPTION_USE_DEFAULT');
			
			if ($row['note'])
			{
				$note = strip_tags($row['note']);

				if (strlen($note) > 300)
				{
					$note = mb_substr($note, 0, 256, 'UTF-8') . '...';
				}
			}
			else
			{
				$note = JText::_('JOPTION_USE_DEFAULT');
			}

			if ($row['prenote'])
			{
				$prenote = strip_tags($row['prenote']);

				if (strlen($prenote) > 300)
				{
					$prenote = mb_substr($prenote, 0, 256, 'UTF-8') . '...';
				}
			}
			else
			{
				$prenote = JText::_('JOPTION_USE_DEFAULT');
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
						<a href="index.php?option=com_vikrestaurants&amp;task=langpayment.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $name; ?></a>
						<?php
					}
					else
					{
						echo $name;
					}
					?>
				</td>

				<td class="hidden-phone"><?php echo $prenote; ?></td>

				<td class="hidden-phone"><?php echo $note; ?></td>

				<td style="text-align: center;">
					<?php echo JHtml::_('vrehtml.site.flag', $row['tag']); ?>
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
	<input type="hidden" name="view" value="langpayments" />
	<input type="hidden" name="id_payment" value="<?php echo $filters['id_payment']; ?>" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>
