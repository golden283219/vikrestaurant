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
JHtml::_('vrehtml.assets.toast', 'bottom-right');

$customer = $this->customer;

$vik = VREApplication::getInstance();

?>

<div class="customer-info-modal" style="padding:10px;">

	<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

		<?php echo $vik->bootStartTabSet('customerinfo', array('active' => $this->getActiveTab('customerinfo_billing', $customer->id), 'cookie' => $this->getCookieTab($customer->id)->name)); ?>

			<!-- BILLING -->
				
			<?php
			echo $vik->bootAddTab('customerinfo', 'customerinfo_billing', JText::_('VRCUSTOMERTABTITLE1'));
			echo $this->loadTemplate('billing');
			echo $vik->bootEndTab();
			?>

			<!-- DELIVERY -->

			<?php
			// hide locations in case they should not be shown
			// or in case the customer doesn't have them
			if ($this->hasLocations)
			{
				// add badge counter to tab
				$options = array(
					'badge' => count($customer->locations),
				);

				echo $vik->bootAddTab('customerinfo', 'customerinfo_delivery', JText::_('VRCUSTOMERTABTITLE2'), $options);
				echo $this->loadTemplate('locations');
				echo $vik->bootEndTab();
			}
			?>

			<!-- RESTAURANT RESERVATIONS -->

			<?php
			if (VikRestaurants::isRestaurantEnabled())
			{
				// add badge counter to tab
				$options = array(
					'badge' => array(
						'count' => $this->totalReservations,
						'class' => 'badge-important',
					),
				);

				echo $vik->bootAddTab('customerinfo', 'customerinfo_restaurant', JText::_('VRMANAGECUSTOMER18'), $options);

				if ($this->reservations)
				{
					echo $this->loadTemplate('restaurant');
				}
				else
				{
					echo $vik->alert(JText::_('VRNORESERVATION'));
				}

				echo $vik->bootEndTab();
			}
			?>

			<!-- TAKE-AWAY ORDERS -->

			<?php
			if (VikRestaurants::isTakeAwayEnabled())
			{
				// add badge counter to tab
				$options = array(
					'badge' => array(
						'count' => $this->totalOrders,
						'class' => 'badge-success',
					),
				);

				echo $vik->bootAddTab('customerinfo', 'customerinfo_takeaway', JText::_('VRMANAGECUSTOMER21'), $options);

				if ($this->orders)
				{	
					echo $this->loadTemplate('takeaway');
				}
				else
				{
					echo $vik->alert(JText::_('VRNOTKRESERVATION'));
				}

				echo $vik->bootEndTab();
			}
			?>

		<?php echo $vik->bootEndTabSet(); ?>

		<input type="hidden" name="view" value="customerinfo" />
		<input type="hidden" name="id" value="<?php echo $customer->id; ?>" />

	</form>

</div>
