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
 */
extract($displayData);

?>

<form action="<?php echo $data['notify_url']; ?>" method="post" name="offlineccpaymform" id="offlineccpaymform">

	<div class="offcc-payment-wrapper">

		<div class="offcc-payment-box">

			<!-- ACCEPTED BRANDS -->
			<div class="offcc-payment-field">

				<div class="offcc-payment-field-wrapper">
					<?php
					foreach ($params['brands'] as $brand)
					{
						?>
						<img src="<?php echo VREADMIN_URI . 'payments/off-cc/resources/icons/' . $brand . '.png'; ?>" title="<?php echo $brand; ?>" alt="<?php echo $brand; ?>" /> 
						<?php
					}
					?>
				</div>

			</div>

			<!-- CARDHOLDER NAME -->
			<div class="offcc-payment-field">

				<div class="offcc-payment-field-wrapper">
					<span class="offcc-payment-icon">
						<i class="fas fa-user"></i>
					</span>
					
					<input type="text" name="cardholder" value="<?php echo $data['details']['purchaser_nominative']; ?>" placeholder="<?php echo JText::_('VRCCNAME'); ?>" />
				</div>

			</div>

			<!-- CREDIT CARD -->
			<div class="offcc-payment-field">

				<div class="offcc-payment-field-wrapper">
					<span class="offcc-payment-icon">
						<i class="fas fa-credit-card"></i>
					</span>
				
					<input type="text" name="cardnumber" value="" placeholder="<?php echo JText::_('VRCCNUMBER'); ?>" maxlength="16" autocomplete="off" />
				
					<span class="offcc-payment-cctype-icon" id="credit-card-brand"></span>
				</div>

			</div>

			<!-- EXPIRY DATE AND CVC -->
			<div class="offcc-payment-field">

				<!-- EXP DATE -->
				<div class="offcc-payment-field-wrapper inline">
					<span class="offcc-payment-icon">
						<i class="fas fa-calendar-alt"></i>
					</span>
					
					<input type="text" name="expdate" value="" placeholder="<?php echo JText::_('VREXPIRINGDATEFMT'); ?>" class="offcc-small" maxlength="7" />
				</div>

				<!-- CVC -->
				<div class="offcc-payment-field-wrapper inline">
					<span class="offcc-payment-icon">
						<i class="fas fa-lock"></i>
					</span>
					
					<input type="text" name="cvc" value="" placeholder="<?php echo JText::_('VRCVV'); ?>" class="offcc-small" maxlength="4" autocomplete="off" />
				</div>

			</div>

			<!-- SUBMIT -->
			<div class="offcc-payment-field">

				<div class="offcc-payment-field-wrapper inline">
					<button type="submit" onclick="return validateCreditCardForm();" class="cc-submit-btn"><?php echo JText::_('VRSUBMIT'); ?></button>
				</div>

			</div>

		</div>

	</div>

</form>
