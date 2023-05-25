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
 * @var  array 				  $orders  A list of orders to display.
 * @var  VREStatisticsWidget  $widget  The instance of the widget to be displayed.
 */
extract($displayData);

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();
$user     = JFactory::getUser();

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

if (count($orders) == 0)
{
	echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
}
else
{
	?>
	<div class="dash-table-wrapper">
		<table>

			<thead>
				<tr>
					<!-- Order Number -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES1'); ?></th>
					<!-- Check-in -->
					<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES3'); ?></th>
					<!-- Customer -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES24'); ?></th>
					<!-- Total -->
					<th width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES8'); ?></th>
					<!-- Status -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES9'); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php
				foreach ($orders as $r)
				{
					?>
					<tr data-orderid="<?php echo $r->id; ?>">
						
						<!-- Order Number -->
						<td>
							<div class="td-primary">
								<?php echo $r->id; ?>

								<span class="actions-group">
									<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=optkprintorders&tmpl=component&cid[]=' . $r->id . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>" target="_blank">
										<i class="fas fa-print"></i>
									</a>

									<a href="javascript:void(0);" class="more-details-handle" data-id="latest-<?php echo $r->id; ?>" onclick="toggleOrderDetails(this);">
										<i class="fas fa-bars"></i>
									</a>
								</span>
							</div>

							<div class="td-secondary">
								<?php
								echo VikRestaurants::formatTimestamp(
									JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'),
									$r->created_on,
									$local = true
								);
								?>
							</div>
						</td>

						<!-- Check-in -->
						<td>
							<div class="td-primary">
								<?php echo JHtml::_('date', $r->checkin_ts, JText::_('DATE_FORMAT_LC3'), date_default_timezone_get()); ?>
							</div>

							<div class="td-secondary">
								<span><?php echo JHtml::_('date', $r->checkin_ts, $config->get('timeformat'), date_default_timezone_get()); ?></span>

								<span class="td-pull-right">
									<?php
									if ($r->delivery_service)
									{
										echo JText::_('VRMANAGETKRES14');
									}
									else
									{
										echo JText::_('VRMANAGETKRES15');
									}
									?>
								</span>
							</div>
						</td>

						<!-- Customer -->
						<td>
							<?php
							// use primary for mail/phone in case the nominative is empty
							$subclass = 'td-primary';

							if ($r->purchaser_nominative)
							{
								// nominative not empty, use secondary class for mail/phone
								$subclass = 'td-secondary';
								?>
								<div class="td-primary">
									<?php echo $r->purchaser_nominative; ?>
								</div>
								<?php
							}
							?>

							<div class="<?php echo $subclass; ?>">
								<?php echo $r->purchaser_phone ? $r->purchaser_phone : $r->purchaser_mail; ?>
							</div>
						</td>

						<!-- Total -->
						<td>
							<div class="td-primary">
								<?php echo $currency->format($r->total_to_pay); ?>
							</div>

							<div class="td-secondary">
								<?php
								if ($r->total_to_pay > $r->tot_paid)
								{
									// display remaining balance
									echo JText::sprintf('VRORDERDUE', $currency->format($r->total_to_pay - $r->tot_paid));
								}
								else if ($r->tot_paid > 0)
								{
									// display amount paid
									echo JText::_('VRORDERPAID') . ':' . $currency->format($r->tot_paid);
								}
								?>
							</div>
						</td>

						<!-- Status -->
						<td>
							<span class="status-text vrreservationstatus<?php echo strtolower($r->status); ?>">
								<?php echo JText::_('VRRESERVATIONSTATUS' . $r->status); ?>
							</span>

							<span class="td-pull-right actions-group">
								<?php
								if ($r->status == 'CONFIRMED')
								{
									if ($r->need_notif)
									{
										?>
										<a href="javascript: void(0);" onclick="ordersNotifyEvent(<?php echo $r->id; ?>, <?php echo $widget->getID(); ?>, this);">
											<i class="fas fa-paper-plane"></i>
										</a>
										<?php
									}
								}
								else if ($r->status == 'PENDING')
								{
									?>
									<a href="javascript: void(0);" onclick="ordersConfirmEvent(<?php echo $r->id; ?>, <?php echo $widget->getID(); ?>, this);">
										<i class="fas fa-check-circle ok"></i>
									</a>

									<a href="javascript: void(0);" onclick="ordersRefuseEvent(<?php echo $r->id; ?>, <?php echo $widget->getID(); ?>, this);">
										<i class="fas fa-times-circle no"></i>
									</a>
									<?php
								}
								?>
							</span>
							
						</td>

					</tr>

					<tr class="more-details" data-id="latest-<?php echo $r->id; ?>">
						<td colspan="5">
							<div class="td-secondary">
								<?php
								if (!empty($r->route->origin))
								{
									?>
									<span style="margin-right:5px;">
										<i class="fas fa-map-pin" style="margin-right:5px;"></i>
										<?php echo $r->route->origin; ?>
									</span>
									<?php
								}
								
								if (strlen($r->purchaser_address))
								{
									?>
									<i class="fas fa-long-arrow-alt-right" style="margin-right:5px;"></i>
									<?php echo $r->purchaser_address; ?>
									<?php
								}

								if (!empty($r->route->distancetext))
								{
									?>
									<i class="fas fa-road" style="margin-left: 5px;"></i>
									&nbsp;<?php echo $r->route->distancetext; ?>
									<?php
								}

								if (!empty($r->route->durationtext))
								{
									?>
									<i class="fas fa-stopwatch" style="margin-left: 5px;"></i>
									&nbsp;<?php echo $r->route->durationtext; ?>
									<?php
								}
								?>
							</div>

							<?php
							if (!empty($r->route->duration) && $r->delivery_service)
							{
								// fetch delivery time
								$leave_at = strtotime('-' . $r->route->duration . ' seconds', $r->checkin_ts);
								// format delivery time
								$leave_at = date($config->get('timeformat'), $leave_at);
								?>
								<div class="td-secondary">
									<i class="fas fa-info-circle" style="margin-right:4px;"></i>
									<?php echo JText::sprintf('VRTK_ADDR_ROUTE_START', $leave_at); ?>
								</div>
								<?php
							}
							?>
							
							<div class="dash-items-cart">
								<?php
								foreach ($r->items as $item)
								{
									?>
									<div class="td-secondary dash-item-record">

										<div class="dash-item-name">
											<span><?php echo $item->quantity; ?>x</span>
											<b><?php echo $item->name; ?></b>
										</div>

										<?php
										if ($item->toppings)
										{
											?>
											<div class="dash-item-toppings-list">
												<?php
												foreach ($item->toppings as $group)
												{
													?>
													<div class="dash-item-topping">
														<span><?php echo $group->title; ?>:</span>
														<b><?php echo $group->str; ?></b>
													</div>
													<?php
												}
												?>
											</div>
											<?php
										}

										if ($item->notes)
										{
											?>
											<div class="dash-item-notes">
												<em><?php echo $item->notes; ?></em>
											</div>
											<?php
										}
										?>

									</div>
									<?php
								}
								?>
							</div>
						</td>
					</tr>
					<?php
				}
				?>
				
			</tbody>

		</table>
	</div>
	<?php
}
