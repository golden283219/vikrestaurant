<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_grid
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$is_price = (bool) $params->get('price');
$is_image = (bool) $params->get('image');
$is_menu  = (bool) $params->get('menu');
$is_attr  = (bool) $params->get('attributes');

$is_rating = (int) $params->get('rating');

$itemid = (int) $params->get('itemid');

$items_per_row = (int) $params->get('numitems');
$items_per_row = max(array(1, $items_per_row));
$items_per_row = min(array($items_per_row, count($products)));

$is_menu_filtering = (bool) $params->get('filtermenu');
$menu_filter_all   = $is_menu_filtering && (bool) $params->get('filtermenuall');

$currency = VREFactory::getCurrency();

?>

<div class="vrtk-grid-wrapper">

	<!-- AVAILABLE TAGS -->

	<?php
	if ($is_menu_filtering && count($menus) > 1)
	{
		?>

		<div class="vrtk-grid-top" id="vrkt-grid-filter">

			<?php
			/**
			 * Display "See All Menus" tab only if specified through
			 * the configuration of the module.
			 *
			 * @since 1.4
			 */
			if ($menu_filter_all)
			{
				?>
				<li>
					<a href="javascript: void(0);" data-group="all" class="active">
						<?php echo JText::_('VRTK_GRID_ALL_MENUS'); ?>
					</a>
				</li>
				<?php
			}

			// get first available menu
			$fm  = reset($menus);
			$fmk = key($menus);

			foreach ($menus as $i => $menu)
			{
				/**
				 * Auto-activate first menu available in case the
				 * "See All" section is turned off.
				 *
				 * @since 1.4
				 */
				$class = !$menu_filter_all && $menu === $fm ? 'active' : '';
				?>
				<li>
					<a href="javascript: void(0);" data-group="<?php echo $menu->id; ?>" class="<?php echo $class; ?>">
						<?php echo $menu->title; ?>
					</a>
				</li>
				<?php
			}
			?>

		</div>

		<?php
	}
	?>

	<!-- GRID LAYOUT -->

	<div class="vrtk-grid-layout" id="vrtk-grid-layout">

		<?php
		foreach ($products as $prod)
		{
			/**
			 * Display product only in case the "See All" section is enabled
			 * or in case the product belongs to the first available menu.
			 * Display also in case the menu filtering is disabled.
			 *
			 * @since 1.4
			 */
			$visible = !$is_menu_filtering || $menu_filter_all || $prod->idMenu == $fmk ? '' : 'display:none;';
			?>
			<div class="vrtk-grid-product" data-groups='<?php echo json_encode(array($prod->idMenu)); ?>' style="width: calc(100% / <?php echo $items_per_row; ?> - 10px);<?php echo $visible; ?>">
				
				<div class="vrtk-grid-product-inner">

					<div class="vrtk-grid-product-boxdiv">

						<div class="product-top">

							<?php
							if ($is_image && !empty($prod->entryImage))
							{
								?>
								<div class="product-image focal-point up-2">
									<div class="product-image-inner">
										<a href="<?php echo JRoute::_("index.php?option=com_vikrestaurants&view=takeawayitem&takeaway_item={$prod->idEntry}&id_option={$prod->idOption}&Itemid=$itemid"); ?>">
											<img src="<?php echo VREMEDIA_URI . $prod->entryImage; ?>" alt="<?php echo $prod->fullName; ?>" />
										</a>
									</div>
								</div>
								<?php
							}
							?>

							<?php
							/**
							 * Proceed only in case the product owns a valid property.
							 * This should solve misconfiguration errors between the module
							 * and the component.
							 *
							 * @since 1.3.1
							 */
							if ($prod->reviewsRatio && ($is_rating == 2 || ($is_rating == 1 && $prod->reviewsRatio->count)))
							{
								?>
								<div class="product-review">
									<?php
									/**
									 * Displays the rating stars.
									 * It is possible to change the $image argument to false
									 * to use FontAwesome 4 instead of the images.
									 * For FontAwesome 5, $image have to be set to "5.0".
									 *
									 * @since 1.4
									 */
									echo JHtml::_('vikrestaurants.rating', $prod->reviewsRatio->halfRating, $image = true);
									?>
								</div>
								<?php
							}
							?>

						</div>

						<div class="product-details">
							<div class="product-details-name"><?php echo $prod->fullName; ?></div>

							<?php
							if ($is_menu)
							{
								?>
								<div class="product-details-menu"><?php echo $prod->menuTitle; ?></div>
								<?php
							}
							?>
						</div>

						<div class="product-bottom">

							<?php
							if ($is_attr)
							{
								?>
								<div class="product-bottom-attributes">
									<?php
									foreach ($prod->attributes as $attr)
									{
										?>
										<img src="<?php echo VREMEDIA_URI . $attr->icon; ?>" alt="<?php echo $attr->name; ?>" title="<?php echo $attr->name; ?>" />
										<?php
									}
									?>
								</div>
								<?php
							}
							
							if ($is_price && $prod->fullPrice > 0)
							{
								?>
								<div class="product-bottom-addcart">
									<a href="<?php echo JRoute::_("index.php?option=com_vikrestaurants&view=takeawayitem&takeaway_item={$prod->idEntry}&id_option={$prod->idOption}&Itemid=$itemid"); ?>">
										<span class="product-addcart-text">+</span>
										<span class="product-cost"><?php echo $currency->format($prod->fullPrice); ?></span>
									</a>
								</div>
								<?php
							}
							?>

						</div>
						
					</div>

				</div>

			</div>

			<?php
		}
		?>

	</div>

</div>

<script type="text/javascript">
	
	jQuery(document).ready(function() {

		jQuery('#vrkt-grid-filter a').on('click', function(e) {
			e.preventDefault();

			if (jQuery(this).hasClass('active')) {
				return;
			}

			jQuery('#vrkt-grid-filter a').removeClass('active');
			jQuery(this).addClass('active');

			var group = jQuery(this).data('group');

			jQuery('#vrtk-grid-layout .vrtk-grid-product').each(function() {
				var groups = jQuery(this).data('groups');

				if (group == "all" || groups == group || groups.indexOf(group) != -1) {
					jQuery(this).fadeIn();
				} else {
					jQuery(this).hide();
				}
			});
		});

	});

</script>
