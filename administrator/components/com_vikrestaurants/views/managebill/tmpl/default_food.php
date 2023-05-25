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

$items_optgroup = array(
	'published'   => 'VRSYSPUBLISHED1',
	'unpublished' => 'VRSYSPUBLISHED0',
	'hidden'      => 'VRSYSHIDDEN'
);

$currency = VREFactory::getCurrency();

$cardLayout = new JLayoutFile('blocks.card');

?>

<div class="btn-toolbar" style="display:inline-block;width:100%;">

	<div class="btn-group pull-left input-append hide-with-size-320">
		<input type="text" id="prodkeysearch" size="28" value="" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" autocomplete="off" />

		<button type="button" class="btn" onclick="jQuery('#prodkeysearch').trigger('change');">
			<i class="icon-search"></i>
		</button>
	</div>

	<?php
	$options = array(
		JHtml::_('select.option', 1, JText::_('VRSYSPUBLISHED1')),
		JHtml::_('select.option', 0, JText::_('VRSYSPUBLISHED0')),
		JHtml::_('select.option', 2, JText::_('VRSYSHIDDEN')),
	);
	?>
	<div class="btn-group pull-left hide-with-size-1100 vr-toolbar-setfont">
		<select id="prodstatus-select">
			<option></option>
			<?php echo JHtml::_('select.options', $options); ?>
		</select>
	</div>

	<div class="btn-group pull-left hide-with-size-860">
		<button type="button" class="btn" onclick="clearFilters();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

	<div class="btn-group pull-left">
		<button type="button" class="btn" id="vr-createnew-btn" onclick="openNewProductCard();">
			<span class="hidden-phone"><?php echo JText::_('VRCREATENEWPROD'); ?></span>
			<i class="fas fa-plus-circle mobile-only"></i>
		</button>
	</div>

</div>

<?php
$attrs = array();
$attrs['id'] = 'no-prod-results-alert';

if ($this->products)
{
	$attrs['style'] = 'display:none';
}

echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning', false, $attrs);
?>

<div class="vr-delivery-locations-container vre-cards-container">

	<?php
	for ($i = 0; $i < count($this->products); $i++)
	{
		$prod = $this->products[$i];
		
		$displayData = array();
		$displayData['id'] = 'product-card-' . $prod->id;

		$displayData['primary'] = $prod->name;

		if ($prod->price > 0)
		{
			$displayData['secondary'] = '<span class="badge badge-info">' . $currency->format($prod->price) . '</span>';
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
			data-status="<?php echo $prod->hidden ? 2 : $prod->published; ?>"
			data-variations="<?php echo $this->escape(json_encode($prod->options)); ?>"
		>
			<?php echo $cardLayout->render($displayData); ?>
		</div>
		<?php
	}
	?>

</div>

<div style="display:none;" id="item-struct">
	
	<?php
	// create delivery location structure for new items
	$displayData = array();
	$displayData['id']        = 'product-card-{id}';
	$displayData['primary']   = '';
	$displayData['secondary'] = '';
	$displayData['edit']      = true;
	$displayData['editText']  = JText::_('VRADD');

	echo $cardLayout->render($displayData);
	?>

</div>

<?php
JText::script('VRTKGROUPVARPLACEHOLDER');
JText::script('VRE_ADD_PRODUCT');
JText::script('VRE_EDIT_PRODUCT');
JText::script('VRSYSTEMCONNECTIONERR');
?>

<script>

	var SELECTED_PROD  = null;
	var SELECTED_INDEX = null;

	jQuery(document).ready(function() {

		jQuery('#prodstatus-select').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRTKGROUPVARPLACEHOLDER'),
			allowClear: true,
			width: 150,
		});

		jQuery('#prodkeysearch, #prodstatus-select').on('change', function() {

			var search = jQuery('#prodkeysearch').val().toLowerCase();

			var at_least_one = false;

			jQuery('.vre-card-fieldset').each(function() {

				if (vreMatchingProdBox(this, search)) {
					jQuery(this).show();
					at_least_one = true;
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
			var status      = jQuery('#prodstatus-select').val();
			var prod_status = jQuery(box).attr('data-status');

			if (status && status != prod_status) {
				// status doesn't match, not compatible
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

		// create new product

		jQuery('#create-product-inspector').on('inspector.show', function() {
			// clear form when opening the inspector
			clearHiddenProductForm();
		});

		jQuery('#create-product-save').on('click', function() {
			// validate form
			if (!newProdValidator.validate()) {
				return false;
			}

			// get product details
			var data = getHiddenProductData();

			// create product and insert into cart
			createProductAndInsert(data);

			jQuery('#create-product-inspector').inspector('dismiss');
		});

		// add product to reservation

		jQuery('#res-product-inspector').on('inspector.show', function() {
			var data = {};
			
			// in case the PRODUCT ID is a number, extract the product data
			if (SELECTED_PROD !== undefined && SELECTED_PROD !== null) {
				var prod = jQuery('#product-fieldset-' + SELECTED_PROD);
				
				data.id         = prod.attr('data-id');
				data.name       = prod.attr('data-name');
				data.price      = prod.attr('data-price');
				data.variations = JSON.parse(prod.attr('data-variations'));
			}

			// in case the INDEX ID is a number, extract the reservation product data
			if (SELECTED_INDEX !== undefined && SELECTED_INDEX !== null) {
				var prod = jQuery('#vrtk-order-cart-item' + SELECTED_INDEX);

				data.index     = prod.attr('data-index');
				data.id_option = prod.attr('data-option');
				data.quantity  = prod.attr('data-quantity');
				data.price     = prod.attr('data-price');
				data.notes     = prod.attr('data-notes');

				// divide price by quantity as the database contains the full amount
				data.price = parseFloat(data.price) / parseInt(data.quantity);
			}

			// fill the form with the retrieved data
			fillProductForm(data);
		});

		jQuery('#res-product-save').on('click', function() {
			// validate form
			if (!addProdValidator.validate()) {
				return false;
			}

			// get product details
			var data = getProductData();

			// add product in cart
			addProductIntoCart(data);

			jQuery('#res-product-inspector').inspector('dismiss');
		});

	});

	function openNewProductCard() {
		// open inspector
		vreOpenInspector('create-product-inspector');
	}

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
		
		// open inspector
		vreOpenInspector('res-product-inspector', {title: title});
	}

	function addProductIntoCart(data) {
		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=reservation.additemajax&tmpl=component',
			{
				id_reservation: <?php echo $this->bill->id; ?>,
				id:             data.index,
				id_product:     data.id,
				id_option:      data.id_option,
				price:          data.price,
				quantity:       data.quantity,
				notes:          data.notes,
			}, function(resp) {
				var obj = jQuery.parseJSON(resp);

				// insert product in cart
				vrCartPushItem(obj.item, obj.total);
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
			'index.php?option=com_vikrestaurants&task=reservation.removeitemajax&tmpl=component',
			{
				id_assoc: id, 
				id_res:   <?php echo $this->bill->id; ?>,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				// remove item from cart
				vrCartRemoveItem(id, obj);
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

	function createProductAndInsert(data) {
		var status = jQuery('#prodstatus-select').val();

		// create post data
		var post = {
			name:   data.name,
			price:  data.price,
			hidden: 1,
		};

		if (status.length) {
			// create the item for the specified status
			post.hidden    = status == 2 ? 1 : 0;
			post.published = status == 2 ? 0 : status;
		}

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=menusproduct.saveajax&tmpl=component',
			post,
			function(resp) {
				// decode response
				var item = jQuery.parseJSON(resp);

				// push product within cards list
				pushProductIntoList(item);

				// add not specified data
				item.index     = 0;
				item.id_option = 0;

				// merge form data with product details
				Object.assign(item, data);

				// add product into the cart
				addProductIntoCart(item);
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

	function pushProductIntoList(item) {
		// obtain card clone
		var html = jQuery('#item-struct').clone().html();

		// replace ID placeholder with product ID
		html = html.replace(/{id}/, item.id);

		// push card within the list
		jQuery('.vre-cards-container').append('<div class="delivery-fieldset vre-card-fieldset" id="product-fieldset-' + item.id + '">' + html + '</div>');

		var card = jQuery('#product-card-' + item.id);

		// update visible card details
		card.vrecard('primary', item.name);

		if (item.price > 0) {
			card.vrecard('secondary', '<span class="badge badge-info">' + Currency.getInstance().format(item.price) + '</span>');
		}

		card.vrecard('edit', 'openProductCard(' + item.id + ')');

		// set up card hidden data
		var fieldset = jQuery('#product-fieldset-' + item.id);

		fieldset.attr('data-id', item.id);
		fieldset.attr('data-price', item.price);
		fieldset.attr('data-name', item.name);
		fieldset.attr('data-description', item.description);
		fieldset.attr('data-status', item.hidden ? 2 : item.published);
		fieldset.attr('data-variations', '{}');

		// trigger search to update results
		jQuery('#prodkeysearch').trigger('change');
	}

	function clearFilters() {
		jQuery('#prodstatus-select').select2('val', '');
		jQuery('#prodkeysearch').val('').trigger('change');
	}

</script>
