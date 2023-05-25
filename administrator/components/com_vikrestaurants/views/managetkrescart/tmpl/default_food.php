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

$currency = VREFactory::getCurrency();

$cardLayout = new JLayoutFile('blocks.card');

?>

<style>

	.cart-menu-wrapper {
		margin-bottom: 15px;
	}

	.cart-menu-wrapper .menu-heading {
		margin: 20px 0;
		border-bottom: 1px solid #eee;
		text-align: center;
		position: relative;
		font-size: 16px;
	}

	.cart-menu-wrapper .menu-heading span {
		background: #fff;
		position: absolute;
		transform: translate(-50%, -50%);
		padding: 0 20px;
		font-size: 16px;
		font-weight: bold;
	}

</style>

<div class="btn-toolbar" style="display:inline-block;width:100%;">

	<div class="btn-group pull-left input-append hide-with-size-320">
		<input type="text" id="prodkeysearch" size="32" value="" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" autocomplete="off" />

		<button type="button" class="btn" onclick="jQuery('#prodkeysearch').trigger('change');">
			<i class="icon-search"></i>
		</button>
	</div>

	<div class="btn-group pull-left hide-with-size-1100 vr-toolbar-setfont">
		<select id="prodmenus-select">
			<option></option>
			<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.takeawaymenus')); ?>
		</select>
	</div>

	<div class="btn-group pull-left hide-with-size-860">
		<button type="button" class="btn" onclick="clearFilters();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

</div>

<?php
$attrs = array();
$attrs['id'] = 'no-prod-results-alert';

if ($this->count)
{
	$attrs['style'] = 'display:none';
}

echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning', false, $attrs);
?>

<?php
foreach ($this->menus as $menu)
{
	?>
	<div class="cart-menu-wrapper">

		<div class="menu-heading"><span><?php echo $menu->title; ?></span></div>

		<div class="vr-delivery-locations-container vre-cards-container">

			<?php
			for ($i = 0; $i < count($menu->products); $i++)
			{
				$prod = $menu->products[$i];
				
				$displayData = array();
				$displayData['id'] = 'product-card-' . $prod->id;

				$displayData['primary'] = $prod->name;

				$displayData['secondary'] = '<span class="badge badge-important">' . $menu->title . '</span>';

				if ($prod->price > 0)
				{
					$displayData['secondary'] .= '<span class="badge badge-info">' . $currency->format($prod->price) . '</span>';
				}

				// fetch edit button
				$displayData['edit']     = 'openProductCard(' . $prod->id . ');';
				$displayData['editText'] = JText::_('VRADD');
				?>

				<div
					class="delivery-fieldset vre-card-fieldset"
					id="product-fieldset-<?php echo $prod->id; ?>"
					data-id="<?php echo $prod->id; ?>"
					data-price="<?php echo $prod->price; ?>"
					data-name="<?php echo $this->escape($prod->name); ?>"
					data-description="<?php echo $this->escape(strip_tags($prod->description)); ?>"
					data-menu="<?php echo $menu->id; ?>"
				>
					<?php echo $cardLayout->render($displayData); ?>
				</div>
				<?php
			}
			?>

		</div>

	</div>
	<?php
}
?>

<?php
JText::script('VRFILTERSELECTMENU');
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRE_ADD_PRODUCT');
JText::script('VRE_EDIT_PRODUCT');
JText::script('VRMANAGETKCARTSTOCK_DIALOG');
JText::script('VRMANAGETKCARTSTOCK_DIALOG_EMPTY');
JText::script('VRMANAGETKCARTSTOCK_DIALOG_BTN1');
JText::script('VRMANAGETKCARTSTOCK_DIALOG_BTN2');
JText::script('JCANCEL');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#prodmenus-select').select2({
			placeholder: Joomla.JText._('VRFILTERSELECTMENU'),
			allowClear: true,
			width: 200,
		});

		jQuery('#prodkeysearch, #prodmenus-select').on('change', function() {

			var search = jQuery('#prodkeysearch').val().toLowerCase();

			var at_least_one = false;

			// iterate menus first
			jQuery('.cart-menu-wrapper').each(function() {

				var at_least_one_per_menu = false;

				// iterate menu products
				jQuery(this).find('.vre-card-fieldset').each(function() {

					if (vreMatchingProdBox(this, search)) {
						jQuery(this).show();
						at_least_one = at_least_one_per_menu = true;
					} else {
						jQuery(this).hide();
					}

				});

				/**
				 * Show/hide menu boxes depending on the products that have been filtered.
				 * While filtering for a specific menu, all the products that DO NOT belong
				 * to that menu will be hidden.
				 * For this reason, any other menu box have to be hidden too.
				 */
				if (at_least_one_per_menu) {
					jQuery(this).show();
				} else {
					jQuery(this).hide();
				}

			 });

			if (at_least_one) {
				jQuery('#no-prod-results-alert').hide();
			} else {
				jQuery('#no-prod-results-alert').show();
			}

		});

		function vreMatchingProdBox(box, search) {
			// compare status
			var id_menu   = jQuery('#prodmenus-select').val();
			var prod_menu = jQuery(box).attr('data-menu');

			if (id_menu && id_menu != prod_menu) {
				// menu doesn't match, not compatible
				return false;
			}

			if (!search.length) {
				// no active search
				return true;
			}

			// search by product name
			var prod_name = jQuery(box).attr('data-name').trim().toLowerCase();

			if (prod_name.indexOf(search) !== -1) {
				return true;
			}

			// search by description
			var prod_desc = jQuery(box).attr('data-description').trim().toLowerCase();

			if (prod_desc.indexOf(search) !== -1) {
				return true;
			}

			return false;
		}

		// handle product save
		jQuery('#res-product-inspector').on('inspector.save', function() {
			var inspector = this;

			// create promise to validate the product details
			new Promise((resolve, reject) => {
				// extract product data
				var data = getProductData();

				// check if stock is supported and make sure the quantity is
				// not exceeding the amount of remaining items
				if (!data.hasOwnProperty('stock') || data.quantity <= data.stock) {
					// resolve promise, don't need to check stock
					resolve(data);
					return true;
				}

				var mess;

				if (data.stock > 0) {
					// there are still some items available
					mess = 'VRMANAGETKCARTSTOCK_DIALOG';
				} else {
					// no items available
					mess = 'VRMANAGETKCARTSTOCK_DIALOG_EMPTY';
				}

				// create stock dialog
				var stockDialog = new VikConfirmDialog(Joomla.JText._(mess));

				if (data.stock > 0) {
					// Use available action
					stockDialog.addButton(Joomla.JText._('VRMANAGETKCARTSTOCK_DIALOG_BTN1'), function(data, event) {
						// update quantity to avoid exceeding the available items
						data.quantity = data.stock;

						// resolve to add the product into the cart
						resolve(data);
					});
				}

				// Go ahead action
				stockDialog.addButton(Joomla.JText._('VRMANAGETKCARTSTOCK_DIALOG_BTN2'), function(data, event) {
					// add product in cart without editing it
					resolve(data);
				});

				// Discard action
				stockDialog.addButton(Joomla.JText._('JCANCEL'), function(data, event) {
					// reject promise, the user cancelled the save action
					reject(data);
				});

				// immediately show the dialog
				stockDialog.show(data);

			}).then(function(data) {
				// add product in cart
				addProductIntoCart(data);

				// dismiss inspector
				jQuery(inspector).inspector('dismiss');
			}).catch(function(error) {
				// do nothing in case of failure
				return false;
			});
		});
	});

	function openProductCard(id_product, index) {
		SELECTED_PROD  = id_product;
		SELECTED_INDEX = index;

		var title;

		if (typeof index === 'undefined') {
			title = Joomla.JText._('VRE_ADD_PRODUCT');
			jQuery('#res-product-delete').hide();
		} else {
			title = Joomla.JText._('VRE_EDIT_PRODUCT');
			jQuery('#res-product-delete').show();
		}

		// build URL to retrieve cart item management form
		var url = 'index.php?option=com_vikrestaurants&tmpl=component&task=tkreservation.cartitem&id_product=' + id_product;

		if (index) {
			 url += '&id_item=' + index;
		}
		
		// open inspector
		vreOpenInspector('res-product-inspector', {title: title, url: url});
	}

	function addProductIntoCart(data) {
		// inject reservation ID
		data.id_reservation = <?php echo $this->order->id; ?>;

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=tkreservation.additemajax&tmpl=component',
			data,
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				// insert product in cart
				vrCartPushItem(obj.item, obj.total, obj.taxes, obj.net);
			}, function(error) {
				if (!error.responseText) {
					// use default connection lost error
					error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
				}

				// raise error
				alert(error.responseText);
			}
		);
	}

	function removeProductFromCart(id) {
		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=tkreservation.removeitemajax&tmpl=component',
			{
				id_assoc: id, 
				id_res:   <?php echo $this->order->id; ?>,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				// remove item from cart
				vrCartRemoveItem(id, obj.total, obj.taxes, obj.net);
			},
			function(error) {
				if (!error.responseText) {
					// use default connection lost error
					error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
				}

				// raise error
				alert(error.responseText);
			}
		);
	}

	function clearFilters() {
		jQuery('#prodmenus-select').select2('val', '');
		jQuery('#prodkeysearch').val('').trigger('change');
	}

</script>
