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
 * Template file used to display a menu block.
 * This file iterates the products that belong to
 * the menu, which are displayed using a different
 * sub-template.
 *
 * @since 1.8
 */

$config = VREFactory::getConfig();

$menu = $this->forMenu;

?>

<div class="vrtk-menu-outer">

	<div class="vrtkmenuheader">

		<div class="vrtkmenutitlediv<?php echo (!$menu->isActive ? ' disabled' : ''); ?>">

			<div class="vrtk-menu-title"><?php echo $menu->title; ?></div>
			
			<?php
			if (!$menu->isActive)
			{
				?>
				<div class="vrtk-menusubtitle-notactive">
					<?php echo $menu->availError; ?>
				</div>
				<?php
			}
			?>

		</div>

		<div class="vrtkmenudescdiv">
			<?php
			// prepare description to properly interpret included plugins
			VREApplication::getInstance()->onContentPrepare($menu->description);

			echo $menu->description->text;
			?>
		</div>

	</div>

	<div class="vrtkitemsofmenudiv">
	
		<?php
		foreach ($menu->products as $item)
		{
			// keep a reference of the current product for
			// being used in a sub-template
			$this->forItem = $item;

			// sisplays the current product block
			echo $this->loadTemplate('item');
		}
		?>

	</div>

</div>
