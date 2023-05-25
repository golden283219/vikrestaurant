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
 * @var  mixed    $order    An object containing the order/reservation details.
 */
extract($displayData);

// get payment details
$payment = $data['payment_info'];

$vik = VREApplication::getInstance();

?>

<a name="payment" style="display: none;"></a>

<?php
if ($order->status == 'PENDING')
{
	/**
	 * Instantiate the payment using the platform handler.
	 *
	 * @since 1.8
	 */
	$obj = $vik->getPaymentInstance($payment->file, $data, $payment->params);
	?>

	<div id="vr-pay-box" class="<?php echo $payment->position; ?>">
	
		<?php
		// display notes before purchase
		if (!empty($order->payment->notes->beforePurchase))
		{
			?>
			<div class="vrpaymentouternotes">
				<div class="vrpaymentnotes">
					<?php
					// assign notes to temporary variable
					$content = $order->payment->notes->beforePurchase;

					/**
					 * Render HTML description to interpret attached plugins.
					 * 
					 * @since 1.8
					 */
					$vik->onContentPrepare($content, $full = true);

					echo $content->text;
					?>
				</div>
			</div>
			<?php
		}

		// display payment form
		$obj->showPayment();
		?>

	</div>

	<?php
}
else if ($order->status == 'CONFIRMED')
{
	?>
	<div id="vr-pay-box" class="<?php echo $payment->position; ?>">
	
		<?php
		// display notes after purchase
		if (!empty($order->payment->notes->afterPurchase))
		{
			?>
			<div class="vrpaymentouternotes">
				<div class="vrpaymentnotes">
					<?php
					// assign notes to temporary variable
					$content = $order->payment->notes->afterPurchase;

					/**
					 * Render HTML description to interpret attached plugins.
					 * 
					 * @since 1.8
					 */
					$vik->onContentPrepare($content, $full = true);

					echo $content->text;
					?>
				</div>
			</div>
			<?php
		}
		?>

	</div>
	<?php
}
