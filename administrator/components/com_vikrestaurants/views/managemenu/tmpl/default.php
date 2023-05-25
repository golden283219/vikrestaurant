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
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.fancybox');

$vik = VREApplication::getInstance();

// always use default tab while creating a new record
$active_tab = $this->menu->id ? $this->getActiveTab('managemenu_details', $this->menu->id) : 'managemenu_details';

// Obtain media manager modal before displaying the first field.
// In this way, we can display the modal outside the bootstrap panels.
$mediaManagerModal = JHtml::_('vrehtml.mediamanager.modal');

?>

<form name="adminForm" id="adminForm" action="index.php" method="post">

	<?php echo $vik->bootStartTabSet('managemenu', array('active' => $active_tab, 'cookie' => $this->getCookieTab($this->menu->id)->name)); ?>

		<?php
		echo $vik->bootAddTab('managemenu', 'managemenu_details', JText::_('VRE_MENU'));
		echo $this->loadTemplate('details');
		echo $vik->bootEndTab();

		$options = array(
			'badge' => count($this->menu->sections),
		);

		echo $vik->bootAddTab('managemenu', 'managemenu_sections', JText::_('VRMANAGEMENU20'), $options);
		echo $this->loadTemplate('sections');
		echo $vik->bootEndTab();

		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewMenu". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			echo $vik->bootAddTab('managemenu', 'managemenu_custom', JText::_('VRE_CUSTOM_FIELDSET'));
			echo $vik->openEmptyFieldset();
			echo $custom;
			echo $vik->closeEmptyFieldset();
			echo $vik->bootEndTab();
		}
		?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $this->menu->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<div style="display:none;" id="section-struct">
	
	<?php
	// create section structure for new items
	$this->drawSection = new stdClass;
	$this->drawSection->id          = '__section_id__';
	$this->drawSection->name        = JText::_('VRMANAGEMENU27');
	$this->drawSection->image       = '';
	$this->drawSection->description = '';
	$this->drawSection->published   = 0;
	$this->drawSection->highlight   = 0;
	$this->drawSection->orderdishes = 0;
	$this->drawSection->products    = array();

	echo $this->loadTemplate('section_struct');

	unset($this->drawSection);
	?>

</div>

<div style="display:none;" id="product-struct">

	<?php
	// create product structure for new items
	$this->drawProduct = new stdClass;
	$this->drawProduct->id            = '__product_id_assoc__';
	$this->drawProduct->idProduct     = '__product_id__';
	$this->drawProduct->idSection     = '__section_id__';
	$this->drawProduct->name          = '__product_name__';
	$this->drawProduct->price         = '__product_price__';
	$this->drawProduct->currencyprice = '__product_currency_price__';
	$this->drawProduct->charge        = 0.00;

	echo $this->loadTemplate('product_struct');

	unset($this->drawProduct);
	?>

</div>

<?php
// display products selection modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-products',
	array(
		'title'       => JText::_('VRMANAGEMENU23'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'footer'	  => '<button type="button" class="btn btn-success" onclick="vrAddSelectedProducts();">' . JText::_('JAPPLY') . '</button>',
	),
	$this->loadTemplate('products_modal')
);

// display media manager modal
echo $mediaManagerModal;
?> 

<script>

	jQuery(document).ready(function() {

		jQuery('#jmodal-products').on('show', function() {

			// get section products
			var products = vrGetSectionProducts(SELECTED_SECTION);

			// initialise modal
			vreInitProductsLayout(products);

		});

	});

	function vrOpenJModal(id, url, jqmodal) {
		<?php echo $vik->bootOpenModalJS(); ?>
	}

	function vrCloseJModal(id, url, jqmodal) {
		<?php echo $vik->bootDismissModalJS(); ?>
	}

	function vrAddSelectedProducts() {
		// get selected products
		var products = vreGetSelectedProducts();

		for (var i = 0; i < products.length; i++) {
			vreAddProductToSection(products[i]);
		}

		vrMakeSortable('#vrmenuprodscont' + SELECTED_SECTION, '.manual-sort-handle', {axis: 'y'});

		vrCloseJModal('products');
	}

	function vrMakeSortable(selector, handle, options) {
		var config = {
			revert: false,
			cursor: 'move',
		};

		if (handle) {
			config.handle = handle;
		}

		if (typeof options === 'object') {
			Object.assign(config, options);
		}

		jQuery(selector).sortable(config);
	}

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate()) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}
	
</script>
