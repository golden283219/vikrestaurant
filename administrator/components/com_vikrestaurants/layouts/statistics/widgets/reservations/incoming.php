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

if (count($reservations) == 0)
{
	echo VREApplication::getInstance()->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
}
else
{
	?>
	<div class="dash-table-wrapper">
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
					<!-- Status -->
					<th width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION12'); ?></th>
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
								
								<span class="actions-group">
									<a href="index.php?option=com_vikrestaurants&amp;view=printorders&amp;tmpl=component&amp;cid[]=<?php echo $r->id; ?>" target="_blank">
										<i class="fas fa-print"></i>
									</a>
								</span>
							</div>

							<div class="td-secondary">
								<?php
								echo JText::sprintf(
									'VRMANAGERESERVATION27',
									VikRestaurants::formatTimestamp(
										JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'),
										$r->checkin_ts,
										$local = true
									)
								);
								?>
							</div>
						</td>

						<!-- Check-in -->
						<td>
							<div class="td-primary">
								<a href="javascript: void(0);" onclick="vrOpenJModal('respinfo', <?php echo $r->id; ?>, 'restaurant'); return false;">
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
									<?php
									if ($r->id_user > 0)
									{
										?>
										<a href="javascript: void(0);" onclick="vrOpenJModal('custinfo', <?php echo $r->id_user; ?>, 'restaurant'); return false;">
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

						<!-- Table -->
						<td>
							<span class="badge badge-warning"><?php echo $r->room_name; ?></span>
							<span class="badge badge-info"><?php echo $r->table_name; ?></span>
						</td>

						<!-- Status -->
						<td>
							<span class="status-text vrreservationstatus<?php echo strtolower($r->status); ?>">
								<?php echo JText::_('VRRESERVATIONSTATUS' . $r->status); ?>
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
