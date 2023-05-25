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
 * Template file used to display a product and its options.
 *
 * @since 1.8
 */

$item = $this->forItem;

$config = VREFactory::getConfig();

$currency = VREFactory::getCurrency();

// get maximum number of characters to display for the description
$max_desc_len = $config->getUint('tkproddesclength');

// save item description in a variable
$article = $item->description;

// prepare description to properly interpret included plugins
VREApplication::getInstance()->onContentPrepare($article);

/**
 * Checks whether the article supports an intro text.
 *
 * @since 1.8.3
 */
if (empty($article->introtext))
{
	// checks whether the plain text of the description
	// exceeds the maximum number of characters
	if (strlen(strip_tags($article->text)) > $max_desc_len)
	{
		// get only the first N characters of the plain text, in
		// order to avoid truncating HTML tags
		$article->introtext = mb_substr(strip_tags($article->text), 0, $max_desc_len, 'UTF-8') . '...';
	}
}

$use_overlay = $config->getUint('tkuseoverlay');

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');
?>
<div class="vrtksingleitemdiv">

	<div class="vrtkitemleftdiv">

		<?php
		// check if we should display the product image
		if ($item->image && $config->getBool('tkshowimages') && is_file(VREMEDIA . DIRECTORY_SEPARATOR . $item->image))
		{
			?>
			<div class="vrtkitemimagediv-outer">
				<div class="vrtkitemimagediv">
					<a href="javascript: void(0);" class="vremodal" onClick="vreOpenGallery(this);">
						<img src="<?php echo VREMEDIA_SMALL_URI . $item->image; ?>" data-caption="<?php echo $this->escape($item->name); ?>" data-menu="<?php echo $this->forMenu->id; ?>" data-prod="<?php echo $item->id; ?>" />
					</a>
				</div>
			</div>
			<?php
		}
		?>

		<div class="vrtkiteminfodiv">

			<div class="vrtkitemtitle">

				<span class="vrtkitemnamesp">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeawayitem&takeaway_item=' . $item->id . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo $item->name; ?>
					</a>
				</span>

				<?php
				if ($item->attributes)
				{
					?>
					<span class="vrtkitemattributes">
						<?php
						foreach ($item->attributes as $attr)
						{
							?>
							<img src="<?php echo VREMEDIA_SMALL_URI . $attr->icon; ?>" alt="<?php echo $attr->name; ?>" title="<?php echo $attr->name; ?>" />
							<?php
						}
						?>
					</span>
					<?php
				}
				?>

			</div>

			<div class="vrtkitemdescsp" id="vrtkitemshortdescsp<?php echo $item->id; ?>">
				<?php
				if ($article->introtext)
				{
					// use intro
					echo $article->introtext;

					?>
					<a href="javascript: void(0);" onClick="showMoreDesc(<?php echo $item->id ?>);">
						<strong><?php echo JText::_('VRTAKEAWAYMOREBUTTON'); ?></strong>
					</a>
					<?php
				}
				else
				{
					// use full text
					echo $article->text;
				}
				?>
			</div>

			<?php
			if ($article->introtext)
			{
				?>
				<div class="vrtkitemdescsp" id="vrtkitemlongdescsp<?php echo $item->id; ?>" style="display: none;">
					<?php echo $article->text; ?>
					<a href="javascript: void(0);" onClick="showLessDesc(<?php echo $item->id ?>);">
						<strong><?php echo JText::_('VRTAKEAWAYLESSBUTTON'); ?></strong>
					</a>
				</div>
				<?php
			}
			?>

		</div>

	</div>
	
	<div id="vrtkitemoptions<?php echo $item->id; ?>" class="vrtkitemvardiv">
		
		<?php
		// display layout with multiple variations
		if (count($item->options))
		{
			foreach ($item->options as $option)
			{
				// checks whether this variation is discounted
				$is_discounted = DealsHandler::isProductInDeals(array(
					'id_product' => $item->id,
					'id_option'  => $option->id,
					'quantity'   => 1,
				), $this->discountDeals);
				
				$price = $item->price + $option->price;

				// calculate new price in case of discount
				if ($is_discounted !== false)
				{
					if ($this->discountDeals[$is_discounted]['percentot'] == 1)
					{
						$price -= $price * $this->discountDeals[$is_discounted]['amount'] / 100.0;
					}
					else
					{
						$price -= $this->discountDeals[$is_discounted]['amount'];
					}
				}
				?>
				
				<div class="vrtksinglevar">

					<span class="vrtkvarnamesp"><?php echo $option->name; ?></span>

					<div class="vrtkvarfloatrdiv">

						<?php
						if ($is_discounted !== false)
						{
							?>
							<span class="vrtk-itemprice-stroke">
								<s><?php echo $currency->format($item->price + $option->price); ?></s>
							</span>
							<?php
						}
						?>

						<span class="vrtkvarpricesp">
							<?php echo $currency->format($price); ?>
						</span>

						<?php
						if ($this->forMenu->isActive)
						{
							?>
							<div class="vrtkvaraddbuttondiv">
								<?php
								if ($use_overlay == 2 || ($use_overlay == 1 && VikRestaurants::hasItemToppings($item->id, $option->id)))
								{
									?>
									<button type="button" class="vrtkvaraddbutton" onClick="vrOpenOverlay('vrnewitemoverlay', '<?php echo $this->escape(addslashes($item->name . ' - ' . $option->name)); ?>', <?php echo $item->id; ?>, <?php echo $option->id; ?>, -1);"><i class="fas fa-plus-square"></i></button>
									<?php
								}
								else
								{
									?>
									<button type="button" class="vrtkvaraddbutton" onClick="vrInsertTakeAwayItem(<?php echo $item->id; ?>, <?php echo $option->id; ?>);"><i class="fas fa-plus-square"></i></button>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>

				</div>
				
				<?php
			}
		
		}
		// display layout without variations
		else
		{
			// checks whether this product is discounted
			$is_discounted = DealsHandler::isProductInDeals(array(
				'id_product' => $item->id,
				'quantity'   => 1
			), $this->discountDeals);

			$price = $item->price;
			
			// calculate new price in case of discount
			if ($is_discounted !== false)
			{
				if ($this->discountDeals[$is_discounted]['percentot'] == 1)
				{
					$price -= $price * $this->discountDeals[$is_discounted]['amount'] / 100.0;
				}
				else
				{
					$price -= $this->discountDeals[$is_discounted]['amount'];
				}
			}
			?>
			
			<div class="vrtksinglevar">

				<span class="vrtkvarnamesp">&nbsp;</span>

				<div class="vrtkvarfloatrdiv">

					<?php
					if ($is_discounted !== false)
					{
						?>
						<span class="vrtk-itemprice-stroke">
							<s><?php echo $currency->format($item->price); ?></s>
						</span>
						<?php
					}
					?>

					<span class="vrtkvarpricesp">
						<?php echo $currency->format($price); ?>
					</span>

					<?php
					if ($this->forMenu->isActive)
					{
						?>
						<div class="vrtkvaraddbuttondiv">
							<?php
							if ($use_overlay == 2 || ($use_overlay == 1 && VikRestaurants::hasItemToppings($item->id)))
							{
								?>
								<button type="button" class="vrtkvaraddbutton" onClick="vrOpenOverlay('vrnewitemoverlay', '<?php echo $this->escape(addslashes($item->name)); ?>', <?php echo $item->id; ?>, 0, -1);"><i class="fas fa-plus-square"></i></button>
								<?php
							}
							else
							{
								?>
								<button type="button" class="vrtkvaraddbutton" onClick="vrInsertTakeAwayItem(<?php echo $item->id; ?>, 0);"><i class="fas fa-plus-square"></i></button>
								<?php
							}
							?>
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
