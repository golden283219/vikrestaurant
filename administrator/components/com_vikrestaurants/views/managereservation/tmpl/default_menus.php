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

$vik = VREApplication::getInstance();
		
foreach ($this->allMenus as $m)
{ 
	echo $vik->openControl($m->name); ?>

		<input
			type="number"
			name="quantity[<?php echo $m->id; ?>]"
			value="<?php echo (int) $m->quantity; ?>"
			class="vrmenuquant"
			min="0"
			max="<?php echo $this->reservation->people; ?>"
			style="text-align: right;"
		/>
		
		<input type="hidden" name="menu_assoc[<?php echo $m->id; ?>]" value="<?php echo $m->id_assoc; ?>" />
	
	<?php echo $vik->closeControl();
}
