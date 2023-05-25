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

/**
 * Layout variables
 * -----------------
 * @var  string   $name     The field name.
 * @var  string   $value    The selected media file.
 * @var  string   $id       The field ID attribute.
 * @var  array    $attrs    A list of field attributes.
 * @var  string   $modal    The media manager modal. In case the string is empty,
 *                          the modal has been already rendered by a different field.
 * @var  boolean  $preview  True to display the preview button.
 */
extract($displayData);

// fetch attributes string
$attrs_str = '';

foreach ($attrs as $k => $v)
{
	$attrs_str .= ' ' . $k;

	if (!is_bool($v))
	{
		$attrs_str .= ' = "' . $this->escape($v) . '"';
	}
}

?>
	
<div class="input-append vre-media-manager-field">

	<?php
	if (!empty($attrs['multiple']))
	{
		if (!is_array($value))
		{
			$value = $value ? (array) $value : array();
		}

		foreach ($value as $file)
		{
			?><input type="hidden" name="<?php echo $name; ?>" value="<?php echo $file; ?>" /><?php
		}

		$count = count($value);

		if ($count > 1)
		{
			$value = JText::plural('VRE_DEF_N_SELECTED', $count);
		}
		else
		{
			$value = (string) array_shift($value);
		}
	}
	?>

	<input
		type="text"
		readonly="readonly"
		name="<?php echo empty($attrs['multiple']) ? $name : ''; ?>"
		data-name="<?php echo $name; ?>"
		value="<?php echo (string) $value; ?>"
		id="<?php echo $id; ?>"
		<?php echo $attrs_str; ?>
	/>

	<?php
	if ($preview)
	{
		?>
		<button type="button" class="btn media-preview" onclick="vreMediaStartPreview('#<?php echo $id; ?>', <?php echo $path ? '\'' . addslashes($path) . '\'' : 'null'; ?>);">
			<i class="fas fa-eye"></i>
		</button>
		<?php
	}
	?>

	<button type="button" class="btn media-select" onclick="vreMediaOpenJModal('#<?php echo $id; ?>', <?php echo $path ? '\'' . base64_encode($path) . '\'' : 'null'; ?>);">
		<i class="fas fa-image"></i>
	</button>

</div>

<?php
if ($modal)
{
	echo $modal;
}
?>
