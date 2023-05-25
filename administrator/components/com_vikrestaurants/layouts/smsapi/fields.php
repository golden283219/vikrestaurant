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

$html = '';

$vik = VREApplication::getInstance();

$hasPassword = false;

if (count($fields))
{
	foreach ($fields as $key => $f)
	{
		$def_val = '';

		if (!empty($params[$key]))
		{
			$def_val = $this->escape($params[$key]);
		}
		else if (!empty($f['default']))
		{
			$def_val = $f['default'];
		}
		
		$_label_arr = explode('//', $f['label']);
		$label 		= str_replace(':', '', $_label_arr[0]);

		$title = $label;

		if (!empty($label))
		{
			$label .= (!empty($f['required']) ? '*' : '');
		}

		unset($_label_arr[0]);
		$helplabel = implode('//', $_label_arr);

		if ($helplabel)
		{
			$helplabel = $vik->createPopover(array(
				'title' 	=> $title,
				'content' 	=> strtoupper($helplabel[0]) . substr($helplabel, 1),
			));
		}

		?>
		<tr>
			<td width="200" class="adminparamcol"> <b><?php echo $label; ?></b><?php echo $helplabel; ?> </td>
			<td>
				<?php
				
				$input = '';

				if ($f['type'] == 'text')
				{
					?>
					<input type="text" class="form-control<?php echo (!empty($f['required']) ? ' required' : ''); ?>" value="<?php echo $def_val; ?>" name="sms_<?php echo $key; ?>" />
					<?php
				}
				else if ($f['type'] == 'password')
				{
					$hasPassword = true;
					?>
					<div class="input-append">
						<input type="password" class="form-control<?php echo (!empty($f['required']) ? ' required' : ''); ?>" value="<?php echo $def_val; ?>" name="sms_<?php echo $key; ?>" />

						<button type="button" class="btn" onclick="switchPasswordField(this);">
							<i class="fas fa-eye"></i>
						</a>
					</div>
					<?php
				}
				else if ($f['type'] == 'select')
				{
					$is_assoc = (array_keys($f['options']) !== range(0, count($f['options']) - 1));
					?>
					<select name="sms_<?php echo $key.(!empty($f['multiple']) ? '[]' : ''); ?>"
						class="<?php echo (!empty($f['required']) ? 'required' : ''); ?>" 
						<?php echo (!empty($f['multiple']) ? 'multiple' : ''); ?>
					>
						<?php 
						foreach ($f['options'] as $opt_key => $opt_val)
						{
							if (!$is_assoc)
							{
								$opt_key = $opt_val;
							}

							?>

							<option 
								value="<?php echo $opt_key; ?>"
								<?php echo ((is_array($def_val) && in_array($opt_key, $def_val)) || $opt_key == $def_val ? 'selected="selected"' : ''); ?>
							><?php echo $opt_val; ?></option>

							<?php
						}
						?>
					</select>
					<?php
				}
				else
				{
					echo $f['html']; 
				}
				?>
			</td>
		</tr>
		<?php
	}

	if ($hasPassword)
	{
		?>
		<script>

			function switchPasswordField(link) {
				var input = jQuery(link).closest('td').find('input');

				if (input.is(':password'))
				{
					input.attr('type', 'text');
					jQuery(link).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
				}
				else
				{
					input.attr('type', 'password');
					jQuery(link).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
				}

			}

		</script>
		<?php
	}
}
else
{
	?>
	<tr>
		<td colspan="2">
			<?php echo $vik->alert(JText::_('VRMANAGEPAYMENT9')); ?>
		</td>
	</tr>
	<?php
}
