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

?>

<style>

	/* modal content pane */

	.contentpane.component {
		padding: 0 10px;
		height: 100%;
		/* do not scroll */
		overflow: hidden;
	}

	/* do not use scroll on devices smaller than 1440px */

	@media screen and (max-width: 1439px) {
		.order-container .order-left-box,
		.order-container .order-right-box {
			overflow-y: scroll;
			height: 100%;
		}
		.order-container .order-left-box .order-left-bottom-box,
		.order-container .order-right-box .order-status-history {
			overflow-y: hidden;
			height: auto;
			max-height: 100%;
			margin-bottom: 12px;
		}
		.order-container .order-right-box .order-payment-details {
			height: auto;
		}
	}

</style>

<!-- container -->

<div class="order-container">

	<!-- left box : order details, customer info, order items -->

	<div class="order-left-box">

		<!-- top box : order details, customer info -->

		<div class="order-left-top-box">

			<!-- left box : order details -->

			<div class="order-global-details">
				<?php echo $this->loadTemplate('details'); ?>
			</div>

			<!-- right box : customer indo -->

			<div class="order-customer-details">
				<?php
				echo $this->loadTemplate('customer');

				if ($this->order->hasFields)
				{
					?>
					<!-- Custom Fields Toggle Button -->
						
					<button type="button" class="btn" id="custom-fields-btn"><?php echo JText::_('VRSHOWCUSTFIELDS'); ?></button>
					<?php
				}
				?>
			</div>

		</div>

		<!-- bottom box: order items, custom fields -->

		<div class="order-left-bottom-box">

			<!-- left box : order items -->

			<div class="order-items-list">
				<?php echo $this->loadTemplate('items'); ?>
			</div>

			<?php
			if ($this->order->hasFields)
			{
				?>
				<!-- right box : custom fields -->

				<div class="order-custom-fields" style="display: none;">
					<?php echo $this->loadTemplate('fields'); ?>
				</div>
				<?php
			}
			?>

		</div>

	</div>

	<!-- right box : payment details, invoices -->

	<div class="order-right-box">

		<!-- top box : payment details -->

		<div class="order-payment-details">
			<?php echo $this->loadTemplate('payment'); ?>
		</div>

	</div>

</div>

<?php
JText::script('VRSHOWCUSTFIELDS');
JText::script('VRHIDECUSTFIELDS');
JText::script('VRSHOWNOTES');
JText::script('VRHIDENOTES');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#custom-fields-btn').on('click', function() {
			var fields = jQuery('.order-custom-fields');

			if (fields.is(':visible')) {
				fields.hide();

				jQuery(this).text(Joomla.JText._('VRSHOWCUSTFIELDS'));
			} else {
				fields.show();

				jQuery(this).text(Joomla.JText._('VRHIDECUSTFIELDS'));
			}
		});

		jQuery('#notes-btn').on('click', function() {
			var notes = jQuery('.order-notes-box');

			if (notes.is(':visible')) {
				notes.hide();

				jQuery(this).text(Joomla.JText._('VRSHOWNOTES'));
			} else {
				notes.show();

				jQuery(this).text(Joomla.JText._('VRHIDENOTES'));
			}
		});

	});

</script>
