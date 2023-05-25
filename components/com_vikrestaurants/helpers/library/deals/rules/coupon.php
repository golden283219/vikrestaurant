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
 * Class used to apply the "Coupon" deal type.
 *
 * @since 	1.8
 */
class DealRuleCoupon extends DealRule
{
	/**
	 * Returns the deal code identifier.
	 *
	 * @return 	integer
	 */
	public function getID()
	{
		return 5;
	}

	/**
	 * Applies the deal to the cart instance, if needed.
	 *
	 * @param 	TakeAwayCart  &$cart  The cart with the items.
	 * @param 	array 		  $deal   The deal to apply.
	 *
	 * @return 	boolean 	  True if applied, false otherwise.
	 */
	public function apply(&$cart, $deal)
	{
		// always false, coupon codes can be added into the cart only manually
		return false;
	}
}
