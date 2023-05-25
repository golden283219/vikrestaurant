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

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.sitescripts.datepicker', '#vrdatefilter:input');

$operator = $this->operator;

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

$config = VREFactory::getConfig();

$rescodes = JHtml::_('vikrestaurants.rescodes', 1);

$vik = VREApplication::getInstance();

?>

<div class="vrfront-manage-titlediv">
	<h2><?php echo JText::_('VROVERSIGHTMENUITEM3'); ?></h2>
	<?php echo VikRestaurants::getToolbarLiveMap($operator); ?>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opreservations' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="POST" name="opreservationsform" id="adminForm">

	<div class="vrfront-list-wrapper">

		<div class="vrfront-manage-headerdiv">
			
			<div class="vrfront-manage-actionsdiv">
				
				<div class="vrfront-manage-btn">
					<input type="text" name="keysearch" value="<?php echo $this->filters['keysearch']; ?>" size="32" placeholder="<?php echo $this->escape(JText::_('VROPRESKEYFILTER')); ?>" />
				</div>

				<div class="vrfront-manage-btn">
					<div class="vre-calendar-wrapper">
						<input type="text" name="datefilter" id="vrdatefilter" class="vre-calendar" size="20" value="<?php echo $this->filters['datefilter']; ?>" placeholder="<?php echo $this->escape(JText::_('VROPRESDATEFILTER')); ?>" />
					</div>
				</div>

				<?php
				// get rooms supported by the operator
				$rooms = $operator->getRooms();

				// display filter only in case of 2+ rooms
				if (count($rooms) > 1)
				{
					// prepend empty option to unset filter
					array_unshift($rooms, JHtml::_('select.option', 0, JText::_('VRMAPSCHOOSEROOM')));
					?>
					<div class="vrfront-manage-btn">
						<div class="vre-select-wrapper">
							<select name="id_room" class="vre-select">
								<?php echo JHtml::_('select.options', $rooms, 'value', 'text', $this->filters['id_room']); ?>
							</select>
						</div>
					</div>
					<?php
				}
				?>

				<div class="vrfront-manage-btn move-right">
					<button type="button" onClick="vrNewReservation();" id="vrfront-manage-btncreate" class="vrfront-manage-button">
						<i class="fas fa-plus-circle"></i>
					</button>
				</div>
				
				<div class="vrfront-manage-btn move-right">
					<button type="submit" id="vrfront-manage-btnfilter" class="vrfront-manage-button">
						<?php echo JText::_('VRMAPSSUBMITSEARCH'); ?>
					</button>
				</div>
				
			</div>
			
		</div>

		<?php
		if (count($this->reservations) == 0)
		{
			echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
		}
		else
		{
			?>
			<div class="vr-allorders-list">
				
				<div class="vr-allorders-singlerow vr-allorders-head vr-allorders-row">
					<span class="vr-allorders-column" style="width: 20%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGERESERVATION1', 'r.id', $this->orderingDir, $this->ordering); ?>
					</span>

					<span class="vr-allorders-column" style="width: 20%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGERESERVATION3', 'r.checkin_ts', $this->orderingDir, $this->ordering); ?>
					</span>
					
					<span class="vr-allorders-column" style="width: 10%;text-align: center;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGERESERVATION4', 'r.people', $this->orderingDir, $this->ordering); ?>
					</span>
					
					<span class="vr-allorders-column" style="width: 10%;text-align: center;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGERESERVATION5', 't.name', $this->orderingDir, $this->ordering); ?>
					</span>
					
					<span class="vr-allorders-column" style="width: 22%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGERESERVATION17', 'r.purchaser_nominative', $this->orderingDir, $this->ordering); ?>
					</span>
					
					<span class="vr-allorders-column" style="width: 18%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGERESERVATION12', 'r.status', $this->orderingDir, $this->ordering); ?>
					</span>
				</div>

				<?php 
				$kk = 1;
				foreach ($this->reservations as $row)
				{
					// use check-in date as primary field by default
					$checkin_1st = JHtml::_('date', $row['checkin_ts'], JText::_('DATE_FORMAT_LC3'), date_default_timezone_get());
					$checkin_2nd = JHtml::_('date', $row['checkin_ts'], $config->get('timeformat'), date_default_timezone_get());

					if (!empty($this->filters['datefilter']))
					{
						// switch date and time in case we are searching for a specific date,
						// so that the check-in time will gain higher priority
						$tmp         = $checkin_1st;
						$checkin_1st = $checkin_2nd;
						$checkin_2nd = $tmp;
					}
					?>
					<div class="vr-allorders-singlerow vr-allorders-row<?php echo $kk; ?>">
						<div class="vr-allorders-column" style="width: 20%; text-align: left;">
							<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opreservation.edit&cid[]=' . $row['id'] . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="break-word">
								<?php echo $row['id'] . '-' . $row['sid']; ?>
							</a>
						</div>

						<div class="vr-allorders-column" style="width: 20%; text-align: left;">
							<div class="td-primary">
								<?php
								echo $checkin_1st;

								if (strip_tags($row['notes']))
								{
									$notes = $row['notes'];
									// always obtain short description, if any
									$vik->onContentPrepare($notes, false);
									?>
									<i class="fas fa-sticky-note hasTooltip" title="<?php echo $this->escape($notes->text); ?>" style="margin-left: 4px;"></i>
									<?php
								}
								?>
							</div>

							<div class="td-secondary">
								<?php echo $checkin_2nd; ?>
							</div>
						</div>
						
						<div class="vr-allorders-column" style="width: 10%; text-align: center;">
							<?php echo JText::plural('VRE_N_PEOPLE', $row['people']); ?>
						</div>
						
						<div class="vr-allorders-column" style="width: 10%; text-align: center;">
							<span class="badge badge-info hasTooltip" title="<?php echo $this->escape($row['room_name']); ?>">
								<?php echo $row['table_name']; ?>
							</span>

							<?php
							/**
							 * Check if the reservation has been merged with
							 * other tables to host a larger group.
							 *
							 * @since 1.8
							 */
							if ($row['cluster'])
							{
								foreach (explode(',', $row['cluster']) as $tname)
								{
									?>
									<span class="badge badge-info badge-table"><?php echo $tname; ?></span>
									<?php
								}
							}
							?>
						</div>
						
						<div class="vr-allorders-column" style="width: 22%; text-align: left;">
							<div class="td-primary">
								<?php echo $row['purchaser_nominative']; ?>
							</div>

							<div class="td-secondary">
								<?php
								if ($row['purchaser_phone'])
								{
									?>
									<a href="tel:<?php echo $row['purchaser_phone']; ?>">
										<?php echo $row['purchaser_phone']; ?>
									</a>
									<?php
								}
								else if ($row['purchaser_mail'])
								{
									?>
									<a href="mailto:<?php echo $row['purchaser_mail']; ?>">
										<?php echo $row['purchaser_mail']; ?>
									</a>
									<?php
								}
								?>
							</div>
						</div>
						
						<div class="vr-allorders-column vrreservationstatus<?php echo strtolower($row['status']); ?>" style="width: 18%; text-align: left;">
							<div class="td-primary">
								<?php echo strtoupper(JText::_('VRRESERVATIONSTATUS' . $row['status'])); ?>
							</div>

							<div class="td-secondary rescode-wrapper">
								<div class="vre-select-wrapper">
									<select class="res-code-selection vre-select" data-order="<?php echo $row['id']; ?>" data-code="<?php echo $row['rescode']; ?>">
										<option value="0">--</option>
										<?php echo JHtml::_('select.options', $rescodes, 'value', 'text', $row['rescode']); ?>
									</select>
								</div>

								<span class="vrrescodelink">
									<?php
									if ($row['rescode'] > 0 && $row['code_icon'])
									{
										?>
										<img src="<?php echo VREMEDIA_SMALL_URI . $row['code_icon']; ?>" title="<?php echo $row['status_code']; ?>" />
										<?php
									}
									?>
								</span>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>

			<?php echo JHtml::_('form.token'); ?>

			<div class="vr-list-pagination"><?php echo $this->navbut; ?></div>

			<?php
		}
		?>

	</div>

	<input type="hidden" name="from" value="opreservations" />
	<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderingDir; ?>" />
	
	<input type="hidden" name="view" value="opreservations" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script>

	function vrNewReservation() {
		Joomla.submitform('opreservation.add', document.opreservationsform);
	}

	jQuery(document).ready(function() {

		jQuery('.res-code-selection').on('change', function() {
			// get clicked select
			var select = jQuery(this);
			// find code icon
			var link = select.closest('.rescode-wrapper').find('.vrrescodelink');

			// create promise to resolve when the status changes
			callback = new Promise((resolve) => {
				
				// disable select
				select.prop('disabled', true);

				// make request to change code
				UIAjax.do(
					'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=oversight.changecodeajax' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
					{
						group:   1,
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
		});

	});

</script>
