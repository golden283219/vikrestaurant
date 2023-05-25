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
 * @var   array  $records   An array of transactions.
 */
extract($displayData);

$vik = VREApplication::getInstance();

?>

<style>
	table.order-status-table table tr:last-of-type td {
		border-bottom: 0;
	}

	table.order-status-table .track-comment table tr td.payload-value {
		word-break: break-all;
	}
</style>
		
<table class="order-status-table">

	<thead>
		<tr>

			<!-- ID -->

			<th width="2%" class="nowrap" style="text-align: left;">
				<?php echo JText::_('VRMANAGECUSTOMER1'); ?>
			</th>

			<!-- SUMMARY -->

			<th width="40%" style="text-align: left;">
				<?php echo JText::_('VRE_STATS_WIDGET_TIMES_SUMMARY'); ?>
			</th>

			<!-- STATUS -->

			<th width="8%" class="nowrap" style="text-align: center;">
				<?php echo JText::_('VRMANAGERESERVATION12'); ?>
			</th>

			<!-- CREATION DATE -->

			<th width="20%" class="nowrap" style="text-align: center;">
				<?php echo JText::_('VRMANAGERESERVATION13'); ?>
			</th>

			<!-- TOGGLE -->

			<th width="2%" class="nowrap" style="text-align: right;">
				<a href="javascript:void(0)" id="tx-toggle-all">
					<i class="fas fa-toggle-off medium-big"></i>
				</a>
			</th>

		</tr>
	</thead>

	<tbody>
		<?php
		foreach ($records as $record)
		{
			?>
			<tr>

				<!-- ID -->

				<td style="text-align: left;">
					<?php echo $record->id; ?>
				</td>

				<!-- SUMMARY -->

				<td style="text-align: left;">
					<?php echo $record->summary; ?>
				</td>

				<!-- STATUS -->
				
				<td style="text-align: center;">
					<?php
					if ($record->status == 1)
					{
						?><i class="fas fa-check-circle ok big"></i><?php
					}
					else if ($record->status == 0)
					{
						?><i class="fas fa-times-circle no big"></i><?php	
					}
					else
					{
						?><i class="fas fa-minus-circle warn big"></i><?php
					}
					?>
				</td>

				<!-- CREATION DATE -->

				<td style="text-align: center;">
					<?php echo JHtml::_('date', $record->creation, JText::_('DATE_FORMAT_LC6')); ?>
				</td>

				<!-- TOGGLE -->

				<td style="text-align: right;">
					<?php
					if ($record->data)
					{
						?>
						<a href="javascript:void(0)" class="tx-record-toggle" data-id="<?php echo $record->id; ?>">
							<i class="fas fa-chevron-right medium-big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-chevron-right medium-big disabled"></i>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
			if ($record->data)
			{
				?>
				<tr class="track-comment" id="order-details-<?php echo $record->id; ?>" style="display:none;">
					<td colspan="5">
						<table style="width: 100%;">
							<?php
							foreach ($record->data as $k => $v)
							{
								if (is_null($v))
								{
									$v = 'NULL';
								}

								if (!is_scalar($v))
								{
									if (defined('JSON_PRETTY_PRINT'))
									{
										// JSON-encode by using pretty-print
										$v = json_encode($v, JSON_PRETTY_PRINT);
									}
									else
									{
										// pretty-print not available, use print_r
										$v = print_r($v, true);
									}

									$v = '<pre>' . $v . '</pre>';
								}

								?>
								<tr>
									<td style="vertical-align: top;" class="payload-key"><?php echo $k; ?></td>
									<td style="vertical-align: top;" class="payload-value"><?php echo $v; ?></td>
								</tr>
								<?php
							}
							?>
						</table>
					</td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>

</table>

<script>

	(function($) {
		'use strict';

		const toggleAll = (link, status) => {
			var toggle = 0;

			if ($(link).find('i').hasClass('fa-toggle-off') || status == 1) {
				// open
				$(link).find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');

				toggle = 1;
			} else {
				// close
				$(link).find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
			}

			if (status == undefined) {
				$('.tx-record-toggle').each(function() {
					toggleDetails(this, toggle);
				});
			}
		}

		const toggleDetails = (link, status) => {
			var id = $(link).data('id');

			if (($(link).find('i').hasClass('fa-chevron-right') && status !== 0) || status == 1) {
				// open
				$(link).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');

				$('#order-details-' + id).show();
			} else {
				// close
				$(link).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-right');

				$('#order-details-' + id).hide();
			}

			if (status == undefined) {
				var open = $('.tx-record-toggle i.fa-chevron-down').length;

				if (open > 0) {
					// at least a record open
					toggleAll($('#tx-toggle-all')[0], 1);
				} else {
					// all records closed
					toggleAll($('#tx-toggle-all')[0], 0);
				}
			}
		}

		$(function() {
			$('#tx-toggle-all').on('click', function() {
				toggleAll(this);
			});

			$('.tx-record-toggle').on('click', function() {
				toggleDetails(this);
			});
		});
	})(jQuery);

</script>
