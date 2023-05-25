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

$order = $this->order;

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

$editor = $vik->getEditor();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
			<div class="span6">
				<?php
				echo $vik->openFieldset(JText::_('VRMANAGETKRESTITLE1'));
				echo $this->loadTemplate('order');
				echo $vik->closeFieldset();
				?>
			</div>


			<div class="span6">
				<?php
				if (count($this->customFields))
				{
					?>
					<div class="row-fluid">
						<div class="span12">
							<?php
							echo $vik->openFieldset(JText::_('VRMANAGETKRESTITLE3'));
							echo $this->loadTemplate('fields');
							echo $vik->closeFieldset();
							?>
						</div>
					</div>
					<?php
				}

				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewTkreservation". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				$custom = $this->onDisplayManageView();

				if ($custom)
				{
					?>
					<div class="row-fluid">
						<div class="span12">
							<?php
							echo $vik->openFieldset(JText::_('VRE_CUSTOM_FIELDSET'));
							echo $custom;
							echo $vik->closeFieldset();
							?>
						</div>
					</div>
					<?php
				}
				?>
			</div>

		</div>
		
		<div class="row-fluid">
			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRMANAGETKRESTITLE4')); ?>
					<div class="control-group">
						<?php echo $editor->display('notes', $order->notes, 400, 200, 70, 20); ?>
					</div>
				<?php echo $vik->closeFieldset(); ?>
			</div>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="from" value="<?php echo $this->returnTask; ?>" />
	<input type="hidden" name="id" value="<?php echo $order->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
// busy modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-busytime',
	array(
		'title'       => JText::_('VRTKRESBUSYMODALTITLE'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);

// new customer modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-addcustomer',
	array(
		'title'       => '<span class="add-customer-title">' . JText::_('VRMAINTITLENEWCUSTOMER') . '</span>',
		'closeButton' => true,
		'keyboard'    => false, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
		'footer'      => '<button type="button" class="btn btn-success" data-role="customer.save">' . JText::_('JAPPLY') . '</button>',
	)
);

JText::script('VRMAINTITLENEWCUSTOMER');
JText::script('VRMAINTITLEEDITCUSTOMER');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('button[data-role="customer.save"]').on('click', function() {
			// trigger click of save button contained in managecustomer view
			window.modalCustomerSaveButton.click();
		});

		jQuery('#jmodal-addcustomer').on('hidden', function() {
			// restore default submit function
			Joomla.submitbutton = JoomlaSubmitButtonFunc;
			
			// check if the customer was saved
			if (window.modalSavedCustomerData) {
				// insert customer within the dropdown
				insertCustomer(window.modalSavedCustomerData);
			}
		});

	});
	
	// validate

	var validator = new VikFormValidator('#adminForm');

	function validateOptionalMail(field) {
		var mailInput = jQuery(field);
		var mail      = mailInput.val();

		// validate e-mail only if not empty
		if (mail.length && !isEmailCompliant(mail)) {
			validator.setInvalid(mailInput);
			return false;
		}
		
		validator.unsetInvalid(mailInput);
		return true;
	}

	var JoomlaSubmitButtonFunc = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate()) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	};

	Joomla.submitbutton = JoomlaSubmitButtonFunc;

	// MODAL BOXES

	function vrOpenJModal(id, url, jqmodal) {
		if (id == 'busytime') {
			// busy table
			url = 'index.php?option=com_vikrestaurants&view=tkbusyres&tmpl=component&date=' + jQuery('#vrdatefilter').val() + '&time=' + jQuery('#vr-hour-sel').val();
		} else if (id == 'addcustomer') {
			// add customer
			url = 'index.php?option=com_vikrestaurants&tmpl=component';

			var title;

			// get selected customer
			var id_customer = jQuery('.vr-users-select').select2('val');

			if (id_customer) {
				// edit existing
				url += '&task=customer.edit&cid[]=' + id_customer;

				title = Joomla.JText._('VRMAINTITLEEDITCUSTOMER');
			} else {
				// create new
				url += '&task=customer.add';

				title = Joomla.JText._('VRMAINTITLENEWCUSTOMER');
			}

			jQuery('#jmodal-addcustomer .add-customer-title').text(title);
		}

		<?php echo $vik->bootOpenModalJS(); ?>
	}
	
</script>
