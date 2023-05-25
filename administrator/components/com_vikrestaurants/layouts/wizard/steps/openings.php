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
 * @var  VREWizardStep  $step  The wizard step instance.
 */
extract($displayData);

$shifts = $step->getShifts();

if (!count($shifts))
{
	// go ahead only in case of shifts
	return;
}

$vik = VREApplication::getInstance();

?>

<ul class="wizard-step-summary">
	<?php
	// display at most 4 shifts
	for ($i = 0; $i < min(array(4, count($shifts))); $i++)
	{
		?>
		<li>
			<b><?php echo $shifts[$i]->name; ?></b>

			<?php
			if ($shifts[$i]->group == 1)
			{
				?><span class="badge badge-important"><?php echo JText::_('VRCUSTOMFGROUPOPTION1'); ?></span><?php
			}
			else
			{
				?><span class="badge badge-warning"><?php echo JText::_('VRCUSTOMFGROUPOPTION2'); ?></span><?php
			}
			?>
		</li>
		<?php
	}

	// count remaining shifts
	$remaining = count($shifts) - 5;

	if ($remaining > 0)
	{
		?>
		<li><?php echo JText::plural('VRWIZARDOTHER_N_ITEMS', $remaining); ?></li>
		<?php
	}
	?>
</ul>

<?php
if ($step->needShift('restaurant'))
{
	echo $vik->alert(JText::_('VRE_WIZARD_STEP_OPENINGS_WARN1'));
}
else if ($step->needShift('takeaway'))
{
	echo $vik->alert(JText::_('VRE_WIZARD_STEP_OPENINGS_WARN2'));
}
