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

JHtml::_('vrehtml.assets.intltel', '.phone-field');

$fields = $this->order->custom_f;

$vik = VREApplication::getInstance();

foreach ($this->customFields as $cf)
{						
	if ($cf['type'] != 'separator')
	{
		// control ID: "vrcf" . $cf['id']
		echo $vik->openControl(JText::_($cf['name']));
	}
		
	$_val = '';

	if (!empty($fields[$cf['name']]))
	{
		$_val = $fields[$cf['name']];
	}

	$classes = array();

	$inputType = 'text';

	$autocomplete = '';

	if (VRCustomFields::isEmail($cf))
	{
		$classes[] = 'mail-field';

		$inputType = 'email';
	}
	else if (VRCustomFields::isPhoneNumber($cf))
	{
		$classes[] = 'phone-field';

		$inputType = 'tel';
	}
	else if (VRcustomFields::isAddress($cf))
	{
		$classes[] = 'address-field';
		$classes[] = 'delivery-field';

		// turn off autocomplete for address fields
		$autocomplete = 'autocomplete="off"';
	}
	else if (VRCustomFields::isDelivery($cf))
	{
		$classes[] = 'delivery-field';
	}
	else if (VRCustomFields::isZip($cf))
	{
		$classes[] = 'zip-field';
		$classes[] = 'delivery-field';
	}
	else if (VRCustomFields::isCity($cf))
	{
		$classes[] = 'city-field';
		$classes[] = 'delivery-field';
	}

	if ($cf['type'] == 'text')
	{
		?>
		<input
			type="<?php echo $inputType; ?>"
			name="vrcf<?php echo $cf['id']; ?>"
			value="<?php echo $this->escape($_val); ?>"
			size="40"	
			class="<?php echo implode(' ', $classes); ?>"
			data-cfname="<?php echo $this->escape($cf['name']); ?>"
			<?php echo $autocomplete; ?>
		/>
		<?php
	}
	else if ($cf['type'] == 'textarea')
	{
		?>
		<textarea name="vrcf<?php echo $cf['id']; ?>" rows="5" cols="30" class="vrtextarea" data-cfname="<?php echo $this->escape($cf['name']); ?>"><?php echo $_val; ?></textarea>
		<?php
	}
	else if ($cf['type'] == 'date')
	{
		echo $vik->calendar($_val, 'vrcf' . $cf['id'], 'vrcf' . $cf['id'] . 'date', null, array('data-cfname' => $cf['name']));
	}
	else if ($cf['type'] == 'select')
	{
		$options = array();

		$choose = array_filter(explode(";;__;;", $cf['choose']));
		$values = $cf['multiple'] ? json_decode($_val ? $_val : '[]') : array($_val);

		foreach ($choose as $aw)
		{
			$options[] = JHtml::_('select.option', $aw, $aw);
		}
		?>
		<select
			name="vrcf<?php echo $cf['id'] . ($cf['multiple'] ? '[]' : ''); ?>"
			class="vr-cf-select"
			data-cfname="<?php echo $this->escape($cf['name']); ?>"
			<?php echo $cf['multiple'] ? 'multiple' : ''; ?>
		>
			<?php echo JHtml::_('select.options', $options, 'value', 'text', $values); ?>
		</select>
		<?php
	}
	else if ($cf['type'] == 'separator')
	{
		?>
		<div class="control-group"><strong><?php echo JText::_($cf['name']); ?></strong></div>
		<?php
	}
	else
	{
		?>
		<input
			type="checkbox"
			name="vrcf<?php echo $cf['id']; ?>"
			value="<?php echo JText::_('VRYES'); ?>"
			data-cfname="<?php echo $this->escape($cf['name']); ?>"
			<?php echo ($_val == JText::_('VRYES') ? 'checked="checked"' : ''); ?>
		/>
		<?php
	}
	
	if ($cf['type'] != 'separator')
	{
		echo $vik->closeControl(); 
	}
} 
?>

<script>
	
	jQuery(document).ready(function() {

		jQuery('.vr-cf-select').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 300,
		});

		// do not submit the form in case the e-mail custom fields own invalid addresses
		validator.addCallback(function() {
			var ok = true;

			jQuery('.mail-field').each(function() {
				// validate e-mail first and get result
				ok = validateOptionalMail(this) && ok;
			});

			return ok;
		});

	});
	
</script>
