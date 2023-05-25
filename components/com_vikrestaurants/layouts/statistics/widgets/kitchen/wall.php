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
 * @var  array 				  $reservations  A list of tables to display.
 * @var  array 				  $waitinglist   A list of prepared dishes.
 * @var  array 				  $filters       An array of filters.
 * @var  VREStatisticsWidget  $widget        The instance of the widget to be displayed.
 */
extract($displayData);

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$vik = VREApplication::getInstance();

if (count($reservations) || count($waitinglist))
{
	?>
	<div class="vrdash-container kitchen" data-widget="<?php echo $widget->getID(); ?>">

		<div class="vr-kitchen-wall">

			<?php
			foreach ($reservations as $res)
			{
				?>
				<div class="kitchen-wall-block-wrap table-parent-wrapper">
					<div class="kitchen-wall-block">

						<div class="wall-block-head">

							<div class="block-head-text">
								<span class="block-head-table">
									<span class="badge badge-info"><?php echo $res->table->name; ?></span>

									<?php
									// show elapsed time only if higher than 4 minutes
									if ($res->elapsedTime > 4)
									{
										?>
										<span class="badge badge-important elapsed-time">
											<i class="fas fa-stopwatch"></i>
											<?php echo $res->elapsedTime; ?>'
										</span>
										<?php
									}
									?>
								</span>

								<span class="block-head-room">
									<?php
									/**
									 * Display the operator name if assigned.
									 * Otherwise fallback to room name.
									 *
									 * @since 1.8.1
									 */
									if ($res->operator)
									{
										?>
										<small class="badge badge-success">
											<?php echo $res->operator; ?>
										</small>
										<?php
									}
									else
									{
										echo $res->room->name;
									}
									?>
								</span>
							</div>

							<div class="block-head-actions">
								<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opreservation.editbill&cid[]=' . $res->id . '&bill_from=opkitchen' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
									<i class="fas fa-shopping-basket"></i>
								</a>
							</div>

						</div>

						<div class="wall-block-list">

							<?php
							if (count($res->dishes) == 0)
							{
								?>
								<div style="padding: 10px;">
									<div class="vr-kitchen-no-result" style="margin: 0;">
										<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
									</div>
								</div>
								<?php
							}
							else
							{
								foreach ($res->dishes as $dish)
								{
									?>
									<div class="block-list-dish">

										<div class="list-dish-quantity">
											<?php echo $dish->quantity; ?>&nbsp;<small>x</small>
										</div>

										<div class="list-dish-main">
											<div class="list-dish-name"><?php echo $dish->name; ?></div>

											<?php
											if ($dish->notes)
											{
												?>
												<div class="list-dish-notes"><?php echo $dish->notes; ?></div>
												<?php
											}
											?>
										</div>

										<div class="list-dish-code">
											<a href="javascript: void(0);" data-id="<?php echo $dish->id; ?>" data-code="<?php echo (int) $dish->rescode; ?>" class="vrrescodelink">
												<?php
												if ($dish->code)
												{
													if ($dish->code->icon)
													{
														?>
														<img src="<?php echo $dish->code->iconURI; ?>" title="<?php echo $this->escape($dish->code->code); ?>" />
														<?php
													}
													else
													{
														?>
														<span title="<?php echo $this->escape($dish->code->code); ?>">
															<?php echo strtoupper(substr($dish->code->code, 0, 2)); ?>
														</span>
														<?php
													}
												}
												else
												{
													echo '--';
												}
												?>
											</a>

											<?php
											echo JHtml::_('vrehtml.statuscodes.popup', 3);
											?>
										</div>

									</div>
									<?php
								}
							}
							?>

						</div>

					</div>
				</div>
				<?php
			}
			?>

		</div>

		<div class="vr-kitchen-waitlist">
			
			<div class="kitchen-waitlist-head">
				<?php echo JText::_('VRE_STATS_WIDGET_KITCHEN_OUTGOING_COURSES'); ?>
			</div>

			<div class="kitchen-waitlist-groups">
				<?php
				foreach ($waitinglist as $res)
				{
					?>
					<div class="table-parent-wrapper">

						<div class="waitlist-group-title">
							<span class="waitlist-group-table">
								<span class="badge badge-info"><?php echo $res->table->name; ?></span>

								<?php
								// show elapsed time only if equals or higher than 1 minute
								if ($res->elapsedTime)
								{
									?>
									<span class="badge badge-important elapsed-time">
										<i class="fas fa-stopwatch"></i>
										<?php echo $res->elapsedTime; ?>'
									</span>
									<?php
								}
								?>
							</span>

							<span class="waitlist-group-room">
								<?php
								/**
								 * Display the operator name if assigned.
								 * Otherwise fallback to room name.
								 *
								 * @since 1.8.1
								 */
								if ($res->operator)
								{
									?>
									<small class="badge badge-success">
										<?php echo $res->operator; ?>
									</small>
									<?php
								}
								else
								{
									echo $res->room->name;
								}
								?>
							</span>
						</div>

						<div class="waitlist-group-courses">
							<?php
							foreach ($res->dishes as $dish)
							{
								?>
								<div class="waitlist-group-dish">
									<div class="waitlist-group-dish-quantity">
										<?php echo $dish->quantity; ?>&nbsp;<small>x</small>
									</div>

									<div class="waitlist-group-dish-name"><?php echo $dish->name; ?></div>

									<div class="waitlist-group-dish-code">
										<a href="javascript: void(0);" data-id="<?php echo $dish->id; ?>" data-code="<?php echo (int) $dish->rescode; ?>" class="vrrescodelink">
											<?php
											if ($dish->code)
											{
												if ($dish->code->icon)
												{
													?>
													<img src="<?php echo $dish->code->iconURI; ?>" title="<?php echo $this->escape($dish->code->code); ?>" />
													<?php
												}
												else
												{
													?>
													<span title="<?php echo $this->escape($dish->code->code); ?>">
														<?php echo strtoupper(substr($dish->code->code, 0, 2)); ?>
													</span>
													<?php
												}
											}
											else
											{
												echo '--';
											}
											?>
										</a>

										<?php
										echo JHtml::_('vrehtml.statuscodes.popup', 3);
										?>
									</div>
								</div>
								<?php
							}
							?>
						</div>

					</div>
					<?php
				}
				?>
			</div>

		</div>

	</div>

	<script>

		jQuery('.vrdash-container.kitchen[data-widget="<?php echo $widget->getID(); ?>"]')
			.find('.vrrescodelink').each(function() {
				jQuery(this).statusCodesPopup({
					group: 3,
					controller: '<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=opkitchen.changecodeajax&tmpl=component'); ?>',
					onShow: function(event) {
						// pause dashboard timer as long as a popup is open
						stopDashboardListener();
					},
					onHide: function(event) {
						// restart dashboard timer after closing the popup
						startDashboardListener();
					},
					onChange: function(resp, root) {
						// delete the badge containing the elapsed time
						// every time the status codes changes
						jQuery(root).closest('.table-parent-wrapper').find('.elapsed-time').remove();
					},
				});
			});

	</script>
	<?php
}
else
{
	?>
	<div class="vr-kitchen-no-result">
		<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
	<?php
}
