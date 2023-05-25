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

// refresh working shifts every time the date changes
JHtml::_('vrehtml.sitescripts.datepicker', '#vrcalendar:input');
JHtml::_('vrehtml.sitescripts.animate');

$menus = $this->menus;

$desc_maximum_length = 180;

$vik = VREApplication::getInstance();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<div class="vrmenuslistform" id="vrmenuslistform">

	<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=menuslist' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post">
		
		<?php
		if ($this->showSearchForm)
		{
			?>
			<div class="vrmenusfieldsdiv">

				<div class="vrmenufielddatediv">
					<label for="vrcalendar"><?php echo JText::_('VRORDERDATETIME'); ?>:</label>
					
					<div class="vre-calendar-wrapper">
						<input type="text" name="date" value="<?php echo $this->filters['date']; ?>" id="vrcalendar" class="vre-calendar" />
					</div>
				</div>

				<button type="submit" class="vrmenufieldsubmit"><?php echo JText::_('VRMENUSEARCH'); ?></button>
			</div>
			<?php
		}
		?>
		
		<div class="vrmenuslistcont">
			
			<?php
			if (count($menus) == 0 && strlen($this->filters['date']))
			{
				?>
				<div class="vrmenusondatenoaverr">
					<?php echo JText::_('VRMENUSEARCHNOAVERR'); ?>
				</div>
				<?php
			}
			else if (count($menus))
			{
				// show menus blocks 
				foreach ($menus as $m)
				{	
					if (empty($m->image) || !is_file(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $m->image))
					{
						// use default image if not specified
						$m->image = 'menu_default_icon.jpg';   
					}
	
					// prepare description to properly interpret included plugins
					$vik->onContentPrepare($m->description);
					
					$desc = $m->description->text;

					// trim description if longer than the maximum limit
					if (strlen(strip_tags($desc)) > $desc_maximum_length)
					{
						$desc = mb_substr(strip_tags($desc), 0, $desc_maximum_length, 'UTF-8') . "...";
					}
					
					// fetch menu details URL
					$url = 'index.php?option=com_vikrestaurants&view=menudetails&id=' . $m->id;

					if ($this->showSearchForm)
					{
						$url .= '&date=' . $this->filters['date'];

						if ($this->filters['shift'])
						{
							$url .= '&shift=' . $this->filters['shift'];
						}
					}

					$url = JRoute::_($url, false);

					$ws = array_filter(explode(',', $m->working_shifts));
						
					?>
					<div class="vrmenublock">

						<div class="vrmenublock-menu">

							<div class="vrmenublockimage">
								<a href="<?php echo $url; ?>">
									<img src="<?php echo VREMEDIA_URI . $m->image; ?>" />
								</a>
							</div>

							<div class="vrmenublockname">
								<a href="<?php echo $url; ?>"><?php echo $m->name; ?></a>
							</div>

							<div class="vrmenublockdesc"><?php echo $desc; ?></div>

							<?php
							if ($ws)
							{
								?>
								<div class="vrmenublockshifts">
									<?php
									foreach ($ws as $s)
									{
										// get time of shift
										$tmp = JHtml::_('vikrestaurants.timeofshift', $s);

										if ($tmp->showlabel && $tmp->label)
										{
											$label = $tmp->label;
										}
										else
										{
											$label = $tmp->fromtime . ' - ' . $tmp->totime;
										}
										?>
										<span class="vrmenublockworksh">
											<span class="vrmenublockworkshname"><?php echo $label; ?></span>
											<span class="vrmenublockworkshtime"><?php echo $tmp->fromtime . ' - ' . $tmp->totime; ?></span>
										</span>
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
			?>

		</div>
			
		<input type="hidden" name="option" value="com_vikrestaurants" />
		<input type="hidden" name="view" value="menuslist" />
		
	</form>

</div>

<script>

	jQuery(document).ready(function() {
	
		jQuery('.vrmenublockworkshname').hover(function() {
			jQuery('.vrmenublockworkshname').removeClass('vrmenublockworkhighlight');
			jQuery('.vrmenublockworkshtime').removeClass('vrmenublockworkexploded');

			jQuery(this).addClass('vrmenublockworkhighlight');

			jQuery(this).siblings().each(function(){
				jQuery(this).addClass('vrmenublockworkexploded');
			});
		}, function(){
			jQuery('.vrmenublockworkshname').removeClass('vrmenublockworkhighlight');
			jQuery('.vrmenublockworkshtime').removeClass('vrmenublockworkexploded');
		});

	});

</script>
