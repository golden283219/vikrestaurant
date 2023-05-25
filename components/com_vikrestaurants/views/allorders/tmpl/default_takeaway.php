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

$currency = VREFactory::getCurrency();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

if (count($this->tkorders) == 0)
{
	?>
	<div class="vr-allorders-void"><?php echo JText::_('VRALLTKORDERSVOID'); ?></div>
	<?php
}
else
{
	?>
	<div class="vr-allorders-box">
		
		<div class="vr-allorders-tinylist">
			<?php
			foreach ($this->tkorders as $ord)
			{
				?>
				<div class="list-order-bar">

					<div class="order-oid">
						<?php echo substr($ord['sid'], 0, 2) . '#' . substr($ord['sid'], -2, 2); ?>
					</div>

					<div class="order-summary">
						<div class="summary-status <?php echo strtolower($ord['status']); ?>">
							<?php echo strtoupper(JText::_('VRRESERVATIONSTATUS' . $ord['status'])); ?>
						</div>

						<div class="summary-service">
							<?php echo JText::_($ord['delivery_service'] ? 'VRTKORDERDELIVERYOPTION' : 'VRTKORDERPICKUPOPTION'); ?>
						</div>
					</div>

					<div class="order-purchase">
						<div class="purchase-date">
							<?php
							$ord['created_on'] = ($ord['created_on'] > 0 ? $ord['created_on'] : $ord['checkin_ts']);

							echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC1'), $ord['created_on']);
							?>
						</div>

						<div class="purchase-price">
							<?php
							if ($ord['total_to_pay'] > 0)
							{
								echo $currency->format($ord['total_to_pay']);
							}
							?>
						</div>
					</div>

					<div class="order-view-button">
						<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=order&ordnum=' . $ord['id'] . '&ordkey=' . $ord['sid'] . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
							<?php echo JText::_('VRVIEWORDER'); ?>					
						</a>
					</div>

				</div>

			<?php } ?>
		</div>
		
		<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=allorders' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post">
			<?php echo JHtml::_('form.token'); ?>
			<div class="vr-list-pagination"><?php echo $this->tkordersNavigation; ?></div>
			<input type="hidden" name="option" value="com_vikrestaurants" />
			<input type="hidden" name="view" value="allorders" />
		</form>
	
	</div>
	<?php
}
