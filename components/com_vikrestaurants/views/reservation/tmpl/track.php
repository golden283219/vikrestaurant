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

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=reservation' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" name="orderform" method="get">

	<div class="vrorderpagediv">

		<div class="vrordertitlediv"><?php echo JText::_('VRORDERTITLE1'); ?></div>

		<div class="vrordercomponentsdiv">

			<div class="vrorderinputdiv">
				<label class="vrorderlabel" for="vrordnum" style="float: left;">
					<?php echo JText::_('VRORDERNUMBER'); ?>:
				</label>
				
				<input class="vrorderinput" type="text" id="vrordnum" name="ordnum" size="16" />
			</div>
			
			<div class="vrorderinputdiv">
				<label class="vrorderlabel" for="vrordkey" style="float: left;">
					<?php echo JText::_('VRORDERKEY'); ?>:
				</label>

				<input class="vrorderinput" type="text" id="vrordkey" name="ordkey" size="16" />
			</div>
			
			<div class="vrorderinputdiv">
				<button type="submit" class="vrordersubmit"><?php echo JText::_('VRSUBMIT'); ?></button>
			</div>

		</div>

	</div>

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="reservation" />

</form>
