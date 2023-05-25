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

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   boolean  $selected   True whether the separator owns an active item.
 * @var   boolean  $collapsed  True whether the separator is collapsed.
 * @var   string   $href       The separator HREF, if specified.
 * @var   string   $icon       The custom icon, if specified.
 * @var   string   $title      The separator title.
 * @var   array    $children   The separator menu items.
 * @var   string   $html       The children HTML.
 */

?>

<div class="parent">
	<div class="title<?php echo $selected || $collapsed ? ' selected' : ''; ?><?php echo $collapsed ? ' collapsed' : ''; ?>">

		<a <?php echo strlen($href) ? 'href="' . $href . '"' : ''; ?>>
			<?php
			if (strlen($icon))
			{
				?><i class="fas fa-<?php echo $icon; ?>"></i><?php
			}

			?><span><?php echo $title; ?></span><?php
			
			if (count($children))
			{
				?><i class="fas fa-angle-down vre-angle-dir"></i><?php
			}
			?>
		</a>
	</div>

	<?php
	if (strlen($html))
	{
		?>
		<div class="wrapper<?php echo $selected || $collapsed ? ' collapsed' : ''; ?>"><?php echo $html; ?></div>
		<?php
	}
	?>
</div>