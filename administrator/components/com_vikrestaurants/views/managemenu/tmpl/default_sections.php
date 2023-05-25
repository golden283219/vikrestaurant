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

$curr_symb = VREFactory::getConfig()->get('currencysymb');

?>

<div class="btn-toolbar" style="height:32px;">
	<div class="btn-group pull-left">
		<button type="button" class="btn btn-success" onclick="vreAddSection();">
			<i class="fas fa-plus-circle"></i>&nbsp;
			<?php echo JText::_('VRMANAGEMENU22'); ?>
		</button>
	</div>
</div>
	
<div class="row-fluid">

	<div class="span12">
		<?php echo $vik->openEmptyFieldset(); ?>
			
			<div id="vrmenusectioncont">

				<?php
				$max_section_id = $max_product_aid = 0;

				foreach ($this->menu->sections as $section)
				{
					$max_section_id = max(array($section->id, $max_section_id));

					foreach ($section->products as $prod)
					{
						$max_product_aid = max(array($prod->id, $max_product_aid));
					}
					
					$this->drawSection = $section;
					echo $this->loadTemplate('section_struct');
					unset($this->drawSection);
				} 
				?>

			</div>
		
		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>

</div>

<?php
JText::script('VRMANAGEMENU28');
?>

<script>

	var sections_cont = <?php echo ((int) $max_section_id + 1); ?>;
	var products_cont = <?php echo ((int) $max_product_aid + 1); ?>;

	var SELECTED_SECTION = null;

	var currency = Currency.getInstance();
	
	jQuery(document).ready(function() { 

		jQuery("#vrsearch-list").select2({
			placeholder: Joomla.JText._('VRMANAGEMENU28'),
			allowClear: true,
			width: 300,
		});

		vrMakeSortable('#vrmenusectioncont', '.vrmenutitle');
		vrMakeSortable('.vrmenuprodscont', '.manual-sort-handle', {axis: 'y'});
		
	});
	
	function vreAddSection() {
		var _html =	jQuery('#section-struct').clone().html();
		// make replacements
		_html = _html.replace(/__section_id__/g, sections_cont);

		jQuery('#vrmenusectioncont').append(_html);

		// unset ID assoc for new sections
		jQuery('#vrsection' + sections_cont).find('input[name^="sec_id"]').val(-1);

		// set up popovers for newly created sections
		jQuery('#vrsection' + sections_cont).find('.vr-quest-popover').popover({sanitize: false, container: 'body'});

		vrMakeSortable('#vrmenuprodscont' + sections_cont, '.vrmenutitle');

		// reach added box if out of screen
		if (isBoxOutOfMonitor(jQuery('#vrsection' + sections_cont))) {
			jQuery('html, body').animate({
				scrollTop: jQuery('#vrsection' + sections_cont).offset().top + 'px',
			});
		}

		// update sections count
		jQuery('#managemenu_sections_tab_badge').attr('data-count', jQuery('#vrmenusectioncont .vrmenusection:not(.ui-sortable-placeholder)').length);
		
		sections_cont++;
	}
	
	function vreSectionPublishedValueChanged(id, is) {
		jQuery('#vrmenusecpubhidden' + id).val(is);
	}
	
	function vreSectionHighlightValueChanged(id, is) {
		jQuery('#vrmenusechighlighthidden' + id).val(is);
	}

	function vreSectionOrderDishesValueChanged(id, is) {
		jQuery('#vrmenusecdisheshidden' + id).val(is);
	}
	
	function vreShowSectionProductsDialog(id_section) {
		SELECTED_SECTION = id_section;
		
		vrOpenJModal('products', null, true);
	}
	
	function vreAddProductToSection(product) {
		var id_section = SELECTED_SECTION;
		
		if (jQuery('#vrmenuprodscont' + id_section).find('input[data-id="' + product.id + '"]').length) {
			// product already added, do not proceed
			return false;
		}
		
		var default_charge = 0;
		
		var _html = jQuery('#product-struct').clone().html();

		// make replacements
		_html = _html.replace(/__section_id__/g, id_section);
		_html = _html.replace(/__product_id_assoc__/g, products_cont);
		_html = _html.replace(/__product_id__/g, product.id);
		_html = _html.replace(/__product_name__/g, product.name);
		_html = _html.replace(/__product_price__/g, product.price);
		_html = _html.replace(/__product_currency_price__/g, currency.format(product.price));

		jQuery('#vrmenuprodscont' + id_section).append(_html);

		jQuery('#vrmenuprodscont' + id_section).find('.hasTooltip').tooltip();

		// unset ID assoc for new products
		jQuery('#vrmenuproduct' + products_cont).find('input[name^="real_prod_id"]').val(-1);

		products_cont++;
	}

	function vreMoveBlockDown(id) {
		var next_id = jQuery('#vrmenuproduct' + id).next('.vrtk-entry-var').attr('id');
		jQuery('#vrmenuproduct' + id).insertAfter('#' + next_id);
	}

	function vreMoveBlockUp(id) {
		var prev_id = jQuery('#vrmenuproduct' + id).prev('.vrtk-entry-var').attr('id');
		jQuery('#vrmenuproduct' + id).insertBefore('#' + prev_id);
	}
	
	function vreRemoveSection(id, from_db) {
		jQuery('#vrsection' + id).remove();

		if (from_db) {
		   jQuery('#adminForm').append('<input type="hidden" name="remove_section[]" value="' + id + '" />');
		}

		// update sections count
		jQuery('#managemenu_sections_tab_badge').attr('data-count', jQuery('#vrmenusectioncont .vrmenusection:not(.ui-sortable-placeholder)').length);
	}
	
	function vreRemoveProduct(id, from_db) {
		jQuery('#vrmenuproduct' + id).remove();

		if (from_db) {
			jQuery('#adminForm').append('<input type="hidden" name="remove_product[]" value="' + id + '" />');
		}
	}
	
	function vreAddSectionTitle(id) {
		var title = jQuery('#vrmenusectext' + id).val();

		if (title.length) {
			jQuery('#vrtitle' + id).html(title);
		}
	}

	function vrGetSectionProducts(id) {
		var products = [];

		jQuery('input[name="prod_id[' + id + '][]"]').each(function() {
			products.push(parseInt(jQuery(this).val()));
		});

		return products;
	}
	
</script>
