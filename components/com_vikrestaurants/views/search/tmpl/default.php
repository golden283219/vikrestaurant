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

JHtml::_('vrehtml.assets.fontawesome');

$config = VREFactory::getConfig();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

if ($this->step == 0 && $config->getUint('reservationreq') == 2)
{
	// automatically increase step in case of none selection
	$this->step = 1;
}

if ($this->step == 0)
{
	// animate to search form only for the first step, otherwise
	// the system could face a conflict with a different animation
	JHtml::_('vrehtml.sitescripts.animate');
}

// display step bar using the view sub-template
echo $this->loadTemplate('stepbar');
?>

<div class="vrreservationform" id="vrresultform">

	<!-- display reservation summary -->

	<?php
	// display summary using the view sub-template
	echo $this->loadTemplate('summary');
	?>

	<!-- display hints in case of failure -->

	<?php
	if ($this->attempt == 3)
	{	
		// display hints using the sub-template
		echo $this->loadTemplate('hints');
	}
	else
	{
		?>
		<div id="vrbookingborderdiv" class="vrbookingouterdiv">
			<?php
			if ($this->step == 0)
			{
				?>
				<div class="vrresultbookdiv vrsuccess" id="vrsearchsuccessdiv">
					<?php
					echo JText::sprintf('VRSUCCESSMESSSEARCH', $this->args['people']);

					switch ($config->getUint('reservationreq'))
					{
						case 0:
							// choose table and room
							echo ' ' . JText::_('VRMESSNOWCHOOSETABLE');
							break;

						case 1: 
							// choose room only
							echo ' ' . JText::_('VRMESSNOWCHOOSEROOM');
							break;
					}
					?>
				</div>

				<div class="vrbookcontinuebuttoncont">
					<button type="button" class="vrresultbookbuttonfind" onClick="showRoomTable(this);">
						<?php echo JText::_('VRCONTINUEBUTTON' . $config->getUint('reservationreq')); ?>
					</button>
				</div>
				<?php
			}
			?>

			<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=search' . ($itemid ? '&Itemid=' . $itemid : '')); ?>"  id="vrresform" name="vrresform" method="post">

				<div id="table-booking-wrapper" style="<?php echo $this->step == 0 ? 'display:none;' : ''; ?>">
					<?php
					if ($config->getUint('reservationreq') != 2)
					{
						// display room details in case it is possible to 
						// choose the table and/or the room
						echo $this->loadTemplate('room');
					}

					if ($config->getUint('reservationreq') == 0)
					{
						// display tables map in case it is possible to
						// choose the table
						echo $this->loadTemplate('tables');
					}

					if (count($this->menus))
					{
						// display menus box in case the customers can
						// choose the menus for their reservations
						?>
						<div id="menu-selection-wrapper" style="display:none;">
							<?php echo $this->loadTemplate('menus'); ?>
						</div>
						<?php
					}
					?>

					<div class="vrbookcontinuebuttoncont">
						<button type="button" id="vrbookcontinuebutton" class="vrresultbookbuttoncontinue" onClick="continueBooking();">
							<?php echo JText::_('VRCONTINUE'); ?>
						</button>
					</div>
				</div>

				<input type="hidden" name="date" value="<?php echo $this->args['date']; ?>" />
				<input type="hidden" name="hourmin" value="<?php echo $this->args['hourmin']; ?>" />
				<input type="hidden" name="people" value="<?php echo $this->args['people']; ?>" />
				<input type="hidden" name="family" value="<?php echo JFactory::getApplication()->getUserState('vre.search.family', 0); ?>" />

				<input type="hidden" name="option" value="com_vikrestaurants" />
				<input type="hidden" name="view" value="search" />

			</form>
		</div>
		<?php
	}
	?>

</div>

<?php
if ($this->attempt != 3)
{
	?>
	<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=confirmres' . ($itemid ? '&Itemid=' . $itemid : '')); ?>"  id="vrconfirmform" name="vrconfirmform" method="post">
					
		<input type="hidden" name="date" value="<?php echo $this->args['date']; ?>" />
		<input type="hidden" name="hourmin" value="<?php echo $this->args['hourmin']; ?>" />
		<input type="hidden" name="people" value="<?php echo $this->args['people']; ?>" />
		<input type="hidden" name="table" value="" />
			
		<input type="hidden" name="option" value="com_vikrestaurants" />
		<input type="hidden" name="view" value="confirmres" />
		
	</form>
	<?php
}
?>

<?php
JText::script('VRERRCHOOSETABLEFIRST');
?>

<script>

	var BOOKING_STEP = <?php echo (int) $this->step; ?>;

	var SELECTED_TABLE = null;

	jQuery(document).ready(function() {
		<?php
		if ($this->attempt != 3 && $config->getUint('reservationreq') != 0)
		{
			$first_avail_table = 0;

			// iterate all tables in room
			for ($i = 0; $i < count($this->selectedRoom->tables) && !$first_avail_table; $i++)
			{
				// check if the table is available
				if ($this->selectedRoom->tables[$i]->available)
				{
					// assign ID of first table available and exit
					$first_avail_table = $this->selectedRoom->tables[$i]->id;
				}
			}
			?>
			// auto-select first available table of the selected room
			SELECTED_TABLE = <?php echo $first_avail_table; ?>;
			<?php
		}

		if ($this->step == 1)
		{
			// auto-scroll page to room/table selection
			?>jQuery('html,body').animate( {scrollTop: (jQuery('#table-booking-wrapper').offset().top - 20)}, {duration:'slow'} );<?php
		}
		?>
	});
	
	function showRoomTable(button) {
		// hide button
		jQuery(button).parent().hide();
		// show table booking box
		jQuery('#table-booking-wrapper').show();

		jQuery('html,body').animate( {scrollTop: (jQuery('#table-booking-wrapper').offset().top - 20)}, {duration:'slow'} );

		BOOKING_STEP++;
	}

	function continueBooking() {
		if (!SELECTED_TABLE) {
			// table selection is mandatory
			jQuery('#vrbooknoselsp').text(Joomla.JText._('VRERRCHOOSETABLEFIRST'));
			jQuery('#vrbooknoselsp').fadeIn('normal').delay(2000).fadeOut('normal');
			return false;
		}

		// get confirmation form
		var form = jQuery('form#vrconfirmform');

		// update table input
		form.find('input[name="table"]').val(SELECTED_TABLE);

		// check if the customers are allowed to select a menu
		var isMenuSelection = <?php echo $this->menus ? 1 : 0; ?>;

		if (BOOKING_STEP == 1 && isMenuSelection) {
			// increase booking step
			BOOKING_STEP++;

			// show menus selection box
			jQuery('#menu-selection-wrapper').show();

			// scroll down to menus list
			jQuery('html,body').animate( {scrollTop: (jQuery('#menu-selection-wrapper').offset().top - 20)}, {duration:'slow'} );

			// do not go ahead
			return false;
		}

		if (BOOKING_STEP == 2 && !validateMenus()) {
			// missing menus selection
			jQuery('#vrbookmenuselsp')
				.addClass('vrbookmenunopeople')
					.delay(2000)
						.queue(function(next){
							jQuery(this).removeClass('vrbookmenunopeople');
							next();
						});

			return false;
		}

		// submit form to confirmation page
		form.submit();
	}

</script>
