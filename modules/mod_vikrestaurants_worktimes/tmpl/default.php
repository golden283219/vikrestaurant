<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_worktimes
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$config = VREFactory::getConfig();

$date_f = $config->get('dateformat');
$time_f = $config->get('timeformat');

$MONTHS = array(
	'JANUARY_SHORT',
	'FEBRUARY_SHORT',
	'MARCH_SHORT',
	'APRIL_SHORT',
	'MAY_SHORT',
	'JUNE_SHORT',
	'JULY_SHORT',
	'AUGUST_SHORT',
	'SEPTEMBER_SHORT',
	'OCTOBER_SHORT',
	'NOVEMBER_SHORT',
	'DECEMBER_SHORT',
);

$DAYS = array(
	'SUN',
	'MON',
	'TUE',
	'WED',
	'THU',
	'FRI',
	'SAT',
);

$view_mode  = $params->get('viewmode');
$start_view = $params->get('startview');

if ($view_mode != 3 && $start_view != $view_mode)
{
	$start_view = $view_mode;
}

/**
 * Count the maximum number of working shifts per day.
 *
 * @since 1.2
 */
$max_count = 0;

foreach ($days as $d)
{
	$max_count = max(array($max_count, count($d['shifts'])));
}

switch ($max_count)
{
	case 0:
		$count_txt = 'one';
		break;

	case 1:
		$count_txt = 'one';
		break;

	case 2:
		$count_txt = 'two';
		break;

	case 3:
		$count_txt = 'three';
		break;

	case 4: 
		$count_txt = 'four';
		break;

	default: 
		$count_txt = 'five';
}

?>

<div id="vrworktmodule">
	
	<div class="vrworkt-page-scroller">

		<div class="vrworkt-pag-nav">
			<a href="javascript: void(0);" onClick="vrModSwipeToPage(1);" class="vrworkt-nav-link <?php echo ($start_view == 1 ? 'vrworkt-nav-selected' : ''); ?>" id="vrworkt-nav1"></a>
			<a href="javascript: void(0);" onClick="vrModSwipeToPage(2);" class="vrworkt-nav-link <?php echo ($start_view == 2 ? 'vrworkt-nav-selected' : ''); ?>" id="vrworkt-nav2"></a>
		</div>

		<div class="vrworkt-day-view" id="vrworkt-page1" style="<?php echo ($start_view == 1 ? '' : 'display: none'); ?>">

			<div class="vrworkt-day-circle <?php echo ($days[0]['status'] ? 'vrwt-day-open' : 'vrwt-day-closed'); ?>">
				<span class="vrworkt-day-num"><?php echo date('d', $days[0]['timestamp']); ?></span>
				<span class="vrworkt-month-text"><?php echo JText::_($MONTHS[date('m', $days[0]['timestamp']) - 1]); ?></span>
			</div>

			<div class="vrworkt-day-status <?php echo ($days[0]['status'] ? 'vrwt-day-status-ok' : 'vrwt-day-status-no'); ?>">
				<?php echo JText::_('VRWT' . ($days[0]['status'] ? 'OPEN' : 'CLOSED')); ?>
			</div>

			<div class="vrworkt-shifts-cont">
				<?php
				foreach ($days[0]['shifts'] as $sh)
				{
					?>
					<div class="vrworkt-shift-row">
						<span class="vrworkt-shift-name"><?php echo $sh['label']; ?></span>
						<span class="vrworkt-shift-clock">
							<?php echo $sh['fromtime'] . ' - ' . $sh['totime']; ?>
						</span>
					</div>
					<?php
				}
				?>
			</div>

		</div>

		<div class="vrworkt-day-view" id="vrworkt-page2" style="<?php echo ($start_view == 2 ? '' : 'display: none'); ?>">	
			<?php
			/**
			 * Do not display working shift labels as the configuration 
			 * might use different working times.
			 *
			 * We could consider using a specific setting in order to let
			 * the administrator choose whether to display the labels or not.
			 *
			 * @since 1.1.2
			 */
			
			foreach ($days as $d)
			{
				?>
				<div class="vrworkt-weekday-row">
					<div class="vrworkt-weekday-head <?php echo ($d['status'] ? 'vrworkt-weekday-head-open' : 'vrworkt-weekday-head-closed'); ?>">
						<span class="vrworkt-weekday-name"><?php echo JText::_($DAYS[date('w', $d['timestamp'])]); ?></span>
						<span class="vrworkt-weekday-num"><?php echo date('d', $d['timestamp']); ?></span>
						<span class="vrworkt-weekday-month"><?php echo JText::_($MONTHS[date('m', $d['timestamp']) - 1]); ?></span>
					</div>
					<div class="vrworkt-weekday-shifts <?php echo ($d['status'] ? 'vrworkt-weekday-shifts-open' : 'vrworkt-weekday-shifts-closed'); ?>">
						<?php
						if ($d['status'])
						{
							foreach ($d['shifts'] as $shift)
							{ 
								?>
								<span class="vrworkt-head-tr <?php echo 'vrworkt-head-tr-' . $count_txt; ?>">
									<?php
									if ($shift !== false)
									{
										?>
										<span class="vrworkt-head-tr-cont">
											<?php echo $shift['fromtime'] . ' - ' . $shift['totime']; ?>
										</span>
										<?php
									}
									?>
								</span>
								<?php
							}
						}
						else
						{
							?>
							<div class="vrworkt-weekday-shift-void vrworkt-head-tr">
								<?php echo JText::_('VRWTCLOSED'); ?>
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

<script type="text/javascript">

	jQuery(document).ready(function() {

		// not working
		jQuery('.vrworkt-page-scroller').on('swipeleft', function(event) {
			if (curr_page < _MAX_PAGE_) {
				vrModSwipeToPage(id_page + 1);
			}
		});

		// not working
		jQuery('.vrworkt-page-scroller').on('swiperight', function(event) {
			if (curr_page > _MIN_PAGE_) {
				vrModSwipeToPage(id_page - 1);
			}
		});
	});

	var curr_page = <?php echo $start_view; ?>;
	var _MIN_PAGE_ = 1;
	var _MAX_PAGE_ = 2;

	function vrModSwipeToPage(page) {
		if (page == curr_page) {
			return;
		}

		jQuery('.vrworkt-nav-link').removeClass('vrworkt-nav-selected');
		jQuery('#vrworkt-nav' + page).addClass('vrworkt-nav-selected');

		jQuery('.vrworkt-day-view').hide();
		//jQuery('#vrworkt-page'+page).animate({width:'toggle'},350);
		//jQuery('#vrworkt-page'+page).show("slide", { direction: (page < curr_page ? "left" : "right") }, 'fast');
		jQuery('#vrworkt-page' + page).show();

		curr_page = page;
	}
	
</script>
