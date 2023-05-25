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

JHtml::_('vrehtml.sitescripts.animate');
JHtml::_('vrehtml.assets.fontawesome');

$config = VREFactory::getConfig();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

// display step bar using the view sub-template
echo $this->loadTemplate('stepbar');

/**
 * Get login requirements:
 * [1] - Never
 * [2] - Optional
 * [3] - Required on confirmation page
 */
$login_req = $config->getUint('loginreq');

// If the login is mandatory/optional and the customer is not logged in, we need to show
// a form to allow the customers to login or at least to create a new account.
if ($login_req > 1 && !VikRestaurants::userIsLogged())
{
	// display login/registration form
	echo $this->loadTemplate('login');
	
	// do not go ahead in case the login is mandatory
	if ($login_req > 2)
	{
		return;
	}
}

// display summary using the view sub-template
echo $this->loadTemplate('summary');

// checks whether the restaurant section uses the coupon codes
if ($this->anyCoupon)
{
	// display form to redeem coupon with a sub-template
	echo $this->loadTemplate('coupon');
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=saveorder' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" id="vrpayform" name="vrpayform" method="post">

	<?php
	$step = 0;

	// make sure there are custom fields to collect
	if ($this->customFields)
	{
		// display custom fields using a sub-template
		echo $this->loadTemplate('fields');
	}
	else
	{
		$step = 1;
	}

	// make sure there is at least a payment available
	// and the customer has to leave a deposit
	if (count($this->payments) && $this->totalDeposit > 0)
	{
		?>
		<div class="vr-payments-list" id="vrpaymentsdiv" style="<?php echo $step == 0 ? 'display: none;' : ''; ?>">
			<?php
			// display payments using a sub-template
			echo $this->loadTemplate('payments');
			?>
		</div>
		<?php
	}
	else
	{
		$step = 1;
	}
	?>

	<button type="button" id="vrconfcontinuebutton" onClick="vrContinueButton(this);">
		<?php echo JText::_($step == 0 ? 'VRCONTINUE' : 'VRCONFIRMRESERVATION'); ?>
	</button>

	<input type="hidden" name="date" value="<?php echo $this->args['date']; ?>" />
	<input type="hidden" name="hourmin" value="<?php echo $this->args['hourmin']; ?>" />
	<input type="hidden" name="people" value="<?php echo $this->args['people']; ?>" />
	<input type="hidden" name="table" value="<?php echo $this->args['table']; ?>" />

	<?php echo JHtml::_('form.token'); ?>

</form>

<?php
JText::script('VRCONFRESFILLERROR');
JText::script('VRCONFIRMRESERVATION');
?>

<script>

	var CONFIRMATION_STEP = <?php echo (int) $step; ?>;

	function vrContinueButton(button) {
		// validate custom fields
		if (!vrCustomFieldsValidator.validate()) {
			// display error message
			jQuery('#vrordererrordiv').html(Joomla.JText._('VRCONFRESFILLERROR')).show();

			// get first invalid input
			var input = jQuery('.vrcustomfields .vrinvalid').filter('input,textarea').first();

			if (input.length == 0) {
				// the label is displayed before the input, get it
				var input = jQuery('.vrcustomfields .vrinvalid').first();
			}

			// animate to element found
			if (input.length) {
				jQuery('html,body').stop(true, true).animate({
					scrollTop: (jQuery(input).offset().top - 100),
				}, {
					duration:'medium'
				}).promise().done(function() {
					// try to focus the input
					jQuery(input).focus();
				});
			}

			// do not go ahead in case of error
			return;
		}

		// hide error message
		jQuery('#vrordererrordiv').html('').hide();

		if (CONFIRMATION_STEP == 0) {
			// display payment gateways
			jQuery('#vrpaymentsdiv').show();

			// change button text
			jQuery(button).text(Joomla.JText._('VRCONFIRMRESERVATION'));

			// increase step and do not go ahead
			CONFIRMATION_STEP++;
			return;
		}

		// do not validate payment gateways selection
		// because the first payment available, if any,
		// is now pre-selected by default

		<?php
		/**
		 * Disable book now button before submitting the
		 * form in order to prevent several clicks.
		 *
		 * @since 1.8
		 */
		?>
		jQuery(button).prop('disabled', true);

		jQuery('#vrpayform').submit();
	}

</script>
