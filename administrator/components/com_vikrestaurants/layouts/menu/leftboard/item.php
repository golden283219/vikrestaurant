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
 * @var   boolean  $selected   True whether the item is active.
 * @var   string   $href       The item HREF.
 * @var   string   $icon       The custom icon, if specified.
 * @var   string   $title      The item title.
 */

?>

<div class="item<?php echo $selected ? ' selected' : ''; ?>">
	
	<a href="<?php echo $href; ?>">
		<?php
		if (strlen($icon))
		{
			?><i class="fas fa-<?php echo $icon; ?>"></i><?php
		}

		?><span><?php echo $title; ?></span>
	</a>

</div>