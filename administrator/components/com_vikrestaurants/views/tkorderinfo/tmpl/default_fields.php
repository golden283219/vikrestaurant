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

?>

<h3><?php echo JText::_('VRMANAGERESERVATIONTITLE2'); ?></h3>

<div class="order-fields">

	<?php
	foreach ($this->order->fields as $k => $v)
	{
		if ($v)
		{
			?>
			<div class="order-field">
				<label><?php echo JText::_($k); ?></label>

				<div class="order-field-value">
					<b><?php echo nl2br($v); ?></b>
				</div>
			</div>
			<?php
		}
	}
	?>

</div>
