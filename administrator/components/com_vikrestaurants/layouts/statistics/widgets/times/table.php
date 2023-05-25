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
 * @var  array 				  $filters   An array of filters
 * @var  array 				  $times     A list of times.
 * @var  VREStatisticsWidget  $widget    The instance of the widget to be displayed.
 */
extract($displayData);

$vik = VREApplication::getInstance();

if (!$times)
{
	echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
}
else
{
	?>
	<style>
		.avail-times-table {
			max-height: 400px;
			overflow-y: scroll;
		}

		.avail-times-table tr.shift-name td {
			background: #e3e3e3;
		}

		.avail-times-table tr.available td:first-child {
			background: #090;
			color: #fff;
		}
		.avail-times-table tr.not-available td:first-child {
			background: #900;
			color: #fff;
		}
		.avail-times-table tr.almost-full td:first-child {
			background: #da7d22;
			color: #fff;
		}
	</style>

	<div class="dash-table-wrapper avail-times-table" data-widget="<?php echo $widget->getID(); ?>">
		<table>

			<thead>
				<tr>
					<!-- Time -->
					<th width="25%" style="text-align: center;"><?php echo JText::_('VRMANAGETKRES11'); ?></th>
					<!-- Orders -->
					<th width="55%" style="text-align: left;"><?php echo JText::_('VRE_STATS_WIDGET_TIMES_SUMMARY'); ?></th>
					<!-- Actions -->
					<th width="20%" style="text-align: center;"><?php echo JText::_('VRMAPACTIONSBUTTON'); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php
				foreach ($times as $group => $shift)
				{
					if ($group)
					{
						// display working shift name as separator
						?>
						<tr class="shift-name">
							<td colspan="3">
								<b><?php echo $group; ?></b>
							</td>
						</tr>
						<?php
					}

					foreach ($shift as $slot)
					{
						if ($slot->disable)
						{
							$class = 'not-available';
						}
						else
						{
							// calculate the 20% of the maximum number of allowed orders
							$percent_20 = ceil($slot->maxOrders * 0.2);

							if ($percent_20 >= $slot->maxOrders - $slot->ordersCount)
							{
								// remaining orders equals or lower than 20%
								$class = 'almost-full';
							}
							else
							{
								$class = 'available';
							}
						}
						?>
						<tr class="<?php echo $class; ?>" data-date="<?php echo $filters['date']; ?>" data-time="<?php echo $slot->value; ?>" data-orders-count="<?php echo $slot->ordersCount; ?>" data-orders-max="<?php echo $slot->maxOrders; ?>">
							
							<!-- Time -->
							<td style="text-align: center;">
								<div class="td-primary">
									<?php echo $slot->text; ?>
								</div>
							</td>

							<!-- Orders -->
							<td>
								<div class="td-primary">
									<?php
									echo JText::plural(
										'VRE_STATS_WIDGET_TIMES_SUMMARY_N_ORDERS',
										$slot->ordersCount,
										$slot->maxOrders
									);
									?>
								</div>

								<div class="td-secondary">
									<?php
									echo JText::plural(
										'VRE_STATS_WIDGET_TIMES_SUMMARY_N_ITEMS',
										$slot->count,
										isset($slot->maxItems) ? $slot->maxItems : 0
									);
									?>
								</div>
							</td>

							<!-- Actions -->
							<td style="text-align: right;">
								<a href="javascript: void(0);" class="avail-time-action">
									<i class="fas fa-ellipsis-h"></i>
								</a>
							</td>

						</tr>
						<?php
					}
				}
				?>

			</tbody>

		</table>

		<script>
			// render context menu
			jQuery('.avail-times-table[data-widget="<?php echo $widget->getID(); ?>"]')
				.find('td a.avail-time-action')
					.availTimesPopup();
		</script>
	<?php
}
