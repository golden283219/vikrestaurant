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

JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.select2');

$input  = JFactory::getApplication()->input;
$from 	= $input->get('from', '', 'string');
$itemid = $input->get('Itemid', 0, 'uint');

$currency = VREFactory::getCurrency();

?>

<div class="vrfront-manage-headerdiv">

	<div class="vrfront-manage-titlediv">
		<h2><?php echo JText::_('VREDITBILL'); ?></h2>
	</div>
	
	<div class="vrfront-manage-actionsdiv">
		
		<div class="vrfront-manage-btn">
			<button type="button" onClick="vrCloseBill();" id="vrfront-manage-btnclose" class="vrfront-manage-button"><?php echo JText::_('VRCLOSE'); ?></button>
		</div>

	</div>

</div>

<div class="vrfront-editbill-menus">
	<div class="vr-kitchen-no-result">
		<?php echo JText::_('VREORDERFOOD_DISABLED_BILLCLOSED'); ?>
	</div>
</div>

<div class="vrfront-food-summary">

	<div class="vrfront-food-list" id="vr-food-container">

		<?php
		foreach ($this->order->items as $food)
		{
			?>
			<div class="food-details" id="food<?php echo $food->id; ?>">

				<div class="food-details-left">
					<?php echo $food->name; ?>
				</div>

				<div class="food-details-right">
					<span class="food-quantity">x<?php echo $food->quantity; ?></span>
					<span class="food-price"><?php echo $currency->format($food->price); ?></span>
				</div>

			</div>
			<?php
		}
		?>

	</div>

	<div class="food-cost-total">
		<span class="food-total-label"><?php echo JText::_('VRTOTAL'); ?>:</span>
		<span class="food-total-value" id="vr-food-tcost"><?php echo $currency->format($this->order->bill_value); ?></span>
	</div>

</div>

<script type="text/javascript">

	function vrCloseBill() {
		<?php
		$bill_from = $input->get('bill_from');

		if ($bill_from)
		{
			// auto-redirect to caller view
			?>
			document.location.href = '<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=' . $bill_from . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		else
		{
			// fallback to reservation management page
			?>
			document.location.href = '<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opreservation.edit&cid[]=' . $this->order->id . ($from ? '&from=' . $from : '') . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>
	}

</script>
