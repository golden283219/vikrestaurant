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

JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen');
JHtml::_('vrehtml.scripts.updateshifts');

VRELoader::import('library.map.factory');

$rooms 				 = $this->rooms;
$selectedRoomId 	 = $this->selectedRoomId;
$tables 			 = $this->tables;
$rows 				 = $this->reservationTableOnDate;
$shared_occurrency 	 = $this->allSharedTablesOccurrency;
$currentReservations = $this->currentReservations;

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

<form name="adminForm" action="index.php?option=com_vikrestaurants" method="post" id="adminForm">

<?php
if (count($rooms) == 0)
{
	echo $vik->alert(JText::_('VRNOROOM'));
}
else
{
	?>
	<div class="btn-toolbar vr-btn-toolbar" style="height:32px;">

		<div class="btn-group pull-left">
			<?php 
			$elements = array();
				
			foreach ($this->rooms as $row)
			{
				$rname = $row->name; 

				if ($row->isClosed)
				{
					$rname .= ' (' . JText::_('VRROOMSTATUSCLOSED') . ')';
				}
				
				$elements[] = JHtml::_('select.option', $row->id, $rname);
			}
			?>
			<select name="selectedroom" id="vrselectedroom" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $elements, 'value', 'text', $selectedRoomId); ?>
			</select>
		</div>
		
		<div class="btn-group pull-right">
			<button type="submit" class="btn"><?php echo JText::_('VRMAPSSUBMITSEARCH'); ?></button>
		</div>
		
		<div class="btn-group pull-right">
			<?php
			$attrs = array(
				'id'    => 'vrpeople',
				'class' => 'dropdown-short',
			);

			echo JHtml::_('vrehtml.site.peopleselect', 'people', $filters['people'], $attrs);
			?>
		</div>
		
		<div class="btn-group pull-right">
			<?php
			$times = JHtml::_('vikrestaurants.times', 1, $filters['date']);

			$attrs = array(
				'id'    => 'vrhour',
				'class' => 'dropdown-short',
			);

			echo JHtml::_('vrehtml.site.timeselect', 'hourmin', $filters['hourmin'], $times, $attrs);
			?>
		</div>
		
		<div class="btn-group pull-right vr-toolbar-setfont">
			<?php
			$attr = array();
			$attr['class'] 		= 'vrdatefilter';
			$attr['data-title'] = JText::_('VRMAPSDATESEARCH');
			$attr['onChange']	= "vrUpdateWorkingShifts('#vrdatefilter', '#vrhour');";

			echo $vik->calendar($filters['date'], 'datefilter', 'vrdatefilter', null, $attr);
			?>
		</div>
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
}
?>
	
	<input type="hidden" name="formsubmitted" value="1" />
	<input type="hidden" name="view" value="maps" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRMAPSBEFORECHOOSEROOM');
?>

<script>

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});

	Joomla.submitbutton = function(task) {
		var selId = document.getElementById('vrselectedroom').value;

		if (task != 'map.edit' || (selId.length && selId != '-1')) {
			Joomla.submitform(task, document.adminForm);
		} else {
			alert(Joomla.JText._('VRMAPSBEFORECHOOSEROOM'));
		}
	}

</script>
