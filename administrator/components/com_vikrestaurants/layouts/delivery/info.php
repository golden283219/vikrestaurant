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
 * @var  mixed  $area  The delivery area if exists, null otherwise.
 * @var  float  $lat   The requested latitude.
 * @var  float  $lng   The requested longitude.
 */
extract($displayData);

if ($area === null)
{
	?>
	<div class="fail"><?php echo JText::_('VRTKDELIVERYLOCNOTFOUND'); ?></div>
	<?php
}
else
{
	$currency = VREFactory::getCurrency();

	?>
	<div class="success">

		<div class="info">
			<div class="data-label"><?php echo JText::_('VRMANAGETKAREA16'); ?></div>
			<div class="data-value"><?php echo $lat . ', ' . $lng; ?></div>
		</div>

		<div class="info">
			<div class="data-label"><?php echo JText::_('VRMANAGETKAREA17'); ?></div>
			<div class="data-value"><?php echo $area['name']; ?></div>
		</div>

		<div class="info">
			<div class="data-label"><?php echo JText::_('VRMANAGETKAREA4'); ?></div>
			<div class="data-value"><?php echo ($area['charge'] > 0 ? '+ ' : '') . $currency->format($area['charge']); ?> </div>
		</div>

		<div class="info">
			<div class="data-label"><?php echo JText::_('VRMANAGETKAREA18'); ?></div>
			<div class="data-value"><?php echo $currency->format($area['min_cost']); ?></div>
		</div>

	</div>
	<?php
}
