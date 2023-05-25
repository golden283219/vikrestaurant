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

$date    = !empty($displayData['date'])    ? $displayData['date']    : null;
$time    = !empty($displayData['time'])    ? $displayData['time']    : null;
$people  = !empty($displayData['people'])  ? $displayData['people']  : null;
$room    = !empty($displayData['room'])    ? $displayData['room']    : null;
$table   = !empty($displayData['table'])   ? $displayData['table']   : null;
$deposit = !empty($displayData['deposit']) ? $displayData['deposit'] : 0;
$suffix  = !empty($displayData['suffix'])  ? $displayData['suffix']  : '';
?>

<div class="vrresultsummarydiv<?php echo $suffix; ?>">

	<?php
	if ($date)
	{
		?>
		<div class="vrresultsuminnerdiv" id="vrresultsumdivdate">
			<span class="vrresultsumlabelsp"><?php echo rtrim(JText::_('VRDATE'), ':'); ?>:</span>

			<span class="vrresultsumvaluesp">
				<?php echo $date; ?>
			</span>
		</div>
		<?php
	}
	
	if ($time)
	{
		?>
		<div class="vrresultsuminnerdiv" id="vrresultsumdivhour">
			<span class="vrresultsumlabelsp"><?php echo rtrim(JText::_('VRTIME'), ':'); ?>:</span>

			<span class="vrresultsumvaluesp">
				<?php echo $time; ?>
			</span>
		</div>
		<?php
	}
	
	if ($people)
	{
		?>
		<div class="vrresultsuminnerdiv" id="vrresultsumdivpeople">
			<span class="vrresultsumlabelsp"><?php echo rtrim(JText::_('VRPEOPLE'), ':'); ?>:</span>
			
			<span class="vrresultsumvaluesp">
				<?php echo $people; ?>
			</span>
		</div>
		<?php
	}

	if ($room || $table)
	{
		?>
		<div class="vrresultsuminnerdiv" id="vrresultsumdivtable">
			<span class="vrresultsumlabelsp">
				<?php
				if ($table)
				{
					echo JText::_('VRTABLE') . ':';
				}
				else
				{
					echo JText::_('VRROOM') . ':';
				}
				?>
			</span>
			
			<span class="vrresultsumvaluesp">
				<?php
				$parts = array($room, $table);
				$parts = array_values(array_filter($parts));
				echo implode(' - ', $parts);
				?>
			</span>
		</div>
		<?php
	}

	if ((float) $deposit > 0)
	{
		?>
		<div class="vrresultsuminnerdiv" id="vrresultsumdivdeposit">
			<span class="vrresultsumlabelsp"><?php echo JText::_('VRORDERRESERVATIONCOST'); ?>:</span>
			
			<span class="vrresultsumvaluesp">
				<?php echo VREFactory::getCurrency()->format($deposit); ?>
			</span>
		</div>
		<?php
	}
	?>
	
</div>
