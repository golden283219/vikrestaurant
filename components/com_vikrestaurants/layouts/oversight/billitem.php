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
 * Layout variables
 * -----------------
 * @var  integer 	  $id_assoc    The product-bill relationship ID.
 * @var  integer 	  $id_product  The product ID.
 * @var  object|null  $item        The item details if the product exists. Otherwise
 * 							       null in case the item has to be created first.
 */
extract($displayData);

?>

<div class="control-group">
	<h4><?php echo ($item !== null ? $item->name : JText::_('VRCREATENEWPROD')); ?></h4>
</div>

<?php
if ($item === null)
{
	?>
	<div class="vrfront-field">
		<span class="field-label"><?php echo JText::_('VRNAME'); ?></span>

		<span class="field-value">
			<input type="text" name="name" value="" size="32" />
		</span>
	</div>

	<div class="vrfront-field">
		<span class="field-label"><?php echo JText::_('VRPRICE'); ?></span>

		<div class="field-value currency">
			<input type="number" name="price" value="0.00" size="4" min="0" step="any" />

			<span><?php echo VREFactory::getCurrency()->getSymbol(); ?></span>
		</div>
	</div>
	<?php
}
else if (count($item->variations))
{
	?>
	<div class="vrfront-field">
		<span class="field-label"><?php echo JText::_('VRVARIATION'); ?></span>

		<div class="field-value vre-select-wrapper">
			<select name="id_option" class="vrtk-variations-reqselect vre-select">
				<?php
				foreach ($item->variations as $var)
				{
					?>
					<option
						value="<?php echo $var->id; ?>"
						<?php echo ($item->id_var == $var->id ? 'selected="selected"' : ''); ?>
					><?php echo $var->name; ?></option>
					<?php
				}
				?>
			</select>
		</div>
	</div>
	<?php
}
?>

<div class="vrfront-field">
	<span class="field-label"><?php echo JText::_('VRTKADDQUANTITY'); ?></span>

	<span class="field-value">
		<input type="number" name="quantity" value="<?php echo ($item !== null ? $item->quantity : 1); ?>" size="4" min="1" step="1" />
	</span>
</div>
			
<div class="vrfront-field">
	<span class="field-label"><?php echo JText::_('VRNOTES'); ?></span>

	<span class="field-value">
		<textarea name="notes" maxlength="128" style="width:100%;height:100px;"><?php echo ($item !== null ? $item->notes : ''); ?></textarea>
	</span>
</div>

<div class="vrfront-field">
	<span class="field-label"></span>

	<span class="field-value">
		<button type="button" class="vrtk-addtocart-button" onClick="vrPostItem(<?php echo ($item !== null ? 1 : 0); ?>);">
			<?php echo strtoupper(JText::_($id_assoc >= 0 ? 'VRSAVE' : 'VRTKADDOKBUTTON')); ?>
		</button>
	</span>
</div>

<input type="hidden" name="item_index" value="<?php echo $id_assoc; ?>" />
<input type="hidden" name="id_entry" value="<?php echo $id_product; ?>" />
