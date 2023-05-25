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

?>

<h3><?php echo JText::_('VRMANAGERESERVATION17'); ?></h3>

<div class="order-fields">

	<?php
	if ($this->order->purchaser_nominative)
	{
		?>
		<!-- Nominative -->

		<div class="order-field">

			<label><?php echo JText::_('VRMANAGECUSTOMER2'); ?></label>

			<div class="order-field-value">
				<b><?php echo $this->order->purchaser_nominative; ?></b>
			</div>

		</div>
		<?php
	}

	if ($this->order->purchaser_mail)
	{
		?>
		<!-- E-mail -->

		<div class="order-field">

			<label><?php echo JText::_('VRMANAGECUSTOMER3'); ?></label>

			<div class="order-field-value">
				<a href="mailto:<?php echo $this->order->purchaser_mail; ?>">
					<?php echo $this->order->purchaser_mail; ?>
				</a>
			</div>

		</div>
		<?php
	}

	if ($this->order->purchaser_phone)
	{
		?>
		<!-- Phone Number -->

		<div class="order-field">

			<label><?php echo JText::_('VRMANAGECUSTOMER4'); ?></label>

			<div class="order-field-value">
				<a href="tel:<?php echo $this->order->purchaser_phone; ?>">
					<?php echo $this->order->purchaser_phone; ?>
				</a>
			</div>

		</div>
		<?php
	}
	?>

</div>
