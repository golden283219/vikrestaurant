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
 * Layout variables
 * -----------------
 * @var  VREOperatorUser  $operator  The operator instance.
 */
extract($displayData);

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'int');

?>

<div class="vr-livemap-rcont">

	<div class="vr-livemap-rbox">
		<div class="vr-livemap-rtitle">
			<a href="javascript: void(0);">
				<?php echo $operator->get('firstname') . ' ' . $operator->get('lastname'); ?>
			</a>
		</div>
	</div>
	
	<div class="vr-livemap-modal" style="display: none;">

		<ul>
			<?php
			if ($operator->isRestaurantAllowed())
			{
				?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo JText::_('VROVERSIGHTMENUITEM1'); ?>
					</a>
				</li>

				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opdashboard' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo JText::_('VROVERSIGHTMENUITEM2'); ?>
					</a>
				</li>

				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opreservations' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo JText::_('VROVERSIGHTMENUITEM3'); ?>
					</a>
				</li>

				<li class="separator">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opkitchen' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo JText::_('VROVERSIGHTMENUITEM6'); ?>
					</a>
				</li>
				<?php
			}

			if ($operator->isTakeawayAllowed())
			{
				?>
				<li class="separator">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=oversight&group=2' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo JText::_('VROVERSIGHTMENUITEM5'); ?>
					</a>
				</li>
				<?php
			}

			if ($operator->canRead('coupon'))
			{
				?>
				<li class="separator">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opcoupons' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
						<?php echo JText::_('VROVERSIGHTMENUITEM4'); ?>
					</a>
				</li>
				<?php
			}
			?>
			
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=oversight.logout' . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
					<?php echo JText::_('VRLOGOUT'); ?>
				</a>
			</li>
		</ul>

	</div>

</div>
		
<script>

	jQuery(document).ready(function() {

		jQuery('html').click(function() {
			jQuery('.vr-livemap-modal').hide();
		});

		jQuery('.vr-livemap-rtitle').click(function(event) {
			event.stopPropagation();
			jQuery('.vr-livemap-modal').toggle();
		});
		
	});

</script>
