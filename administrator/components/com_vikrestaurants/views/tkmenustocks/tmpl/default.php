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

JHtml::_('formbehavior.chosen');
JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');

$filters = $this->filters;

$vik = VREApplication::getInstance();

$deliveryLayout = new JLayoutFile('blocks.card');
?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<div class="btn-toolbar vr-btn-toolbar" style="height:32px;">
		
		<div class="btn-group pull-left input-append">
			<input type="text" name="keysearch" id="vrkeysearch" size="32" 
				value="<?php echo $filters['keysearch']; ?>" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />

			<button type="button" class="btn" onclick="document.adminForm.submit();">
				<i class="icon-search"></i>
			</button>
		</div>
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>

		<div class="btn-group pull-right hidden-phone">
			<select name="id_menu" id="vr-menu-sel" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $this->menus, 'value', 'text', $filters['id_menu']); ?>
			</select>
		</div>
	
	</div>

<?php
if (count($this->products) == 0)
{
	echo $vik->alert(JText::_('VRNOTKPRODUCT'));
}
else
{
	?>
	<div class="vr-delivery-locations-container vre-cards-container">

		<?php
		foreach ($this->products as $product)
		{
			?>
			<div class="delivery-fieldset vre-card-fieldset" id="product-fieldset-<?php echo $product->id; ?>">

				<?php
				$displayData = array();

				// fetch card ID
				$displayData['id'] = 'product-card-' . $product->id;

				// fetch image
				if ($product->image)
				{
					$displayData['image'] = VREMEDIA_URI . $product->image;
				}
				else
				{
					$displayData['image'] = VREASSETS_ADMIN_URI . 'images/entry-placeholder.png';
				}

				// fetch primary text
				$displayData['primary']  = $product->name;

				// fetch secondary text
				$displayData['secondary'] = '<span class="badge badge-info prod-stock hasTooltip" title="' . JText::_('VRMANAGETKSTOCK3') . '">' . $product->items_in_stock . '</span>';

				if ($product->options)
				{
					$displayData['secondary'] .= '<span class="badge badge-info prod-vars">' . JText::plural('VRE_N_VARIATIONS', count($product->options)) . '</span>';
				}

				$displayData['secondary'] .= '<span class="badge badge-important prod-notify hasTooltip" title="' . JText::_('VRMANAGETKSTOCK4') . '">' . $product->notify_below . '</span>';

				// fetch edit button
				$displayData['edit'] = 'openProductCard(' . $product->id . ');';

				// render layout
				echo $deliveryLayout->render($displayData);
				?>
				
			</div>
			<?php
		}
		?>

	</div>
	<?php

	foreach ($this->products as $p)
	{
		// register current product for being used in sub-template
		$this->currentProduct = $p;

		// render inspector to manage stock
		echo JHtml::_(
			'vrehtml.inspector.render',
			'product-stock-inspector-' . $p->id,
			array(
				'title'       => JText::_('VRMANAGETKMENUSTOCKS'),
				'closeButton' => true,
				'keyboard'    => false,
				'footer'      => '<button type="button" class="btn btn-success product-stock-save" data-role="save">' . JText::_('JAPPLY') . '</button>',
			),
			$this->loadTemplate('stock_modal')
		);
	}

}
?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="tkmenustocks" />
</form>

<?php
JText::script('VRMANAGETKSTOCK3');
JText::script('VRMANAGETKSTOCK4');
JText::script('VRE_N_VARIATIONS');
JText::script('VRE_N_VARIATIONS_1');
?>

<script>

	var SELECTED_INDEX = null;

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

		jQuery('#vrkeysearch').on('keydown', function(event) {
			// submit form on enter
			if (event.keyCode == 13) {
				document.adminForm.submit();
			}
		});

	});

	function clearFilters() {
		jQuery('#vrkeysearch').val('');

		document.adminForm.submit();
	}

	function openProductCard(index) {
		SELECTED_INDEX = index;
		
		// open inspector
		vreOpenInspector('product-stock-inspector-' + SELECTED_INDEX);
	}

	function refreshProductCard(index, data) {
		var card = jQuery('#product-card-' + index);

		// update secondary text
		var secondary = '<span class="badge badge-info prod-stock hasTooltip" title="' + Joomla.JText._('VRMANAGETKSTOCK3') + '">' + data.items_in_stock + '</span>';

		if (data.options.length) {
			var vars_badge_text;

			if (data.options.length == 1) {
				vars_badge_text = Joomla.JText._('VRE_N_VARIATIONS_1');
			} else {
				vars_badge_text = Joomla.JText._('VRE_N_VARIATIONS').replace(/%d/, data.options.length);
			}

			secondary += '<span class="badge badge-info prod-vars">' + vars_badge_text + '</span>';
		}

		secondary += '<span class="badge badge-important prod-notify hasTooltip" title="' + Joomla.JText._('VRMANAGETKSTOCK4') + '">' + data.notify_below + '</span>';

		card.vrecard('secondary', secondary);

		card.find('.badge.hasTooltip').tooltip();
	}

	/**
	 * INSPECTOR EVENTS
	 */

	jQuery(document).ready(function() {

		// toggle input status when checkbox value changes
		jQuery('.inspector-form').find('input[type="checkbox"]').on('change', function() {
			var checked = jQuery(this).is(':checked');

			jQuery(this).closest('tr').find('input').prop('readonly', checked ? false : true);

			if (checked) {
				jQuery(this).closest('tr').find('.stock-enabled-tip').hide();
			} else {
				jQuery(this).closest('tr').find('.stock-enabled-tip').show();
			}
		});

		// register click event for "update all variations" button
		jQuery('.inspector-form').find('tfoot button').on('click', function() {
			// get product details
			var prodRow = jQuery(this).closest('table').find('.product-row');
			var stock   = prodRow.find('input.product-stock').val();
			var notify  = prodRow.find('input.product-notify').val();

			// update variations
			jQuery('.inspector-form').find('.option-row').each(function() {
				// update only if the option is enabled
				if (jQuery(this).find('input[type="checkbox"]').is(':checked')) {
					jQuery(this).find('input.option-stock').val(stock);
					jQuery(this).find('input.option-notify').val(notify);
				}
			});
		});

		// restore input with the values registered in the hidden fields
		jQuery('.record-inspector[id^="product-stock-inspector"]').on('inspector.show', function() {
			// restore "items_in_stock" for product
			jQuery(this).find('.product-stock').val(jQuery(this).find('input[name="product_items_in_stock[]"]').val());
			// restore "notify_below" for product
			jQuery(this).find('.product-notify').val(jQuery(this).find('input[name="product_notify_below[]"]').val());

			// iterate variations
			jQuery(this).find('.option-row').each(function() {
				// restore "stock_enabled" for option
				var enabled = parseInt(jQuery(this).find('input[name^="option_stock_enabled["]').val());
				jQuery(this).find('input[type="checkbox"]').prop('checked', enabled ? true : false).trigger('change');

				// restore "items_in_stock" for option
				jQuery(this).find('.option-stock').val(jQuery(this).find('input[name^="option_items_in_stock["]').val());
				// restore "notify_below" for option
				jQuery(this).find('.option-notify').val(jQuery(this).find('input[name^="option_notify_below["]').val());
			});

		});

		// commit any changes to the stock of the selected product
		jQuery('.record-inspector[id^="product-stock-inspector"]').on('inspector.save', function() {
			var inspector = jQuery(this).closest('.record-inspector');

			var data = {};

			// get ID
			data.id = inspector.find('input[name="product_id[]"]').val();

			// update "items_in_stock" for product
			data.items_in_stock = parseInt(inspector.find('.product-stock').val());
			inspector.find('input[name="product_items_in_stock[]"]').val(data.items_in_stock);

			// update "notify_below" for product
			data.notify_below = parseInt(inspector.find('.product-notify').val());
			inspector.find('input[name="product_notify_below[]"]').val(data.notify_below);

			data.options = [];

			// iterate variations
			inspector.find('.option-row').each(function() {
				var option = {};

				// update "stock_enabled" for option
				option.enabled = jQuery(this).find('input[type="checkbox"]').is(':checked') ? 1 : 0;
				jQuery(this).find('input[name^="option_stock_enabled["]').val(option.enabled);

				// update "items_in_stock" for option
				option.items_in_stock = parseInt(jQuery(this).find('.option-stock').val());
				jQuery(this).find('input[name^="option_items_in_stock["]').val(option.items_in_stock);

				// update "notify_below" for option
				option.notify_below = parseInt(jQuery(this).find('.option-notify').val());
				jQuery(this).find('input[name^="option_notify_below["]').val(option.notify_below);

				data.options.push(option);
			});

			// refresh card
			refreshProductCard(data.id, data);

			inspector.inspector('dismiss');
		});

	});

</script>
