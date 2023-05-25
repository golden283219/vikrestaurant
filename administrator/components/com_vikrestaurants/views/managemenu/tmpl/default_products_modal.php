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

<div class="vre-products-modal">

	<div class="btn-toolbar" style="height: 32px;">

		<div class="btn-group pull-left input-append hide-with-size-320">
			<input type="text" id="prodkeysearch" size="32" value="" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" autocomplete="off" />

			<button type="button" class="btn" onclick="jQuery('#prodkeysearch').trigger('change');">
				<i class="icon-search"></i>
			</button>
		</div>

		<div class="btn-group pull-left hide-with-size-390">
			<button type="button" class="btn" onclick="jQuery('#prodkeysearch').val('').trigger('change');"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="btn-group pull-left hide-with-size-540">
			<button type="button" class="btn" onclick="vreToggleSelectedProducts(this);" id="prod-selection-count">
				<i class="fas fa-dot-circle"></i>&nbsp;&nbsp;
				<span class="text-box"></span>
			</button>
		</div>

		<div class="btn-group pull-right hide-with-size-860">
			<button type="button" class="btn" onclick="vreSelectAllProducts(1);"><?php echo JText::_('JGLOBAL_SELECTION_ALL'); ?></button>
			<button type="button" class="btn" onclick="vreSelectAllProducts(0);"><?php echo JText::_('JGLOBAL_SELECTION_NONE'); ?></button>
		</div>

	</div>

	<div class="row-fluid">

		<div class="span12">
			<?php echo $vik->openEmptyFieldset(); ?>

				<?php
				$attrs = array();
				$attrs['id'] = 'no-prod-results-alert';

				if ($this->products)
				{
					$attrs['style'] = 'display:none';
				}

				echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning', false, $attrs);
				?>

				<div class="vre-products-gallery vre-cards-container">

					<?php
					foreach ($this->products as $prod)
					{
						if (empty($prod->image) || !is_file(VREMEDIA . DIRECTORY_SEPARATOR . $prod->image))
						{
							$image = VREASSETS_ADMIN_URI . 'images/product-placeholder.png';
						}
						else
						{
							$image = VREMEDIA_URI . $prod->image;
						}

						$description = strip_tags($prod->description);

						$displayData = array();

						// fetch image
						$displayData['image'] = $image;

						// fetch primary block
						$displayData['primary']  = '<strong class="product-name">' . $prod->name . '</strong>';
						$displayData['primary'] .= '<span class="product-price">' . $currency->format($prod->price) . '</span>';

						// fetch secondary block
						if ($description)
						{
							$displayData['secondary'] = $description;
						}

						?>
						<div class="vre-product-block vre-card-fieldset"
							data-id="<?php echo $prod->id; ?>"
							data-name="<?php echo $this->escape($prod->name); ?>"
							data-price="<?php echo $prod->price; ?>"
							data-description="<?php echo $this->escape($description); ?>"
							data-selected="0"
						>
							
							<?php echo $cardLayout->render($displayData); ?>

						</div>
						<?php
					}
					?>

				</div>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	</div>

</div>

<?php
JText::script('VRE_DEF_N_SELECTED');
JText::script('VRE_DEF_N_SELECTED_1');
JText::script('VRE_DEF_N_SELECTED_0');
?>

<script type="text/javascript">

	jQuery(document).ready(function(){

		jQuery('.vre-product-block').on('click', function() {
			var checked = parseInt(jQuery(this).attr('data-selected'));

			jQuery(this).attr('data-selected', (checked + 1) % 2);

			vreUpdateProductSelectionCount();
		});

		jQuery('#prodkeysearch').on('change', function() {

			var search = jQuery(this).val().toLowerCase();

			var showSelected = jQuery('#prod-selection-count').hasClass('active');

			var at_least_one = false;

			jQuery('.vre-product-block').each(function() {

				if ((!search.length || vreMatchingProdBox(this, search))
					// and make sure the box is selected when viewing selected records only
					&& (!showSelected || jQuery(this).attr('data-selected') == 1)) {
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

	});

	function vreSelectAllProducts(is) {
		jQuery('.vre-product-block:visible').each(function() {
			jQuery(this).attr('data-selected', is);
		});

		vreUpdateProductSelectionCount();
	}

	function vreMatchingProdBox(box, search) {
		// search by product name
		var prod_name = jQuery(box).data('name').trim().toLowerCase();

		if (prod_name.indexOf(search) !== -1) {
			return true;
		}

		// search by description
		var prod_desc = jQuery(box).data('description').trim().toLowerCase();

		if (prod_desc.indexOf(search) !== -1) {
			return true;
		}

		return false;
	}

	function vreUpdateProductSelectionCount() {
		var count = jQuery('.vre-product-block[data-selected="1"]').length;

		var text = '';

		switch (count) {
			case 0:
				text = Joomla.JText._('VRE_DEF_N_SELECTED_0');
				break;

			case 1:
				text = Joomla.JText._('VRE_DEF_N_SELECTED_1');
				break;

			default:
				text = Joomla.JText._('VRE_DEF_N_SELECTED').replace(/%d/, count);
		}

		jQuery('#prod-selection-count .text-box').text(text);

		if (jQuery('#prod-selection-count').hasClass('active')) {
			jQuery('#prodkeysearch').trigger('change');
		}
	}

	function vreToggleSelectedProducts(btn) {
		if (jQuery(btn).hasClass('active')) {
			jQuery(btn).removeClass('active');

			jQuery(btn).find('i').attr('class', 'fas fa-dot-circle');
		} else {
			jQuery(btn).addClass('active');

			jQuery(btn).find('i').attr('class', 'fas fa-check-circle');
		}

		jQuery('#prodkeysearch').trigger('change');
	}

	function vreGetSelectedProducts() {
		var products = [];

		jQuery('.vre-product-block[data-selected="1"]').each(function() {
			products.push({
				id: jQuery(this).data('id'),
				name: jQuery(this).data('name'),
				price: jQuery(this).data('price'),
				description: jQuery(this).data('description'),
			});
		});

		return products;
	}

	function vreInitProductsLayout(products) {

		jQuery('.vre-product-block').each(function() {
			var id = parseInt(jQuery(this).data('id'));

			jQuery(this).attr('data-selected', products.indexOf(id) === -1 ? 0 : 1);
		});

		vreUpdateProductSelectionCount();
	}

</script>
