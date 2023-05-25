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

JHtml::_('vrehtml.sitescripts.animate');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.toast', 'bottom-center');

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<div class="vre-order-dishes-wrapper">

	<?php
	if (count($this->menus) > 1)
	{
		// display a list of filters to show only per menu per time
		?>
		<div class="vre-order-dishes-menu-selection">

			<?php
			foreach ($this->menus as $menu)
			{
				?>
				<div class="vre-order-dishes-menu-picker" data-id="<?php echo $menu->id; ?>">

					<?php
					if ($menu->image)
					{
						?>
						<div class="order-menu-image">
							<img src="<?php echo VREMEDIA_URI . $menu->image; ?>" />
						</div>
						<?php
					}
					?>

					<div class="order-menu-title">
						<?php echo $menu->name; ?>
					</div>

				</div>
				<?php
			}
			?>

		</div>
		<?php
	}
	?>

	<div class="vre-order-dishes-menus">
		<?php
		foreach ($this->menus as $menu)
		{
			// leave menu hidden in case there is more than one menu available
			?>
			<div class="vre-order-dishes-menu-wrapper" data-id="<?php echo $menu->id; ?>" style="<?php echo count($this->menus) > 1 ? 'display:none;' : ''; ?>">
				<?php
				// assign menu for being used in a sub-template
				$this->foreachMenu = $menu;
				
				// display menu block
				echo $this->loadTemplate('menu');
				?>
			</div>
			<?php
		}
		?>
	</div>

</div>

<?php
// load floating cart 
echo $this->loadTemplate('cart');

// load HTML and scripts to handle the dishes overlay
echo $this->loadTemplate('overlay');

// load payment overlay
echo $this->loadTemplate('payment');
?>

<script>

	var SELECTED_MENU_ID     = 0;
	var STOP_SCROLLING_EVENT = false;

	jQuery(document).ready(function() {

		jQuery('.vre-order-dishes-menu-picker').on('click', function() {
			// remove active class
			jQuery('.vre-order-dishes-menu-picker').removeClass('active');
			// hide all menus
			jQuery('.vre-order-dishes-menu-wrapper').hide();
			// get ID of the selected menu
			var id = jQuery(this).addClass('active').data('id');
			// show selected menu block
			var box = jQuery('.vre-order-dishes-menu-wrapper[data-id="' + id + '"]').show();

			SELECTED_MENU_ID = id;

			// auto-scroll to menu
			jQuery('html, body').animate({ scrollTop: box.offset().top - 30 });

			// store select menu in a cookie for the whole session
			document.cookie = 'vre.orderdishes.menu=' + id + '; path=/'; 
		});

		<?php
		$cookie = JFactory::getApplication()->input->cookie;

		// get last selected menu from cookie
		$id_menu = $cookie->getUint('vre_orderdishes_menu');

		if ($id_menu)
		{
			?>
			// auto-click menu
			jQuery('.vre-order-dishes-menu-picker[data-id="<?php echo $id_menu; ?>"]').trigger('click');

			SELECTED_MENU_ID = <?php echo (int) $id_menu; ?>;
			<?php
		}

		if ($this->canOrder)
		{
			?>
			jQuery('.vre-order-dishes-product.clickable').on('click', function() {
				// get ID of the clicked product
				var id = jQuery(this).data('id');

				// show popup to insert the product
				vrOpenDishOverlay(id);
			});
			<?php
		}
		?>

		// callback used to find the closest section according
		// to the current scroll position
		var sectionScrollHandle = function(elem) {
			// check if the current scroll top (minus a fixed threshold) is higher
			// than the top offset of the specified sections
			return jQuery(window).scrollTop() + 100 > jQuery(elem).offset().top;
		};

		// register scroll event only once the document is ready
		onDocumentReady().then((data) => {
			// auto-select the closest section while scrolling the page
			jQuery(window).on('scroll', function() {
				if (!SELECTED_MENU_ID || STOP_SCROLLING_EVENT) {
					return false;
				}

				// get sections in reverse ordering to always start from the last one
				var sections = jQuery('.vre-order-dishes-menu-wrapper[data-id="' + SELECTED_MENU_ID + '"]')
					.find('.vre-order-dishes-section.can-highlight')
						.get().reverse();

				if (sections.length == 0) {
					return false;
				}

				var found = false;

				jQuery(sections).each(function() {
					// reverse ordering to always start from the last one
					if (sectionScrollHandle(this)) {
						// highlight new section
						vrHighlightSection(jQuery(this).data('id'));

						found = true;

						// abort after finding the closest section
						return false;
					}
				});

				if (!found) {
					// highlight the first section available, because the page
					// scroll is probably lower than the offset of the first section
					vrHighlightSection(jQuery(sections.pop()).data('id'));
				}
			});
		});

	});

	function vrHighlightSection(id_section) {
		var section = jQuery('#vrmenuseclink' + id_section);

		// proceed only in case the section is not highlighted yet
		if (!section.hasClass('vrmenu-sectionlight')) {
			// remove highlight from all sections
			jQuery('.vrmenu-sectionlink').removeClass('vrmenu-sectionlight');
			// highlight selected section only
			section.addClass('vrmenu-sectionlight');

			var bar = jQuery('.vre-order-dishes-menu-wrapper[data-id="' + SELECTED_MENU_ID + '"]')
				.find('.vrmenu-sectionsbar');

			// calculate real offset by ignoring the offset of the wrapper
			var offset = section.offset().left - section.offsetParent().offset().left;

			if (offset < -10 || offset > 10) {
				// animate only if the section is not already displayed within the first 10px
				bar.stop(true, true).animate({
					scrollLeft: bar.scrollLeft() + offset,
				});
			}
		}
	}

	function vrFadeSection(id_section) {
		// highlight section
		vrHighlightSection(id_section);

		var sectionsHeight = 0;

		jQuery('.vrmenu-sectionsbar').each(function() {
			if (sectionsHeight < jQuery(this).outerHeight()) {
				sectionsHeight = jQuery(this).outerHeight();
			}
		});

		// temporarily stop scrolling event
		STOP_SCROLLING_EVENT = true;

		jQuery('html, body').stop(true, true).animate({
			scrollTop: jQuery('#vrmenusection' + id_section).offset().top - (20 + sectionsHeight),
		}).promise().done(function() {
			// restart scrolling event once the animation is completed,
			// so that the sections bar won't face any flashes
			STOP_SCROLLING_EVENT = false;
		});
	}

</script>
