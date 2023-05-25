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

$customer = $this->customer;

$vik = VREApplication::getInstance();

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

?>

<style>
	.area-draft-wrapper {
		margin-top: 0;
		position: relative;
		display: flex;
	}
	.area-draft-wrapper textarea {
		width: 100%;
		padding-right: 24px;
	}
	.area-draft-wrapper .draft-tip {
		position: absolute;
		top: 0px;
		right: 8px;
	}
	.vr-customer-image {
		cursor: default;
	}
</style>

<div class="row-fluid">
	
	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('VRMANAGECUSTOMERTITLE2'), 'form-horizontal'); ?>

			<div class="order-fields extended">

				<!-- BILLING NAME - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER2'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->billing_name; ?></b></div>
				</div>
				
				<!-- BILLING MAIL - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER3'); ?></label>
					<div class="order-field-value">
						<b><?php echo $customer->billing_mail; ?></b>

						<?php
						if ($customer->billing_mail)
						{
							?>
							<a href="mailto:<?php echo $customer->billing_mail; ?>" style="margin-left:4px;">
								<i class="fas fa-envelope"></i>
							</a>
							<?php
						}
						?>
					</div>
				</div>
				
				<!-- BILLING PHONE - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER4'); ?></label>
					<div class="order-field-value">
						<b><?php echo $customer->billing_phone; ?></b>

						<?php
						if ($customer->billing_phone)
						{
							?>
							<a href="tel:<?php echo $customer->billing_phone; ?>" style="margin-left:4px;">
								<i class="fas fa-phone"></i>
							</a>
							<?php
						}
						?>
					</div>
				</div>
				
				<!-- BILLING COUNTRY - Select -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER5'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->country; ?></b></div>
				</div>
				
				<!-- BILLING STATE - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER6'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->billing_state; ?></b></div>
				</div>
				
				<!-- BILLING CITY - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER7'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->billing_city; ?></b></div>
				</div>
				
				<!-- BILLING ADDRESS - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER8'); ?></label>
					<div class="order-field-value">
						<b>
							<?php
							echo $customer->billing_address;

							if ($customer->billing_address_2)
							{
								echo ' (' . $customer->billing_address_2 . ')';
							}
							?>
						</b>
					</div>
				</div>
				
				<!-- BILLING ZIP CODE - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER9'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->billing_zip; ?></b></div>
				</div>
				
				<!-- BILLING COMPANY - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER10'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->company; ?></b></div>
				</div>
				
				<!-- BILLING VAT NUMBER - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER11'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->vatnum; ?></b></div>
				</div>
				
				<!-- BILLING SSN - Text -->
				<div class="order-field">
					<label><?php echo JText::_('VRMANAGECUSTOMER20'); ?></label>
					<div class="order-field-value"><b><?php echo $customer->ssn; ?></b></div>
				</div>

			</div>
			
		<?php echo $vik->closeFieldset(); ?>
	</div>

	<div class="span6">

		<div class="row-fluid">

			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRMANAGECUSTOMERTITLE1')); ?>
			
					<!-- JOOMLA AVATAR - Dropdown -->
					<a href="javascript:void(0);" id="avatar-handle">
						<?php
						if (empty($customer->image))
						{
							?>
							<img src="<?php echo VREASSETS_URI . 'css/images/default-profile.png'; ?>" class="vr-customer-image" />
							<?php
						}
						else
						{
							?>
							<img src="<?php echo VRECUSTOMERS_AVATAR_URI . $customer->image; ?>" class="vr-customer-image" />
							<?php
						}
						?>
					</a>

					<?php
					if ($customer->user->id)
					{
						?>
						<div class="order-fields extended">

							<!-- CMS ACCOUNT NAME - Text -->
							<div class="order-field">
								<label><?php echo JText::_('VRMANAGECUSTOMER2'); ?></label>
								<div class="order-field-value"><b><?php echo $customer->user->name; ?></b></div>
							</div>
							
							<!-- CMS ACCOUNT USERNAME - Text -->
							<div class="order-field">
								<label><?php echo JText::_('VRMANAGEOPERATOR11'); ?></label>
								<div class="order-field-value"><b><?php echo $customer->user->username; ?></b></div>
							</div>
							
							<!-- CMS ACCOUNT MAIL - Text -->
							<div class="order-field">
								<label><?php echo JText::_('VRMANAGECUSTOMER3'); ?></label>
								<div class="order-field-value"><b><?php echo $customer->user->email; ?></b></div>
							</div>

						</div>
						<?php
					}
					else
					{
						?>
						<div class="customer-info-guest">
							<?php echo $vik->alert(JText::_('VRMANAGECUSTOMER15')); ?>
						</div>
						<?php
					}
					?>
					
				<?php echo $vik->closeFieldset(); ?>
			</div>

		</div>

		<div class="row-fluid">

			<div class="span12">
				<?php
				echo $vik->openFieldset(JText::_('VRMANAGECUSTOMERTITLE4'));
				?>
					<div class="area-draft-wrapper">
						<textarea
							id="area-draft"
							class="full-width"
							style="height: 200px;resize:vertical;"
							<?php echo $canEdit ? '' : 'readonly'; ?>
						><?php echo $customer->notes; ?></textarea>

						<div class="draft-tip">
							<?php
							echo $vik->createPopover(array(
								'title'     => JText::_('VRMANAGECUSTOMERTITLE4'),
								'content'   => JText::_('VRE_NOTES_AUTO_SAVE'),
								'placement' => 'left',
							));
							?>
						</div>
					</div>
				<?php echo $vik->closeFieldset(); ?>
			</div>

		</div>

	</div>

</div>

<?php
JText::script('JLIB_APPLICATION_SAVE_SUCCESS');
?>

<script>

	var DRAFT_CACHE;

	jQuery(document).ready(function() {

		// cache current notes
		DRAFT_CACHE = jQuery('#area-draft').val();

		jQuery('#area-draft').on('keyup', __debounce(updateDrafts, 3000));

	});

	function updateDrafts() {

		var draft = jQuery('#area-draft').val();

		if (draft == DRAFT_CACHE) {
			// nothing to save
			return;
		}

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=customer.savenotesajax&tmpl=component',
			{
				id:    <?php echo (int) $customer->id; ?>,
				notes: draft,
			},
			function(resp) {
				ToastMessage.enqueue({
					status: 1,
					text: Joomla.JText._('JLIB_APPLICATION_SAVE_SUCCESS'),
					delay: 3000,
				});

				// update current text
				DRAFT_CACHE = draft;
			},
			function(error) {
				// fetch error message to show
				var msg = error.responseText ? error.responseText : Joomla.JText._('VRE_AJAX_GENERIC_ERROR');

				ToastMessage.enqueue({
					status: 0,
					text:   msg,
				});
			}
		);

	}

</script>
