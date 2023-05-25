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

$label = $displayData['label'];
$value = $displayData['value'];
$cf    = $displayData['field'];

$class = '';
$class .= strlen($value) ? ' has-value' : '';
$class .= $cf['required'] ? ' required' : '';

$date_format = VREFactory::getConfig()->get('dateformat');

// handle datepicker scripts
JHtml::_('vrehtml.sitescripts.calendar', '#vrcfinput' . $cf['id'] . ':input');

?>

<div>

	<div class="cf-value">

		<?php
		/**
		 * Added a hidden label before the input to fix the auto-complete
		 * bug on Safari, which always expects to have the inputs displayed
		 * after their labels.
		 *
		 * @since 1.8.2
		 */
		?>
		<label for="vrcfinput<?php echo $cf['id']; ?>" style="display: none;"><?php echo $label; ?></label>
		
		<input
			type="text"
			name="vrcf<?php echo $cf['id']; ?>"
			id="vrcfinput<?php echo $cf['id']; ?>"
			value="<?php echo $this->escape($value); ?>"
			class="vrinput vrcalendar<?php echo $class; ?>"
			size="40"
		/>

		<span class="cf-highlight"><!-- input highlight --></span>

		<span class="cf-bar"><!-- input bar --></span>

		<span class="cf-label">
			
			<?php if ($cf['required']) { ?>

				<span class="vrrequired"><sup>*</sup></span>

			<?php } ?>

			<span id="vrcf<?php echo $cf['id']; ?>"><?php echo $label; ?></span>

		</span>

	</div>

</div>

<script>

	jQuery(document).ready(function() {
		var date = new Date();

		// create range of available years
		var years = [
			date.getFullYear() - 100,
			date.getFullYear() + 5,
		];

		// users can change year through the dropdown
		jQuery('#vrcfinput<?php echo $cf['id']; ?>:input').datepicker('option', 'changeYear', true);
		jQuery('#vrcfinput<?php echo $cf['id']; ?>:input').datepicker('option', 'yearRange', years.join(':'));
	});

</script>
