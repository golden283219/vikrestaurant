/**
 * This global variable indicates the TOP PADDING
 * taken by the module. It is possible to increase this
 * value in case the template uses a sticky menu.
 *
 * @var integer
 */
var TK_CART_TOP_PADDING = 15;

/**
 * This function handles the auto-scroll feature of the cart.
 * Every time the page is scrolled, the module recalculates
 * its position for being displayed always on the top of the page.
 */
jQuery(function() {

	if (jQuery('.vrtkcartfixed').length) {
		var cart   = jQuery('.vrtkcartfixed');
		var offset = cart.offset();

		var divstart = cart.siblings('.vrtkcartstart');
		var divlimit = jQuery('.vrtkgotopaydiv');
		
		jQuery(window).scroll(function() {
		
			if (jQuery(window).scrollTop() > offset.top) {
				var toTop = jQuery(window).scrollTop() - offset.top + TK_CART_TOP_PADDING;

				if ((divlimit.offset().top - divstart.offset().top) >= (cart.height() + toTop)) {
					cart.stop().animate({
						marginTop: toTop,
					});
				}
			} else {
				cart.stop().animate({
					marginTop: 0,
				});
			}

		});
	}
	
});