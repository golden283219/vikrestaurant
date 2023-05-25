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

if (!$step->isCompleted())
{
	// go ahead only if completed
	return;
}
?>

<ul class="wizard-step-summary">
	<?php
	$menus = $step->getMenus();

	// display at most 4 menus
	for ($i = 0; $i < min(array(4, count($menus))); $i++)
	{
		?>
		<li>
			<i class="fas fa-<?php echo $menus[$i]->published ? 'check-circle ok' : 'dot-circle no'; ?> medium"></i>
			<b><?php echo $menus[$i]->name; ?></b>
		</li>
		<?php
	}

	// count remaining menus
	$remaining = count($menus) - 5;

	if ($remaining > 0)
	{
		?>
		<li><?php echo JText::plural('VRWIZARDOTHER_N_ITEMS', $remaining); ?></li>
		<?php
	}
	?>
</ul>
