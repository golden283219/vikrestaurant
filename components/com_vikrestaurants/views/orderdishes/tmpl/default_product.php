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

$product = $this->foreachProduct;

$currency = VREFactory::getCurrency();

?>

<div
	class="vre-order-dishes-product<?php echo ($this->canOrder ? ' clickable' : ''); ?>"
	data-id="<?php echo $product->idAssoc; ?>"
	data-name="<?php echo $product->name; ?>"
>

	<div class="vre-order-dishes-product-inner">

		<div class="dishes-product-text">

			<!-- PRODUCT DETAILS -->

			<div class="dishes-product-name"><?php echo $product->name; ?></div>

			<?php
			if ($product->description)
			{
				$maxlen = $product->image ? 100 : 150;

				// do not show more than 100 characters
				if (mb_strlen(strip_tags($product->description), 'UTF-8') > $maxlen)
				{
					$product->description = mb_substr(strip_tags($product->description), 0, $maxlen - 20, 'UTF-8') . '...';
				}
				?>
				<div class="dishes-product-description">
					<?php echo $product->description; ?>
				</div>
				<?php
			}

			if ($product->price > 0)
			{
				?>
				<div class="dishes-product-price">
					<?php echo $currency->format($product->price); ?>
				</div>
				<?php
			}
			?>

		</div>

		<?php
		if ($product->image)
		{
			?>
			<!-- PRODUCT IMAGE -->

			<div class="dishes-product-image" style="background-image: url(<?php echo VREMEDIA_SMALL_URI . $product->image; ?>);">

			</div>
			<?php
		}
		?>

	</div>

</div>
