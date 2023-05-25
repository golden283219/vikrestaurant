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
 * @param 	integer  id      The ID of the invoice.
 * @param 	string 	 number  The invoice unique number.
 * @param 	string 	 file    The file to which the invoice is linked.
 * @param 	string   name    An optional file name to use.
 */
extract($displayData);

if (!isset($name))
{
	// missing name, extract it from the file name
	$name = substr($file, 0, strrpos($file, '.'));
}

?>
	
<div class="vr-archive-fileblock">

	<div class="vr-archive-fileicon">
		<img src="<?php echo VREASSETS_ADMIN_URI . 'images/invoice@big.png'; ?>" />
	</div>

	<div class="vr-archive-filename">
		<a href="<?php echo VREINVOICE_URI . $file; ?>?t=<?php echo time(); ?>" target="_blank">
			<?php echo $number; ?>
		</a>

		<br />

		<small class="break-word"><?php echo $name; ?></small>
	</div>

	<input type="checkbox" style="display:none;" id="cb<?php echo md5($number . $name); ?>" name="cid[]" value="<?php echo $id; ?>" onchange="<?php echo VREApplication::getInstance()->checkboxOnClick(); ?>" />

</div>
