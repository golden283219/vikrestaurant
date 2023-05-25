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

$vik = VREApplication::getInstance();

$product = $this->currentProduct;

$help = array();

// "items in stock" help popover
$help[] = $vik->createPopover(array(
	'title'     => JText::_('VRMANAGETKSTOCK3'),
	'content'   => JText::_('VRMANAGETKSTOCK3_HELP'),
	'placement' => 'left',
));

// "notify below" help popover
$help[] = $vik->createPopover(array(
	'title'     => JText::_('VRMANAGETKSTOCK4'),
	'content'   => JText::_('VRMANAGETKSTOCK4_HELP'),
	'placement' => 'left',
));

?>

<div class="inspector-form" id="inspector-stock-form-<?php echo $product->id; ?>">

	<div class="inspector-fieldset">

		<!-- TOPPINGS SELECTION - Select -->
		<div class="control-group">
			<table class="inspector-selection-table">

				<thead>
					<tr>
						<th width="30%" style="text-align: left;"><?php echo JText::_('VRMANAGETKSTOCK1'); ?></th>
						<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGETKSTOCK3') . $help[0]; ?></th>
						<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKSTOCK4') . $help[1]; ?></th>
					</tr>
				</thead>

				<tbody>
					<tr class="product-row">
						<td>
							<input type="hidden" name="product_id[]" value="<?php echo $product->id; ?>" />

							<strong><?php echo $product->name; ?></strong>
						</td>

						<td>
							<input type="hidden" name="product_items_in_stock[]" value="<?php echo $product->items_in_stock; ?>" />

							<input type="number" value="<?php echo $product->items_in_stock; ?>" size="6" min="0" max="999999" step="1" class="product-stock" />
						</td>

						<td>
							<input type="hidden" name="product_notify_below[]" value="<?php echo $product->notify_below; ?>" />

							<input type="number" value="<?php echo $product->notify_below; ?>" size="6" min="0" max="999999" step="1" class="product-notify" />
						</td>
					</tr>

					<?php
					foreach ($product->options as $option)
					{
						?>
						<tr class="option-row">
							<td>
								<input type="hidden" name="option_id[<?php echo $product->id; ?>][]" value="<?php echo $option->id; ?>" />
								<input type="hidden" name="option_stock_enabled[<?php echo $product->id; ?>][]" value="<?php echo $option->stock_enabled; ?>" />

								<input type="checkbox" value="1" id="option-enabled-<?php echo $product->id; ?>-<?php echo $option->id; ?>" <?php echo ($option->stock_enabled ? 'checked="checked"' : ''); ?> />

								<span style="margin-left: 4px;">
									<label for="option-enabled-<?php echo $product->id; ?>-<?php echo $option->id; ?>"><?php echo $option->name; ?></label>

									<span class="stock-enabled-tip" style="<?php echo ($option->stock_enabled ? 'display:none;' : ''); ?>">
										<?php
										echo $vik->createPopover(array(
											'title'     => JText::_('VRMANAGECONFIGTKSECTION2'),
											'content'   => JText::_('VRE_OPTION_STOCK_DISABLED_HELP'),
											'placement' => 'top',
										));
										?>
									</span>
								</span>
							</td>

							<td>
								<input type="hidden" name="option_items_in_stock[<?php echo $product->id; ?>][]" value="<?php echo $option->items_in_stock; ?>" />

								<input type="number" value="<?php echo $option->items_in_stock; ?>" size="6" min="0" max="999999" step="1" class="option-stock" />
							</td>

							<td>
								<input type="hidden" name="option_notify_below[<?php echo $product->id; ?>][]" value="<?php echo $option->notify_below; ?>" />

								<input type="number" value="<?php echo $option->notify_below; ?>" size="6" min="0" max="999999" step="1" class="option-notify" />
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>

				<?php
				if ($product->options)
				{
					?>
					<tfoot>
						<tr>
							<td colspan="3" style="text-align: right;">
								<button type="button" class="btn"><?php echo JText::_('VRMANAGETKSTOCK6'); ?></button>
							</td>
						</tr>
					</tfoot>
					<?php
				}
				?>

			</table>
		</div>
	</div>

</div>
