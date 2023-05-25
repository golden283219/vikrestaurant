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

JHtml::_('vrehtml.assets.fontawesome');

$rows = $this->rows;

$filters = $this->filters;

$vik = VREApplication::getInstance();

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
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOAPIPLUGIN'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGEAPIUSER22'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGEAPIUSER23'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="35%" style="text-align: left;"><?php echo JText::_('VRMANAGEAPIUSER24'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="2%" style="text-align: center;">&nbsp;</th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];

			// get plugin description
			$short_desc = $desc = $row->getDescription();

			if ($short_desc)
			{
				// split description line by line
				$chunks = preg_split("/(\R|<br\s*\/\s*>)/", $short_desc);
				// remove empty lines
				$chunks = array_filter($chunks);
				// use only the first paragraph
				$short_desc = strip_tags(array_shift($chunks));
			}
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->getName(); ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>
				
				<td><b><?php echo $row->getTitle(); ?></b></td>
				
				<td class="hidden-phone"><?php echo $row->getName() . '.php'; ?></td>

				<td class="hidden-phone"><?php echo $short_desc; ?></td>
				
				<td style="text-align: center;">
					<?php
					if ($desc)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=apiplugin.edit&amp;cid[]=<?php echo $row->getName(); ?>" >
							<i class="fas fa-sticky-note big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<a href="javascript: void(0);" class="disabled" disabled="disabled">
							<i class="fas fa-sticky-note big"></i>
						</a>
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
	<input type="hidden" name="view" value="apiplugins" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		
		document.adminForm.submit();
	}
	
</script>
