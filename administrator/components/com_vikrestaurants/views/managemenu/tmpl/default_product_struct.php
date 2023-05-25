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

$curr_symb = VREFactory::getConfig()->get('currencysymb');

$prod = $this->drawProduct;

$format_price = isset($prod->currencyprice) ? $prod->currencyprice : VikRestaurants::printPriceCurrencySymb($prod->price);

$charge_title = JText::sprintf('VRE_PRODUCT_INC_PRICE', $format_price);

?>

<div class="vrtk-entry-var" id="vrmenuproduct<?php echo $prod->id; ?>">

	<span class="manual-sort-handle hidden-phone"><i class="fas fa-ellipsis-v"></i></span>

	<input type="text" value="<?php echo $this->escape($prod->name); ?>" data-id="<?php echo $prod->idProduct; ?>" size="32" readonly tabindex="-1" />

	<div class="input-prepend currency-field">
		<button type="button" class="btn hasTooltip" title="<?php echo $charge_title; ?>"><?php echo $curr_symb; ?></button>

		<input type="number" name="charge[<?php echo $prod->idSection; ?>][]" value="<?php echo $prod->charge; ?>" size="6" min="-<?php echo $prod->price; ?>" max="99999999" step="any" />
	</div>

	<a href="javascript: void(0);" class="trash-button-link" onClick="vreRemoveProduct(<?php echo $prod->id; ?>, 1);">
		<i class="fas fa-times"></i>
	</a>

	<a href="javascript:void(0);" class="manual-sort-arrow mobile-only" onclick="vreMoveBlockDown(<?php echo $prod->id; ?>);">
		<i class="fas fa-chevron-down"></i>
	</a>
	<a href="javascript:void(0);" class="manual-sort-arrow mobile-only" onclick="vreMoveBlockUp(<?php echo $prod->id; ?>);">
		<i class="fas fa-chevron-up"></i>
	</a>

	<input type="hidden" name="prod_id[<?php echo $prod->idSection; ?>][]" value="<?php echo $prod->idProduct; ?>" />
	<input type="hidden" name="real_prod_id[<?php echo $prod->idSection; ?>][]" value="<?php echo $prod->id; ?>" />

</div>
