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

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=confirmres' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" name="vrcouponform" method="post">
	
	<div class="vrcouponcodediv">
		<h3 class="vrheading3"><?php echo JText::_('VRENTERYOURCOUPON'); ?></h3>
		<input type="text" name="couponkey" class="vrcouponcodetext" />
		<button type="submit" class="vrcouponcodesubmit"><?php echo JText::_('VRAPPLYCOUPON'); ?></button>
	</div>
	
	<input type="hidden" name="date" value="<?php echo $this->args['date']; ?>" />
	<input type="hidden" name="hourmin" value="<?php echo $this->args['hourmin']; ?>" />
	<input type="hidden" name="people" value="<?php echo $this->args['people']; ?>" />
	<input type="hidden" name="table" value="<?php echo $this->args['table']; ?>" />
	
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="confirmres" />

</form>
