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
 * @var  string   $text     The text to display within the alert.
 * @var  string   $type     The alert type.
 * @var  string   $id       A unique alert signature.
 * @var  boolean  $dismiss  True if the alert can be dismissed.
 * @var  mixed    $expdate  If specified, indicates the date for
 *                          the cookie expiration.
 * @var  array    $attrs    A list of field attributes.
 */
extract($displayData);

// fetch attributes string
$attrs_str = '';

foreach ($attrs as $k => $v)
{
	if ($k != 'class')
	{
		$attrs_str .= ' ' . $k;

		if (!is_bool($v))
		{
			$attrs_str .= ' = "' . $this->escape($v) . '"';
		}
	}
}

?>

<div class="alert alert-<?php echo $type . (!empty($attrs['class']) ? ' ' . $attrs['class'] : ''); ?>"<?php echo $attrs_str; ?>>
	
	<?php
	if ($dismiss)
	{
		?>
		<button type="button" class="close" data-dismiss="alert" data-signature="<?php echo $id; ?>" data-expdate="<?php echo $expdate; ?>">×</button>
		<?php
	}
	?>

	<div class="alert-message">
		<?php
		/**
		 * Add support for alert icons in Joomla 4.
		 * Do not add the icon in case the text starts with a 
		 * paragraph or a div.
		 *
		 * @since 1.8.3
		 */
		if (VersionListener::isJoomla4x() && !preg_match("/^<(?:p|div)/", $text))
		{
			if ($type == 'info')
			{
				?>
				<span class="fas fa-info-circle" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('INFO'); ?></span>
				<?php
			}
			else if ($type == 'warning')
			{
				?>
				<span class="fas fa-exclamation-triangle" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('WARNING'); ?></span>
				<?php
			}
			else if ($type == 'error')
			{
				?>
				<span class="fas fa-exclamation-circle" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('WARNING'); ?></span>
				<?php
			}
			else if ($type == 'success')
			{
				?>
				<span class="fas fa-check-circle" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('NOTICE'); ?></span>
				<?php
			}
		}
		
		echo $text;
		?>
	</div>

</div>
