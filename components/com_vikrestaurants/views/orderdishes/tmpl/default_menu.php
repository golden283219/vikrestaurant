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

$menu = $this->foreachMenu;

$sections = array();

foreach ($menu->sections as $s)
{
	if ($s->highlight)
	{
		$opt = new stdClass;
		$opt->id       = $s->id;
		$opt->name     = $s->name;
		$opt->selected = $sections ? false : true;
		
		// copy section in head bar
		$sections[] = $opt;
	}
}

?>

<div class="vre-order-dishes-menu">

	<!-- MENU DETAILS -->

	<h3><?php echo $menu->name; ?></h3>

	<?php
	if ($menu->description)
	{
		?>
		<div class="dishes-menu-description">
			<?php echo $menu->description; ?>
		</div>
		<?php
	}

	/**
	 * Display sections filter
	 *
	 * @since 1.8.1
	 */
	if (count($sections))
	{
		?>
		<div class="vrmenu-sectionsbar orderdishes-page">
			<?php
			foreach ($sections as $s)
			{
				?>
				<span class="vrmenu-sectionsp">
					<a href="javascript: void(0);" class="vrmenu-sectionlink <?php echo ($s->selected ? 'vrmenu-sectionlight' : ''); ?>" onClick="vrFadeSection(<?php echo $s->id; ?>);" id="vrmenuseclink<?php echo $s->id; ?>">
						<?php echo $s->name; ?>
					</a>
				</span>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>

	<!-- SECTIONS LIST -->

	<div class="vre-order-dishes-sections">
		<?php
		foreach ($menu->sections as $section)
		{
			// assign section for being used in a sub-template
			$this->foreachSection = $section;

			// display section block
			echo $this->loadTemplate('section');
		}
		?>
	</div>

</div>
