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
 * Template file used to display the suggested times
 * in case of no available tables.
 *
 * @since 1.8
 */

$config = VREFactory::getConfig();

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<div class="vrresultbookdiv vrfault">
	<span><?php echo JText::_('VRRESNOSINGTABLEFOUND'); ?></span>
</div>

<div class="vrhintsouterdiv">
	<?php
	// make sure we have at least a valid hint
	if (array_filter($this->hints))
	{
		// insert selected time within the middle of the hints
		array_splice($this->hints, floor(count($this->hints) / 2), 0, array($this->checkinTime));
		?>
		<div class="vrresultbooktrydiv">
			<?php echo JText::_('VRRESTRYHINTS'); ?>
		</div>
	
		<div class="vrresulttruehintsdiv">
			<?php
			// fetch base URL to try a different time
			$base_url = 'index.php?option=com_vikrestaurants&view=search&date=' . $this->args['date'] . '&people=' . $this->args['people'];

			if ($itemid)
			{
				$base_url .= '&Itemid=' . $itemid;
			}

			// iterate hints
			foreach ($this->hints as $hint)
			{
				// make sure we have a valid hint
				if ($hint)
				{
					// compare hint timestamp with checkin
					if ($hint->ts != $this->checkinTime->ts)
					{
						// display clickable hint
						?>
						<div class="vrresulthintsdiv">
							<a href="<?php echo JRoute::_($base_url . '&hourmin=' . $hint->hour . ':' . $hint->min); ?>" class="vrresulthintsbutton">
								<?php echo $hint->format; ?>
							</a>
						</div>
						<?php
					}
					else
					{
						// display selected time slot
						?>
						<div class="vrresultdisabledhintsdiv">
							<span class="vrresulthintsdisabledbutton">
								<?php echo $hint->format; ?>
							</span>
						</div>
						<?php
					}
				}
			}
			?>
		</div>
		<?php 
	}
	else
	{
		// no available hints
		?>
		<div class="vrresultfalsehintdiv">
			<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=restaurants' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
				<?php echo JText::_('VRRESNOTABLESSELECTNEWDATES'); ?>
			</a>
		</div>
		<?php
	}
?>
</div>
