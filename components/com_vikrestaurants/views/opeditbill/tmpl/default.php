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

$vik = VREApplication::getInstance();

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

<div class="vrfront-search-toolbar">
	<input type="hidden" id="vr-users-select" value="" style="width:100%;" />
</div>

<div class="vrfront-editbill-menus">

	<form action="index.php" method="POST" id="editbillform">

		<div id="vrfront-menus-container">

			<?php
			foreach ($this->menus as $menu)
			{ 
				if (empty($menu->image))
				{
					$menu->image = 'menu_default_icon.jpg';
				}
				?>
				<div class="vrfront-menu-block">

					<div class="menu-image">
						<a href="javascript: void(0);" onclick="openMenuSections(<?php echo $menu->id; ?>, this);">
							<img src="<?php echo VREMEDIA_URI . $menu->image; ?>" />
						</a>
					</div>

					<div class="menu-title"><?php echo $menu->name; ?></div>

				</div>
				<?php
			}
			?>

			<a href="javascript: void(0);" onclick="openProductDetails(0);">
				<div class="vrfront-menu-block ghost">
					<i class="fas fa-plus"></i>
				</div>
			</a>

		</div>

		<div id="vr-sections-container">

		</div>

		<div id="vr-products-container">

		</div>

		<div id="vr-product-details">

		</div>

		<input type="hidden" name="id" value="<?php echo $this->order->id; ?>" />

	</form>

</div>

<div class="vrfront-food-summary">

	<div class="vrfront-food-list" id="vr-food-container">

		<?php
		foreach ($this->order->items as $food)
		{
			?>
			<div class="food-details" id="food<?php echo $food->id; ?>">

				<div class="food-details-left">
					<a href="javascript: void(0);" onclick="openProductDetails(<?php echo $food->id_product; ?>, <?php echo $food->id; ?>);">
						<?php echo $food->name; ?>
					</a>
				</div>

				<div class="food-details-right">
					<span class="food-quantity">x<?php echo $food->quantity; ?></span>
					<span class="food-price"><?php echo $currency->format($food->price); ?></span>
					<span class="food-remove">
						<a href="javascript: void(0);" onclick="removeProduct(<?php echo $food->id; ?>);">
							<i class="fas fa-times"></i>
						</a>
					</span>
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

<?php
JText::script('VRSEARCHPRODPLACEHOLDER');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('#vr-users-select').select2({
			placeholder: Joomla.JText._('VRSEARCHPRODPLACEHOLDER'),
			allowClear: true,
			width: 'resolve',
			minimumInputLength: 2,
			ajax: {
				url: '<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opreservation.searchproductajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				dataType: 'json',
				type: 'POST',
				quietMillis: 50,
				data: function(term) {
					return {
						term: term,
					};
				},
				results: function(data) {
					return {
						results: jQuery.map(data, function (item) {
							return {
								text: item.name,
								id: item.id,
							};
						}),
					};
				},
			},
			formatSelection: function(data) {
				if (jQuery.isEmptyObject(data.name)) {
					// display data retured from ajax parsing
					return data.text;
				}

				// display pre-selected value
				return data.name;
			}
		});

		jQuery('#vr-users-select').on('change', function() {
			var val = jQuery(this).val();

			if (val.length) {
				closeSections();
				closeProducts();

				openProductDetails(val);
			} else {
				closeProductDetails();
			}
		});

	});

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

	// SECTIONS

	function openMenuSections(id, wrapper) {
		closeSections();

		if (id == 0) {
			jQuery(wrapper).closest('.vrfront-menu-block').addClass('active');
			
			// dispatch load products
			openSectionProducts(0);
			return;
		}

		if (jQuery('#vrsections' + id).length) {

			jQuery(wrapper).closest('.vrfront-menu-block').addClass('active');

			var sections = jQuery('#vrsections' + id).show();

			// animate to the sections position
 			jQuery('html, body').animate({ scrollTop: sections.offset().top + 2 });

		} else {
			loadMenuSections(id, function() {
				openMenuSections(id, wrapper);
			});
		}
	}

	function loadMenuSections(id, callback) {
		openLoadingOverlay(true);

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=opreservation.menusectionsajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				id_menu: id,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				var html = '<div id="vrsections' + id + '" class="vrfront-menu-sections">\n';

				jQuery.each(obj, function(i, section) {
					if (!section.image.length) {
						section.image = 'menu_default_icon.jpg';
					}

					html += '<div class="vrfront-section-block">\n';

					html += '<div class="section-image">\n';
					html += '<a href="javascript: void(0);" onclick="openSectionProducts(' + section.id + ', this);">';
					html += '<img src="<?php echo VREMEDIA_URI; ?>' + section.image + '" />\n';
					html += '</a>\n';
					html += '</div>\n';

					html += '<div class="section-title">' + section.name + '</div>\n';

					html += '</div>\n';
				});

				html += '</div>\n';

				closeLoadingOverlay();

				jQuery('#vr-sections-container').append(html);
				callback();
			},
			function(err) {
				closeLoadingOverlay();
			}
		);
	}

	function closeSections() {
		// close sections
		jQuery('.vrfront-menu-sections').hide();

		jQuery('.vrfront-menu-block').removeClass('active');

		// close products
		closeProducts();
	}

	// PRODUCTS

	function openSectionProducts(id, wrapper) {
		closeProducts();

		// register the currently open section ID
		VIKRESTAURANTS_SECTION_ID = id;

		if (jQuery('#vrproducts' + id).length) {

			jQuery(wrapper).closest('.vrfront-section-block').addClass('active');

			var products = jQuery('#vrproducts' + id).show();

			// animate to the products position
 			jQuery('html, body').animate({ scrollTop: products.offset().top + 2 });

		} else {
			loadSectionProducts(id, function() {
				openSectionProducts(id, wrapper);
			});
		}
	}

	var VIKRESTAURANTS_SECTION_ID = 0;

	function loadSectionProducts(id, callback) {
		openLoadingOverlay(true);

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=opreservation.sectionproductsajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				id_section: id,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				var html = '<div id="vrproducts' + id + '" class="vrfront-section-products">\n';

				jQuery.each(obj, function(i, prod) {
					if (!prod.image.length) {
						prod.image = 'menu_default_icon.jpg';
					}

					html += '<div class="vrfront-product-block">\n';

					html += '<div class="product-image">\n';
					html += '<a href="javascript: void(0);" onclick="openProductDetails(' + prod.id + ', 0, this);">';
					html += '<img src="<?php echo VREMEDIA_URI; ?>' + prod.image + '"/>\n';
					html += '</a>\n';
					html += '</div>\n';

					html += '<div class="product-title">' + prod.name + '</div>\n';

					html += '</div>\n';
				});

				html += '</div>\n';

				closeLoadingOverlay();

				jQuery('#vr-products-container').append(html);
				callback();
			},
			function(error) {
				closeLoadingOverlay();
			}
		);
	}

	function closeProducts() {
		// close products
		jQuery('.vrfront-section-products').hide();

		jQuery('.vrfront-section-block').removeClass('active');

		// close product details
		closeProductDetails();
	}

	// PROD DETAILS

	var PRODUCT_DETAILS = {};

	function openProductDetails(id, assoc, wrapper) {
		if (id == 0) {
			// close sections because no menu is selected
			closeSections();
		}

		jQuery('.vrfront-product-block').removeClass('active');

		if (assoc === undefined) {
			assoc = 0;
		}

		if (wrapper) {
			jQuery(wrapper).closest('.vrfront-product-block').addClass('active');
		}

		var hash = id + '.' + assoc;

		if (PRODUCT_DETAILS.hasOwnProperty(hash)) {
			// get from local pool
			var det = jQuery('#vr-product-details').html(PRODUCT_DETAILS[hash]);

			// animate to the form position
 			jQuery('html, body').animate({ scrollTop: det.offset().top - 5 });
		} else {
			// get from controller via AJAX
			openLoadingOverlay(true);

			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=opreservation.getproducthtml&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				{
					id_product: id,
					id_assoc: assoc,
				},
				function(resp) {
					var obj = jQuery.parseJSON(resp);

					if (obj.status) {
						var det = jQuery('#vr-product-details').html(obj.html);

						// animate to the form position
 						jQuery('html, body').animate({ scrollTop: det.offset().top - 5 });

						PRODUCT_DETAILS[hash] = obj.html;
					}

					closeLoadingOverlay();
				},
				function(err) {
					closeLoadingOverlay();
				}
			);
		}
	}

	function closeProductDetails() {
		// close product details
		jQuery('#vr-product-details').html('');

		jQuery('.vrfront-product-block').removeClass('active');
	}

	function vrPostItem(exists) {
		openLoadingOverlay(true);

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=opreservation.additemajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			jQuery('#editbillform').serialize() + '&id_section=' + VIKRESTAURANTS_SECTION_ID,
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				if (obj.status) {
					
					var html = '<div class="food-details" id="food' + obj.id + '">\n'+
						'<div class="food-details-left">\n'+
							'<a href="javascript: void(0);" onclick="openProductDetails(' + obj.object.item_id + ', ' + obj.id + ')">' + obj.object.item_name + '</a>\n'+
						'</div>\n'+
						'<div class="food-details-right">\n'+
							'<span class="food-quantity">x' + obj.object.quantity + '</span>\n'+
							'<span class="food-price">' + Currency.getInstance().format(obj.object.price) + '</span>\n'+
							'<span class="food-remove">\n'+
								'<a href="javascript: void(0);" onclick="removeProduct(' + obj.id + ')">\n'+
									'<i class="fas fa-times"></i>\n'+
								'</a>\n'+
							'</span>\n'+
						'</div>\n'+
					'</div>\n';

					if (jQuery('#food' + obj.id).length) {
						// replace existing item
						jQuery('#food' + obj.id).replaceWith(html);

						// clean storage to be always updated
						var hash = obj.object.item_id + '.' + obj.id;
						delete PRODUCT_DETAILS[hash];
					} else {
						// otherwise append it
						jQuery('#vr-food-container').append(html);
					}

					// update total cost
					updateTotalCost(obj.grand_total);

					// always close product details on insert
					closeProductDetails();

					if (!exists) {
						// remove "other" section to re-load items correctly
						jQuery('#vrproducts0').remove();
					}
				} else {
					alert(obj.error);
				}

				closeLoadingOverlay();
			},
			function(err) {
				closeLoadingOverlay();
			}
		);
	}

	function removeProduct(id) {
		// always close product details on delete
		closeProductDetails();

		UIAjax.do(
			'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=opreservation.removeitemajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
			{
				id_assoc: id,
				id_res: <?php echo $this->order->id; ?>,
			},
			function(resp) {
				jQuery('#food' + id).remove();

				updateTotalCost(resp);
			}
		);
	}

	function updateTotalCost(grand_total) {
		jQuery('#vr-food-tcost').html(Currency.getInstance().format(grand_total));
	}

</script>
