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

if (!$this->ACCESS)
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JHtml::_('behavior.keepalive');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.sitescripts.updateshifts', $restaurant = 1);
JHtml::_('vrehtml.sitescripts.datepicker', '#vrdatefield:input');
VRELoader::import('library.map.factory');

$operator = $this->user;

$refresh_ms 	= 90000; // 90 seconds
$enable_refresh = true;

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

$config = VREFactory::getConfig();

$rooms 					= $this->rooms;
$selectedRoomId 		= $this->selectedRoomId;
$tables 				= $this->tables;
$rows 					= $this->reservationTableOnDate;
$shared_occurrency 		= $this->allSharedTablesOccurrency;
$currentReservations 	= $this->currentReservations;

$filters = $this->filters;

// SETTING AVAILABLE TABLES

for ($i = 0, $n = count($tables); $i < $n; $i++)
{
	$found = false;

	for ($j = 0, $m = count($rows); $j < $m && !$found; $j++)
	{
		$found = $rows[$j]->id == $tables[$i]->id;
	}
	
	$tables[$i]->available = $found;

	if (isset($shared_occurrency[$tables[$i]->id]))
	{
		$tables[$i]->occurrency = $shared_occurrency[$tables[$i]->id];
	}
	else
	{
		$tables[$i]->occurrency = 0;
	}
}

// ASSIGNING THE RESERVATIONS

for ($i = 0, $n = count($tables); $i < $n; $i++)
{
	$tables[$i]->reservations = array();
	
	$found = false;

	for ($j = 0; $j < count($currentReservations) && !$found; $j++)
	{
		if ($tables[$i]->id == $currentReservations[$j]->id_table)
		{
			$tables[$i]->reservations[] = $currentReservations[$j];
			
			if ($tables[$i]->multi_res == 0)
			{
				// stop only in case of non shared table
				$found = true;

				// update occupancy
				$tables[$i]->occurrency = (int) $currentReservations[$j]->people;
			}
		}
	}
}

for ($room_index = 0; $room_index < count($rooms) && $rooms[$room_index]->id != $selectedRoomId; $room_index++);

$vik = VREApplication::getInstance();

?>

<div class="vroversighthead">
	<h2><?php echo JText::sprintf('VRLOGINOPERATORHI', $operator->get('firstname')); ?></h2>

	<?php echo VikRestaurants::getToolbarLiveMap($operator); ?>
</div>

<form name="oversightform" action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" id="vroversightform" class="vrfront-manage-form">

<?php
if (count($rooms) == 0)
{
	?>
	<p><?php echo JText::_('VRNOROOM');?></p>
	<?php
}
else
{
	?>

	<div id="vrmapinputsdiv">
		<?php
		if (count($rooms) > 1)
		{
			?>
			<div id="vrselectedroomdiv">
				<?php
				foreach ($rooms as $r)
				{
					?>
					<div class="vroversight-room-block <?php echo ($selectedRoomId == $r->id ? 'vroversight-room-selected' : ''); ?>">
						<a href="javascript: void(0);" class="vroversight-room-link" onClick="vrRoomClicked(<?php echo $r->id; ?>);"><?php echo $r->name; ?></a>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
			
		<div id="vrsearchinputdiv">

			<div id="vrdatefilterdiv">
				<label for="vrdatefilter"><b><?php echo JText::_('VRMAPSDATESEARCH');?></b></label>
				
				<div class="vre-calendar-wrapper">
					<input type="text" value="<?php echo $filters['date']; ?>" id="vrdatefield" class="vre-calendar" name="datefilter" size="20" />
				</div>
			</div>
			
			<div id="vrselecthoursdiv">
				<label for="vrselecthour"><b><?php echo JText::_('VRMAPSTIMESEARCH');?></b></label>
				
				<div class="vre-select-wrapper">
					<?php
					$times = JHtml::_('vikrestaurants.times', 1, $filters['date']);

					$attrs = array(
						'id'    => 'vrhour',
						'class' => 'vre-select dropdown-short',
					);

					echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $filters['hourmin'], $times, $attrs);
					?>
				</div>
			</div>
			
			<div id="vrselectpeoplediv">
				<label for="vrselectpeople"><b><?php echo JText::_('VRMAPSPEOPLESEARCH');?></b></label>
				
				<div class="vre-select-wrapper">
					<?php
					$attrs = array(
						'id'    => 'vrpeople',
						'class' => 'vre-select dropdown-short',
					);

					echo JHtml::_('vrehtml.site.peopleselect', 'people', $filters['people'], $attrs);
					?>
				</div>
			</div>
			
			<div id="vrsubmitfinddiv">
				<button type="submit" id="vrsubmitfind"><?php echo JText::_('VRMAPSSUBMITSEARCH');?></button>
			</div>

		</div>
		
		<?php
		if (!$this->timeOk)
		{
			?>
			<div class="vroversight-notime-warning"><?php echo JText::_('VRMAPSNOTIMEWARNING'); ?></div>
			<?php
		}
		else
		{
			?>
			<div class="vroversight-current-details">
				<?php
				$ts = VikRestaurants::createTimestamp($filters['date'], $filters['hour'], $filters['min']);
				?>
				<span class="vroversight-current-date">
					<?php echo JHtml::_('date', 'now', JText::_('DATE_FORMAT_LC1'), date_default_timezone_get()); ?>
				</span>

				<span class="vroversight-current-time">
					<?php echo date($config->get('timeformat'), VikRestaurants::now()); ?>
				</span>

				<span class="vroversight-current-people">x&nbsp;<?php echo $filters['people']; ?></span>
				
				<div class="vroversight-nowlink-div">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=oversight&hourmin=0:0' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="vroversight-now-link">
						<?php echo JText::_('VRNOWBUTTON'); ?>
					</a>
				</div>
			</div>
			<?php
		}
		?>
	
	</div>
		
	<?php
	if ($selectedRoomId > 0)
	{
		$options = array(
			'filters' => (object) $this->filters,
		);

		?>
		<div class="vre-map-svg-wrapper">
			<?php
			echo VREMapFactory::getInstance($options)
				->admin()
				->setRoom($rooms[$room_index])
				->setTables($tables)
				->build();
			?>
		</div>
		<?php
	}
	?>

		
<?php } ?>

	<?php
	// display reservations widget
	echo $this->loadTemplate('widget');
	?>
	
	<input type="hidden" name="formsubmitted" value="1" />
	<input type="hidden" name="selectedroom" value="<?php echo $selectedRoomId; ?>" id="vrselecetedroom" />
	<input type="hidden" name="view" value="oversight" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRMINSHORT');
?>

<script>

	jQuery(document).ready(function() {
		
		jQuery('#vrdatefield:input').on('change', function() {
			// refresh times
			vrUpdateWorkingShifts('#vrdatefield', '#vrhour');
		});

	});

	function vrRoomClicked(id_room) {
		if (id_room != <?php echo $selectedRoomId; ?>) {
			jQuery('#vrselecetedroom').val(id_room);
			document.oversightform.submit();
		}
	}

	// initialize the process that will update the 
	// orders remaining/arriving time every 15 seconds
	var EXPIRATION_TIMER = setInterval(function() {
		// iterate all the records
		jQuery('#vrbodyreslist1, #vrbodyreslist2').find('*[data-timer]').each(function() {
			var timer = jQuery(this).attr('data-timer');
			timer -= 15;
			jQuery(this).attr('data-timer', Math.max(0, timer));

			var text = '';

			if (timer > 0)
			{
				text = Math.ceil(timer / 60) + ' ' + Joomla.JText._('VRMINSHORT');
			}

			jQuery(this).text(text);
		});
	}, 15000);

</script>
