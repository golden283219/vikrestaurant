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
 * @var  VREDishesCart  $cart         The current cart instance.
 * @var  object         $reservation  The object holding the reservation details.
 */
extract($displayData);

$currency = VREFactory::getCurrency();

foreach ($cart->getItemsList() as $index => $item)
{
	// check if the item can be updated or deleted
	$canEdit = $item->isWritable() && !$reservation->bill_closed;
	?>
	<div class="dishes-cart-item-row" data-id="<?php echo $item->getRecordID(); ?>">

		<div class="dish-item-quantity">
			<?php echo $item->getQuantity(); ?>x
		</div>

		<div class="dish-item-name">
			<span class="basename">
				<?php
				if ($canEdit)
				{
					?>
					<a href="javascript: void(0);" onclick="vrOpenDishOverlay(0, <?php echo $index; ?>);">
						<?php echo $item->getName(); ?>
					</a>
					<?php
				}
				else
				{
					echo $item->getName();
				}
				?>
			</span>

			<?php
			if ($var = $item->getVariation())
			{
				?><span class="varname"><?php echo $var->name; ?></span><?php
			}
			?>
		</div>

		<div class="dish-item-price">
			<?php echo $currency->format($item->getTotalCost()); ?>	
		</div>

		<?php
		if ($canEdit)
		{
			?>
			<a href="javascript: void(0);" class="dish-item-delete" onclick="vrRemoveDishFromCart(<?php echo $index; ?>);">
				<i class="fas fa-minus-circle"></i>
			</a>
			<?php
		}
		else
		{
			?>
			<span class="dish-item-delete">&nbsp;</span>
			<?php
		}
		?>
		
	</div>
	<?php
}
