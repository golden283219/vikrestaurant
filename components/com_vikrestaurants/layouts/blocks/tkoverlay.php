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

$itemid = isset($displayData['Itemid']) ? $displayData['Itemid'] : null;

if (is_null($itemid))
{
	$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');
}

$vik = VREApplication::getInstance();
?>

<div class="vr-overlay" id="vrnewitemoverlay" style="display: none;">

	<div class="vr-modal-box">

		<div class="vr-modal-head">

			<div class="vr-modal-head-title">
				<h3></h3>
			</div>

			<div class="vr-modal-head-dismiss">
				<a href="javascript: void(0);" onClick="vrCloseOverlay('vrnewitemoverlay');">×</a>
			</div>

		</div>

		<div class="vr-modal-body">
			
		</div>

	</div>

</div>

<?php
JText::script('VRTKADDITEMERR2');
?>

<script>

	function vrOpenOverlay(ref, title, id_entry, id_option, index) {
		// change overlay title
		jQuery('.vr-modal-head-title h3').text(title);
		
		// add loading image
		jQuery('.vr-modal-body').html(
			'<div class="vr-modal-overlay-loading">\n'+
				'<img id="img-loading" src="<?php echo VREASSETS_URI . 'css/images/hor-loader.gif'; ?>" />\n'+
			'</div>\n'
		);
		
		// show modal
		jQuery('#' + ref).show();

		// prevent body from scrolling
		jQuery('body').css('overflow', 'hidden');
		
		// make request to load product details
		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&view=tkadditem&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				eid:   id_entry,
				oid:   id_option,
				index: index,
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

				jQuery('.vr-modal-body').html(resp);
			},
			function(error) {
				if (!error.responseText || error.responseText.length > 1024) {
					// use default generic error
					error.responseText = Joomla.JText._('VRTKADDITEMERR2');
				}

				alert(error.responseText);
			}
		);
	}

	function vrCloseOverlay(ref) {
		// make body scrollable again
		jQuery('body').css('overflow', 'auto');

		// hide overlay
		jQuery('#' + ref).hide();
		// clear overlay body
		jQuery('.vr-modal-body').html('');
	}

	jQuery('.vr-modal-box').on('click', function(e) {
		// ignore outside click
		e.stopPropagation();
	});

	jQuery('.vr-overlay').on('click', function() {
		// close overlay when the background is clicked
		vrCloseOverlay(jQuery(this).attr('id'));
	});

</script>
