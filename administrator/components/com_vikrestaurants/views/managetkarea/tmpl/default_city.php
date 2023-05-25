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
	
	<div class="vr-city-container">
		<?php
		if ($area->type == 4)
		{
			foreach ($area->content as $i => $city)
			{
				?>
				<div class="vrtk-entry-var" id="vrcity<?php echo $i; ?>">
					<input type="text" name="city[]" value="<?php echo $city; ?>" placeholder="<?php echo $this->escape(JText::_('VRCUSTFIELDRULE7')); ?>" size="28" class="form-control" />

					<span>
						<a href="javascript: void(0);" class="" onClick="removeCityField(<?php echo $i; ?>);">
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
		<button type="button" class="btn" onClick="addCityField();">
			<?php echo JText::_('VRTKAREACITYADD'); ?>
		</button>
	</div>

</div>

<?php
JText::script('VRCUSTFIELDRULE7');
?>

<script>

	var CITY_COUNT = <?php echo ($area->type == 4 ? count((array) $area->content) : 0); ?>;

	function addCityField() {

		jQuery('.vr-city-container').append(
			'<div class="vrtk-entry-var" id="vrcity' + CITY_COUNT + '">\n'+
				'<input type="text" name="city[]" value="" placeholder="' + Joomla.JText._('VRCUSTFIELDRULE7') + '" size="28" class="form-control" />\n'+
				'<span>\n'+
					'<a href="javascript: void(0);" class="" onClick="removeCityField(' + CITY_COUNT + ');">\n'+
						'<i class="fas fa-times big"></i>\n'+
					'</a>\n'+
				'</span>\n'+
			'</div>\n'
		);

		CITY_COUNT++;
	}

	function removeCityField(id) {
		jQuery('#vrcity' + id).remove();
	}

</script>
