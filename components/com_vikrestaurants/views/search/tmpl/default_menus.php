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
 * Template file used to display the menus selection.
 *
 * @since 1.8
 */

$currency = VREFactory::getCurrency();

?>

<div class="vrsearchmenucont">

	<div class="vrsearchmenutitle"><?php echo JText::_('VRSEARCHCHOOSEMENU'); ?></div>

	<div class="vrsearchmenulist">
		<?php
		foreach ($this->menus as $menu)
		{ 	
			if (empty($m['image']) || !is_file(VREMEDIA . DIRECTORY_SEPARATOR . $menu->image))
			{
				// use default image in case it is missing
				$menu->image = 'menu_default_icon.jpg';   
			}
			
			// generate URL to visit menu details
			$url = JRoute::_('index.php?option=com_vikrestaurants&view=menudetails&id=' . $menu->id);
			?>
			<div class="vrsearchmenudetails">

				<div class="vrsearchmenuinnerdetails">

					<div class="vrsearchmenuimage">
						<a href="<?php echo $url; ?>" target="_blank">
							<img src="<?php echo VREMEDIA_URI . $menu->image; ?>" alt="<?php echo $menu->name; ?>" />
						</a>
					</div>

					<?php
					if ($menu->freechoose)
					{
						// layout with multi-choice
						?>
						<div class="vrsearchmenuname">
							<span class="menu-title"><?php echo $menu->name; ?></span>

							<?php
							if ($menu->cost > 0)
							{
								?>
								<span class="menu-cost"><?php echo $currency->format($menu->cost); ?></span>
								<?php
							}
							?>
						</div>

						<div class="vrsearchmenufoot" data-id="<?php echo $menu->id; ?>">
							<span class="vrsearchmenufootleft selected-quantity">0</span>

							<span class="vrsearchmenufootright">
								<a href="javascript: void(0);" class="vrsearchmenuaddlink">
									<i class="fas fa-plus-square"></i>
								</a>

								<a href="javascript: void(0);" class="vrsearchmenudellink vrsearchlinkdisabled">
									<i class="fas fa-minus-square"></i>
								</a>
							</span>
						</div>
						<?php
					}
					else
					{
						// layout with single choice
						?>
						<div class="vrsearchmenuname">
							<span class="menu-title">
								<label for="menu_radio_sel_<?php echo $menu->id; ?>"><?php echo $menu->name; ?></label>
							</span>

							<span class="menu-radio-sel">
								<input type="radio" name="menus_radio_selection" id="menu_radio_sel_<?php echo $menu->id; ?>" value="<?php echo $menu->id; ?>" />
							</span>
						</div>

						<?php
						if ($menu->cost > 0)
						{
							?>
							<div class="menu-cost-sub"><?php echo $currency->format($menu->cost); ?></div>
							<?php
						}
					}
					?>

				</div>

			</div>
			<?php 
		}
		?>
	</div>

</div>

<div class="vryourmenusdiv">
	<span id="vrbookmenuselsp">
		<?php echo JText::sprintf('VRSEARCHCHOOSEMENUSTATUS', '0/' . $this->args['people']); ?>
	</span>
</div>

<?php
JText::script('VRSEARCHCHOOSEMENUSTATUS');
?>

<script>

	jQuery(document).ready(function() {

		// add menu event
		jQuery('.vrsearchmenuaddlink').on('click', function() {
			var selected = getTotalSelectedMenus();
			var total    = <?php echo $this->args['people']; ?>;

			var id_menu = jQuery(this).closest('.vrsearchmenufoot[data-id]').data('id');

			// add menu only if not reached the total
			if (selected < total) {
				var quantity = 1;

				// check if the menu was already added
				var menu = jQuery('#vrconfirmform input[name="menus[' + id_menu + ']"]');

				if (menu.length) {
					// update existing record
					quantity += parseInt(menu.val());
					menu.val(quantity);
				} else {
					// insert new record
					jQuery('#vrconfirmform').append(
						'<input type="hidden" name="menus[' + id_menu + ']" value="1" />'
					);
				}

				selected++;

				// update texts
				updateMenuStatus(id_menu, quantity, selected);

				// check if we reached the total
				if (selected == total) {
					// mark total count as OK
					jQuery('#vrbookmenuselsp').addClass('vrbookmenuokpeople');
					// disable any ADD button
					jQuery('.vrsearchmenuaddlink').addClass('vrsearchlinkdisabled');
					// scroll to continue button
					jQuery('html,body').animate( {scrollTop: (jQuery('#vrbookcontinuebutton').offset().top - 5)}, {duration:'slow'} );
				}

				// enable delete button
				jQuery('.vrsearchmenufoot[data-id="' + id_menu + '"]')
					.find('.vrsearchmenudellink')
						.removeClass('vrsearchlinkdisabled');
			}
		});

		// remove menu event
		jQuery('.vrsearchmenudellink').on('click', function() {
			var selected = getTotalSelectedMenus();
			var id_menu  = jQuery(this).closest('.vrsearchmenufoot[data-id]').data('id');

			// get menu box
			var menu = jQuery('#vrconfirmform input[name="menus[' + id_menu + ']"]');

			// get menu selected quantity
			var quantity = menu.length ? parseInt(menu.val()) : 0;

			if (selected > 0 && quantity > 0) {
				quantity--;

				if (quantity > 0) {
					// update existing record
					menu.val(quantity);
				} else {
					// delete record
					menu.remove();

					// disable remove link
					jQuery(this).addClass('vrsearchlinkdisabled');
				}

				selected--;

				// update texts
				updateMenuStatus(id_menu, quantity, selected);

				// mark total count as NOT OK
				jQuery('#vrbookmenuselsp').removeClass('vrbookmenuokpeople');

				// enable add buttons
				jQuery('.vrsearchmenuaddlink')
					.removeClass('vrsearchlinkdisabled');
			}
		});

		jQuery('input[name="menus_radio_selection"]').on('change', function() {
			var id_menu = parseInt(jQuery(this).val());
			var total   = <?php echo $this->args['people']; ?>;

			// remove all the selected menus
			jQuery('#vrconfirmform input[name^="menus["]').remove();
			// add new menu for all guests
			jQuery('#vrconfirmform').append(
				'<input type="hidden" name="menus[' + id_menu + ']" value="' + total + '" />'
			);

			updateMenuStatus(id_menu, total, total);
		});

	});

	function getTotalSelectedMenus() {
		var total = 0;

		jQuery('#vrconfirmform input[name^="menus["]').each(function() {
			total += parseInt(jQuery(this).val());
		});

		return total;
	}
	
	function updateMenuStatus(id_menu, quantity, selected) {
		// update menu quantity
		jQuery('.vrsearchmenufoot[data-id="' + id_menu + '"]')
			.find('.selected-quantity')
				.html(quantity);

		// update total quantity
		var text = Joomla.JText._('VRSEARCHCHOOSEMENUSTATUS');
		text = text.replace(/%s/, selected + '/<?php echo $this->args['people']; ?>');

		jQuery('#vrbookmenuselsp').html(text);
	}

	function validateMenus() {
		var selected = getTotalSelectedMenus();
		var total    = <?php echo $this->args['people']; ?>;

		return selected == total;
	}

</script>
