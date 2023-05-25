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

$origin = $this->origin;

$vik = VREApplication::getInstance();

?>

<!-- NAME -->

<?php echo $vik->openControl(JText::_('VRMANAGELANG2') . '*'); ?>
	<input type="text" name="name" value="<?php echo $this->escape($origin->name); ?>" class="required" size="40" />
<?php echo $vik->closeControl(); ?>

<!-- ADDRESS -->

<?php echo $vik->openControl(JText::_('VRCUSTFIELDRULE4') . '*'); ?>
	<input type="text" name="address" value="<?php echo $this->escape($origin->address); ?>" class="required" size="40" />
<?php echo $vik->closeControl(); ?>

<!-- LATITUDE -->

<?php echo $vik->openControl(JText::_('VRMANAGETKAREA7')); ?>
	<input type="number" name="latitude" value="<?php echo $this->escape($origin->latitude); ?>" size="40" step="any" />
<?php echo $vik->closeControl(); ?>

<!-- LONGITUDE -->

<?php echo $vik->openControl(JText::_('VRMANAGETKAREA8')); ?>
	<input type="number" name="longitude" value="<?php echo $this->escape($origin->longitude); ?>" size="40" step="any" />
<?php echo $vik->closeControl(); ?>

<!-- PUBLISHED - Number -->
<?php
$elem_yes = $vik->initRadioElement('', '', $origin->published);
$elem_no  = $vik->initRadioElement('', '', !$origin->published);

echo $vik->openControl(JText::_('VRMANAGETKTOPPING3'));
echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
echo $vik->closeControl();
?>

<!-- IMAGE UPLOAD - Media Manager -->

<?php
$help = $vik->createPopover([
	'title'   => JText::_('VRE_ORIGIN_MARKER_IMAGE'),
	'content' => JText::_('VRE_ORIGIN_MARKER_IMAGE_DESC'), 
]);

echo $vik->openControl(JText::_('VRE_ORIGIN_MARKER_IMAGE') . $help);
echo $vik->getMediaField('image', $origin->image);
echo $vik->closeControl(); ?>

<!-- DESCRIPTION -->

<?php
$help = $vik->createPopover([
	'title'   => JText::_('VRMANAGELANG3'),
	'content' => JText::_('VRE_ORIGIN_DESCRIPTION_SCOPE'),
]);

echo $vik->openControl(JText::_('VRMANAGELANG3') . $help); ?>
	<textarea name="description" style="width: 90%; height: 180px; resize: vertical;"><?php echo $origin->description; ?></textarea>
<?php echo $vik->closeControl(); ?>

<script>

	(function($) {
		'use strict';

		// register Google Autocomplete
		$(function() {
			if (typeof google === 'undefined' || typeof google.maps.places === 'undefined') {
				// Missing Google API Key or Places API not enabled, do not proceed
				return false;
			}

			$('input[name="name"]').on('change', function() {
				changeOriginTitle($('input[name="name"]').val());
			});

			$('input[name="image"]').on('change', function() {
				changeOriginIcon($('input[name="image"]').val());
			});

			$('input[name="latitude"], input[name="longitude"]').on('change', () => {
				changeOriginLatLng(
					$('input[name="latitude"]').val(),
					$('input[name="longitude"]').val()
				);
			});

			<?php
			if (VikRestaurants::isGoogleMapsApiEnabled('places'))
			{
				// include JavaScript code to support the addresses autocompletion
				// only in case the Places API is enabled in the configuration
				?>
				const input = $('input[name="address"]')[0];

				// use Google Autocomplete feature
				const googleAddress = new google.maps.places.Autocomplete(
					input, {}
				);

				googleAddress.addListener('place_changed', function() {
					const place = googleAddress.getPlace();

					// auto-fill latitude and longitude
					if (place.geometry) {
						$('input[name="latitude"]').val(place.geometry.location.lat());
						$('input[name="longitude"]').val(place.geometry.location.lng()).trigger('change');
					}
				});

				$(window).on('google.autherror google.apidisabled.places', () => {
					// disable autocomplete on failure
					VikMapsFailure.disableAutocomplete(input, googleAddress);
				});

				VikGeo.getCurrentPosition().then((coord) => {
					// coordinates retrieved, set up google bounds
					const circle = new google.maps.Circle({
						center: coord,
						radius: 100,
					});

		  			googleAddress.setBounds(circle.getBounds());
				}).catch((error) => {
					// unable to obtain current position, show error
					console.error(error);
				});
				<?php
			}
			?>
		});
	})(jQuery);

</script>
