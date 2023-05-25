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
 * Template file used to display the section from which it
 * is possible to select/change room.
 *
 * @since 1.8
 */

$vik = VREApplication::getInstance();

$config = VREFactory::getConfig();

$availableRooms = array();

foreach ($this->avail as $table)
{
	if (!in_array($table->id_room, $availableRooms))
	{
		$availableRooms[] = $table->id_room;
	}
}

// create map to easily access rooms descriptions
$desc_lookup = array();

// create map to easily access rooms available tables
$tables_lookup = array();

foreach ($this->avail as $table)
{
	if (!isset($tables_lookup[$table->id_room]))
	{
		// set only the first available table
		$tables_lookup[$table->id_room] = (int) $table->id;
	}
}

?>

<!-- ROOM SELECTION -->

<div id="vrchooseroomouterdiv">
	
	<span id="vrchooseroomsp"><?php echo JText::_('VRCHOOSEROOM'); ?></span>

	<div id="vrchooseroomdiv" class="vre-select-wrapper">
		<select class="vre-select" id="vrroomselect" name="room" onChange="roomSelectionChanged(this);">
			<?php
			/**
			 * The rooms in the dropdown are now displayed using
			 * the correct ordering.
			 *
			 * It is not possible to use always the ordering set for the rooms
			 * as the system needs to show first the rooms that offer non-shared tables.
			 * So, we should follow the ordering used by $availableRooms array.
			 *
			 * @since 1.8
			 */
			foreach ($availableRooms as $id_room)
			{
				// find room
				$room = array_filter($this->rooms, function($room) use ($id_room)
				{
					return $room->id == $id_room;
				});

				if ($room)
				{
					// take only first value
					$room = array_shift($room);

					// copy description into a tmp variable
					$description = $room->description;

					/**
					 * Properly render the contents also for the description
					 * of the other rooms in the list.
					 *
					 * @since 1.8.5
					 */
					$vik->onContentPrepare($description);
					$desc_lookup[$room->id] = $description->text;

					?>
					<option value="<?php echo $room->id; ?>" <?php echo ($room->id == $this->selectedRoom->id ? 'selected="selected"' : ''); ?>>
						<?php echo $room->name; ?>
					</option>
					<?php
				}
			}
			?>
		</select>
	</div>

</div>

<!-- ROOM DESCRIPTION -->

<div id="vrroomdescriptionactiondiv" style="<?php echo ($this->selectedRoom->description ? '' : 'display:none;'); ?>">
	<a id="vrroomdescriptionactionlink" onClick="changeRoomDescriptionDisplay(this);">
		<?php echo JText::_('VRSHOWDESCRIPTION'); ?>
	</a>
</div>

<?php
foreach ($desc_lookup as $room_id => $description)
{
	if ($description)
	{
		?>
		<div class="vrroomdescriptiondiv" id="vrroomdescriptiondiv<?php echo $room_id; ?>" style="display: none;">
			<?php echo $description; ?>
		</div>
		<?php
	}
}

JText::script('VRSHOWDESCRIPTION');
JText::script('VRHIDEDESCRIPTION');
?>

<script>

	var ROOMS_TABLES_LOOKUP = <?php echo json_encode($tables_lookup); ?>;
	
	function roomSelectionChanged(select) {
		<?php
		if ($config->getUint('reservationreq') == 0)
		{
			// reload page to display new map
			?>jQuery(select).closest('form').submit();<?php
		}
		else
		{
			// switch room description and auto-select
			// first available table of the picked room
			?>
			// get selected room ID
			var id_room = parseInt(jQuery(select).val());

			// update selected table with first one available
			SELECTED_TABLE = ROOMS_TABLES_LOOKUP.hasOwnProperty(id_room) ? ROOMS_TABLES_LOOKUP[id_room] : null;

			// get room description
			var desc = jQuery('#vrroomdescriptiondiv' + id_room);

			if (desc.length) {
				// show description details button
				jQuery('#vrroomdescriptionactiondiv').show();
			} else {
				// hide description details button
				jQuery('#vrroomdescriptionactiondiv').hide();
			}

			if (jQuery('.vrroomdescriptiondiv').is(':visible')) {
				jQuery('#vrroomdescriptionactionlink').trigger('click');
			}

			jQuery('.vrroomdescriptiondiv').not(desc).hide();
			<?php
		}
		?>
	}

	function changeRoomDescriptionDisplay(link) {
		// get selected room ID
		var id_room = parseInt(jQuery('#vrroomselect').val());
		// get room description
		var desc = jQuery('#vrroomdescriptiondiv' + id_room);

		if (desc.is(':visible') || desc.length == 0) {
			// hide description if visible
			jQuery(link).text(Joomla.JText._('VRSHOWDESCRIPTION'));
			desc.hide();
		} else {
			// otherwise show description
			jQuery(link).text(Joomla.JText._('VRHIDEDESCRIPTION'));
			desc.show();
		}
	}

</script>
