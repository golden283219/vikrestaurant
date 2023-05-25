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

$vik = VREApplication::getInstance();

$config = VREFactory::getConfig();

$currency = VREFactory::getCurrency();

?>

<table class="order-status-table">

	<thead>
		<tr>
			<!-- Order Number -->
			<th width="20%" style="text-align: left;" class="hidden-phone"><?php echo JText::_('VRMANAGERESERVATION1'); ?></th>
			<!-- Checkin -->
			<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION3'); ?></th>
			<!-- People -->
			<th width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION4'); ?></th>
			<!-- Table -->
			<th width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION5'); ?></th>
			<!-- Bill -->
			<th width="10%" style="text-align: left;" class="hidden-phone"><?php echo JText::_('VRMANAGERESERVATION10'); ?></th>
			<!-- Toggle -->
			<th width="2%" style="text-align: right;">
				<a href="javascript:void(0);" id="restaurant-toggle-all">
					<i class="fas fa-toggle-off medium-big"></i>
				</a>
			</th>
		</tr>
	</thead>

	<tbody>
		
		<?php
		foreach ($this->reservations as $res)
		{
			?>
			<tr>
				
				<!-- Order Number, Creation Date -->

				<td class="hidden-phone">
					<div class="td-primary">
						<?php echo $res->id . '-' . $res->sid; ?>
					</div>

					<div class="td-secondary">
						<?php echo JHtml::_('date', JDate::getInstance($res->created_on), JText::_('DATE_FORMAT_LC1') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?>
					</div>
				</td>

				<!-- Checkin -->

				<td>
					<div class="td-primary">
						<?php echo JHtml::_('date', JDate::getInstance($res->checkin_ts), JText::_('DATE_FORMAT_LC1'), date_default_timezone_get()); ?>
					</div>

					<div class="td-secondary">
						<i class="fas fa-sign-in" style="margin-right: 2px;"></i>
						<?php echo date($config->get('timeformat'), $res->checkin_ts); ?>

						<i class="fas fa-sign-out-alt" style="margin-right: 2px;margin-left: 6px;"></i>
						<?php echo date($config->get('timeformat'), $res->checkout); ?>
					</div>
				</td>

				<!-- People -->

				<td style="text-align: center;">
					<?php
					echo $res->people . ' ';

					for ($p = 1; $p <= min(array(2, $res->people)); $p++)
					{
						?><i class="fas fa-male"></i><?php
					}
					?>
				</td>

				<!-- Table -->

				<td>
					<span class="badge badge-info hidden-phone">
						<?php echo $res->room_name; ?>
					</span>

					<span class="badge badge-warning">
						<?php echo $res->table_name; ?>
					</span>
				</td>

				<!-- Bill -->

				<td class="hidden-phone">
					<div class="td-primary">
						<?php echo $currency->format($res->bill_value); ?>
					</div>

					<div class="td-secondary">
						<?php
						if ($res->bill_value > $res->deposit && !$res->bill_closed)
						{
							// display remaining balance
							echo JText::sprintf('VRORDERDUE', $currency->format($res->bill_value - $res->deposit));
						}
						else if ($res->deposit > 0)
						{
							// display deposit left
							echo JText::sprintf('VRORDERDEP', $currency->format($res->deposit));
						}
						?>
					</div>
				</td>

				<!-- Toggle -->

				<td style="text-align: right;">
					<a href="javascript:void(0);" class="restaurant-res-toggle" data-id="<?php echo $res->id; ?>">
						<i class="fas fa-chevron-right medium-big"></i>
					</a>
				</td>

			</tr>

			<tr class="track-comment" id="reservation-details-<?php echo $res->id; ?>" style="display:none;">
				
				<!-- Items -->

				<td colspan="3" style="vertical-align: top;">

					<?php
					if ($res->items)
					{
						?>
						<ul class="items-list">
							<?php
							foreach ($res->items as $item)
							{
								?>
								<li class="item-row">

									<div class="item-row-details">
										<div class="item-name"><?php echo $item->name; ?></div>
										
										<div class="item-quantity">x<?php echo $item->quantity; ?></div>

										<div class="item-price"><?php echo $currency->format($item->price); ?></div>
									</div>

									<?php
									if ($item->notes)
									{
										?>
										<div class="item-row-comment">
											<?php echo $item->notes; ?>		
										</div>
										<?php
									}
									?>

								</li>
								<?php
							}
							?>
						</ul>
						<?php
					}
					else
					{
						echo $vik->alert(JText::_('VREMPTYCART'));
					}
					?>

				</td>

				<!-- Reservation Notes -->

				<td colspan="3" style="vertical-align: top;">

					<div class="hidden-phone">
						<?php
						if ($res->notes)
						{
							echo $vik->alert($res->notes, 'info');
						}
						else
						{
							echo $vik->alert(JText::_('VRE_NOTES_MISSING'));
						}
						?>
					</div>

				</td>

			</tr>
			<?php
		}
		?>

	</tbody>

	<?php
	if ($this->totalReservations > 1)
	{
		?>
		<tfoot>
			<tr>
				<td>
					<b><?php echo JText::plural('VRE_N_RESERVATIONS', $this->totalReservations); ?></b>
				</td>

				<td class="hidden-phone">&nbsp;</td>

				<td>&nbsp;</td>

				<td style="text-align: right;" class="hidden-phone">
					<?php echo JText::_('VRMANAGEBILL2'); ?>
				</td>

				<td>
					<b style="font-size: larger;"><?php echo $currency->format($this->restaurantTotal); ?></b>
				</td>

				<td>&nbsp;</td>
			</tr>
		</tfoot>
		<?php
	}
	?>

</table>

<?php
if ($this->restaurantNav)
{
	echo '<br />' . $this->restaurantNav;
}
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#restaurant-toggle-all').on('click', function() {
			toggleAllReservations(this);
		});

		jQuery('.restaurant-res-toggle').on('click', function() {
			toggleReservationDetails(this);
		});

	});

	function toggleAllReservations(link, status) {
		var toggle = 0;

		if (jQuery(link).find('i').hasClass('fa-toggle-off') || status == 1) {
			// open
			jQuery(link).find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');

			toggle = 1;
		} else {
			// close
			jQuery(link).find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
		}

		if (status == undefined) {
			jQuery('.restaurant-res-toggle').each(function() {
				toggleReservationDetails(this, toggle);
			});
		}
	}

	function toggleReservationDetails(link, status) {
		var id = jQuery(link).data('id');

		if ((jQuery(link).find('i').hasClass('fa-chevron-right') && status !== 0) || status == 1) {
			// open
			jQuery(link).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');

			jQuery('#reservation-details-' + id).show();
		} else {
			// close
			jQuery(link).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-right');

			jQuery('#reservation-details-' + id).hide();
		}

		if (status == undefined) {
			var open = jQuery('.restaurant-res-toggle i.fa-chevron-down').length;

			if (open > 0) {
				// at least a record open
				toggleAllReservations(jQuery('#restaurant-toggle-all')[0], 1);
			} else {
				// all records closed
				toggleAllReservations(jQuery('#restaurant-toggle-all')[0], 0);
			}
		}
	}

</script>
