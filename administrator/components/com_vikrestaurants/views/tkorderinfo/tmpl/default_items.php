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

$currency = VREFactory::getCurrency();

?>

<h3><?php echo JText::_('VRMANAGETKRES22'); ?></h3>

<?php
if (!$this->order->items)
{
	echo VREApplication::getInstance()->alert(JText::_('VREMPTYCART'));
}
else
{
	?>
	<div class="order-items-cart">

		<?php
		foreach ($this->order->items as $item)
		{
			?>
			<div class="cart-item-record">

				<div class="cart-item-details">
					
					<div class="cart-item-name">
						<span class="item-prod-name"><?php echo $item->productName; ?></span>

						<?php
						if ($item->id_option)
						{
							?><span class="item-option-name badge badge-info"><?php echo $item->optionName; ?></span><?php
						}
						?>
					</div>

					<div class="cart-item-quantity">
						x<?php echo $item->quantity; ?>
					</div>

					<div class="cart-item-price">
						<?php echo $currency->format($item->price); ?>
					</div>

				</div>

				<?php
				if ($item->toppings)
				{
					foreach ($item->toppings as $group)
					{
						?>
						<div class="cart-item-toppings">
							<span class="cart-item-toppings-group">
								<?php echo $group->title; ?>:
							</span>
							<span class="cart-item-toppings-list">
								<?php echo $group->str; ?>
							</span>
						</div>
						<?php
					}
				}

				if ($item->notes)
				{
					?><div class="cart-item-notes"><?php echo $item->notes; ?></div><?php
				}
				?>

			</div>
			<?php
		}
		?>

	</div>
	<?php
}
