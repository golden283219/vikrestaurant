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

$display = isset($displayData['display']) ? (bool) $displayData['display'] : true;
$active  = !empty($displayData['active']) ? $displayData['active']         : 1;
$args    = !empty($displayData['args'])   ? (array) $displayData['args']   : array();
$itemid  = !empty($displayData['Itemid']) ? $displayData['Itemid']         : null;

if (!$display)
{
	// do not proceed in case the step bar shouldn't be displayed
	return;
}

if (is_null($itemid))
{
	$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');
}

$queryString = '';

if ($args)
{
	$queryString .= '&' . http_build_query($args);
}

if ($itemid)
{
	$queryString .= '&Itemid=' . $itemid;
}

$resreq = VREFactory::getConfig()->getUint('reservationreq');

?>

<!-- step one -->

<div class="vrstepbardiv">

	<!-- STEP ONE -->

	<?php
	if ($active == 1)
	{
		// first step active
		?>
		<div class="vrstep vrstepactive step-current">
			<div class="vrstep-inner">
				<span class="vrsteptitle"><?php echo JText::_('VRSTEPONETITLE'); ?></span>
				<span class="vrstepsubtitle"><?php echo JText::_('VRSTEPONESUBTITLE'); ?></span>
			</div>
		</div>
		<?php
	}
	else
	{
		// first step already completed
		?>
		<div class="vrstep vrstepactive">
			<div class="vrstep-inner">
				<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=restaurants' . $queryString); ?>">
					<span class="vrsteptitle"><i class="fas fa-check"></i></span>
					<span class="vrstepsubtitle"><?php echo JText::_('VRSTEPONESUBTITLE'); ?></span>
				</a>
			</div>
		</div>
		<?php
	}
	?>

	<!-- STEP TWO -->

	<?php
	if ($resreq == 0)
	{
		$step_2_title = JText::_('VRSTEPTWOSUBTITLEZERO');
	}
	else if ($resreq == 1)
	{
		$step_2_title = JText::_('VRSTEPTWOSUBTITLEONE');
	}
	else
	{
		$step_2_title = JText::_('VRSTEPTWOSUBTITLETWO');
	}
	
	if ($active == 2)
	{
		// second step active
		?>
		<div class="vrstep vrstepactive step-current">
			<div class="vrstep-inner">
				<span class="vrsteptitle"><?php echo JText::_('VRSTEPTWOTITLE'); ?></span>
				<span class="vrstepsubtitle"><?php echo $step_2_title; ?></span>
			</div>
		</div>
		<?php
	}
	else if ($active > 2)
	{
		// second step already completed
		?>
		<div class="vrstep vrstepactive">
			<div class="vrstep-inner">
				<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=search' . $queryString); ?>">
					<span class="vrsteptitle"><i class="fas fa-check"></i></span>
					<span class="vrstepsubtitle"><?php echo $step_2_title; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
	else
	{
		// second step not reached
		?>
		<div class="vrstep">
			<div class="vrstep-inner">
				<span class="vrsteptitle"><?php echo JText::_('VRSTEPTWOTITLE'); ?></span>
				<span class="vrstepsubtitle"><?php echo $step_2_title; ?></span>
			</div>
		</div>
		<?php
	}
	?>

	<!-- STEP THREE -->

	<?php
	if ($active == 3)
	{
		// third step active
		?>
		<div class="vrstep vrstepactive step-current">
			<div class="vrstep-inner">
				<span class="vrsteptitle"><?php echo JText::_('VRSTEPTHREETITLE'); ?></span>
				<span class="vrstepsubtitle"><?php echo JText::_('VRSTEPTHREESUBTITLE'); ?></span>
			</div>
		</div>
		<?php
	}
	else
	{
		// third step not reached
		?>
		<div class="vrstep">
			<div class="vrstep-inner">
				<span class="vrsteptitle"><?php echo JText::_('VRSTEPTHREETITLE'); ?></span>
				<span class="vrstepsubtitle"><?php echo JText::_('VRSTEPTHREESUBTITLE'); ?></span>
			</div>
		</div>
		<?php
	}
	?>

</div>
