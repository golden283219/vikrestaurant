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
 * @var  array 				  $reservations  A list of reservations to display.
 * @var  VREStatisticsWidget  $widget        The instance of the widget to be displayed.
 */
extract($displayData);

$config = VREFactory::getConfig();

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

if (count($reservations) == 0)
{
	echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
}
else
{
	?>
	<div class="dash-table-wrapper" data-widget="<?php echo $widget->getID(); ?>">
		<table>

			<thead>
				<tr>
					<!-- Order Number -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION1'); ?></th>
					<!-- Check-in -->
					<th width="25%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION3'); ?></th>
					<!-- Customer -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION17'); ?></th>
					<!-- Table -->
					<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION5'); ?></th>
					<!-- Reservation Code -->
					<th width="15%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION19'); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php
				foreach ($reservations as $r)
				{
					?>
					<tr>
						
						<!-- Order Number -->
						<td>
							<div class="td-primary">
								<?php echo $r->id; ?>
							</div>

							<div class="td-secondary">
								<?php
								echo JText::sprintf(
									'VRMANAGERESERVATION28',
									VikRestaurants::formatTimestamp(
										JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'),
										$r->checkout,
										$local = true
									)
								);
								?>
							</div>
						</td>

						<!-- Check-in -->
						<td>
							<div class="td-primary">
								<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opreservation.edit&from=oversight&cid[]=' . $r->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
									<?php echo JHtml::_('date', $r->checkin_ts, JText::_('DATE_FORMAT_LC3'), date_default_timezone_get()); ?>
								</a>
							</div>

							<div class="td-secondary">
								<span><?php echo JHtml::_('date', $r->checkin_ts, $config->get('timeformat'), date_default_timezone_get()); ?></span>

								<span class="td-pull-right">
									<?php
									echo $r->people . ' ';

									for ($p = 1; $p <= min(array(2, $r->people)); $p++)
									{
										?><i class="fas fa-male"></i><?php
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

						<!-- Table -->
						<td>
							<span class="badge badge-warning"><?php echo $r->room_name; ?></span>
							<span class="badge badge-info"><?php echo $r->table_name; ?></span>
						</td>

						<!-- Status Code -->
						<td style="text-align: center;">
							<span class="vrrescodelink">
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
							</span>
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
