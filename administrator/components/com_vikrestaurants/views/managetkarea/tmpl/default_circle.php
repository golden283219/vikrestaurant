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
				
<!-- CENTER - Number -->
<?php
$help = $vik->createPopover(array(
	'title'   => JText::_('VRMANAGETKAREA6'),
	'content' => JText::_('VRTKAREA_CIRCLE_LATLNG_HELP'),
));

echo $vik->openControl(JText::_('VRMANAGETKAREA6') . '*' . $help, 'multi-field'); ?>

	<input type="number" name="center_latitude" value="<?php echo (isset($area->content->center) ? $area->content->center->latitude : ''); ?>" step="any" placeholder="<?php echo JText::_('VRMANAGETKAREA7'); ?>" class="vr-circle-field <?php echo ($area->type == 2 ? 'required' : ''); ?>"/>
	<input type="number" name="center_longitude" value="<?php echo (isset($area->content->center) ? $area->content->center->longitude : ''); ?>" step="any" placeholder="<?php echo JText::_('VRMANAGETKAREA8'); ?>" class="vr-circle-field <?php echo ($area->type == 2 ? 'required' : ''); ?>"/>
	
	<a href="javascript: void(0);" id="circle-get-coords"  style="margin-left: 10px;text-decoration: none;">
		<i class="fas fa-location-arrow big"></i>
	</a>

<?php echo $vik->closeControl(); ?>

<!-- RADIUS - Number -->
<?php echo $vik->openControl(JText::_('VRMANAGETKAREA9') . '*'); ?>
	<div class="input-append">
		<input type="number" name="radius" value="<?php echo (isset($area->content->radius) ? $area->content->radius : 1); ?>" step="any" min="0" class="vr-circle-field <?php echo ($area->type == 2 ? 'required' : ''); ?>" />
		
		<button type="button" class="btn">km</button>
	</div>
<?php echo $vik->closeControl(); ?>

<?php
JText::script('VRTKAREAUSERPOSITION');
?>

<script type="text/javascript">

	// MAP UTILS	

	jQuery(document).ready(function(){

		<?php if ($area->type == 2) { ?>
			initializeCircleMap();
		<?php } ?>

		jQuery('.vr-circle-field').on('change', function() {
			changeCircleContents(
				jQuery('input[name="center_latitude"]').val(),
				jQuery('input[name="center_longitude"]').val(),
				jQuery('input[name="radius"]').val()
			);
		});

		jQuery('.vr-attribute-field').on('change', function() {
			if (!CIRCLE_SHAPE) {
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

			// update circle style
			CIRCLE_SHAPE.setOptions(options);
		});

		jQuery('#circle-get-coords').on('click', function() {

			// retrieve user coordinates
			VikGeo.getCurrentPosition().then(function(coord) {
				// coordinates retrieved, change circle center
				circleCoordHandler(coord.lat, coord.lng);
			}).catch(function(error) {
				// unable to obtain current position, show error
				alert(error);
			});

		});

	});
	
	var CIRCLE = {
		lat: 	null,
		lng: 	null,
		radius: 0,
	};

	<?php if ($area->type == 2 && isset($area->content->center)) { ?>
		CIRCLE.lat    = <?php echo floatval($area->content->center->latitude); ?>;
		CIRCLE.lng    = <?php echo floatval($area->content->center->longitude); ?>;
		CIRCLE.radius = <?php echo floatval($area->content->radius); ?>;
	<?php } ?>
	
	var CIRCLE_MAP    = null;
	var CIRCLE_MARKER = null;
	var CIRCLE_SHAPE  = null;

	function initializeCircleMap() {

		var prop = {
			zoom: 13,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			clickableIcons: false,
		};
		
		var coord = new google.maps.LatLng(CIRCLE.lat, CIRCLE.lng);

		prop.center = coord;
		
		CIRCLE_MAP = new google.maps.Map(document.getElementById('circle-googlemap'), prop);
		
		CIRCLE_MARKER = new google.maps.Marker({
			position: coord,
			draggable: true,
		});
			
		CIRCLE_MARKER.setMap(CIRCLE_MAP);

		// fill all areas
		fillMapShapes();

		var fillColor = jQuery('input[name="color"]').val();
		if (!fillColor.length) {
			fillColor = '#FF0000';
		}

		var strokeColor = jQuery('input[name="strokecolor"]').val();
		if (!strokeColor.length) {
			strokeColor = fillColor;
		}

		var strokeWeight = parseInt(jQuery('input[name="strokeweight"]').val());
		if (isNaN(strokeWeight)) {
			strokeWeight = 2;
		}

		CIRCLE_SHAPE = new google.maps.Circle({
			strokeColor: strokeColor,
			strokeOpacity: 0.8,
			strokeWeight: strokeWeight,
			fillColor: fillColor,
			fillOpacity: 0.35,
			map: CIRCLE_MAP,
			center: coord,
			radius: CIRCLE.radius * 1000,
			clickable: false,
		});

		CIRCLE_MAP.addListener('click', function(e) {
			circleCoordHandler(e.latLng.lat(), e.latLng.lng());
		});

		// update circle position after dragging the marker
		CIRCLE_MARKER.addListener('dragend', function(e) {
	        var coord = CIRCLE_MARKER.getPosition();

	        circleCoordHandler(coord.lat(), coord.lng(), true);
	    });

	}
	
	function changeCircleContents(lat, lng, radius) {
		lat = parseFloat(lat);
		lng = parseFloat(lng);

		if (isNaN(lat) || isNaN(lng)) {
			// do not go ahead
			return false;
		}

		var center_map = false;

		if (CIRCLE && (Math.abs(CIRCLE.lat - lat) >= 0.3 || Math.abs(CIRCLE.lng - lng) >= 0.3)) {
			// center map in case the difference between the current coordinates and
			// the previous ones is equals or greater than 0.3
			center_map = true;
		}

		CIRCLE.lat = lat;
		CIRCLE.lng = lng;

		if (radius !== undefined) {
			CIRCLE.radius = parseFloat(radius);
		}

		if (CIRCLE.lat.length == 0 || CIRCLE.lng.length == 0) {
			return;
		}

		if (CIRCLE_SHAPE) {
			var coord = new google.maps.LatLng(CIRCLE.lat, CIRCLE.lng);

			// update marker position
			CIRCLE_MARKER.setPosition(coord);

			// update circle
			CIRCLE_SHAPE.setCenter(coord);
			CIRCLE_SHAPE.setRadius(CIRCLE.radius * 1000);

			if (center_map) {
				CIRCLE_MAP.setCenter(coord);
			}
		} else {
			// initialize the map for the first time
			initializeCircleMap();
		}
	}

	function circleCoordHandler(lat, lng, confirmed) {
		
		var r = true;

		if (CIRCLE.lat !== null && !isNaN(CIRCLE.lat)) {
			// skip confirmation in case the 3rd argument is specified
			r = confirmed || confirm(Joomla.JText._('VRTKAREAUSERPOSITION'));
		}
		
		if (r) {
			jQuery('input[name="center_latitude"]').val(lat);
			jQuery('input[name="center_longitude"]').val(lng);

			jQuery('.vr-circle-field').trigger('change');
		}
	}

</script>
