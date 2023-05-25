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

$vik = VREApplication::getInstance();

if (count($orders) == 0)
{
	echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
}
else
{
	$rescodes = JHtml::_('vikrestaurants.rescodes', 2);
	?>
	<div class="dash-table-wrapper" data-widget="<?php echo $widget->getID(); ?>">
		<table>

			<thead>
				<tr>
					<!-- Order Number -->
					<th width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES1'); ?></th>
					<!-- Check-in -->
					<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES3'); ?></th>
					<!-- Customer -->
					<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES24'); ?></th>
					<!-- Total -->
					<th width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES8'); ?></th>
					<!-- Reservation Code -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES26'); ?></th>
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
									<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=optkprintorders&tmpl=component&cid[]=' . $r->id . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>" target="_blank">
										<i class="fas fa-print"></i>
									</a>

									<a href="javascript:void(0);" class="more-details-handle" data-id="incoming-<?php echo $r->id; ?>" onclick="toggleOrderDetails(this);">
										<i class="fas fa-bars"></i>
									</a>
								</span>
							</div>
						</td>

						<!-- Check-in -->
						<td>
							<div class="td-primary">
								<?php echo JHtml::_('date', $r->checkin_ts, $config->get('timeformat'), date_default_timezone_get()); ?>

								<?php
								if ($r->preparation_ts)
								{
									// subtract a time slot from the preparation time
									$r->preparation_ts = strtotime('-' . $config->get('tkminint') . ' minutes', $r->preparation_ts);
									// fetch preparation time hint
									$prepTip = JText::sprintf('VRE_TKRES_PREP_TIME_HINT', date($config->get('timeformat'), $r->preparation_ts));

									?>
									<div style="font-weight: normal;display: inline-block;">
										<i class="fas fa-info-circle hasTooltip" title="<?php echo $this->escape($prepTip); ?>" style="margin-left:4px;"></i>
									</div>
									<?php
								}
								?>
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

						<!-- Status Code -->
						<td style="text-align: left;">
							<div class="vre-select-wrapper">
								<select class="res-code-selection vre-select" data-order="<?php echo $r->id; ?>" data-code="<?php echo $r->rescode; ?>">
									<option value="0">--</option>
									<?php echo JHtml::_('select.options', $rescodes, 'value', 'text', $r->rescode); ?>
								</select>
							</div>

							<span class="vrrescodelink">
								<?php
								if ($r->rescode > 0 && $r->code_icon)
								{
									?>
									<img src="<?php echo VREMEDIA_SMALL_URI . $r->code_icon; ?>" title="<?php echo $r->status_code; ?>" />
									<?php
								}
								?>
							</span>
						</td>

					</tr>

					<tr class="more-details" data-id="incoming-<?php echo $r->id; ?>">
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

	<script>
		jQuery('.dash-table-wrapper[data-widget="<?php echo $widget->getID(); ?>"]')
			.find('.res-code-selection').on('change', function() {
				// get clicked select
				var select = jQuery(this);
				// find code icon
				var link = select.closest('td').find('.vrrescodelink');

				// create promise to resolve when the status changes
				callback = new Promise((resolve) => {
					
					// disable select
					select.prop('disabled', true);

					// make request to change code
					UIAjax.do(
						'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=oversight.changecodeajax' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
						{
							group:   2,
							id:      select.data('order'),
							id_code: select.val(),
						},
						function(resp) {
							// decode JSON response
							var code = JSON.parse(resp);

							if (!code) {
								// empty code
								code = {
									id:   0,
									icon: null,
									code: '',
								};
							}
							
							if (code.icon) {
								link.html('<img src="" />');
								link.find('img')
									.attr('src', code.iconURI)
									.attr('title', code.code);
							} else {
								link.html('');
							}

							// update current code
							select.attr('data-code', code.id);

							// re-enable select
							select.prop('disabled', false);

							// resolve promise
							resolve();
						},
						function(resp) {
							// re-enable select and hide it
							select.prop('disabled', false);

							// revert to previous code
							select.val(select.attr('data-code'));
						}
					);
				});

				waitListenerForAction(callback);
			});

	</script>
	<?php
}
