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

JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.googlemaps', null, 'places');

$customer = $this->customer;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm" style="<?php echo $this->isTmpl ? 'padding:10px;' : ''; ?>">

	<?php
	if ($this->isTmpl)
	{
		?>
		<div class="btn-toolbar vr-btn-toolbar" id="filter-bar" style="display:none;">
			<div class="btn-group pull-left">
				<button type="button" class="btn btn-success" name="tmplSaveButton" onclick="vrValidateFieldsAndDisableButton(this);">
					<i class="icon-apply"></i>&nbsp;<?php echo JText::_('VRSAVE'); ?>
				</button>
			</div>
		</div>
		<?php
	}
	?>

	<?php echo $vik->bootStartTabSet('customer', array('active' => $this->getActiveTab('customer_billing'), 'cookie' => $this->getCookieTab()->name)); ?>

		<!-- BILLING -->
			
		<?php echo $vik->bootAddTab('customer', 'customer_billing', JText::_('VRCUSTOMERTABTITLE1')); ?>

			<?php echo $this->loadTemplate('billing'); ?>

		<?php echo $vik->bootEndTab(); ?>

		<!-- DELIVERY -->

		<?php
		// add badge counter to tab
		$options = array(
			'badge' => count($this->customer->locations),
		);

		echo $vik->bootAddTab('customer', 'customer_delivery', JText::_('VRCUSTOMERTABTITLE2'), $options); ?>

			<?php echo $this->loadTemplate('locations'); ?>

		<?php echo $vik->bootEndTab(); ?>

		<!-- CUSTOM FIELDS -->

		<?php echo $vik->bootAddTab('customer', 'customer_fields', JText::_('VRMANAGECUSTOMERTITLE3')); ?>

			<?php echo $this->loadTemplate('customfields'); ?>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $customer->id; ?>" />
	<input type="hidden" name="option" value="com_vikrestaurants"/>

	<?php
	if ($this->isTmpl)
	{
		?>
		<input type="hidden" name="tmpl" value="component" />
		<?php
	}
	?>

	<input type="hidden" name="task" value="" />

</form>

<?php
$footer  = '<button type="button" class="btn btn-success" id="delivery-location-save">' . JText::_('JAPPLY') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="delivery-location-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

// render inspector to manage delivery locations
echo JHtml::_(
	'vrehtml.inspector.render',
	'delivery-location-inspector',
	array(
		'title'       => JText::_('VRE_ADD_DELIVERY_LOCATION'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
	),
	$this->loadTemplate('location_modal')
);
?>

<script>

	jQuery(document).ready(function() {

		// listen console to catch any interesting error
		VikMapsFailure.listenConsole();
		
	});

	// validate

	var customerValidator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		// check if we clicked a "save" button and the form is not valid
		if (task.indexOf('save') !== -1 && !customerValidator.validate()) {
			// abort request
			return false;
		}

		// submit form
		Joomla.submitform(task, document.adminForm);
		return true;
	}

	<?php if ($this->isTmpl) { ?>

		function vrValidateFieldsAndDisableButton(button) {
			if (jQuery(button).prop('disabled')) {
				// button already submitted
				return false;
			}

			// disable button
			jQuery(button).prop('disabled', true);

			// submit form
			if (Joomla.submitbutton('customer.save') === false) {
				// invalid fields, enable button again
				jQuery(button).prop('disabled', false);
			}
		}

		// transfer submit button instance to parent for being clicked
		window.parent.modalCustomerSaveButton = document.adminForm.tmplSaveButton;

		// transfer created ID to parent
		window.parent.modalSavedCustomerData = <?php echo $this->customer->id ? json_encode($this->customer) : 0; ?>;

	<?php } ?>
	
</script>
