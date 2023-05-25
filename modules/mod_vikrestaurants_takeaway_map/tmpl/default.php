<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_map
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$shapes = VikRestaurants::getAllDeliveryAreas(true);

$module_id = $module->id;

// MAP SETTINGS

$width  = $params->get('width');
$height = $params->get('height');

$def_zoom       = $params->get('zoom');
$def_center_lat = $params->get('center_lat');
$def_center_lng = $params->get('center_lng');

$mapstyle = $params->get('mapstyle');

if ($mapstyle == 0)
{
	$mapstyle = "[{\"featureType\": \"landscape.natural.landcover\", \"elementType\": \"geometry\"}]";
}
else if ($mapstyle == 1)
{
	$mapstyle = "[{\"featureType\":\"landscape\",\"stylers\":[{\"saturation\":-100},{\"lightness\":65},{\"visibility\":\"on\"}]},{\"featureType\":\"poi\",\"stylers\":[{\"saturation\":-100},{\"lightness\":51},{\"visibility\":\"simplified\"}]},{\"featureType\":\"road.highway\",\"stylers\":[{\"saturation\":-100},{\"visibility\":\"simplified\"}]},{\"featureType\":\"road.arterial\",\"stylers\":[{\"saturation\":-100},{\"lightness\":30},{\"visibility\":\"on\"}]},{\"featureType\":\"road.local\",\"stylers\":[{\"saturation\":-100},{\"lightness\":40},{\"visibility\":\"on\"}]},{\"featureType\":\"transit\",\"stylers\":[{\"saturation\":-100},{\"visibility\":\"simplified\"}]},{\"featureType\":\"administrative.province\",\"stylers\":[{\"visibility\":\"off\"}]},{\"featureType\":\"water\",\"elementType\":\"labels\",\"stylers\":[{\"visibility\":\"on\"},{\"lightness\":-25},{\"saturation\":-100}]},{\"featureType\":\"water\",\"elementType\":\"geometry\",\"stylers\":[{\"hue\":\"#ffff00\"},{\"lightness\":-25},{\"saturation\":-97}]}]";
}
else if ($mapstyle == 2)
{
	$mapstyle = "[{\"featureType\":\"water\",\"stylers\":[{\"color\":\"#021019\"}]},{\"featureType\":\"landscape\",\"stylers\":[{\"color\":\"#08304b\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#0c4152\"},{\"lightness\":5}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#000000\"}]},{\"featureType\":\"road.highway\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#0b434f\"},{\"lightness\":25}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#000000\"}]},{\"featureType\":\"road.arterial\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#0b3d51\"},{\"lightness\":16}]},{\"featureType\":\"road.local\",\"elementType\":\"geometry\",\"stylers\":[{\"color\":\"#000000\"}]},{\"elementType\":\"labels.text.fill\",\"stylers\":[{\"color\":\"#ffffff\"}]},{\"elementType\":\"labels.text.stroke\",\"stylers\":[{\"color\":\"#000000\"},{\"lightness\":13}]},{\"featureType\":\"transit\",\"stylers\":[{\"color\":\"#146474\"}]},{\"featureType\":\"administrative\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"color\":\"#000000\"}]},{\"featureType\":\"administrative\",\"elementType\":\"geometry.stroke\",\"stylers\":[{\"color\":\"#144b53\"},{\"lightness\":14},{\"weight\":1.4}]}]";
}
else if ($mapstyle == 3)
{
	$mapstyle = "[{\"featureType\":\"landscape.natural\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"visibility\":\"on\"},{\"color\":\"#e0efef\"}]},{\"featureType\":\"poi\",\"elementType\":\"geometry.fill\",\"stylers\":[{\"visibility\":\"on\"},{\"hue\":\"#1900ff\"},{\"color\":\"#c0e8e8\"}]},{\"featureType\":\"landscape.man_made\",\"elementType\":\"geometry.fill\"},{\"featureType\":\"road\",\"elementType\":\"geometry\",\"stylers\":[{\"lightness\":100},{\"visibility\":\"simplified\"}]},{\"featureType\":\"road\",\"elementType\":\"labels\",\"stylers\":[{\"visibility\":\"off\"}]},{\"featureType\":\"water\",\"stylers\":[{\"color\":\"#7dcdcd\"}]},{\"featureType\":\"transit.line\",\"elementType\":\"geometry\",\"stylers\":[{\"visibility\":\"on\"},{\"lightness\":700}]}]";
}
else if ($mapstyle == 4)
{
	$mapstyle = "[{\"featureType\":\"landscape\",\"stylers\":[{\"hue\":\"#FFA800\"},{\"saturation\":0},{\"lightness\":0},{\"gamma\":1}]},{\"featureType\":\"road.highway\",\"stylers\":[{\"hue\":\"#53FF00\"},{\"saturation\":-73},{\"lightness\":40},{\"gamma\":1}]},{\"featureType\":\"road.arterial\",\"stylers\":[{\"hue\":\"#FBFF00\"},{\"saturation\":0},{\"lightness\":0},{\"gamma\":1}]},{\"featureType\":\"road.local\",\"stylers\":[{\"hue\":\"#00FFFD\"},{\"saturation\":0},{\"lightness\":30},{\"gamma\":1}]},{\"featureType\":\"water\",\"stylers\":[{\"hue\":\"#00BFFF\"},{\"saturation\":6},{\"lightness\":8},{\"gamma\":1}]},{\"featureType\":\"poi\",\"stylers\":[{\"hue\":\"#679714\"},{\"saturation\":33.4},{\"lightness\":-25.4},{\"gamma\":1}]}]";
}

$stylesize = 'style="width:100%;height:300px;"';

if (!empty($width) && !empty($height))
{
	$stylesize = 'style="width:' . $width . ';height:' . $height . ';"';
}

/**
 * In case of the number of locations are lower than 2
 * and the zoom level is empty, use a default value in 
 * order to display the map properly.
 *
 * @since 1.0.2
 */
if (count($locations) < 2 && !$def_zoom)
{
	$def_zoom = 12;
}

// DELIVERY

$enable_delivery   = $params->get('enable_delivery') && (JFactory::getApplication()->input->get('view') != 'takeawayconfirm');
$delivery_position = $enable_delivery ? $params->get('delivery_position') : '';
$delivery_text     = $params->get('delivery_text'); 

$itemid = $params->get('itemid', 0);

JText::script('VRTKMAPADDRNOTFOUND');
JText::script('VRTKMAPCONNECTERR');
JText::script('VRTKMAPGEOERRDENIED');
JText::script('VRTKMAPGEOERRNOTAV');
JText::script('VRTKMAPGEOERRTIMEOUT');
JText::script('VRTKMAPGEOERRUNKNOWN');
JText::script('VRTKMAPGEOERRNOTSUPP');
?>

<script type="text/javascript">

	jQuery.noConflict();
	
	jQuery(document).ready(function() {

		vrtkInitMap();

		jQuery('#vrtk-delivery-addr').on('change', function() {
			evaluateCoordinatesFromAddress(jQuery(this).val());
		});

		jQuery('#vrtk-address-icon').on('click', function() {
			vrtkGeoButtonClicked(this);
		});

		<?php
		if ($address)
		{
			?>
			vrtkMapUpdateMarker({
				lat: <?php echo (float) $address->latitude; ?>,
				lng: <?php echo (float) $address->longitude; ?>,
			});
			<?php
		}
		?>

	});

	var VRTK_MAP = null;
	var VRTK_ADDR_MARKER = null;

	function vrtkInitMap() {

		VRTK_MAP = new google.maps.Map(document.getElementById("vrtkgmap<?php echo $module_id; ?>"), {
			<?php echo (!empty($def_zoom) ? 'zoom: '.intval($def_zoom).', ' : '').
			(!empty($def_center_lat) && !empty($def_center_lng) ? 'center: new google.maps.LatLng('.floatval($def_center_lat).','.floatval($def_center_lng).'), ' : ''); ?>
			mapTypeId: google.maps.MapTypeId.ROADMAP, 
			scrollwheel: false, 
			styles:<?php echo $mapstyle; ?>
		});

		<?php
		if ($params->get('delivery_shapes'))
		{
			?>
			vrtkFillMapShapes(VRTK_MAP);
			<?php
		}
		?>

		var marker 			= null;
		var tooltip 		= null;
		var infoWindow 		= new google.maps.InfoWindow();
		var markerBounds 	= new google.maps.LatLngBounds();
	
		<?php
		foreach ($locations as $location)
		{
			?>	
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(<?php echo $location->latitude; ?>, <?php echo $location->longitude; ?>),
				map: VRTK_MAP,
				title: '<?php echo addslashes($location->name); ?>',
				<?php
				if(!empty($location->image))
				{
					?>
				icon: '<?php echo $location->image; ?>',
					<?php
				}
				?>
			});	

			markerBounds.extend(marker.position);

			google.maps.event.addListener(marker, 'click', (function(marker) {
				return function() {
					content = [];

					content.push('<h3><?php echo addslashes($location->name); ?></h3>');

					<?php
					if ($location->description)
					{
						?>
						content.push('<div class="marker-desc"><?php echo addslashes($location->description); ?></div>');
						<?php
					}
					?>
					
					content.push('<div class="marker-addr"><?php echo addslashes($location->address); ?></div>');

					// wrap contents into a parent div
					content = jQuery('<div class="vrtk-map-apinfow"></div>').html(content);

					infoWindow.setContent(content.html());
					infoWindow.open(VRTK_MAP, marker);
				}
			})(marker));
			<?php
		}

		if (empty($def_zoom) || empty($def_center_lng) || empty($def_center_lat))
		{
			if (count($locations) > 1 && empty($def_zoom))
			{
				?>
				VRTK_MAP.fitBounds(markerBounds);
				<?php
			}
			?>
			VRTK_MAP.setCenter(markerBounds.getCenter());
			<?php
		}
		?>
	}

	var MAP_SHAPES = <?php echo json_encode($shapes); ?>;

	function vrtkFillMapShapes(map) {
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

				shapes.push( 
					new google.maps.Circle({
						strokeColor: MAP_SHAPES[i].attributes.strokecolor,
						strokeOpacity: 0.5,
						strokeWeight: MAP_SHAPES[i].attributes.strokeweight,
						fillColor: MAP_SHAPES[i].attributes.color,
						fillOpacity: 0.20,
						map: map,
						center: new google.maps.LatLng(MAP_SHAPES[i].content.center.latitude, MAP_SHAPES[i].content.center.longitude),
						radius: MAP_SHAPES[i].content.radius * 1000,
						clickable: false,
					})
				);

			}

		}

	}

	function evaluateCoordinatesFromAddress(address) {
		
		if (VRTK_ADDR_MARKER !== null) {
			VRTK_ADDR_MARKER.setMap(null);
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

				// extract data from best result
				var components = VikGeo.extractDataFromPlace(results[0]);
				// include full address
				components.fullAddress = results[0].formatted_address;

				vrtkMapUpdateMarker(coord);

				getLocationDeliveryInfo(coord, components);
			} else {
				<?php
				/**
				 * Raise an error message as it wasn't possible
				 * to find the specified address.
				 *
				 * @since 1.0.2
				 */
				?>
				jQuery('#vrtk-map-response .fail').html(Joomla.JText._('VRTKMAPADDRNOTFOUND'));
				jQuery('#vrtk-map-response .fail').slideDown();
			}
		});
	}

	function getLocationDeliveryInfo(coord, address) {

		jQuery('#vrtk-map-response .esit').hide();

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=get_location_delivery_info&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				lat:     coord.lat, 
				lng:     coord.lng,
				zip:     address.zip,
				city:    address.city,
				address: address,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				if (obj.status == 1) {

					jQuery('#vrtk-area-name').html(obj.area.name);
					jQuery('#vrtk-area-charge').html(obj.area.fullChargeLabel);
					jQuery('#vrtk-area-mincost').html(obj.area.minCostLabel);

					jQuery('#vrtk-map-response .success').slideDown();

				} else {
					jQuery('#vrtk-map-response .fail').html(obj.error);
					jQuery('#vrtk-map-response .fail').slideDown();
				}
			},
			function(error) {
				alert(Joomla.JText._('VRTKMAPCONNECTERR'));
			}
		);
	}

	var VRTK_ADDRESS_STR = null;

	function vrtkGeoButtonClicked(btn) {

		if (VRTK_ADDRESS_STR !== null) {
			jQuery('#vrtk-delivery-addr').val(VRTK_ADDRESS_STR);
			jQuery('#vrtk-delivery-addr').trigger('change');
			return;
		}

		jQuery('#vrtk-delivery-addr').val('');
		
		// Try HTML5 geolocation
		if (navigator.geolocation) {

			jQuery('#vrtk-delivery-addr').prop('readonly', true);

			navigator.geolocation.getCurrentPosition(function(position) {

				var geocoder = new google.maps.Geocoder();

				var latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

				geocoder.geocode({'latLng': latLng}, function(results, status) {

					if (status == 'OK') {
						VRTK_ADDRESS_STR = results[0].formatted_address;
					} else {
						VRTK_ADDRESS_STR = position.coords.latitude + ', ' + position.coords.longitude;
					}

					jQuery(btn).addClass('active');
					jQuery('#vrtk-delivery-addr').val(VRTK_ADDRESS_STR);
					jQuery('#vrtk-delivery-addr').trigger('change');

					jQuery('#vrtk-delivery-addr').prop('readonly', false);
				});
				
			}, function(err) {
				switch(err.code) {
					case err.PERMISSION_DENIED:
						//alert(Joomla.JText._('VRTKMAPGEOERRDENIED'));
						break;
					case err.POSITION_UNAVAILABLE:
						alert(Joomla.JText._('VRTKMAPGEOERRNOTAV'));
						break;
					case err.TIMEOUT:
						alert(Joomla.JText._('VRTKMAPGEOERRTIMEOUT'));
						break;
					default:
						alert(Joomla.JText._('VRTKMAPGEOERRUNKNOWN'));
				}

				jQuery('#vrtk-delivery-addr').prop('readonly', false);
			}, {
				enableHighAccuracy: true
			});
		} else {
			alert(Joomla.JText._('VRTKMAPGEOERRNOTSUPP'));
		}
	}

	function vrtkMapUpdateMarker(coord) {
		if (VRTK_ADDR_MARKER) {
			// update position of existing marker
			VRTK_ADDR_MARKER.setPosition(coord);
		} else {
			// create marker from scratch
			VRTK_ADDR_MARKER = new google.maps.Marker({
				position: coord,
			});
		}

		VRTK_ADDR_MARKER.setAnimation(google.maps.Animation.DROP);
		VRTK_ADDR_MARKER.setMap(VRTK_MAP);

		VRTK_MAP.setCenter(VRTK_ADDR_MARKER.position);
	}

</script>

<div class="vrtk-map-container <?php echo $params->get('moduleclass_sfx'); ?> <?php echo $delivery_position; ?>">
	
	<?php
	if ($enable_delivery)
	{
		// check if we are under HTTPS
		$ssl = JUri::getInstance()->isSSL();
		?>
		<div class="vrtk-map-delivery-search <?php echo $delivery_position; ?>">
			
			<div class="map-fieldset">
				<h3><?php echo JText::_('VRTKMAPDELIVERYHEAD'); ?></h3>

				<div class="input-address-container">
					<input type="text" id="vrtk-delivery-addr" value="" autocomplete="off" placeholder="<?php echo htmlspecialchars(JText::_('VRTKMAPADDRPLACEHOLDER')); ?>" class="<?php echo $ssl ? '' : 'no-search'; ?>" />
					
					<?php
					/**
					 * Display the icon to retrieve the current position
					 * only in case the website owns a SSL connection.
					 * Otherwise the following error would be raised:
					 * "Access to geolocation was blocked over insecure connection"
					 *
					 * @since 1.1
					 */
					if ($ssl)
					{
						?>
						<i class="address-icon" id="vrtk-address-icon"></i>
						<?php
					}
					?>
				</div>
			</div>

			<div class="map-response" id="vrtk-map-response">

				<div class="fail esit" style="display: none;"></div>

				<div class="success esit" style="display: none;">
						
					<div class="info-block">
						<div class="info-label"><?php echo JText::_('VRTKMAPAREANAME'); ?></div>
						<div class="info-value" id="vrtk-area-name"></div>
					</div>

					<div class="info-block">
						<div class="info-label"><?php echo JText::_('VRTKMAPAREACHARGE'); ?></div>
						<div class="info-value" id="vrtk-area-charge"></div>
					</div>

					<div class="info-block">
						<div class="info-label"><?php echo JText::_('VRTKMAPAREAMINCOST'); ?></div>
						<div class="info-value" id="vrtk-area-mincost"></div>
					</div>

				</div>

			</div>

		</div>
		<?php
	}
	?>

	<div class="vrtk-map-content">	
		<div id="vrtkgmap<?php echo $module_id; ?>" <?php echo $stylesize; ?>></div>
	</div>

</div>

<?php
if (strlen($delivery_text))
{
	?>
	<div class="vrtk-map-undertext"><?php echo $delivery_text; ?></div>
	<?php
}
