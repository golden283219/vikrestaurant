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
 * @var  array 				    $rooms     A list of rooms to display.
 * @var  array 				    $times     A list of times.
 * @var  array 					$bookings  A map of bookings for the selected date.
 * @var  VREAvailabilitySearch  $search    The availability search instance.
 * @var  VREStatisticsWidget    $widget    The instance of the widget to be displayed.
 */
extract($displayData);

$config = VREFactory::getConfig();

$vik = VREApplication::getInstance();

// get active room
$active = JFactory::getApplication()->input->cookie->get('vre_widget_' . $widget->getName() . '_active_' . $widget->getID(), null);

if (is_null($active) && count($rooms))
{
	// use first room available if there's no active room
	$active = $rooms[0]->id;
}

// create timestamp for selected date
$date = VikRestaurants::createTimestamp($search->get('date'), 0, 0);

$now = VikRestaurants::now();

?>

<div class="vrdash-container overview" data-widget="<?php echo $widget->getID(); ?>">

	<!-- display rooms nav -->

	<div class="vrdash-tab-head">
		<?php
		foreach ($rooms as $room)
		{
			?>
			<div class="vrdash-tab-button">
				<a href="javascript: void(0);" data-pane="<?php echo $room->id; ?>" class="<?php echo ($active == $room->id ? 'active' : ''); ?>" onclick="overviewSwitchRoom(this);">
					<?php
					echo $room->name;

					if ($room->is_closed)
					{
						?><i class="fas fa-ban" style="margin-left:4px;color:#900;"></i><?php
					}
					?>
				</a>
			</div>
			<?php
		}
		?>
	</div>

	<div class="widget-floating-box top-right">
		<span class="badge badge-important">
			<?php echo JHtml::_('date', $date, JText::_('DATE_FORMAT_LC3'), date_default_timezone_get()); ?>
		</span>
	</div>

	<!-- display rooms tables -->

	<?php
	foreach ($rooms as $room)
	{
		?>
		<div class="vrdash-tab-pane vr-dash-roomcont-table" data-pane="<?php echo $room->id; ?>" style="<?php echo $active == $room->id ? '' : 'display:none'; ?>">

			<table class="vr-dashboard-overview-table">

				<!-- display available times as columns -->
				<thead>
					<tr>
						<th>&nbsp;</th>
						<?php
						foreach ($times as $shift)
						{
							$class = 'first-of-shift';

							foreach ($shift as $hourmin)
							{
								?>
								<th class="<?php echo $class; ?>" data-time="<?php echo $hourmin->value; ?>"><?php echo $hourmin->text; ?></th>
								<?php
								$class = '';
							}
						}
						?>
					</tr>
				</thead>

				<!-- display room tables as rows -->
				<tbody>
					<?php
					foreach ($room->tables as $table)
					{
						$colspan = 0;
						?>
						<tr>
							<td data-table="<?php echo $table->id; ?>">
								<?php
								echo $table->name;
								echo ' (' . $table->min_capacity . ' - ' . $table->max_capacity . ')';
								?>
							</td>
							<?php
							foreach ($times as $shift)
							{
								foreach ($shift as $time_index => $hourmin)
								{
									if ($colspan)
									{
										$colspan--;
									}

									if (!$colspan || $table->multi_res)
									{
										// extract hour and minutes from time
										list($hour, $min) = explode(':', $hourmin->value);
										// create shift timestamp
										$ts = VikRestaurants::createTimestamp($search->get('date'), $hour, $min);

										$info = null;
										
										$ids      = array();
										$names    = array();
										$statuses = array();

										if (!empty($bookings[$table->id]))
										{
											foreach ($bookings[$table->id] as $booking)
											{
												if ($booking->checkin_ts <= $ts && $ts < $booking->checkout_ts)
												{
													if ($info === null)
													{
														$info = $booking;
													}

													$ids[]   = $booking->id;
													$names[] = $booking->purchaser_nominative;

													if (!array_key_exists($booking->status, $statuses))
													{
														$statuses[$booking->status] = 0;
													}

													$statuses[$booking->status]++;
												}
											} 
										}

										$class = '';
										$style = '';
										$title = '';

										$onclick     = '';
										$link_href   = 'javascript:void(0);';
										$link_target = '';

										// check if the cell is empty
										if ($info === null)
										{
											$time_closed = false;

											// look for an hourly closure
											if (!empty($room->closures))
											{
												// iterate hourly closures
												for ($rc = 0; $rc < count($room->closures) && !$time_closed; $rc++)
												{
													$closure = $room->closures[$rc];

													$time_closed = $closure->start_ts < strtotime('+' . $filters['intervals'] . ' minutes', $ts)
														&& $ts < $closure->end_ts;
												}
											}

											if ($time_closed)
											{
												// rooms closed at this date and time
												$class = 'red';
												$title = JText::_('VRROOMSTATUSCLOSED');
											}
											// compare timestamp with real current time
											else if (strtotime('+' . $filters['intervals'] . ' minutes', $ts) > $now)
											{
												// available for bookings
												$link_href = 'index.php?option=com_vikrestaurants&task=reservation.add&date=' . $search->get('date') . '&hourmin=' . $hourmin->value . '&people=' . $table->min_capacity . '&idt=' . $table->id . '&from=restaurant';
											}
											else
											{
												// date in the past
												$class = 'red';
												$title = JText::_('VRDATEPAST');
											}
										}
										// there is at least a reservation
										else
										{
											if (!$info->closure)
											{
												$class = $info->status == 'CONFIRMED' ? 'green' : 'orange';
											}
											else
											{
												$class = 'red closure';
											}

											// check for single reservation
											if (count($ids) == 1)
											{
												// calculate the remaining stay time in case the reservation doesn't
												// begins within this cell (e.g. when there is a gab between 2 shifts)
												$stay_time = $info->stay_time - ceil(abs($ts - $info->checkin_ts) / 60);

												// calculate number of cell that the reservation should occupy
												$colspan = ceil($stay_time / $filters['intervals']);

												// fetch maximum number of cells that can be used from now on
												$max_cell = count($shift) - $time_index;

												// make sure the colspan doesn't exceed the maximum number of cells
												$colspan = min(array($colspan, $max_cell));

												// register reservation ID for being used when trying
												// to open the details modal
												$onclick = 'data-id="' . $info->id . '"';
											}
											// check for multiple reservations
											else
											{
												$title = implode(', ', $names);

												$link_href = 'index.php?option=com_vikrestaurants&view=reservations&ids[]=' . implode('&ids[]=', $ids);

												$link_target = 'target="_blank"';

												// fetch gradient in case of different statuses
												if (($gradient = VikRestaurants::getCssGradientFromStatuses($statuses, 'bottom')))
												{
													// use gradient only in case there are at least 2 statuses
													$class = '';
													$style = $gradient;
												}
											}
										}
										?>
										<td class="<?php echo $class; ?>" style="<?php echo $style; ?>" <?php echo $colspan && !$table->multi_res ? 'colspan="' . $colspan . '"' : ''; ?> data-time="<?php echo $hourmin->value; ?>">

											<a href="<?php echo $link_href; ?>" title="<?php echo $title; ?>" class="vr-sight-cal-box" <?php echo $onclick; ?> <?php echo $link_target; ?>>
												<?php
												if (count($ids) == 1)
												{
													if ($info->id_parent == 0)
													{
														?>
														<div class="td-primary" style="text-align: left;">
															<?php
															echo $info->purchaser_nominative;

															if (strip_tags($info->notes))
															{
																$notes = $info->notes;
																// always obtain short description, if any
																$vik->onContentPrepare($notes, false);
																?>
																<i class="fas fa-sticky-note reservation-notes" title="<?php echo $this->escape($notes->text); ?>" style="margin-left: 2px;"></i>
																<?php
															}

															if ($info->operator_name)
															{
																?>
																<br />
																
																<small class="badge badge-info"><?php echo trim($info->operator_name); ?></small>
																<?php
															}
															?>
														</div>

														<div class="td-pull-right">
															<span class="vrrescodelink" data-id="<?php echo $info->id; ?>" data-code="<?php echo (int) $info->rescode; ?>">
																<?php
																if (empty($info->code_icon))
																{
																	echo $info->code ? $info->code : '--';
																}
																else
																{
																	?>
																	<img src="<?php echo VREMEDIA_SMALL_URI . $info->code_icon; ?>" />
																	<?php
																}
																?>
															</span>
															<?php
															echo JHtml::_('vrehtml.statuscodes.popup', 1);
															?>
														</div>
														<?php
													}
													else
													{
														?>
														<i class="fas fa-link fa-flip-horizontal big linked-reservation"></i>
														<?php
													}
												}
												?>
											</a>

										</td>
										<?php
									}
								}
							}
							?>
						</tr>
						<?php
					}
					?>
				</tbody>
				
			</table>

		</div>
		<?php
	}
	?>

</div>

<script>

	jQuery('.vr-sight-cal-box, .reservation-notes').tooltip({container: 'body'});

	/**
	 * Declares flag that will be used to check
	 * whether there's at least a popup open.
	 *
	 * @var boolean
	 */
	if (typeof OVERVIEW_CHANGING_CODE === 'undefined') {
		var OVERVIEW_CHANGING_CODE = false;
	}

	/**
	 * Temporary variable used to store the current target
	 * that has been clicked to start a closure.
	 *
	 * @var mixed
	 */
	if (typeof OVERVIEW_CLOSURE_TARGET === 'undefined') {
		var OVERVIEW_CLOSURE_TARGET = null;
	} 

	jQuery('.vrdash-container.overview[data-widget="<?php echo $widget->getID(); ?>"]')
		.find('.vrrescodelink').each(function() {
			jQuery(this).statusCodesPopup({
				group: 1,
				controller: '<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=reservation.changecodeajax&tmpl=component'); ?>',
				onShow: function() {
					// pause dashboard timer as long as
					// a popup is open
					stopDashboardListener();
					
					// popup open
					OVERVIEW_CHANGING_CODE = true;
				},
				onHide: function() {
					// restart dashboard timer after
					// closing the popup
					startDashboardListener();

					// mark popup as closed with a little
					// delay in order to prevent any click
					// event related to the parent element
					setTimeout(function() {
						// popup closed
						OVERVIEW_CHANGING_CODE = false;
					}, 250);
				},
			});
		});

	jQuery('.vrdash-container.overview[data-widget="<?php echo $widget->getID(); ?>"]')
		.find('a.vr-sight-cal-box[data-id]')
			.each(function() {
				jQuery(this).on('click', function(e) {
					// do not open reservation modal in case
					// the status code was clicked
					if (OVERVIEW_CHANGING_CODE) {
						return false;
					}

					vrOpenJModal('respinfo', jQuery(this).data('id'), 'restaurant');
				});
			});

	jQuery('.vrdash-container.overview[data-widget="<?php echo $widget->getID(); ?>"]')
		.find('.linked-reservation')
			.hover(function() {
				// recover parent ID
				var id = jQuery(this).closest('*[data-id]').data('id');

				// leave opaque only the reservations that belong to the same parent
				jQuery('.vr-sight-cal-box').not('*[data-id="' + id + '"]').css('opacity', 0.2);
			}, function() {
				// remove transparent effect on hover leave
				jQuery('.vr-sight-cal-box').css('opacity', 1);
			});

	jQuery('.vrdash-container.overview[data-widget="<?php echo $widget->getID(); ?>"]')
		.find('td:not([data-table])')
			.click(function(event) {
				// check if SHIFT key is pressed
				if (event.shiftKey == false) {
					// no SHIFT key, propagate error
					return true;
				}

				// SHIFT key is pressed, we need to prevent any
				// registered action in order to handle the closure
				event.preventDefault();
				event.stopPropagation();

				// get cell row
				var tr = jQuery(this).closest('tr');

				if (OVERVIEW_CLOSURE_TARGET === null) {
					// pause dashboard timer until the closure has been created
					stopDashboardListener();

					// register current target
					OVERVIEW_CLOSURE_TARGET = this;

					// add red closure class to clicked cell
					jQuery(this).addClass('closure-tmp');

					// register hover event for all TDs that belong to
					// the same TR of the clicked element
					tr.find('td:not([data-table])')
						.on('mouseenter', overviewHandleClosureHover);
				} else {
					// create promise to save closure
					new Promise((resolve, reject) => {
						// make sure we clicked a cell in the same row
						var oriTR = jQuery(OVERVIEW_CLOSURE_TARGET).closest('tr');

						// turn off hover event
						oriTR.find('td:not([data-table])')
							.off('mouseenter', overviewHandleClosureHover);

						if (!tr.is(oriTR)) {
							// use original row to properly unset the temporary closure
							tr = oriTR;

							// reject promise
							reject('invalid row');

							// break all
							return false;
						}

						// recover closure range 
						var start = tr.find('td.closure-tmp').first().data('time').split(':');
						var end   = tr.find('td.closure-tmp').last().data('time').split(':');

						// create start date time
						var ds = <?php echo JHtml::_('vrehtml.sitescripts.jsdate', $search->get('date')); ?>;
						ds.setHours(start[0], start[1]);

						// create end date time
						var de = new Date(ds);
						de.setHours(end[0], end[1]);

						// calculate diff between dates
						var diff = Math.floor((de - ds) / 60 / 1000) + <?php echo $filters['intervals']; ?>;

						// save closure asynchronously
						UIAjax.do(
							'index.php?option=com_vikrestaurants&task=reservation.saveclosureajax',
							{
								id_table:  tr.find('td[data-table]').data('table'),
								date:      '<?php echo $search->get('date'); ?>',
								hour:      start[0],
								min:       start[1],
								stay_time: diff,
							},
							function(resp) {
								// apply new status
								tr.find('td.closure-tmp').addClass('red closure')
									.find('a')
										.attr('href', 'javascript: void(0);')
										.attr('data-id', resp);

								// resolve promise with success
								resolve(resp);
							},
							function(err) {
								// reject promise
								reject(err);
							}
						);
					}).finally((data) => {
						// unset closure target
						OVERVIEW_CLOSURE_TARGET = null;

						// remove closure status from cell
						tr.find('td').removeClass('closure-tmp');

						// restart dashboard timer
						startDashboardListener();
					});
				}

				return false;
			});

	if (typeof overviewHandleClosureHover !== 'function') {
		function overviewHandleClosureHover(event) {
			if (event.type == 'mouseleave') {
				// do not catch leave event
				return false;
			}

			// get hovered cell
			var cell = jQuery(event.originalEvent.srcElement);

			if (!cell.is('td')) {
				// recover parent cell
				cell = cell.closest('td');
			}

			// retrieve index of cells
			var start = jQuery(OVERVIEW_CLOSURE_TARGET).index();
			var end   = cell.index();

			if (start > end) {
				// reverse closure, swap delimiters
				var tmp = start;
				start = end;
				end = tmp;
			}

			// iterate all columns within the row
			jQuery(OVERVIEW_CLOSURE_TARGET).parent('tr').find('td').each(function() {
				// get index of current cell
				var i = jQuery(this).index();

				if (start <= i && i <= end) {
					// add closure class in case the index is between the range
					jQuery(this).addClass('closure-tmp');
				} else {
					// otherwise remove closure class
					jQuery(this).removeClass('closure-tmp');
				}
			});
		}
	}

</script>
