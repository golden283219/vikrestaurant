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

$fields = !empty($displayData['fields']) ? $displayData['fields'] : array();
$params = !empty($displayData['params']) ? $displayData['params'] : array();
$prefix = !empty($displayData['prefix']) ? $displayData['prefix'] : '';

$html = '';

$vik = VREApplication::getInstance();

$hasPassword = false;
$hasColor    = false;

if (count($fields))
{
	foreach ($fields as $key => $f)
	{
		$def_val = '';

		if (isset($params[$key]))
		{
			$def_val = $params[$key];
		}
		else if (!empty($f['default']))
		{
			$def_val = $f['default'];
		}

		if (!isset($f['label']))
		{
			$f['label'] = '';
		}

		if (!empty($f['help']))
		{
			// register help within description
			$f['description'] = $f['help'];
		}

		if (!empty($f['label']) && strpos($f['label'], '//') !== false)
		{
			// extract help string from label
			$_label_arr = explode('//', $f['label']);
			// trim trailing colon
			$f['label'] = str_replace(':', '', $_label_arr[0]);
			// overwrite field description
			$f['description'] = $_label_arr[1];
		}
	
		$title = $f['label'];

		if ($title && !empty($f['required']))
		{
			$f['label'] .= '*';
		}

		if (!empty($f['description']))
		{
			$f['label'] .= $vik->createPopover(array(
				'title' 	=> $title,
				'content' 	=> $f['description'],
			));
		}

		echo $vik->openControl($f['label']);
		
		$input = '';

		if ($f['type'] == 'text')
		{
			?>
			<input type="text" class="form-control<?php echo (!empty($f['required']) ? ' required' : ''); ?>" value="<?php echo $this->escape($def_val); ?>" name="<?php echo $prefix . $key; ?>" size="40" />
			<?php
		}
		else if ($f['type'] == 'password')
		{
			$hasPassword = true;
			?>
			<input type="password" class="form-control<?php echo (!empty($f['required']) ? ' required' : ''); ?>" value="<?php echo $this->escape($def_val); ?>" name="<?php echo $prefix . $key; ?>" size="40" />

			<a href="javascript: void(0);" class="input-align" onclick="switchPasswordField(this);"><i class="fas fa-lock big" style="margin-left: 10px;"></i></a>
			<?php
		}
		else if ($f['type'] == 'select')
		{
			$is_assoc = (array_keys($f['options']) !== range(0, count($f['options']) - 1));
			?>
			<select name="<?php echo $prefix . $key . (!empty($f['multiple']) ? '[]' : ''); ?>"
				class="<?php echo (!empty($f['required']) ? 'required' : ''); ?>" 
				<?php echo (!empty($f['multiple']) ? 'multiple' : ''); ?>
			>
				<?php
				// check if we have a list of objects/arrays
				if (!$is_assoc && $f['options'] && !is_scalar($f['options'][0]))
				{
					// displays options by using JHtml
					echo JHtml::_('select.options', $f['options'], 'value', 'text', $def_val);
				}
				else
				{
					// otherwise iterate the options to build the select elements
					foreach ($f['options'] as $opt_key => $opt_val)
					{
						if (!$is_assoc)
						{
							$opt_key = $opt_val;
						}

						?>

						<option 
							value="<?php echo $this->escape($opt_key); ?>"
							<?php echo ((is_array($def_val) && in_array($opt_key, $def_val)) || $opt_key == $def_val ? 'selected="selected"' : ''); ?>
						><?php echo $opt_val; ?></option>

						<?php
					}
				}
				?>
			</select>
			<?php
		}
		else if ($f['type'] == 'date' || $f['type'] == 'calendar')
		{
			echo $vik->calendar($def_val, $prefix . $key, null, null, isset($f['attributes']) ? $f['attributes'] : array());
		}
		else if ($f['type'] == 'checkbox')
		{
			$yes = $vik->initRadioElement('', JText::_('JYES'), $def_val);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$def_val);

			echo $vik->radioYesNo($prefix . $key, $yes, $no);
		}
		else if ($f['type'] == 'color')
		{
			$hasColor = true;

			// make sure the string starts with "#"
			$def_val = $def_val ? '#' . ltrim($def_val, '#') : '';
			?>
			<div class="input-append color-field">
				<input type="text" class="form-control<?php echo (!empty($f['required']) ? ' required' : ''); ?>" value="<?php echo $this->escape($def_val); ?>" name="<?php echo $prefix . $key; ?>" />

				<button type="button" class="btn"><i class="fas fa-eye-dropper"></i></button>
			</div>
			<?php
		}
		else
		{
			echo $f['html']; 
		}
		
		echo $vik->closeControl();
	}

	if ($hasPassword)
	{
		?>
		<script>

			function switchPasswordField(link) {
				
				if (jQuery(link).prev().is(':password'))
				{
					jQuery(link).prev().attr('type', 'text');
					jQuery(link).find('i').removeClass('fa-lock').addClass('fa-unlock');
				}
				else
				{
					jQuery(link).prev().attr('type', 'password');
					jQuery(link).find('i').removeClass('fa-unlock').addClass('fa-lock');
				}

			}

		</script>
		<?php
	}

	if ($hasColor)
	{
		?>
		<script>
			jQuery('.color-field').each(function() {
				var input  = jQuery(this).find('input');
				var button = jQuery(this).find('button');

				button.ColorPicker({
					color: input.val(),
					onChange: function (hsb, hex, rgb) {
						input.val('#' + hex.toUpperCase());
					},
				});
			});
		</script>
		<?php
	}
}
else
{
	echo $vik->alert(JText::_('VRMANAGEPAYMENT9'));
}
