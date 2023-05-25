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

$area = $this->area;

$vik = VREApplication::getInstance();

?>

<div class="vr-delivery-contents-wrapper">
	
	<div class="vr-zip-container">
		<?php
		if ($area->type == 3)
		{
			foreach ($area->content as $i => $zip)
			{
				?>
				<div class="vrtk-entry-var" id="vrzip<?php echo $i; ?>">
					<input type="text" name="from_zip[]" value="<?php echo $zip->from; ?>" size="12" class="form-control" placeholder="<?php echo $this->escape(JText::_('VRTKZIPPLACEHOLDER1')); ?>" />
				
					<input type="text" name="to_zip[]" value="<?php echo $zip->to; ?>" size="12" class="form-control" placeholder="<?php echo $this->escape(JText::_('VRTKZIPPLACEHOLDER2')); ?>" />
					
					<span>
						<a href="javascript: void(0);" class="" onClick="removeZipField(<?php echo $i; ?>);">
							<i class="fas fa-times big"></i>
						</a>
					</span>
				</div>
				<?php
			}
		}
		?>
	</div>

</div>

<div class="btn-toolbar">
	
	<div class="btn-group pull-left">
		<button type="button" class="btn" onClick="addZipField();">
			<?php echo JText::_('VRMANAGECONFIG29'); ?>
		</button>
	</div>

</div>

<?php
JText::script('VRTKZIPPLACEHOLDER1');
JText::script('VRTKZIPPLACEHOLDER2');
?>

<script>

	var ZIP_COUNT = <?php echo ($area->type == 3 ? count((array) $area->content) : 0); ?>;

	function addZipField() {

		jQuery('.vr-zip-container').append(
			'<div class="vrtk-entry-var" id="vrzip' + ZIP_COUNT + '">\n'+
				'<input type="text" name="from_zip[]" value="" size="12" class="form-control" placeholder="' + Joomla.JText._('VRTKZIPPLACEHOLDER1') + '" />\n'+
				'<input type="text" name="to_zip[]" value="" size="12" class="form-control" placeholder="' + Joomla.JText._('VRTKZIPPLACEHOLDER2') + '" />\n'+
				'<span>\n'+
					'<a href="javascript: void(0);" class="" onClick="removeZipField(' + ZIP_COUNT + ');">\n'+
						'<i class="fas fa-times big"></i>\n'+
					'</a>\n'+
				'</span>\n'+
			'</div>\n'
		);

		ZIP_COUNT++;
	}

	function removeZipField(id) {
		jQuery('#vrzip' + id).remove();
	}

</script>
