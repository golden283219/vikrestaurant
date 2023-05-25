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
	
	<div class="vr-polygon-container vrtk-entry-variations">
		<?php
		if ($area->type == 1)
		{
			foreach ($area->content as $i => $point)
			{
				?>
				<div id="vrpoint<?php echo $i; ?>" class="vrtk-entry-var control">
					<span class="manual-sort-handle hidden-phone"><i class="fas fa-ellipsis-v"></i></span>

					<input type="number" name="polygon_latitude[]" value="<?php echo (isset($point->latitude) ? $point->latitude : ''); ?>"
						id="vrpointlat<?php echo $i; ?>" class="vr-polygon-point form-control" step="any" data-id="<?php echo $i; ?>" placeholder="<?php echo $this->escape(JText::_('VRMANAGETKAREA7')); ?>" />

					<input type="number" name="polygon_longitude[]" value="<?php echo (isset($point->longitude) ? $point->longitude : ''); ?>"
						id="vrpointlng<?php echo $i; ?>" class="vr-polygon-point form-control" step="any" data-id="<?php echo $i; ?>" placeholder="<?php echo $this->escape(JText::_('VRMANAGETKAREA8')); ?>" />

					<span>
						<a href="javascript: void(0);" onClick="getUserCoordinates(<?php echo $i; ?>);" style="text-decoration:none;">
							<i class="fas fa-location-arrow big"></i>
						</a>
					</span>

					<span>
						<a href="javascript: void(0);" onClick="removePolygonPoint(<?php echo $i; ?>);" style="text-decoration:none;">
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
		<button type="button" class="btn" onClick="addPolygonPoint();">
			<?php echo JText::_('VRMANAGETKAREA11'); ?>
		</button>
	</div>

	<div class="btn-group pull-left">
		<?php
		echo $vik->createPopover(array(
			'title'   => JText::_('VRTKAREATYPE1'),
			'content' => JText::_('VRTKAREA_POLYGON_LEGEND_HELP'),
		));
		?>
	</div>

	<div class="btn-group pull-right">
		<button type="button" class="btn" onClick="togglePolygonCoordinates();">
			<?php echo JText::_('VRMANAGETKAREA13'); ?>
		</button>
	</div>

</div>

<?php
JText::script('VRMANAGETKAREA7');
JText::script('VRMANAGETKAREA8');
JText::script('VRTKAREAUSERPOSITION');
?>

<script>

	var POLYGON_MAP          = null;
	var POLYGON_SHAPE        = null;
	var POLYGON_MARKERS      = {};
	var POLYGON_POINTS_COUNT = <?php echo ($area->type == 1 ? count((array) $area->content) : 0); ?>;

	<?php
	if ($area->type == 1)
	{
		foreach ($area->content as $i => $point)
		{
			echo sprintf(
				"updatePolygonMarker(%d, %s, %s)\n",
				$i,
				$point->latitude,
				$point->longitude
			);
		}
	}
	?>

	jQuery(document).ready(function(){

		<?php if ($area->type == 1) { ?>
			initializePolygonMap();
		<?php } ?>

		jQuery('.vr-polygon-point').on('change', function(){
			coordinateValueChanged(jQuery(this).data('id'));
		});

		jQuery('.vr-polygon-container').sortable({
			axis:   'y',
			cursor: 'move',
			handle: '.manual-sort-handle',
			revert: false,
			stop: function() {
				// refresh polygon
				refreshPolygonShape();
			},
		});

		jQuery('.vr-attribute-field').on('change', function() {
			if (!POLYGON_SHAPE) {
				return false;
			}

			var options = {};

			// fetch fill color
			options.fillColor = jQuery('input[name="color"]').val();
			if (!options.fillColor) {
				options.fillColor = '#FF0000';
			}

			// fetch border color
			options.strokeColor = jQuery('input[name="strokecolor"]').val();
			if (!options.strokeColor) {
				options.strokeColor = fillColor;
			}

			// fetch border width
			options.strokeWeight = parseInt(jQuery('input[name="strokeweight"]').val());
			if (isNaN(options.strokeWeight) || options.strokeWeight < 0) {
				options.strokeWeight = 2;
			}

			// update polygon style
			POLYGON_SHAPE.setOptions(options);
		});

	});

	function togglePolygonCoordinates() {
		if (jQuery('.vr-polygon-container').is(':visible')) {
			jQuery('.vr-polygon-container').slideUp();
		} else {
			jQuery('.vr-polygon-container').slideDown();
		}
	}

	function initializePolygonMap() {
		var prop = {
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			clickableIcons: false,
		};
		
		POLYGON_MAP = new google.maps.Map(document.getElementById('polygon-googlemap'), prop);

		// get bounds handler
		var markerBounds = new google.maps.LatLngBounds();
		var position = null;

		// define the LatLng coordinates for the polygon's path
		var shape_coordinates = getPolygonPoints();

		for (var i = 0; i < shape_coordinates.length; i++) {
			position = new google.maps.LatLng(shape_coordinates[i].lat, shape_coordinates[i].lng);
			
			markerBounds.extend(position);
		}

		var fillColor = jQuery('input[name="color"]').val();
		if (!fillColor.length) {
			fillColor = '#FF0000';
		}

		var strokeColor = jQuery('input[name="strokecolor"]').val();
		if (!strokeColor.length) {
			strokeColor = '#FF0000';
		}

		var strokeWeight = parseInt(jQuery('input[name="strokeweight"]').val());
		if (isNaN(strokeWeight)) {
			strokeWeight = 2;
		}

		// fill all areas
		fillMapShapes();

		// construct the polygon
		POLYGON_SHAPE = new google.maps.Polygon({
			paths: shape_coordinates,
			strokeColor: strokeColor,
			strokeOpacity: 0.8,
			strokeWeight: strokeWeight,
			fillColor: fillColor,
			fillOpacity: 0.35,
			clickable: false,
			map: POLYGON_MAP,
		});

		POLYGON_MAP.fitBounds(markerBounds);
		POLYGON_MAP.setCenter(markerBounds.getCenter());

		for (var k in POLYGON_MARKERS) {
			if (POLYGON_MARKERS.hasOwnProperty(k)) {
				POLYGON_MARKERS[k].setMap(POLYGON_MAP);
			}
		} 

		POLYGON_MAP.addListener('click', function(e) {
			polygonMapClickListener(e.latLng.lat(), e.latLng.lng());
		});
	}

	function addPolygonPoint(lat, lng) {
		if (lat === undefined) {
			lat = '';
		}

		if (lng === undefined) {
			lng = '';
		}

		jQuery('.vr-polygon-container').append(
			'<div id="vrpoint' + POLYGON_POINTS_COUNT + '" class="vrtk-entry-var control"\n>'+
				'<span class="manual-sort-handle hidden-phone"><i class="fas fa-ellipsis-v"></i></span>\n'+
				'<input type="number" name="polygon_latitude[]" value="' + lat + '" id="vrpointlat' + POLYGON_POINTS_COUNT + '" class="vr-polygon-point form-control" step="any" data-id="' + POLYGON_POINTS_COUNT + '" placeholder="' + Joomla.JText._('VRMANAGETKAREA7') + '" />\n'+
				'<input type="number" name="polygon_longitude[]" value="' + lng + '" id="vrpointlng' + POLYGON_POINTS_COUNT + '" class="vr-polygon-point form-control" step="any" data-id="' + POLYGON_POINTS_COUNT + '" placeholder="' + Joomla.JText._('VRMANAGETKAREA8') + '" />\n'+
				'<span>\n'+
					'<a href="javascript: void(0);" onClick="getUserCoordinates(' + POLYGON_POINTS_COUNT + ');" style="text-decoration:none;">\n'+
						'<i class="fas fa-location-arrow big"></i>\n'+
					'</a>\n'+
				'</span>\n'+
				'<span>\n'+
					'<a href="javascript: void(0);"onClick="removePolygonPoint(' + POLYGON_POINTS_COUNT + ');" style="text-decoration:none;">\n'+
						'<i class="fas fa-times big"></i>\n'+
					'</a>\n'+
				'</span>\n'+
			'</div>\n'
		);

		jQuery('#vrpoint' + POLYGON_POINTS_COUNT).find('.vr-polygon-point').on('change', function() {
			coordinateValueChanged(jQuery(this).data('id'));
		});

		if (lat && lng) {
			updatePolygonMarker(POLYGON_POINTS_COUNT, lat, lng);
		}

		POLYGON_POINTS_COUNT++;
	}

	function removePolygonPoint(id) {
		jQuery('#vrpoint' + id).remove();

		if (POLYGON_MARKERS.hasOwnProperty(id)) {
			POLYGON_MARKERS[id].setMap(null);
			delete POLYGON_MARKERS[id];
		}

		refreshPolygonShape();
	}

	function getPolygonPoints() {
		var points = [];

		var p = null, value = null;

		jQuery('.vr-polygon-point').each(function(k, v){
			if (k % 2 == 0) {
				p = {};
				p.lat = parseFloat(jQuery(v).val());
			} else {
				p.lng = parseFloat(jQuery(v).val());

				if (!isNaN(p.lat) && !isNaN(p.lng)) {
					points.push(p);
				}
			}
		});

		return points;
	}

	function polygonMapClickListener(lat, lng) {
		var latInput = jQuery('input[name="polygon_latitude[]"]').last();
		var lngInput = jQuery('input[name="polygon_longitude[]"]').last();

		if (!latInput.length || !lngInput.length || (latInput.val().length && lngInput.val().length)) {
			// add new point in case the list is empty or in case
			// the last added point has been already filled in
			addPolygonPoint(lat, lng);
		} else {
			// otherwise update coordinates of last element
			polygonCoordHandler(latInput.data('id'), lat, lng);
		}
	}

	function getUserCoordinates(index) {
		// retrieve user coordinates
		VikGeo.getCurrentPosition().then(function(coord) {
			// coordinates retrieved, change circle center
			polygonCoordHandler(index, coord.lat, coord.lng);
		}).catch(function(error) {
			// unable to obtain current position, show error
			alert(error);
		});
	}

	function polygonCoordHandler(index, lat, lng, confirmed) {
		var curr_lat = jQuery('#vrpointlat' + index).val();
		
		var r = true;

		if (curr_lat.length) {
			// skip confirmation in case the 3rd argument is specified
			r = confirmed || confirm(Joomla.JText._('VRTKAREAUSERPOSITION'));
		}
		
		if (r) {
			jQuery('#vrpointlat' + index).val(lat);
			jQuery('#vrpointlng' + index).val(lng);

			if (!POLYGON_MAP) {
				initializePolygonMap();
			}

			updatePolygonMarker(index, lat, lng);
		}
	}

	function updatePolygonMarker(index, lat, lng) {
		var coord = new google.maps.LatLng(lat, lng);

		if (POLYGON_MARKERS.hasOwnProperty(index)) {
			POLYGON_MARKERS[index].setPosition(coord);
		} else {
			// origins, anchor positions and coordinates of the marker increase in the X
			// direction to the right and in the Y direction down
			var image = {
			  	url: '<?php echo VREASSETS_ADMIN_URI; ?>images/pin-circle.png',
			  	// this marker is 16x16 pixel
			  	size: new google.maps.Size(16, 16),
			  	// the origin for this image is (0, 0)
			  	origin: new google.maps.Point(0, 0),
			  	// the anchor for this image is the center of the pin
			  	anchor: new google.maps.Point(8, 8)
			};

			var marker = new google.maps.Marker({
				position: coord,
				draggable: true,
				map: POLYGON_MAP,
				icon: image,
			});

			// update vertex position after dragging the marker
			marker.addListener('dragend', function(e) {
		        var coord = marker.getPosition();

		        polygonCoordHandler(index, coord.lat(), coord.lng(), true);
		    });

		    marker.addListener('click', function(e) {
		    	jQuery('#vrpointlat' + index).select().focus();
		    });

			POLYGON_MARKERS[index] = marker;
		}

		refreshPolygonShape();
	}

	function refreshPolygonShape() {
		if (POLYGON_SHAPE) {
			POLYGON_SHAPE.setPaths(getPolygonPoints());
		}
	}

	function coordinateValueChanged(index) {
		var lat = jQuery('#vrpointlat' + index);
		var lng = jQuery('#vrpointlng' + index);

		if (!POLYGON_MAP) {
			initializePolygonMap();
		}

		updatePolygonMarker(index, lat.val(), lng.val());
	}

</script>
