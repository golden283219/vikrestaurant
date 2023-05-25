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

$vik = VREApplication::getInstance();

?>

<div class="inspector-form" id="inspector-position-form">

	<div class="inspector-fieldset">

		<?php
		$help = $vik->createPopover(array(
			'title'   => JText::_('VRE_WIDGET_POSITION'),
			'content' => JText::_('VRE_WIDGET_POSITION_ADD_HELP'),
		));

		echo $vik->openControl(JText::_('VRE_WIDGET_POSITION') . $help); ?>
			<input type="text" name="position_name" value="" class="field required" />
		<?php echo $vik->closeControl(); ?>

	</div>

</div>

<?php
JText::script('VRE_WIDGET_POSITION_EXISTS_ERR');
?>

<script>

	var positionValidator = new VikFormValidator('#inspector-position-form');

	jQuery(document).ready(function() {

		positionValidator.addCallback(function() {
			// get position input
			var input = jQuery('#inspector-position-form input[name="position_name"]');

			// get position value
			var data = getPositionData();

			// make sure the position is not empty
			if (!data.position) {
				positionValidator.setInvalid(input);

				return false;
			}
			// make sure the position doesn't already exist
			else if (jQuery('.widgets-position-row[data-position="' + data.position + '"]').length) {
				positionValidator.setInvalid(input);

				// inform the user that the position already exists
				alert(Joomla.JText._('VRE_WIDGET_POSITION_EXISTS_ERR'));

				return false;
			}

			// position is ok
			positionValidator.unsetInvalid(input);

			return true;
		});

	});

	function clearPositionForm() {
		jQuery('#inspector-position-form input[name="position_name"]').val('');
	}

	function getPositionData() {
		var data = {};

		// get specified position
		data.position = jQuery('#inspector-position-form input[name="position_name"]').val();

		// strip any non supported character
		data.position = data.position.replace(/[^a-zA-Z0-9_-]/g, '');

		return data;
	}

</script>
