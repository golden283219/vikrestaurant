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
 * Template file used to display the tables map.
 *
 * @since 1.8
 */

$room_has_shared_table = (bool) array_filter($this->selectedRoom->tables, function($t)
{
	// return true if the table is shared and available
	return $t->multi_res && $t->available;
});

if ($room_has_shared_table)
{
	// shows shared table legend
	?>
	<div id="vrlegendsharedtablediv">
		<?php echo JText::_('VRLEGENDSHAREDTABLE'); ?>
	</div>
	<?php
}

/**
 * Display new map layout using SVG factory.
 *
 * @since 1.7.4
 */
?>
	
<div id="vre-tables-map" class="vre-map-svg-wrapper">
	<?php
	VRELoader::import('library.map.factory');

	$options = array();
	$options['callback'] = 'selectTable';
	
	echo VREMapFactory::getInstance($options)
		->setRoom($this->selectedRoom)
		->setTables($this->selectedRoom->tables)
		->build();
	?>
</div>

<div class="vryourtablediv">
	<span id="vrbooknoselsp" style="display: none;"></span>
	<span id="vrbooktabselsp" style="display: none;"></span>
</div>

<?php
JText::script('VRYOURTABLESEL');
?>

<script>

	function selectTable(id, tableName, tableAvailable) {
		if (tableAvailable == 1) {
			// check if a table was already selected
			var wasSelected = SELECTED_TABLE ? true : false;

			SELECTED_TABLE = id;
	
			jQuery('#vrbooknoselsp').hide();
	
			jQuery('#vrbooktabselsp').text(Joomla.JText._('VRYOURTABLESEL').replace('%s', tableName));
			jQuery('#vrbooktabselsp').fadeIn('normal');
			// jQuery('#vrbooktabselsp').show();

			if (!wasSelected) {
				// animate only for the first time
				jQuery('html,body').animate({scrollTop: (jQuery('#vrbookcontinuebutton').offset().top - 300)}, {duration: 'slow'});
			}
		}
	}

</script>
