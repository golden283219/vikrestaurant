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

// get global attributes
$table 	  = isset($displayData['table']) ? $displayData['table'] : null;
$is_admin = isset($displayData['admin']) ? $displayData['admin'] : false;

if (!$table)
{
	return;
}

$available = (int) $table->getData('available', false);
$occupancy = (int) $table->getData('occurrency', 0);

$reservations = $table->getData('reservations', array());

/**
 * In case of cluster reservation, display also the name
 * of the primary table.
 *
 * @since 1.8
 */
if (count($reservations) == 1 && $reservations[0]->parent_table)
{
	$tname_sfx = ' (' . $reservations[0]->parent_table . ')';
}
else
{
	$tname_sfx = '';
}
?>

<!-- draw graphic wrapper -->
<g 
	fill="<?php echo $table->fill; ?>"
	id="table-<?php echo $table->getData('id'); ?>"
	class="table-graphic"
	data-id="<?php echo $table->getData('id'); ?>"
	data-available="<?php echo $available; ?>"
	data-max="<?php echo $table->getData('max_capacity', 0); ?>"
	data-shared="<?php echo $table->getData('multi_res', 0); ?>"
	transform="<?php echo $table->transform; ?>"
	cursor="pointer"
	opacity="<?php echo $available ? 1 : 0.7; ?>"
>
	
	<!-- draw shape -->
	<?php
	// get table layout
	$shape = @$displayData['layout'];

	// make sure the layout is callable
	if (is_callable(array($shape, 'render')))
	{
		// render table layout
		echo $shape->render($displayData);
	}
	?>

	<!-- draw table name -->
	<text
		class="table-name-text"
		x="<?php echo (int) $table->x + (int) $table->width / 2; ?>"
		y="<?php echo (int) $table->y + 20; ?>"
		text-anchor="middle"
		fill="#<?php echo $table->getData('fgColor', 'ffffff'); ?>"><?php echo $table->getData('name') . $tname_sfx; ?></text>

	<?php
	if ($is_admin && (int) $table->getData('multi_res', 0) == 0 && count($reservations) == 1)
	{
		$names = array_filter(explode(' ', $reservations[0]->purchaser_nominative));
		?>
		<!-- draw customer name -->
		<text
			class="table-customer-text"
			x="<?php echo (int) $table->x + (int) $table->width / 2; ?>"
			y="<?php echo (int) $table->y + (int) $table->height / 2; ?>"
			text-anchor="middle"
			text-baseline="center"
			fill="#<?php echo $table->getData('fgColor', 'ffffff'); ?>"><?php echo end($names); ?></text>
		<?php
	}

	if ($is_admin && (int) $table->getData('multi_res', 0) == 0)
	{
		$href = $title = '';
		if (count($reservations) == 1 && $reservations[0]->code_icon)
		{
			$href  = VREMEDIA_SMALL_URI . $reservations[0]->code_icon;
			$title = $reservations[0]->code;
		}

		?>
		<!-- draw reservation code badge -->
		<image
			xlink:href="<?php echo $href; ?>"
			preserveAspectRatio="true"
			class="table-rescode-badge"
			x="<?php echo (int) $table->x + (int) $table->width - 14; ?>"
			y="<?php echo (int) $table->y - 10; ?>"
			width="24"
			height="24">
			<title><?php echo $title; ?></title>
		</image>
		<?php
	}
	
	if ($is_admin)
	{
		?>
		<!-- draw table capacity -->
		<text
			class="table-capacity-text"
			x="<?php echo (int) $table->x + 4; ?>"
			y="<?php echo (int) $table->y + (int) $table->height - 4; ?>"
			fill="#<?php echo $table->getData('fgColor', 'ffffff'); ?>"><?php echo $table->getData('min_capacity') . '-' . $table->getData('max_capacity'); ?></text>
		<?php
	}

	$sharedBadgeWidth = 0;

	if ((int) $table->getData('multi_res', 0))
	{
		$size = getimagesize(implode(DIRECTORY_SEPARATOR, array(VREADMIN, 'assets', 'css', 'images', 'sharedtable.png')));

		$sharedBadgeWidth = $size[0];

		if ($is_admin)
		{
			// display badge on right side
			$x = (int) $table->x + (int) $table->width - (int) $size[0] - ($occupancy ? 22 : 4);
		}
		else
		{
			// display badge on left side
			$x = (int) $table->x + 4;
		}

		?>
		<!-- draw shared badge -->
		<image
			xlink:href="<?php echo VREASSETS_URI . 'css/images/sharedtable.png'; ?>"
			preserveAspectRatio="none"
			class="table-shared-badge"
			x="<?php echo $x; ?>"
			y="<?php echo (int) $table->y + (int) $table->height - (int) $size[1] - 4; ?>"
			width="<?php echo $size[0]; ?>"
			height="<?php echo $size[1]; ?>"></image>
		<?php
	}
	
	/**
	 * Display occupancy only in case of ADMIN or SHARED table.
	 *
	 * @since 1.8
	 */
	if ($occupancy > 0 && ($is_admin || $table->getData('multi_res', 0)))
	{
		if ($is_admin)
		{
			// display text on right side
			$x = (int) $table->x + (int) $table->width - 4;
		}
		else
		{
			// display text on left side
			$x = (int) $table->x + 4 + $sharedBadgeWidth + 4;
		}

		?>
		<!-- draw table capacity -->
		<text
			class="table-occupancy-text"
			x="<?php echo $x; ?>"
			y="<?php echo (int) $table->y + (int) $table->height - 4; ?>"
			text-anchor="<?php echo $is_admin ? 'end' : 'start'; ?>"
			fill="#<?php echo $table->getData('fgColor', 'ffffff'); ?>"><?php echo $occupancy; ?></text>
		<?php
	}

	if (!$is_admin)
	{
		// draw selected badge and hide it by default
		$size = getimagesize(implode(DIRECTORY_SEPARATOR, array(VREBASE, 'assets', 'css', 'images', 'selected.png')));

		?>
		<!-- draw selection badge -->
		<image
			xlink:href="<?php echo VREASSETS_URI . 'css/images/selected.png'; ?>"
			preserveAspectRatio="none"
			class="table-selected-badge"
			style="display:none;"
			x="<?php echo (int) $table->x + (int) $table->width - (int) $size[0] - 4; ?>"
			y="<?php echo (int) $table->y + (int) $table->height - (int) $size[1] - 4; ?>"
			width="<?php echo $size[0]; ?>"
			height="<?php echo $size[1]; ?>"></image>
		<?php
	}
	?>

</g>
