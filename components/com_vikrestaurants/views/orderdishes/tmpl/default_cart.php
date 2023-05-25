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

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

?>

<div class="vre-order-dishes-cart">

	<div class="dishes-cart-collapsed">

		<div class="dishes-transmit-wrapper">
			<?php
			if (!$this->reservation->bill_closed)
			{
				?>
				<button type="button" class="btn" id="vre-transmit-btn">
					<?php echo JText::_('VRTAKEAWAYORDERBUTTON'); ?>
				</button>

				<button type="button" class="btn" id="vre-closebill-btn">
					<?php echo JText::_('VREORDERFOOD_CLOSE_BILL'); ?>
				</button>
				<?php
			}
			?>

			<button type="button" class="btn" id="vre-paynow-btn" style="<?php echo $this->reservation->bill_closed ? '' : 'display:none;'; ?>">
				<?php echo JText::_('VREORDERFOOD_PAY_NOW'); ?>
			</button>
		</div>

		<div class="dishes-cart-items" id="vre-cart-items">
			<?php
			// display cart by using a layout
			echo JLayoutHelper::render('orderdish.cart', [
				'cart'        => $this->cart,
				'reservation' => $this->reservation,
			]);
			?>
		</div>
		
	</div>

	<button type="button" class="btn dishes-cart-minified">
		<i class="fas fa-shopping-cart"></i>

		<?php
		$cart_total = $this->cart->getTotalCost();
		?>

		<span id="vre-cart-total" data-total="<?php echo $cart_total; ?>">
			<?php echo JText::sprintf('VRCARTTOTALBUTTON', $currency->format($cart_total)); ?>
		</span>
	</button>

</div>

<?php
JText::script('VRTKCARTDISHESHOWWORK');
JText::script('VRTKCARTDISHTRANSMITTED');
JText::script('VRTKCARTDISHTRANSMITTED_SHORT');
JText::script('VREORDERFOOD_CLOSE_BILL_PENDING');
JText::script('VREORDERFOOD_CLOSE_BILL_PROCEED');
JText::script('VREORDERFOOD_CLOSE_BILL_DISCLAIMER');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('button.dishes-cart-minified').on('click', function() {
			// get collapsed cart
			var cart = jQuery('.dishes-cart-collapsed');

			// check if the current device is (probably) a mobile
			var isMobile = window.matchMedia && window.matchMedia("only screen and (max-width: 640px)").matches;

			if (cart.is(':visible')) {
				cart.slideUp();

				// make body scrollable again
				jQuery('body').css('overflow', 'auto');
			} else {
				// open cart only in case one ore more items have been
				// added or transimtted to the kitchen
				if (jQuery('#vre-cart-items').children().length) {
					cart.slideDown();

					if (isMobile) {
						// prevent body from scrolling when the cart
						// is expanded and the device is pretty small
						jQuery('body').css('overflow', 'hidden');
					}
				}
			}
		});

		jQuery('#vre-transmit-btn').on('click', function() {
			// transmit pending orders
			vrTransmitPendingOrder(this).then((data) => {
				var cached = false;

				if (typeof Storage !== 'undefined') {
					// check if we already alerted the message
					cached = sessionStorage.getItem('vreTransmitAlert');
				}

				if (!cached) {
					<?php
					if ($config->getBool('editfood'))
					{
						// inform the customers that they are still allowed to
						// edit dishes as long as they are not under preparation
						?>var transmitMsgKey = 'VRTKCARTDISHTRANSMITTED';<?php
					}
					else
					{
						// inform the customers that the dishes have been 
						// transmitted to the kitchen
						?>var transmitMsgKey = 'VRTKCARTDISHTRANSMITTED_SHORT';<?php
					}
					?>

					// alert message only once
					ToastMessage.dispatch({
						text:   Joomla.JText._(transmitMsgKey),
						status: ToastMessage.SUCCESS_STATUS,
						delay:  15000,
						action: function() {
							// dispose message when clicked
							ToastMessage.dispose(true);
						},
					});

					if (typeof Storage !== 'undefined') {
						sessionStorage.setItem('vreTransmitAlert', 1);
					}
				}
			});
		});

		jQuery('#vre-closebill-btn').on('click', function() {
			var msg;

			if (jQuery('#vre-cart-items').children().filter('[data-id="0"]').length) {
				// pending orders
				msg = Joomla.JText._('VREORDERFOOD_CLOSE_BILL_PENDING');
			} else {
				// no pending order
				msg = Joomla.JText._('VREORDERFOOD_CLOSE_BILL_PROCEED');
			}

			// add disclaimer
			msg += "\n" + Joomla.JText._('VREORDERFOOD_CLOSE_BILL_DISCLAIMER');

			// ask for a confirmation
			var r = confirm(msg);

			if (!r) {
				// action refused
				return false;
			}

			// request bill closure
			vrCloseBill(this).then((data) => {
				// remove buttons used to transmit and close the bill
				jQuery('#vre-transmit-btn, #vre-closebill-btn').remove();

				// show button to proceed with the payment
				jQuery('#vre-paynow-btn').show();
			});
		});

		jQuery('#vre-paynow-btn').on('click', function() {
			<?php
			if (count($this->payments))
			{
				?>
				// open payment overlay
				vrOpenPaymentOverlay();
				<?php
			}
			else
			{
				?>
				// redirect to reservation summary page
				document.location.href = '<?php echo $this->paymentURL; ?>';
				<?php
			}
			?>
		});

		// check status of trasmit button
		vrCheckTransmitBtnStatus();
		// check status of close bill button
		vrCheckCloseBillBtnStatus();

	});

	function vrCheckTransmitBtnStatus() {
		// get transmit button
		var btn = jQuery('#vre-transmit-btn');

		// check if we have any pending items
		if (jQuery('#vre-cart-items').children().filter('[data-id="0"]').length) {
			// enable button to allow dishes transmission
			btn.prop('disabled', false);
		} else {
			// disable button
			btn.prop('disabled', true);
		}
	}

	function vrCheckCloseBillBtnStatus() {
		// get close bill button
		var btn = jQuery('#vre-closebill-btn');

		// check if we have any items already transmitted
		if (jQuery('#vre-cart-items').children().filter('[data-id!="0"]').length) {
			// display button
			btn.show();
		} else {
			// hide button
			btn.hide();
		}
	}

	function vrUpdateCartTotal(total) {
		// format total as currency
		var ftotal = Currency.getInstance().format(total);
		// fetch total text
		ftotal = Joomla.JText._('VRTKADDTOTALBUTTON').replace(/%s/, ftotal);
		// update text
		jQuery('#vre-cart-total').text(ftotal).attr('data-total', total);
	}

	function vrGetCartTotal() {
		return parseFloat(jQuery('#vre-cart-total').attr('data-total'));
	}

	function vrAddDishToCart(data) {
		// create promise
		return new Promise((resolve, reject) => {
			// make request to add dish within the cart
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=orderdish.addcart' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				data,
				function(resp) {
					// decode response
					resp = JSON.parse(resp);

					// update total
					vrUpdateCartTotal(resp.total);

					// refresh cart
					jQuery('#vre-cart-items').html(resp.cartHTML);

					// check status of trasmit button
					vrCheckTransmitBtnStatus();
					// check status of close bill button
					vrCheckCloseBillBtnStatus();

					var cached = false;

					if (typeof Storage !== 'undefined') {
						// check if we already alerted the message
						cached = sessionStorage.getItem('vreOrderDishesGuide');
					}

					if (!cached) {
						// check if the cart is expanded
						if (!jQuery('.dishes-cart-collapsed').is(':visible')) {
							// expand cart
							jQuery('button.dishes-cart-minified').trigger('click');
						}

						// display how the system works only once
						ToastMessage.dispatch({
							text:   Joomla.JText._('VRTKCARTDISHESHOWWORK'),
							status: ToastMessage.NOTICE_STATUS,
							delay:  25000,
							action: function() {
								// dispose message when clicked
								ToastMessage.dispose(true);

								// hide cart if visible
								if (jQuery('.dishes-cart-collapsed').is(':visible')) {
									jQuery('button.dishes-cart-minified').trigger('click');
								}
							},
						});

						if (typeof Storage !== 'undefined') {
							sessionStorage.setItem('vreOrderDishesGuide', 1);
						}
					}

					// resolve with received response
					resolve(resp);
				},
				function(error) {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// display reason of the error
					alert(error.responseText);

					// reject with error
					reject(error);
				}
			);
		});
	}

	function vrRemoveDishFromCart(index) {
		// create promise
		return new Promise((resolve, reject) => {
			// make request to add dish within the cart
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=orderdish.removecart' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				{
					ordnum: <?php echo $this->reservation->id; ?>,
					ordkey: '<?php echo $this->reservation->sid; ?>',
					index:  index,
				},
				function(resp) {
					// decode response
					resp = JSON.parse(resp);

					// update total
					vrUpdateCartTotal(resp.total);

					// refresh cart
					jQuery('#vre-cart-items').html(resp.cartHTML);

					if (jQuery('#vre-cart-items').children().length == 0) {
						// toggle cart in case of no children
						jQuery('button.dishes-cart-minified').trigger('click');
					}

					// check status of trasmit button
					vrCheckTransmitBtnStatus();
					// check status of close bill button
					vrCheckCloseBillBtnStatus();

					// resolve with received response
					resolve(resp);
				},
				function(error) {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// display reason of the error
					alert(error.responseText);

					// reject with error
					reject(error);
				}
			);
		});
	}

	function vrTransmitPendingOrder(btn) {
		// create promise
		return new Promise((resolve, reject) => {
			// make request to add dish within the cart
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=orderdish.transmit' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				{
					ordnum: <?php echo $this->reservation->id; ?>,
					ordkey: '<?php echo $this->reservation->sid; ?>',
				},
				function(resp) {
					// decode response
					resp = JSON.parse(resp);

					// refresh cart
					jQuery('#vre-cart-items').html(resp.cartHTML);

					// check status of trasmit button
					vrCheckTransmitBtnStatus();
					// check status of close bill button
					vrCheckCloseBillBtnStatus();

					// resolve with received response
					resolve(resp);
				},
				function(error) {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// display reason of the error
					alert(error.responseText);

					// reject with error
					reject(error);
				}
			);
		});
	}

	function vrCloseBill(btn) {
		// create promise
		return new Promise((resolve, reject) => {
			// make request to add dish within the cart
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=orderdish.closebill' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				{
					ordnum: <?php echo $this->reservation->id; ?>,
					ordkey: '<?php echo $this->reservation->sid; ?>',
				},
				function(resp) {
					// decode response
					resp = JSON.parse(resp);

					// update total
					vrUpdateCartTotal(resp.total);

					// refresh cart
					jQuery('#vre-cart-items').html(resp.cartHTML);

					// resolve with received response
					resolve(resp);
				},
				function(error) {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// display reason of the error
					alert(error.responseText);

					// reject with error
					reject(error);
				}
			);
		});
	}

</script>
