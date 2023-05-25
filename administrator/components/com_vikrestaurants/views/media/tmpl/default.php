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

JHtml::_('vrehtml.assets.fancybox');
JHtml::_('vrehtml.assets.fontawesome');

$rows = $this->rows;

$filters = $this->filters;

$vik = VREApplication::getInstance();

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

$gallery = array();

foreach ($rows as $media)
{
	/**
	 * Append timestamp of creation date in order to
	 * automatically remove the cached image in case
	 * of changes to the media file.
	 *
	 * @since 1.8.3
	 */
	$gallery[] = $media['uri'] . '?' . $media['timestamp'];
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
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOMEDIA'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="35%" style="text-align: left;"><?php echo JText::_('VRMANAGEMEDIA1'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEMEDIA12'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEMEDIA13'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGEMEDIA3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGEMEDIA4'); ?></th>
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
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['name']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>

				<td>
					<div class="name break-word">
						<?php
						if ($canEdit)
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;task=media.edit&amp;cid[]=<?php echo $row['name']; ?>"><?php echo $row['name']; ?></a>
							<?php
						}
						else
						{
							echo $row['name'];
						}
						?>
					</div>
				</td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php echo $row['width'] . ' x ' . $row['height']; ?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo $row['size']; ?>
				</td>

				<td style="text-align: center;" class="hidden-phone"><?php echo $row['creation']; ?></td>

				<td style="text-align: center;">
					<a href="javascript: void(0);" class="vremodal" onClick="vreOpenGallery(<?php echo $i; ?>);">
						<img src="<?php echo VREMEDIA_SMALL_URI . $row['name'] . '?' . $row['timestamp']; ?>" style="max-width: 64px;height: auto;" />
					</a>
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
	<input type="hidden" name="view" value="media" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	function vreOpenGallery(index) {
		var instance = vreOpenModalImage(<?php echo json_encode($gallery); ?>);

		if (index > 0) {
			// jump to selected image ('0' turns off the animation)
			instance.jumpTo(index, 0);
		}
	}

	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		
		document.adminForm.submit();
	}

</script>
