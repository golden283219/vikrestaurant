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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.sitescripts.animate');
JHtml::_('vrehtml.assets.toast', 'bottom-center');
JHtml::_('vrehtml.assets.fontawesome', 'bottom-center');

$item = $this->item;

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<!-- display "breadcrumb" -->

<div class="vrtk-itemdet-category">

	<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeaway' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
		<?php echo JText::_('VRTAKEAWAYALLMENUS'); ?>
	</a>

	<span class="arrow-separator">&raquo;</span>

	<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeaway&takeaway_menu=' . $item->menu->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
		<?php echo $item->menu->title; ?>
	</a>

</div>

<?php
/**
 * Changed "takeaway_entry" with "takeaway_item" within the form action.
 * This field is required for those products that own at least
 * a topping group assigned to a specific variation. In this case,
 * after switching variation, the form is self-submitted to reload 
 * the toppings to show. Since this field were missing, the view 
 * wasn't able to recover the correct product, causing a redirect
 * to the takeaway list page.
 *
 * @since 1.7.4
 */
?>
<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeawayitem&takeaway_item=' . $item->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" name="vrtkitemform" id="vrtkitemform">

	<div class="vrtk-itemdet-page">
			
		<!-- Product Wrapper -->
		<div class="vrtk-itemdet-product">
			<?php
			// display the product details with a sub-template
			echo $this->loadTemplate('item');
			?>
		</div>

	</div>

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="takeawayitem" />
	<input type="hidden" name="id_entry" value="<?php echo $item->id; ?>" />
	<input type="hidden" name="item_index" value="-1" />

</form>

<!-- delimiter for take-away cart module -->

<div class="vrtkgotopaydiv">&nbsp;</div>

<?php
if (count($this->attributes))
{
	?>
	<div class="vrtk-attributes-legend">
		<?php
		foreach ($this->attributes as $attr)
		{ 
			?>
			<div class="vrtk-attribute-box">
				<img src="<?php echo VREMEDIA_URI . $attr->icon; ?>" />
				<span><?php echo $attr->name; ?></span>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Creates the popup that will be used to display the details
 * of the products that are going to be added.
 *
 * The popup will be shown when trying to edit a product
 * from the cart.
 */
echo $this->loadTemplate('overlay');

if ($this->reviews !== false)
{
	// display the reviews list by using a sub-template
	echo $this->loadTemplate('reviews');
}
