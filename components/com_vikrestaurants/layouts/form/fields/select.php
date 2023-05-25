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
$class = isset($displayData['class']) ? ' ' . $displayData['class'] : '';

$original = array_filter(explode(';;__;;', $cf['_choose']));
$options  = explode(';;__;;', $cf['choose']);

for ($i = 0; $i < count($options); $i++)
{
	// use original option when translation is empty
	if (!strlen($options[$i]) && isset($original[$i]))
	{
		$options[$i] = $original[$i];
	}
}

if ($cf['multiple'])
{
	if (strlen($value))
	{
		// decode stringified value
		$values = json_decode($value);

		if (is_null($values))
		{
			// we are probably using a scalar value,
			// push it within an array
			$values = (array) $value;
		}
	}
	else
	{
		// use an empty array
		$values = array();
	}
}
else
{
	// push the value within an array (for in_array compliance)
	$values = array($value);
}

$class .= $cf['required'] ? ' required' : '';

// check if we should display the address response box
$address_response_box = isset($displayData['addressResponseBox']) ? (bool) $displayData['addressResponseBox'] : false;
?>

<div>

	<div class="cf-value cf-dropdown">

		<span class="cf-label">
			
			<?php if ($cf['required']) { ?>

				<span class="vrrequired"><sup>*</sup></span>

			<?php } ?>

			<span id="vrcf<?php echo $cf['id']; ?>"><?php echo $label; ?></span>

		</span>

		<div class="<?php echo !$cf['multiple'] ? 'vre-select-wrapper' : 'vre-multiselect-wrapper'; ?>">
			<select
				name="vrcf<?php echo $cf['id'] . ($cf['multiple'] ? '[]' : ''); ?>"
				id="vrcfinput<?php echo $cf['id']; ?>"
				class="vr-cf-select vre-select<?php echo $class; ?>"
				<?php echo ($cf['multiple'] ? 'multiple' : ''); ?>
			>

				<?php foreach ($options as $i => $opt) { ?>

					<option 
						value="<?php echo $this->escape($original[$i]); ?>"
						<?php echo (in_array($original[$i], $values) ? 'selected="selected"' : ''); ?>
					><?php echo JText::_($opt); ?></option>

				<?php } ?>

			</select>
		</div>

		<?php
		if ($address_response_box)
		{
			?>
			<div class="vrtk-address-response" style="display: none;"></div>
			<?php
		}
		?>

	</div>

</div>
