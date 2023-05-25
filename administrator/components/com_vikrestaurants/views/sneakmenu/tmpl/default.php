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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');

?>

<div class="vrmenuprevcont" style="padding: 10px;">

	<?php
	foreach ($this->sections as $s)
	{		
		$icon_type  = 1;
		$icon_name  = 'imagepreview';
		$icon_class = 'image ok';

		if (empty($s->image))
		{
			// image not uploaded
			$icon_type  = 2;
			$icon_name  = 'imageno';
			$icon_class = 'image no';
		}
		
		if (!file_exists(VREMEDIA . DIRECTORY_SEPARATOR . $s->image))
		{
			// image not found
			$icon_type  = 0;
			$icon_name  = 'imagenotfound';
			$icon_class = 'eye-slash no';
		}
		
		$img_title = JText::_('VRIMAGESTATUS' . $icon_type);

		?>
		<div class="vrmenuprevblock" id="vrsec<?php echo $s->id; ?>">
			<div class="vrmenuprevsection">
				<span class="vrmenuprevsectionname" onClick="changeSectionStatus(<?php echo $s->id; ?>);"><?php echo $s->name; ?></span>
				<span class="vrmenuprevsectionright">
					<span class="vrmenuprevsectionimg">
						<img src="<?php echo VREASSETS_ADMIN_URI . 'images/' . $icon_name . '.png'; ?>" title="<?php echo $img_title ?>" class="hidden-phone" />
						<i class="fas fa-<?php echo $icon_class; ?> mobile-only"></i>
					</span>

					<span class="vrmenuprevsectionpubl">
						<?php
						$title = '';

						if ($s->published)
						{
							$sfx = 'check-circle ok';
						}
						else if ($s->orderdishes)
						{
							$sfx   = 'minus-circle warn hasTooltip';
							$title = JText::_('VRMANAGEMENU34_DESC_SHORT');
						}
						else
						{
							$sfx = 'dots-circle no';
						}
						?>
						<i class="fas fa-<?php echo $sfx; ?> big hidden-phone" title="<?php echo $this->escape($title); ?>"></i>
						<i class="fas fa-<?php echo $sfx; ?> mobile-only"></i>
					</span>
				</span>
			</div>
			
			<?php
			if (count($s->products))
			{
				?>	
				<div class="vrmenuprevproducts" id="vrmenuprevproducts<?php echo $s->id; ?>">

					<?php
					foreach ($s->products as $p)
					{
						$icon_type  = 1;
						$icon_name  = 'imagepreview';
						$icon_class = 'image ok';

						if (empty($p->image))
						{
							// image not uploaded
							$icon_type  = 2;
							$icon_name  = 'imageno';
							$icon_class = 'image no';
						}
						
						if (!file_exists(VREMEDIA . DIRECTORY_SEPARATOR . $p->image))
						{
							// image not found
							$icon_type  = 0;
							$icon_name  = 'imagenotfound';
							$icon_class = 'eye-slash no';
						}
						
						$img_title = JText::_('VRIMAGESTATUS' . $icon_type);
						?>
						<div class="vrmenuprevprod" id="vrprod<?php echo $p->idAssoc; ?>">
							<span class="vrmenuprevprodname"><?php echo $p->name; ?></span>
							<span class="vrmenuprevprodprice">
								<?php
								if ($p->charge != 0)
								{
									?><del><?php echo VikRestaurants::printPriceCurrencySymb($p->price - $p->charge); ?></del><?php
								}
								?>
								<span><?php echo VikRestaurants::printPriceCurrencySymb($p->price); ?></span>
							</span>
							<span class="vrmenuprevprodright">
								<span class="vrmenuprevprodimg">
									<img src="<?php echo VREASSETS_ADMIN_URI . 'images/' . $icon_name . '.png'; ?>" title="<?php echo $img_title ?>" class="hidden-phone" />
									<i class="fas fa-<?php echo $icon_class; ?> mobile-only"></i>
								</span>

								<span class="vrmenuprevprodpubl">
									<i class="fas fa-<?php echo $p->published ? 'check-circle ok' : 'dot-circle no'; ?> big hidden-phone"></i>
									<i class="fas fa-<?php echo $p->published ? 'check-circle ok' : 'dot-circle no'; ?> mobile-only"></i>
								</span>
							</span>
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
		
<script>
	
	function changeSectionStatus(id) {
		if( jQuery('#vrmenuprevproducts' + id).is(':visible') ) {
			jQuery('#vrmenuprevproducts' + id).slideUp();
		} else {
			jQuery('#vrmenuprevproducts' + id).slideDown();
		}
	}
	
</script>
