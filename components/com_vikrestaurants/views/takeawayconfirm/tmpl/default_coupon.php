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
 * Template file used to display the form to let the
 * customers can redeem a coupon code.
 *
 * @since 1.8
 */

?>

<div class="vrcouponcodediv">

	<h3 class="vrheading3">
		<?php echo JText::_('VRENTERYOURCOUPON'); ?>
	</h3>
	
	<input type="text" class="vrcouponcodetext" name="couponkey" />
	
	<button type="submit" class="vrcouponcodesubmit">
		<?php echo JText::_('VRAPPLYCOUPON'); ?>
	</button>

</div>
