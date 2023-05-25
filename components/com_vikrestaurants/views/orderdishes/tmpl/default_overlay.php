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

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$vik = VREApplication::getInstance();

?>

<div class="vr-overlay" id="vrdishoverlay" style="display: none;">

	<div class="vr-modal-box">

		<div class="vr-modal-head">

			<div class="vr-modal-head-title">
				<h3></h3>
			</div>

			<div class="vr-modal-head-dismiss">
				<a href="javascript: void(0);" onClick="vrCloseDishOverlay();">×</a>
			</div>

		</div>

		<div class="vr-modal-body">
			
		</div>

	</div>

</div>

<?php
JText::script('VRTKADDITEMERR2');
JText::script('VRTKADDTOTALBUTTON');
JText::script('VRTKADDEDITDISHTITLE');
?>

<script>

	var VRE_OVERLAY_XHR = null;

	function vrOpenDishOverlay(id_dish, index) {
		// fetch title
		var title;

		if (index !== undefined && index != -1) {
			title = Joomla.JText._('VRTKADDEDITDISHTITLE');
		} else {
			title = jQuery('.vre-order-dishes-product[data-id="' + id_dish + '"]').data('name');
		}

		// change overlay title
		jQuery('.vr-modal-head-title h3').text(title);
		
		// add loading image
		jQuery('#vrdishoverlay .vr-modal-body').html(
			'<div class="vr-modal-overlay-loading">\n'+
				'<img id="img-loading" src="<?php echo VREASSETS_URI . 'css/images/hor-loader.gif'; ?>" />\n'+
			'</div>\n'
		);
		
		// show modal
		jQuery('#vrdishoverlay').show();

		// prevent body from scrolling
		jQuery('body').css('overflow', 'hidden');
		
		// make request to load product details
		VRE_OVERLAY_XHR = UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=orderdish.add&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				ordnum: <?php echo $this->reservation->id; ?>,
				ordkey: '<?php echo $this->reservation->sid; ?>',
				id:     id_dish,
				index:  typeof index === 'undefined' ? -1 : index,
			},
			function(resp) {
				// try to decode JSON response
				try {
					resp = JSON.parse(resp);

					if (Array.isArray(resp)) {
						// extract HTML from array
						resp = resp.shift();
					}
				} catch (err) {
					// no JSON, plain HTML was returned
				}

				jQuery('#vrdishoverlay .vr-modal-body').html(resp);
			},
			function(error) {
				if (error.statusText !== 'abort') {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// auto-close overlay on error
					vrCloseDishOverlay();

					setTimeout(function() {
						// raise error with a short delay to complete
						// the closure of the overlay
						alert(error.responseText);
					}, 32);
				}
			}
		);
	}

	function vrCloseDishOverlay() {
		// make body scrollable again
		jQuery('body').css('overflow', 'auto');

		// hide overlay
		jQuery('#vrdishoverlay').hide();
		// clear overlay body
		jQuery('#vrdishoverlay .vr-modal-body').html('');

		if (VRE_OVERLAY_XHR) {
			// abort request
			VRE_OVERLAY_XHR.abort();
		}
	}

	jQuery('.vr-modal-box').on('click', function(e) {
		// ignore outside click
		e.stopPropagation();
	});

	jQuery('#vrdishoverlay').on('click', function() {
		// close overlay when the background is clicked
		vrCloseDishOverlay();
	});

</script>
