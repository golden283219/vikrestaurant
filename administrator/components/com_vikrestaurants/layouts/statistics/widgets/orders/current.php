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

// make sure the user can edit the state of the orders
$canEditState = $user->authorise('core.edit.state', 'com_vikrestaurants')
	&& $user->authorise('core.access.tkorders', 'com_vikrestaurants');

$vik = VREApplication::getInstance();

if (count($orders) == 0)
{
	echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
}
else
{
	?>
	<div class="dash-table-wrapper" data-widget="<?php echo $widget->getID(); ?>">
		<table>

			<thead>
				<tr>
					<!-- Order Number -->
					<th width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES1'); ?></th>
					<!-- Check-in -->
					<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES3'); ?></th>
					<!-- Customer -->
					<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES24'); ?></th>
					<!-- Total -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES8'); ?></th>
					<!-- Reservation Code -->
					<th width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGETKRES26'); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php
				foreach ($orders as $r)
				{
					?>
					<tr>
						
						<!-- Order Number -->
						<td>
							<div class="td-primary">
								<?php echo $r->id; ?>
								
								<span class="actions-group">
									<a href="index.php?option=com_vikrestaurants&amp;view=printorders&amp;type=1&amp;tmpl=component&amp;cid[]=<?php echo $r->id; ?>" target="_blank">
										<i class="fas fa-print"></i>
									</a>

									<a href="javascript:void(0);" class="more-details-handle" data-id="current-<?php echo $r->id; ?>" onclick="toggleOrderDetails(this);">
										<i class="fas fa-bars"></i>
									</a>
								</span>
							</div>
						</td>

						<!-- Check-in -->
						<td>
							<div class="td-primary">
								<a href="javascript: void(0);" onclick="vrOpenJModal('respinfo', <?php echo $r->id; ?>, 'takeaway'); return false;">
									<?php echo JHtml::_('date', $r->checkin_ts, $config->get('timeformat'), date_default_timezone_get()); ?>
								</a>
							</div>

							<div class="td-secondary">
								<?php
								echo VikRestaurants::formatTimestamp(
									JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'),
									$r->checkin_ts,
									$local = true
								);
								?>

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
									<?php
									if ($r->id_user > 0)
									{
										?>
										<a href="javascript: void(0);" onclick="vrOpenJModal('custinfo', <?php echo $r->id_user; ?>, 'takeaway'); return false;">
											<?php echo $r->purchaser_nominative; ?>
										</a>
										<?php
									}
									else
									{
										echo $r->purchaser_nominative;
									}
									?>
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

						<!-- Status Code -->
						<td style="text-align: center;">
							<a href="javascript: void(0);" data-id="<?php echo $r->id; ?>" data-code="<?php echo (int) $r->rescode; ?>" class="vrrescodelink">
								<?php
								if ($r->rescode > 0)
								{
									if ($r->code_icon)
									{
										?>
										<img src="<?php echo VREMEDIA_SMALL_URI . $r->code_icon; ?>" title="<?php echo $r->status_code; ?>" />
										<?php
									}
									else
									{
										echo $r->status_code;
									}
								}
								else
								{
									echo '--';
								}
								?>
							</a>

							<?php
							echo JHtml::_('vrehtml.statuscodes.popup', 2);
							?>
						</td>

					</tr>

					<tr class="more-details" data-id="current-<?php echo $r->id; ?>">
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
							
							if (count($r->items))
							{
								?>
								<div class="td-secondary">
									<i class="fas fa-burn" style="margin-right:4px;"></i>
									<?php echo JText::sprintf('VRTKRESITEMSINCART', $r->itemsToBeCooked, $r->itemsCount); ?>
								</div>
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
				
			</tbody>

		</table>
	</div>

	<script>

		jQuery('.dash-table-wrapper[data-widget="<?php echo $widget->getID(); ?>"]')
			.find('.vrrescodelink').each(function() {
				jQuery(this).statusCodesPopup({
					group: 2,
					controller: '<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=tkreservation.changecodeajax&tmpl=component'); ?>',
					onShow: function(event) {
						// pause dashboard timer as long as
						// a popup is open
						stopDashboardListener();
					},
					onHide: function(event) {
						// restart dashboard timer after
						// closing the popup
						startDashboardListener();
					},
				});
			});

	</script>
	<?php
}
