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

$section = $this->foreachSection;

?>

<div class="vre-order-dishes-section<?php echo $section->highlight ? ' can-highlight' : ''; ?>" id="vrmenusection<?php echo $section->id; ?>" data-id="<?php echo $section->id; ?>">

	<!-- SECTION DETAILS -->

	<h4><?php echo $section->name; ?></h4>

	<?php
	if ($section->description)
	{
		?>
		<div class="dishes-section-description">
			<?php echo $section->description; ?>
		</div>
		<?php
	}
	?>

	<!-- PRODUCTS LIST -->

	<div class="vre-order-dishes-products">
		<?php
		foreach ($section->products as $product)
		{
			// assign product for being used in a sub-template
			$this->foreachProduct = $product;

			// display product block
			echo $this->loadTemplate('product');
		}
		?>
	</div>

</div>
