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

$locations = $this->customer->locations;

$vik = VREApplication::getInstance();

if (count($locations) > 1)
{
	echo $vik->alert(JText::_('VRCUSTOMERDELIVERYHEAD'), 'info');
}

$deliveryLayout = new JLayoutFile('blocks.card');

echo $vik->openEmptyFieldset();
?>

<div class="vr-delivery-locations-container vre-cards-container">

	<?php
	for ($i = 0; $i < count($locations); $i++)
	{
		$loc = $locations[$i];
		?>
		<div class="delivery-fieldset vre-card-fieldset" id="delivery-fieldset-<?php echo $i; ?>">

			<?php
			$displayData = array();
			$displayData['id'] = 'delivery-card-' . $i;

			// fetch image
			$displayData['image'] = VREASSETS_ADMIN_URI . 'images/map-loading.png';

			if ($loc->latitude || $loc->longitude)
			{
				$options = array(
					// define image center
					'center' => array(
						'lat' => $loc->latitude,
						'lng' => $loc->longitude,
					),
					// define image size (800x400)
					'size' => array(
						'width'  => 640,
						'height' => 300,
					),
					// use default image
					'default' => $displayData['image'],
				);

				// fetch map image through Google
				$displayData['image'] = JHtml::_('vrehtml.site.googlemapsimage', $options);
			}

			// fetch badge
			switch ($loc->type)
			{
				case 1:
					$icon = 'home';
					break;

				case 2:
					$icon = 'briefcase';
					break;

				default:
					$icon = 'ellipsis-h';
			}

			$displayData['badge'] = '<i class="fas fa-' . $icon . '"></i>';

			// fetch primary text
			$parts = array(
				trim($loc->address . ' ' . $loc->address_2),
				$loc->zip,
			);

			$displayData['primary'] = implode(', ', array_filter($parts));

			if (strlen($loc->note))
			{
				$displayData['primary'] .= $vik->createPopover(array(
					'title'   => JText::_('VRMANAGERESCODE5'),
					'content' => $loc->note,
					'trigger' => 'click',
					'class'   => 'delivery-notes-tip',
				));
			}

			// fetch secondary text
			$parts = array(
				$loc->city,
				$loc->state,
				$loc->country,
			);

			$displayData['secondary'] = implode(', ', array_filter($parts));

			// fetch edit button
			$displayData['edit'] = 'openDeliveryCard(' . $i . ');';

			// render layout
			echo $deliveryLayout->render($displayData);
			?>

			<input type="hidden" name="delivery_type[]" value="<?php echo $loc->type; ?>" />
			<input type="hidden" name="delivery_country[]" value="<?php echo $loc->country; ?>" />
			<input type="hidden" name="delivery_state[]" value="<?php echo $this->escape($loc->state); ?>" />
			<input type="hidden" name="delivery_city[]" value="<?php echo $this->escape($loc->city); ?>" />
			<input type="hidden" name="delivery_address[]" value="<?php echo $this->escape($loc->address); ?>" />
			<input type="hidden" name="delivery_address_2[]" value="<?php echo $this->escape($loc->address_2); ?>" />
			<input type="hidden" name="delivery_zip[]" value="<?php echo $this->escape($loc->zip); ?>" />
			<input type="hidden" name="delivery_note[]" value="<?php echo $this->escape($loc->note); ?>" />
			<input type="hidden" name="delivery_lat[]" value="<?php echo $loc->latitude; ?>" />
			<input type="hidden" name="delivery_lng[]" value="<?php echo $loc->longitude; ?>" />
			<input type="hidden" name="delivery_id[]" value="<?php echo $loc->id; ?>" />

		</div>
		<?php
	}
	?>

	<div class="delivery-fieldset vre-card-fieldset" id="add-delivery-location">
		<div class="vre-card">
			<i class="fas fa-plus"></i>
		</div>
	</div>

</div>

<?php echo $vik->closeEmptyFieldset(); ?>

<div style="display:none;" id="delivery-struct">
	
	<?php
	// create delivery location structure for new items
	$displayData = array();
	$displayData['id']        = 'delivery-card-{id}';
	$displayData['image']     = VREASSETS_ADMIN_URI . 'images/map-loading.png';
	$displayData['badge']     = '<i class="fas fa-home"></i>';
	$displayData['primary']   = '';
	$displayData['secondary'] = '';
	$displayData['edit']      = true;

	echo $deliveryLayout->render($displayData);
	?>

</div>

<?php
JText::script('VRE_ADD_DELIVERY_LOCATION');
JText::script('VRE_EDIT_DELIVERY_LOCATION');
JText::script('VRSYSTEMCONFIRMATIONMSG');
JText::script('VRMANAGERESCODE5');
?>

<script type="text/javascript">

	var LOCATIONS_COUNT = <?php echo count($locations); ?>;
	var SELECTED_INDEX  = null;

	jQuery(document).ready(function() {

		jQuery('.vr-delivery-locations-container').sortable({
			// exclude "add" boxs
			items: '.delivery-fieldset:not(#add-delivery-location)',
			// defines a bounding box that the items are constrained to while dragging
			containment: 'parent',
			// hide "add" box when sorting starts
			start: function() {
				jQuery('#add-delivery-location').hide();
			},
			// show "add" box again when sorting stops
			stop: function() {
				jQuery('#add-delivery-location').show();
			},
		});

		jQuery('#add-delivery-location').on('click', function() {
			openDeliveryCard();
		});

		jQuery('#delivery-location-save').on('click', function() {
			// validate form
			if (!locationValidator.validate()) {
				return false;
			}

			// get updated delivery data
			var data = getDeliveryData();

			var index = SELECTED_INDEX;

			if (index === undefined || index === null) {
				index = LOCATIONS_COUNT;

				addDeliveryCard(data);

				// update locations badge
				jQuery('#customer_delivery_tab_badge').attr('data-count', jQuery('.delivery-fieldset[id^="delivery-fieldset-"]').length);
			}

			refreshDeliveryCard(index, data);

			// inject data within the form
			for (var k in data) {
				if (data.hasOwnProperty(k)) {
					jQuery('#delivery-fieldset-' + index)
						.find('input[name="delivery_' + k + '[]"]').val(data[k]);
				}
			}

			// evaluate coordinates for address
			var geocoder = new google.maps.Geocoder();

			var address = extractAddressComponents(data);

			geocoder.geocode({address: address}, function(results, status) {
				if (status == 'OK') {
					data.lat = results[0].geometry.location.lat();
					data.lng = results[0].geometry.location.lng();

					// update lat and lng
					jQuery('#delivery-fieldset-' + index).find('input[name="delivery_lat[]"]').val(data.lat);
					jQuery('#delivery-fieldset-' + index).find('input[name="delivery_lng[]"]').val(data.lng);

					// refresh delivery map with Google
					refreshDeliveryMap(index, data);
				}
			});

			jQuery('#delivery-location-inspector').inspector('dismiss');
		});

		jQuery('#delivery-location-delete').on('click', function() {
			var r = confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'));

			if (!r) {
				return false;
			}

			jQuery('#delivery-location-inspector').inspector('dismiss');

			var index = SELECTED_INDEX;

			var fieldset = jQuery('#delivery-fieldset-' + SELECTED_INDEX);
			var id       = parseInt(fieldset.find('input[name="delivery_id[]"]').val());

			if (!isNaN(id) && id > 0) {
				jQuery('#adminForm').append('<input type="hidden" name="delete_delivery[]" value="' + id + '" />');
			}

			fieldset.remove();

			// update locations badge
			jQuery('#customer_delivery_tab_badge').attr('data-count', jQuery('.delivery-fieldset[id^="delivery-fieldset-"]').length);
		});

		// fill the form before showing the inspector
		jQuery('#delivery-location-inspector').on('inspector.show', function() {
			var data = {};

			// in case the INDEX is a number, extract the delivery location data
			if (SELECTED_INDEX !== undefined && SELECTED_INDEX !== null) {
				jQuery('#delivery-fieldset-' + SELECTED_INDEX)
					.find('input[type="hidden"][name^="delivery_"]')
						.each(function() {
							var name  = jQuery(this).attr('name').match(/^delivery_([a-z0-9_]+)\[\]$/i);
							var value = jQuery(this).val();

							if (name && name.length) {
								data[name[1]] = value;
							}
						});
			}

			// fill the form with the retrieved data
			fillDeliveryLocationForm(data);
		});

		jQuery(document).on('click', function(event) {
			// get clicked element
			var src = jQuery(event.target);

			// check if we clicked one of the following items:
			// - TIP handle
			// - a popover dialog
			// - a box within the popover
			if (src.hasClass('delivery-notes-tip') || src.is(jQuery('.popover')) || jQuery('.popover').find(src).length) {
				// do nothing
				return;
			}

			// hide all popovers, instead
			jQuery('.delivery-notes-tip').popover('hide');
		});

	});

	function openDeliveryCard(index) {
		SELECTED_INDEX = index;

		var title;

		if (typeof index === 'undefined') {
			title = Joomla.JText._('VRE_ADD_DELIVERY_LOCATION');
			jQuery('#delivery-location-delete').hide();
		} else {
			title = Joomla.JText._('VRE_EDIT_DELIVERY_LOCATION');
			jQuery('#delivery-location-delete').show();
		}
		
		// open inspector
		vreOpenInspector('delivery-location-inspector', {title: title});
	}

	function refreshDeliveryCard(index, data) {
		var card = jQuery('#delivery-card-' + index);

		var icon;

		switch (parseInt(data.type)) {
			case 1:
				icon = 'home';
				break;

			case 2:
				icon = 'briefcase';
				break;

			default:
				icon = 'ellipsis-h';
		}

		card.vrecard('badge', '<i class="fas fa-' + icon + '"></i>');

		var primary = [
			(data.address + ' ' + data.address_2).trim(),
			data.zip,
		].filter(function(elem) {
			return elem ? true : false;
		}).join(', ');

		if (data.note.length) {
			primary += '<i class="fas fa-question-circle delivery-notes-tip"></i>';
		}

		card.vrecard('primary', primary);

		if (data.note.length) {
			card.find('.delivery-notes-tip').popover({
				title:     Joomla.JText._('VRMANAGERESCODE5'),
				content:   data.note,
				placement: 'right',
				trigger:   'click',
				container: 'body',
			});
		}

		var secondary = [
			data.city,
			data.state,
			data.country,
		].filter(function(elem) {
			return elem ? true : false;
		}).join(', ');

		card.vrecard('secondary', secondary);
	}

	function refreshDeliveryMap(index, data) {
		var card = jQuery('#delivery-card-' + index);

		var url = card.vrecard('image');

		if (typeof url !== 'string') {
			url = '';
		}

		if (url.match(/googleapis/i)) {
			url = url.replace(
				/([&?](?:center|markers))=([0-9.,\s-]+)&/g,
				'$1=' + data.lat + ',' + data.lng + '&'
			);

			card.vrecard('image', url);
		} else {
			UIAjax.do(
				'index.php?option=com_vikrestaurants&task=get_googlemaps_image',
				{
					lat:  data.lat,
					lng:  data.lng,
					size: '640x300',
				},
				function(resp) {
					// extract image src
					var url = jQuery(JSON.parse(resp)).attr('src');

					// preload image
					var image = new Image();
					image.src = url;

					// change image only when ready
					image.onload = function() {
						card.vrecard('image', url);
					};
				}
			);
		}
	}

	function addDeliveryCard(data) {
		var index = LOCATIONS_COUNT;

		var html = jQuery('#delivery-struct').clone().html();

		html = html.replace(/{id}/, index);

		jQuery(
			'<div class="delivery-fieldset vre-card-fieldset" id="delivery-fieldset-' + index + '">' + html + '</div>'
		).insertBefore('#add-delivery-location');

		jQuery('#delivery-card-' + index).vrecard('edit', 'openDeliveryCard(' + index + ')');

		jQuery('#delivery-fieldset-' + index).append(
			'<input type="hidden" name="delivery_type[]" value="' + data.type + '" />\n' +
			'<input type="hidden" name="delivery_country[]" value="' + data.country + '" />\n' + 
			'<input type="hidden" name="delivery_state[]" value="' + data.state + '" />\n' + 
			'<input type="hidden" name="delivery_city[]" value="' + data.city + '" />\n' + 
			'<input type="hidden" name="delivery_address[]" value="' + data.address + '" />\n' + 
			'<input type="hidden" name="delivery_address_2[]" value="' + data.address_2 + '" />\n' + 
			'<input type="hidden" name="delivery_zip[]" value="' + data.zip + '" />\n' + 
			'<input type="hidden" name="delivery_note[]" value="' + data.note + '" />\n' + 
			'<input type="hidden" name="delivery_lat[]" value="' + data.lat + '" />\n' + 
			'<input type="hidden" name="delivery_lng[]" value="' + data.lng + '" />\n' + 
			'<input type="hidden" name="delivery_id[]" value="0" />\n'
		);

		LOCATIONS_COUNT++;
	}

	function extractAddressComponents(data) {
		var address    = [
			data.address,
			data.zip,
		];

		if (data.city) {
			address.push(data.city);
		}

		if (data.state) {
			address.push(data.state);
		}

		if (data.country) {
			address.push(data.country);
		}

		return address.join(', ');
	}

</script>
