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
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.select2');

$vik = VREApplication::getInstance();

// always use default tab while creating a new record
$active_tab = $this->menu->id ? $this->getActiveTab('tkmenu_details', $this->menu->id) : 'tkmenu_details';

// Obtain media manager modal before displaying the inspector.
// In this way, we can display the modal outside the bootstrap panels.
$mediaManagerModal = JHtml::_('vrehtml.mediamanager.modal');

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	
	<?php echo $vik->bootStartTabSet('tkmenu', array('active' => $active_tab, 'cookie' => $this->getCookieTab($this->menu->id)->name)); ?>

		<!-- MENU -->
			
		<?php echo $vik->bootAddTab('tkmenu', 'tkmenu_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<?php echo $this->loadTemplate('menu'); ?>

		<?php echo $vik->bootEndTab(); ?>

		<!-- ENTRIES -->

		<?php
		$options = array(
			'badge' => count($this->menu->entries),
		);

		echo $vik->bootAddTab('tkmenu', 'tkmenu_entries', JText::_('VRMENUMENUSPRODUCTS'), $options); ?>

			<?php echo $this->loadTemplate('entries'); ?>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $this->menu->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	
</form>

<?php
$footer  = '<button type="button" class="btn btn-success" id="menu-entry-save">' . JText::_('JAPPLY') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="menu-entry-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

// render inspector to manage menu entries
echo JHtml::_(
	'vrehtml.inspector.render',
	'menu-entry-inspector',
	array(
		'title'       => JText::_('VRE_ADD_PRODUCT'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
	),
	$this->loadTemplate('entry_modal')
);

echo $mediaManagerModal;
?>

<!-- Struct for variations -->

<div style="display: none;" id="variation-struct">
	<div id="vrtkoptdiv{__var_index__}" class="vrtk-entry-var">
		<span class="manual-sort-handle hidden-phone"><i class="fas fa-ellipsis-v"></i></span>
		<input type="text" class="entry_option_name" value="{__var_name__}" size="36" placeholder="<?php echo $this->escape(JText::_('VRMANAGETKMENU4')); ?>" />
		<div class="input-prepend currency-field">
			<button type="button" class="btn hasTooltip" title="<?php echo $this->escape(JText::_('VRE_PRODUCT_INC_PRICE_SHORT')); ?>"><?php echo VREFactory::getCurrency()->getSymbol(); ?></button>
			<input type="number" class="entry_option_price" value="{__var_price__}" size="6" min="-99999999" max="99999999" step="any" />
		</div>
		<a href="javascript: void(0);" class="trash-button-link" onClick="removeVariation({__var_index__});">
			<i class="fas fa-times"></i>
		</a>
		<input type="hidden" class="entry_option_id" value="{__var_id__}" />
	</div>
</div>

<script>

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
