<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_cart
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$cart = TakeAwayCart::getInstance();

$title = $params->get('carttitle');

$currency = VREFactory::getCurrency();

/**
 * The module can be published more the once per page,
 * as all the IDs have been replaced with classes.
 *
 * @since 1.4.3
 */

$scroll = (int) $params->get('usefixed');
$sticky = (int) $params->get('mobilesticky') && $_TAKEAWAY_;

/**
 * Include starting block only in case the module should
 * follow the page scroll.
 *
 * @since 1.5.1
 */
if ($scroll)
{
	/**
	 * Use an empty space within the start tag because sometimes the
	 * div may follow the page scroll when the content is empty.
	 *
	 * @since 1.5.2
	 */
	?>
	<div class="vrtkcartstart">&nbsp;</div>
	<?php
}
?>

<div class="vrtkcartitemsmodule<?php echo ($scroll ? ' vrtkcartfixed cart-fixed' : '') . ($sticky ? ' cart-mobile-sticky' : ''); ?>">
	
	<div class="cart-inner-wrapper">

		<?php
		if (!empty($title))
		{
			?>
			<div class="vrtkmodcarttitlecont">
				<span class="vrtkmodcarttitle"><?php echo $title; ?></span>
			</div>    
			<?php
		}
		?>
		
		<div class="vrtkitemcontainer">
			<?php
			foreach ($cart->getItemsList() as $k => $item)
			{
				?>
				<div class="vrtkcartoneitemrow">

					<?php
					if ($_TAKEAWAY_)
					{
						?>
						<a href="javascript: void(0);" onClick="vrOpenOverlay('vrnewitemoverlay', '<?php echo htmlspecialchars($item->getFullName()); ?>', -1, -1, <?php echo $k; ?>);">
							<div class="vrtkcartleftrow">
								<span class="vrtkcartenamesp"><?php echo $item->getItemName(); ?></span>
								<?php
								if ($item->getVariationID() > 0)
								{
									?>
									<span class="vrtkcartonamesp"><?php echo $item->getVariationName(); ?></span>
									<?php
								}
								?>
							</div>
						</a>
						<?php
					}
					else
					{
						?>
						<div class="vrtkcartleftrow">
							<span class="vrtkcartenamesp"><?php echo $item->getItemName(); ?></span>
							<?php
								if ($item->getVariationID() > 0)
								{
									?>
									<span class="vrtkcartonamesp"><?php echo $item->getVariationName(); ?></span>
									<?php
								}
								?>
						</div>
						<?php
					}
					?>
					
					<div class="vrtkcartrightrow">
						<span class="vrtkcartquantitysp"><?php echo JText::_('VRTKMODQUANTITYSUFFIX') . $item->getQuantity(); ?></span>

						<?php
						$item_total_price = $item->getTotalCost();
						
						if ($item_total_price > 0)
						{
							$item_total_price = $currency->format($item->getTotalCost());
						}
						else
						{
							$item_total_price = JText::_('VRFREE');
						}
						?>

						<span class="vrtkcartpricesp"><?php echo $item_total_price; ?></span>

						<?php
						if ($item->getPrice() != $item->getOriginalPrice())
						{
							?>
							<span class="vrtkcartpricesp-full"><s><?php echo $currency->format($item->getTotalCostNoDiscount()); ?></s></span>
							<?php
						}

						if (!$_TAKEAWAY_CONFIRM_ && $item->canBeRemoved())
						{
							?>
							<span class="vrtkcartremovesp">
								<a href="javascript: void(0);" onClick="vrRemoveFromCart(<?php echo $k; ?>)" class="vrtkcartremovelink">
									<i class="fas fa-minus-circle"></i>
								</a>
							</span>
							<?php
						}
						?>
					</div>

				</div>
				<?php
			}
			?>
		</div>
		
		<div class="vrtkcartdiscountoutmodule">
			<span class="vrtkcartdiscountlabelmodule">
				<?php echo JText::_('VRTKMODCARTTOTALDISCOUNT'); ?>
			</span>

			<div class="vrtkcartdiscountmodule">
				<?php echo $currency->format($cart->getTotalDiscount()); ?>
			</div>
		</div>
		
		<div class="vrtkcartfullcostoutmodule" style="<?php echo ($cart->getTotalDiscount() == 0 ? "display: none;" : ""); ?>">
			<div class="vrtkcartfullcostmodule">
				<?php echo $currency->format($cart->getTotalCost()); ?>
			</div>
		</div>
		
		<div class="vrtkcartpriceoutmodule">
			<span class="vrtkcartpricelabelmodule">
				<?php echo JText::_('VRTKMODCARTTOTALPRICE'); ?>
			</span>
			<div class="vrtkcartpricemodule">
				<?php echo $currency->format($cart->getRealTotalCost()); ?>
			</div>
		</div>
		
		<div class="vrtkcartminorderdiv" style="display: none;">
			<?php
			echo JText::_('VRTKMODCARTMINORDER') . ' ' . 
			$currency->format($minCostPerOrder);
			?>
		</div>
		
		<?php
		if(!$_TAKEAWAY_CONFIRM_)
		{
			?>
			<div class="vrtkcartbuttonsmodule">
				<div class="vrtkcartemptydivmodule">
					<span class="vrtkcartemptyspmodule">
						<button type="button" onClick="vrFlushCart();" class="vrtkcartemptybutton">
							<i class="fas fa-trash"></i>
						</button>
					</span>
				</div>

				<div class="vrtkcartorderdivmodule">
					<span class="vrtkcartorderspmodule">
						<button type="button" onClick="modVrGoToPay();" class="vrtkcartorderbutton"><?php echo JText::_('VRTKMODORDERBUTTON'); ?></button>
					</span>
				</div>
			</div>
			<?php
		}
		?>
	</div>

	<?php
	/**
	 * In case the mobile mode is supported, display
	 * the sticky button.
	 *
	 * @since 1.5.1
	 */
	if ($sticky)
	{
		?>
		<button type="button" class="btn cart-sticky-button" onclick="vrCartToggleMenu(this);">
			<i class="fas fa-shopping-basket"></i>

			<span class="vrtkcartpricemodule">
				<?php echo $currency->format($cart->getRealTotalCost()); ?>
			</span>
		</button>
		<?php
	}
	?>
</div>

<?php
JText::script('VRFREE');
JText::script('VRTKMODQUANTITYSUFFIX');
JText::script('VRTKADDITEMERR2');
?>

<script type="text/javascript">

	var vrcart_curr_price = <?php echo $cart->getRealTotalCost(); ?>;
	
	var VIKRESTAURANTS_CART_INSTANCE = 1;
	
	function vrCartRefreshItems(items, tcost, tdisc, grand_total) {
		var currency = Currency.getInstance();
		
		var html = '';

		for (var i = 0; i < items.length; i++) {
			var item = items[i];
		
			var stroke = '';
			if (item.price != item.original_price) {
				stroke = '<span class="vrtkcartpricesp-full"><s>' + currency.format(item.original_price) + '</s></span>\n';
			}
			
			html += '<div class="vrtkcartoneitemrow">\n' +
				'<a href="javascript: void(0);" onClick="vrOpenOverlay(\'vrnewitemoverlay\', \'' + item.item_name + (item.var_name.length > 0 ? " - " + item.var_name : "") + '\', -1, -1, ' + item.index + ');">\n' +
					'<div class="vrtkcartleftrow">\n' +
						'<span class="vrtkcartenamesp">' + item.item_name + '</span>\n'+
						'<span class="vrtkcartonamesp">' + item.var_name + '</span>\n'+
					'</div>\n' +
				'</a>\n' +
				'<div class="vrtkcartrightrow">\n' +
					'<span class="vrtkcartquantitysp">' + Joomla.JText._('VRTKMODQUANTITYSUFFIX') + item.quantity + '</span>\n';

					if (item.price > 0) {
						html += '<span class="vrtkcartpricesp">' + currency.format(item.price) + '</span>\n';
					} else {
						html += '<span class="vrtkcartpricesp">' + Joomla.JText._('VRFREE') + '</span>\n';
					}

			html += stroke;

			<?php
			if (!$_TAKEAWAY_CONFIRM_)
			{
				?>
				if (item.removable) { 
					html += '<span class="vrtkcartremovesp">\n'+
						'<a href="javascript: void(0);" onClick="vrRemoveFromCart(' + item.index + ')" class="vrtkcartremovelink">\n'+
							'<i class="fas fa-minus-circle"></i>\n'+
						'</a>\n'+
					'</span>\n';
				}
				<?php
			}
			?>

			html += '</div>\n' +
				'</div>\n';
		
		}

		jQuery('.vrtkitemcontainer').html(html);
		
		vrCartUpdateTotalCost(tcost, tdisc, grand_total);
	}
	
	function vrCartUpdateTotalCost(tcost, tdisc, grand_total) {
		var currency = Currency.getInstance();

		jQuery('.vrtkcartpricemodule').html(currency.format(grand_total));
		jQuery('.vrtkcartfullcostmodule').html(currency.format(tcost));
		jQuery('.vrtkcartdiscountmodule').html(currency.format(tdisc));
		
		if (tdisc > 0) {
			jQuery('.vrtkcartfullcostoutmodule').show();
		} else {
			jQuery('.vrtkcartfullcostoutmodule').hide();
		}
		
		vrcart_curr_price = grand_total;
	}
	
	function vrRemoveFromCart(id) {
		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=tkreservation.removefromcartajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				index: id,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				vrCartRefreshItems(obj.items, obj.total, obj.discount, obj.finalTotal);
			},
			function(error) {
				if (!error.responseText || error.responseText.length > 1024) {
					error.responseText = Joomla.JText._('VRTKADDITEMERR2');
				}

				alert(error.responseText);
			}
		);
	}
	
	function vrFlushCart() {
		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=tkreservation.emptycartajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{},
			function(resp) {
				// remove all records
				jQuery('.vrtkcartoneitemrow').remove();
				// reset totals
				vrCartUpdateTotalCost(0, 0, 0);
			},
			function(error) {
				if (!error.responseText || error.responseText.length > 1024) {
					error.responseText = Joomla.JText._('VRTKADDITEMERR2');
				}

				alert(error.responseText);
			}
		);		
	}
	
	function modVrGoToPay() {
		var min_cost_per_res = <?php echo $minCostPerOrder; ?>;

		if (min_cost_per_res > vrcart_curr_price) {
			jQuery('.vrtkcartminorderdiv').fadeIn().delay(2000).fadeOut();
		} else {
			document.location.href = '<?php echo $TAKEAWAY_CONFIRM_URL; ?>';
		}
	}

	function vrIsCartVisibleOnScreen() {
		// get screen position and height
		var screen_y = jQuery(window).scrollTop();
		var screen_h = jQuery(window).height();

		// do not consider buttons, discount and total cost on cart bottom (around 100px)
		var some_cart_bottom_padding = 100;

		var visible = false;

		/**
		 * Since there may be several modules published
		 * within the same page, we should iterate all the
		 * modules to check if at least one of them is visible.
		 *
		 * @since 1.5
		 */
		jQuery('.vrtkcartitemsmodule').each(function() {
			var cart_y = parseInt(jQuery(this).offset().top);
			var cart_h = parseInt(jQuery(this).height());

			if (screen_y <= cart_y && cart_y + cart_h - some_cart_bottom_padding < screen_y + screen_h) {
				// current module is visible
				visible = true;
				// break EACH
				return false;
			}
		});

		return visible;
	}

	function vrCartToggleMenu(button) {
		// get collapsed cart
		var cart = jQuery(button).siblings('.cart-inner-wrapper');

		if (cart.is(':visible')) {
			cart.slideUp();

			// make body scrollable again
			jQuery('body').css('overflow', 'auto');
		} else {
			// open cart only in case one ore more items have been
			// added or transimtted to the kitchen
			if (jQuery(cart).find('.vrtkcartoneitemrow').length) {
				cart.slideDown();

				// prevent body from scrolling when the cart
				// is expanded and the device is pretty small
				jQuery('body').css('overflow', 'hidden');
			}
		}
	}
	
</script>
