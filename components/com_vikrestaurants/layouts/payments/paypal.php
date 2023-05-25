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

/**
 * Layout variables
 * -----------------
 * @var  array    $data     An associative array containing the transaction details.
 * @var  array    $params   An associative array with the payment configuration.
 * @var  array    $form     An associative array with the form data.
 * @var  string   $payurl   The PayPal end-point URL used to start the payment.
 * @var  boolean  $paid     True in case the payment transaction has been already made but
 * 							the reservation hasn't been yet confirmed.
 */
extract($displayData);

// NOTE: it is possible to override attributes of the $form
// array in order to change those fields that have been already
// filled in by the payment gateway.

if ($paid)
{
	// transaction already made, display a message and wait for PayPal
	// notifies the validation end-point
	JFactory::getApplication()->enqueueMessage(JText::_('VRE_PAYMENT_PAYPAL_PAID_MSG'));
}
else
{
	?>
	<form action="<?php echo $payurl; ?>" method="post" name="paypalform">

		<?php	
		foreach ($form as $k => $v)
		{
			// include input only if the value is not an empty string
			if (strlen($v))
			{
				?>
				<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $this->escape($v); ?>" />
				<?php
			}
		}
		?>

		<input type="image" src="<?php echo $params['image']; ?>" name="submit" id="paypal-btn" alt="PayPal - The safer, easier way to pay online!" style="border: 0;">

		<?php
		if ($params['autosubmit'])
		{
			?>
			<script>
				(function($) {
					'use strict';

					if (typeof Storage !== 'undefined') {
						// build auto-submit key
						const autoSubmitFlag = 'paypalAutoSubmit<?php echo $data['oid']; ?>';

						// check if PayPal was already submitted
						if (sessionStorage.getItem(autoSubmitFlag) == 1) {
							// auto-submit only once per order
							return false;
						}

						// register auto-submit
						sessionStorage.setItem(autoSubmitFlag, 1);
					}

					const doAutoRedirect = () => {
						// Auto-submit payment form.
						// Don't need to wait for the page loading.
						document.paypalform.submit();

						// hide button after submitting the form
						document.getElementById('paypal-btn').style.display = 'none';
					}

					// check whether both the elements are available
					if (document.paypalform && document.getElementById('paypal-btn')) {
						// immediately auto-redirect
						doAutoRedirect();
					} else {
						// elements not yet available, wait for the page loading
						$(function() {
							doAutoRedirect();
						});
					}
				})(jQuery);
			</script>
			<?php
		}
		?>

	</form>
	<?php
}
