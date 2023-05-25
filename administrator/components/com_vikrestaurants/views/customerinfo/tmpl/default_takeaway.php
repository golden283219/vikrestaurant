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
			<th width="20%" style="text-align: left;" class="hidden-phone"><?php echo JText::_('VRMANAGETKRES1'); ?></th>
			<!-- Checkin -->
			<th width="20%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES3'); ?></th>
			<!-- Service -->
			<th width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES13'); ?></th>
			<!-- Order Total -->
			<th width="10%" style="text-align: left;" class="hidden-phone"><?php echo JText::_('VRMANAGETKRES8'); ?></th>
			<!-- Toggle -->
			<th width="2%" style="text-align: right;">
				<a href="javascript:void(0);" id="takeaway-toggle-all">
					<i class="fas fa-toggle-off medium-big"></i>
				</a>
			</th>
		</tr>
	</thead>

	<tbody>
		
		<?php
		foreach ($this->orders as $ord)
		{
			?>
			<tr>
				
				<!-- Order Number, Creation Date -->

				<td class="hidden-phone">
					<div class="td-primary">
						<?php echo $ord->id . '-' . $ord->sid; ?>
					</div>

					<div class="td-secondary">
						<?php echo JHtml::_('date', JDate::getInstance($ord->created_on), JText::_('DATE_FORMAT_LC1') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?>
					</div>
				</td>

				<!-- Checkin -->

				<td>
					<div class="td-primary">
						<?php echo JHtml::_('date', JDate::getInstance($ord->checkin_ts), JText::_('DATE_FORMAT_LC1'), date_default_timezone_get()); ?>
					</div>

					<div class="td-secondary">
						<i class="fas fa-sign-in" style="margin-right: 2px;"></i>
						<?php echo date($config->get('timeformat'), $ord->checkin_ts); ?>
					</div>
				</td>

				<!-- Service -->

				<td>
					<div class="td-primary">
						<?php
						if ($ord->delivery_service)
						{
							?><span class="badge badge-info"><?php echo JText::_('VRMANAGETKRES14'); ?></span><?php
						}
						else
						{
							?><span class="badge badge-warning"><?php echo JText::_('VRMANAGETKRES15'); ?></span><?php
						}
						?>
					</div>
				</td>

				<!-- Order Total -->

				<td class="hidden-phone">
					<div class="td-primary">
						<?php echo $currency->format($ord->total_to_pay); ?>
					</div>

					<div class="td-secondary">
						<?php
						if ($ord->total_to_pay > $ord->tot_paid)
						{
							// display remaining balance
							echo JText::sprintf('VRORDERDUE', $currency->format($ord->total_to_pay - $ord->tot_paid));
						}
						?>
					</div>
				</td>

				<!-- Toggle -->

				<td style="text-align: right;">
					<a href="javascript:void(0);" class="takeaway-res-toggle" data-id="<?php echo $ord->id; ?>">
						<i class="fas fa-chevron-right medium-big"></i>
					</a>
				</td>

			</tr>

			<tr class="track-comment" id="order-details-<?php echo $ord->id; ?>" style="display:none;">
				
				<!-- Items -->

				<td colspan="2" style="vertical-align: top;">

					<?php
					if ($ord->items)
					{
						?>
						<ul class="items-list">
							<?php
							foreach ($ord->items as $item)
							{
								?>
								<li class="item-row">

									<div class="item-row-details">
										<div class="item-name"><?php echo $item->name; ?></div>
										
										<div class="item-quantity">x<?php echo $item->quantity; ?></div>

										<div class="item-price"><?php echo $currency->format($item->price); ?></div>
									</div>

									<?php
									if ($item->toppings)
									{
										foreach ($item->toppings as $group)
										{
											?>
											<div class="item-row-toppings-group">
												<small>
													<?php echo $group->title . ': '; ?><b><?php echo $group->str; ?></b>
												</small>
											</div>
											<?php
										}
									}

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
						if ($ord->notes)
						{
							echo $vik->alert($ord->notes, 'info');
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
	if ($this->totalOrders > 1)
	{
		?>
		<tfoot>
			<tr>
				<td>
					<b><?php echo JText::plural('VRE_N_RESERVATIONS', $this->totalOrders); ?></b>
				</td>

				<td>&nbsp;</td>

				<td style="text-align: right;" class="hidden-phone">
					<?php echo JText::_('VRMANAGEBILL2'); ?>
				</td>

				<td>
					<b style="font-size: larger;"><?php echo $currency->format($this->takeawayTotal); ?></b>
				</td>

				<td>&nbsp;</td>
			</tr>
		</tfoot>
		<?php
	}
	?>

</table>

<?php
if ($this->takeawayNav)
{
	echo '<br />' . $this->takeawayNav;
}
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#takeaway-toggle-all').on('click', function() {
			toggleAllOrders(this);
		});

		jQuery('.takeaway-res-toggle').on('click', function() {
			toggleOrderDetails(this);
		});

	});

	function toggleAllOrders(link, status) {
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
			jQuery('.takeaway-res-toggle').each(function() {
				toggleOrderDetails(this, toggle);
			});
		}
	}

	function toggleOrderDetails(link, status) {
		var id = jQuery(link).data('id');

		if ((jQuery(link).find('i').hasClass('fa-chevron-right') && status !== 0) || status == 1) {
			// open
			jQuery(link).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');

			jQuery('#order-details-' + id).show();
		} else {
			// close
			jQuery(link).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-right');

			jQuery('#order-details-' + id).hide();
		}

		if (status == undefined) {
			var open = jQuery('.takeaway-res-toggle i.fa-chevron-down').length;

			if (open > 0) {
				// at least a record open
				toggleAllOrders(jQuery('#takeaway-toggle-all')[0], 1);
			} else {
				// all records closed
				toggleAllOrders(jQuery('#takeaway-toggle-all')[0], 0);
			}
		}
	}

</script>
