<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_items
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$backcolor = $params->get('backcolor', '#666');
$textcolor = $params->get('textcolor', '#fff');

$pagination = $params->get('pagination', 0);
$navigation = $params->get('navigation', 1);
$autoplay   = $params->get('autoplay', 1);

JFactory::getDocument()->addStyleDeclaration(
<<<CSS
.vikre_item-price {
	background: $backcolor;
	color: $textcolor;
}
.vikre_item-title {
	color: $backcolor;
}
CSS
);

$currency = VREFactory::getCurrency();

/**
 * Use the module ID instead the module_id parameters, which
 * is no longer available within the module settings.
 *
 * @since 1.3.3
 */
$module_id = isset($module) && is_object($module) && property_exists($module, 'id') ? $module->id : rand(1, 999);
?>

<div class="vikre_item-container">
	<!-- <div> -->
		<div id="vremoditems-<?php echo $module_id; ?>" class="owl-carousel vikre_item-list">
			<?php
			for ($v = 0; $v < count($products); $v++)
			{
				$path = VREMEDIA . DIRECTORY_SEPARATOR . $products[$v]['image'];
				$uri  = VREMEDIA_URI . $products[$v]['image'];

				?>
				<div class="vikre_item-boxdiv">
					<?php
					// Image
					if ($params->get('image') && $products[$v]['image'] && @getimagesize($path))
					{ 
						?>
						<img src="<?php echo $uri; ?>" class="vikre_item-img" />
						<?php
					}

					// Top Field : Price
					if ($params->get('price'))
					{
						?>
						<span class="vikre_item-price"><?php echo $currency->format($products[$v]['price']); ?></span>
						<?php
					}
					?>

					<div class="vikre_item-inf">
						<div class="vikre_item-infdiv">
							<?php
							// Item Title
							if ($params->get('showtitle'))
							{
								?>
								<span class="vikre_item-title"><?php echo $products[$v]['name']; ?></span>
								<?php
							}

							// Item Description
							if ($params->get('desc'))
							{
								?>
								<span class="vikre_item-desc"><?php echo $products[$v]['description']; ?></span>
								<?php
							} 
							?>
						</div>
					</div>

				</div>
				<?php
			}
			?>
		</div>
	<!-- </div> -->
</div>

<?php
/**
 * The buttons named "next" and "prev" are now translatable.
 * 
 * @since 1.3.3
 */
JText::script('JPREV');
JText::script('JNEXT');
?>

<script>

	(function($) {
		'use strict';

		$(function() {
			$("#vremoditems-<?php echo $module_id; ?>").owlCarousel({
				items:           <?php echo $params->get('numb_itemrow', 4); ?>,
				autoPlay:        <?php echo $autoplay == 1 || $autoplay == 'true' ? 'true' : 'false'; ?>,
				autoplayTimeout: <?php echo (int) $params->get('autoplaytime', 5000); ?>,
				pagination:      <?php echo $pagination == 1 || $pagination == 'true' ? 'true' : 'false'; ?>,
				navigation:      <?php echo $navigation == 1 || $navigation == 'true' ? 'true' : 'false'; ?>,
				navigationText:  [Joomla.JText._('JPREV'), Joomla.JText._('JNEXT')],
				lazyLoad:        true,
			});
		});
	})(jQuery);

</script>