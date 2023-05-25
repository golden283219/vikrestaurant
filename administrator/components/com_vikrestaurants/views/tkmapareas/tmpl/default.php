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

JHtml::_('vrehtml.assets.googlemaps');
JHtml::_('vrehtml.assets.toast', 'top-left');

$vik = VREApplication::getInstance();

?>

<div class="control-group"><div id="googlemap" class="gm-fixed"></div></div>

<div class="vr-map-address-box">

	<input type="text" name="address" value="" id="vraddress" autocomplete="off" size="64" placeholder="<?php echo JText::_('VRTKMAPTESTADDRESS'); ?>" />

	<div class="vr-map-address-box-response" style="display: none;"></div>

</div>

<!-- HIDDEN LINKS -->

<a href="index.php?option=com_vikrestaurants&view=editconfig#googleapikey" id="google-error-link" style="display:none;"></a>

<?php
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRE_GOOGLE_API_KEY_ERROR');
?>

<script type="text/javascript">

	var MAP_SHAPES = <?php echo json_encode($this->shapes); ?>;
	
	jQuery(document).ready(function(){

		// load map with 256 milliseconds of delay in order to allow Google
		// to read the correct size of the screen
		setTimeout(initializeMap, 256);

		jQuery('#vraddress').on('change', function(){
			evaluateCoordinatesFromAddress(jQuery(this).val());
		});

		var response = jQuery('.vr-map-address-box-response');

		jQuery('#vraddress').on('input propertychange paste', function(){
			if (response.is(':visible')) {
				response.slideUp();
			}
		});

		// display error in case Google fails the authentication
		jQuery(window).on('google.autherror', function() {
			// display alert
			ToastMessage.dispatch({
				text:   Joomla.JText._('VRE_GOOGLE_API_KEY_ERROR'),
				status: 2,
				delay:  20000,
				style: {
					// do not use BOLD to make text more readable
					'font-weight': 'normal',
				},
				action: function(event) {
					// Go to configuration page and focus the API Key setting.
					// Extract HREF from link in order to use the correct platform URL.
					window.parent.location.href = jQuery('#google-error-link').attr('href');
				},
			});
		});

	});

	var map = null;
	var marker = null;

	function initializeMap() {
		
		map = new google.maps.Map(document.getElementById('googlemap'), {
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
		});

		// get bounds handler
		var markerBounds = new google.maps.LatLngBounds();

		// Define the LatLng coordinates for the polygon's path.

		var shapes = [];
		var coords = [];

		for (var i = 0; i < MAP_SHAPES.length; i++) {

			if (MAP_SHAPES[i].type == 1) {

				coords = [];

				for (var j = 0; j < MAP_SHAPES[i].content.length; j++) {
					coords.push({
						lat: parseFloat(MAP_SHAPES[i].content[j].latitude),
						lng: parseFloat(MAP_SHAPES[i].content[j].longitude),
					});
				}

				shapes.push(
					new google.maps.Polygon({
						paths: coords,
						strokeColor: MAP_SHAPES[i].attributes.strokecolor,
						strokeOpacity: 0.5,
						strokeWeight: MAP_SHAPES[i].attributes.strokeweight,
						fillColor: MAP_SHAPES[i].attributes.color,
						fillOpacity: 0.20,
						map: map,
						clickable: false,
					})
				);

			} else if (MAP_SHAPES[i].type == 2) {

				coords = [{
					lat: MAP_SHAPES[i].content.center.latitude,
					lng: MAP_SHAPES[i].content.center.longitude,
				}];

				shapes.push( 
					new google.maps.Circle({
						strokeColor: MAP_SHAPES[i].attributes.strokecolor,
						strokeOpacity: 0.5,
						strokeWeight: MAP_SHAPES[i].attributes.strokeweight,
						fillColor: MAP_SHAPES[i].attributes.color,
						fillOpacity: 0.20,
						map: map,
						center: coords[0],
						radius: MAP_SHAPES[i].content.radius * 1000,
						clickable: false,
					})
				);

			}

			for (var k = 0; k < coords.length; k++) {
				markerBounds.extend(new google.maps.LatLng(coords[k].lat, coords[k].lng));
			}

		}

		if (coords.length > 1) {
			map.fitBounds(markerBounds);
			map.setCenter(markerBounds.getCenter());
		} else if (coords.length == 1) {
			map.setCenter(markerBounds.getCenter());
			map.setZoom(14);
		} else {
			// recover user current position
			VikGeo.getCurrentPosition().then((data) => {
				// prepare center object
				var center = new google.maps.LatLng(data.lat, data.lng);

				// set map center
				map.setCenter(center);
				map.setZoom(14);
			});
		}
	}

	function evaluateCoordinatesFromAddress(address) {

		if (marker !== null) {
			marker.setMap(null);
		}

		if (address.length == 0) {
			return;
		}

		var geocoder = new google.maps.Geocoder();

		var coord = null;

		geocoder.geocode({'address': address}, function(results, status) {
			if (status == 'OK') {
				coord = {
					lat: results[0].geometry.location.lat(),
					lng: results[0].geometry.location.lng(),
				};

				// extract components from place
				var data = VikGeo.extractDataFromPlace(results[0]);

				marker = new google.maps.Marker({
					position: coord,
				});

				marker.setAnimation(google.maps.Animation.DROP);
				marker.setMap(map);

				map.setCenter(marker.position);

				// inject city and zip into coordinates object
				coord.zip  = data.zip;
				coord.city = data.city;

				getLocationDeliveryInfo(coord);
			}
		});
	}

	function getLocationDeliveryInfo(coord) {
		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=tkarea.getinfoajax&tmpl=component',
			coord,
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				jQuery('.vr-map-address-box-response').html(obj);
				jQuery('.vr-map-address-box-response').slideDown();
			},
			function(error) {
				alert(Joomla.JText._('VRSYSTEMCONNECTIONERR'));
			}
		);
	}
	
</script>
