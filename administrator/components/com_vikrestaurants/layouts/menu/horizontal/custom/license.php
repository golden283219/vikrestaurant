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
 * @var   boolean  $pro  True if there is an active PRO license.
 */
extract($displayData);

?>

<div class="license-box custom <?php echo $pro ? 'is-pro' : 'get-pro'; ?>">
	
	<?php
	if (!$pro)
	{
		?>
			<a href="admin.php?page=vikrestaurants&view=gotopro">
				<i class="fas fa-rocket"></i>
				<span><?php echo JText::_('VREGOTOPROBTN'); ?></span>
			</a>
		<?php
	}
	else
	{
		?>
		<a href="admin.php?page=vikrestaurants&view=gotopro">
			<i class="fas fa-trophy"></i>
			<span><?php echo JText::_('VREISPROBTN'); ?></span>
		</a>
		<?php
	}
	?>

</div>