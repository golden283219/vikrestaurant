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
JHtml::_('vrehtml.assets.fancybox');
JHtml::_('vrehtml.assets.fontawesome');

$menu = $this->menu;

$all_sections_opt = new stdClass;
$all_sections_opt->id       = 0;
$all_sections_opt->name     = JText::_('VRMENUDETAILSALLSECTIONS');
$all_sections_opt->selected = true;

$sections = array($all_sections_opt);

foreach ($menu->sections as $s)
{
	if ($s->highlight)
	{
		$opt = new stdClass;
		$opt->id       = $s->id;
		$opt->name     = $s->name;
		$opt->selected = false;
		
		// copy section in head bar
		$sections[] = $opt;
	}
}

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$last_section_highlighted = -1;

$gallery = array();

?>

<div class="vrmenu-detailsmain">
	
	<?php
	if ($this->isPrintable)
	{
		?>
		<div class="vrmenu-print-btn">
			<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=menudetails&tmpl=component&id=' . $menu->id . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>" target="_blank">
				<i class="fas fa-print"></i>
			</a>
		</div>
		<?php
	}
	?>
	
	<div class="vrmenu-detailshead" >
		<h3><?php echo $menu->name; ?></h3>
		
		<div class="vrmenu-detailsheadsub">
			<?php
			if (!empty($menu->image))
			{
				$gallery[] = array(
					'caption' => $menu->name,
					'uri'     => VREMEDIA_URI . $menu->image,
					'thumb'   => VREMEDIA_SMALL_URI . $menu->image,
				);
				?>
				<div class="vrmenu-detailsheadsubimage">
					<a href="javascript: void(0);" onClick="vreOpenGallery(<?php echo count($gallery) - 1; ?>);" class="vremodal">
						<img src="<?php echo VREMEDIA_URI . $menu->image; ?>"/>
					</a>
				</div>
				<?php
			}

			if (!empty($menu->description))
			{
				?>
				<div class="vrmenu-detailsheadsubdesc">
					<?php
					// prepare description to properly interpret included plugins
					$vik->onContentPrepare($menu->description);

					echo $menu->description->text;
					?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	
	<?php
	/**
	 * Show section bar only in case there is at least
	 * an highlighted section, otherwise only the "ALL"
	 * option would be displayed.
	 *
	 * @since 1.8
	 */
	if (count($sections) > 1)
	{
		?>
		<div class="vrmenu-sectionsbar">
			<?php
			foreach ($sections as $s)
			{
				?>
				<span class="vrmenu-sectionsp">
					<a href="javascript: void(0);" class="vrmenu-sectionlink <?php echo ($s->selected ? 'vrmenu-sectionlight' : ''); ?>" onClick="vrFadeSection(<?php echo $s->id; ?>);" id="vrmenuseclink<?php echo $s->id; ?>">
						<?php echo $s->name; ?>
					</a>
				</span>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
	
	<div class="vrmenu-detailslist">
		
		<?php
		foreach ($menu->sections as $s)
		{
			if ($s->highlight)
			{
				$last_section_highlighted = $s->id;
			}
			?>
			
			<div class="vrmenu-detailssection <?php echo 'vrmenusubsection' . $last_section_highlighted; ?>" id="vrmenusection<?php echo $s->id; ?>">
				<h3><?php echo $s->name; ?></h3>

				<div class="vrmenu-detailssectionsub">
					<?php
					if (!empty($s->image))
					{
						$gallery[] = array(
							'caption' => $s->name,
							'uri'     => VREMEDIA_URI . $s->image,
							'thumb'   => VREMEDIA_SMALL_URI . $s->image,
						);
						?>
						<div class="vrmenu-detailssectionsubimage">
							<a href="javascript: void(0);" onClick="vreOpenGallery(<?php echo count($gallery) - 1; ?>);" class="vremodal">
								<img src="<?php echo VREMEDIA_URI . $s->image; ?>" />
							</a>
						</div>
						<?php
					}

					if (!empty($s->description))
					{
						?>
						<div class="vrmenu-detailssectionsubdesc"><?php echo $s->description; ?></div>
						<?php
					}
					?>
				</div>
				
				<?php
				if (count($s->products))
				{
					?>
					<div class="vrmenu-detailsprodlist">
						<?php
						foreach ($s->products as $p)
						{
							?>
							<div class="vrmenu-detailsprod">
								<div class="vrmenu-detailsprodsub">

									<div class="vrmenu-detailsprodsubleft">
										<?php
										if($p->image)
										{
											$gallery[] = array(
												'caption' => $p->name,
												'uri'     => VREMEDIA_URI . $p->image,
												'thumb'   => VREMEDIA_SMALL_URI . $p->image,
											);
											?>
											<div class="vrmenu-detailsprodsubimage">
												<a href="javascript: void(0);" onClick="vreOpenGallery(<?php echo count($gallery) - 1; ?>);" class="vremodal">
													<img src="<?php echo VREMEDIA_URI . $p->image; ?>" />
												</a>
											</div>
											<?php
										}
										?>
										<div class="vr-menudetailsprodsubnamedesc">
											<h3><?php echo $p->name; ?></h3>

											<?php
											if (!empty($p->description))
											{
												?>
												<div class="vrmenu-detailsprodsubdesc">
													<?php
													// prepare description to properly interpret included plugins
													$vik->onContentPrepare($p->description);

													echo $p->description->text;
													?>
												</div>
												<?php
											}
											?>
										</div>
									</div>

									<div class="vrmenu-detailsprodsubright">
										<?php
										if (count($p->options))
										{
											?>
											<div class="vrmenu-detailsprod-optionslist">
												<?php
												foreach ($p->options as $o)
												{
													?>
													<div class="vrmenu-detailsprod-option">
														<div class="option-name"><?php echo $o->name; ?></div>
														<?php
														if ($p->price + $o->price > 0)
														{
															?>
															<div class="option-price">
																<?php echo $currency->format($p->price + $o->price); ?>
															</div>
															<?php
														}
														?>
													</div>
													<?php
												}
												?>
											</div>
											<?php
										}
										else if ($p->price > 0)
										{
											?>
											<div class="vrmenu-detailsprodsubprice">
												<span class="vrmenu-detailsprodsubpricesp">
													<?php echo $currency->format($p->price); ?>
												</span>
											</div>
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
					<?php
				}
				?>
			</div>
			
			<?php
		}
		?>
	</div>
	 
</div>

<script>

	var GALLERY_DATA = [];

	jQuery(function() {
		var images = <?php echo json_encode($gallery); ?>;

		// prepare gallery data on load
		for (var i = 0; i < images.length; i++) {
			GALLERY_DATA.push({
				src:  images[i].uri,
				type: 'image',
				opts : {
					caption : images[i].caption,
					thumb   : images[i].thumb,
				},
			});
		}
	});

	function vreOpenGallery(index) {
		var instance = jQuery.fancybox.open(GALLERY_DATA);

		if (index > 0) {
			// jump to selected image ('0' turns off the animation)
			instance.jumpTo(index, 0);
		}
	}
	
	function vrFadeSection(id_section) {
		jQuery('.vrmenu-sectionlink').removeClass('vrmenu-sectionlight');
		
		jQuery('#vrmenuseclink' + id_section).addClass('vrmenu-sectionlight');
		
		if (id_section == 0) {
			jQuery('.vrmenu-detailssection').fadeIn('fast');
		} else {
			jQuery('.vrmenu-detailssection').hide();
			jQuery('#vrmenusection' + id_section).fadeIn('fast');
			jQuery('.vrmenusubsection' + id_section).fadeIn('fast');
		}
	}
	
</script>
