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

if ($origin->latitude === null || $origin->longitude === null)
{
	// display notice
	echo $vik->alert(JText::_('VRE_ORIGIN_COORD_INFO'), 'info', false, array('id' => 'origin-map-warning'));
}

?>

<div id="origin-googlemap" style="width:100%; height:400px;<?php echo ($origin->latitude === null ? 'display:none;' : ''); ?>"></div>

<script>

	(function($) {
		'use strict';

		let map, marker, infoWindow;

		<?php
		if ($origin->latitude !== null && $origin->longitude !== null)
		{
			?>
			let originLat = <?php echo floatval($origin->latitude); ?>;
			let originLng = <?php echo floatval($origin->longitude); ?>;
			<?php
		}
		else
		{
			?>
			let originLat = '';
			let originLng = '';
			<?php
		}
		?>
		
		window['changeOriginLatLng'] = (lat, lng) => {
			originLat = lat;
			originLng = lng;

			if (originLat.length == 0 || originLng.length == 0) {
				originLat = originLng = '';
			}

			initializeMap();
		}

		window['changeOriginTitle'] = (title) => {
			if (marker) {
				marker.setTitle(name);
			}
		}

		window['changeOriginIcon'] = (icon) => {
			if (marker) {
				if (icon.length) {
					marker.setIcon('<?php echo JUri::root(); ?>' + icon);
				} else {
					marker.setIcon(null);
				}
			}
		}
		
		const initializeMap = () => {
			if (originLat.length == 0) {
				$('#origin-googlemap').hide();
				$('#origin-map-warning').show();
				return;
			}

			const coord = new google.maps.LatLng(originLat, originLng);

			$('#origin-map-warning').hide();

			if (map) {
				// map already created, just display it
				$('#origin-googlemap').show();
				// and update the marker
				marker.setAnimation(google.maps.Animation.DROP);
				marker.setPosition(coord);
				map.setCenter(coord);
				return;
			}
			
			const mapProp = {
				center: coord,
				zoom: 17,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
			};
			
			map = new google.maps.Map($('#origin-googlemap')[0], mapProp);

			// create marker
			marker = new google.maps.Marker({
				position: coord,
				draggable: true,
				title: $('input[name="name"]').val(),
			});

			let icon = $('input[name="image"]').val();

			if (icon.length) {
				marker.setIcon('<?php echo JUri::root(); ?>' + icon);
			}

			// update circle position after dragging the marker
			marker.addListener('dragend', (e) => {
				const markerCoord = marker.getPosition();

				$('input[name="latitude"]').val(markerCoord.lat());
				$('input[name="longitude"]').val(markerCoord.lng());
			});

			infoWindow = new google.maps.InfoWindow();

			marker.addListener('click', (e) => {
				content = [
					$('<h3></h3>').html($('input[name="name"]').val()).html(),
					$('textarea[name="description"]').val(),
					$('input[name="address"]').val(),
				].filter((c) => {
					return c.length;
				}).join("<br /><br />");

				infoWindow.setContent(content);
				infoWindow.open(map, marker);
			});
			
			marker.setMap(map);
			
			$('#origin-googlemap').show();
		}

		$(function() {
			initializeMap();
		});
	})(jQuery);
	
</script>
