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
 * @param 	array    list    An array of supported special days.
 * @param 	boolean  closed  True in case of closure, false otherwise.
 * @param 	array    args    An associative array containing the searched arguments.
 * @param 	array 	 shifts  A list of supported shifts.
 */
extract($displayData);

?>

<table class="git-table">

	<thead>
		<tr>
			<th><b><?php echo JText::_('VRMANAGESHIFT1'); ?></b></th>
			<th><b><?php echo JText::_('VRMANAGESHIFT2'); ?></b></th>
			<th><b><?php echo JText::_('VRMANAGESHIFT3'); ?></b></th>
		</tr>
	</thead>

	<tbody>

		<?php
		foreach ($shifts as $shift)
		{
			?>
			<tr>
				<td><?php echo $shift->name; ?></td>
				<td><?php echo $shift->fromtime; ?></td>
				<td><?php echo $shift->totime; ?></td>
			</tr>
			<?php
		}
		?>

	</tbody>

</table>
