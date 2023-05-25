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

$label = $displayData['label'];
$cf    = $displayData['field'];

?>

<div class="vrseparatorcf<?php echo $cf['choose']; ?>" id="vrcfinput<?php echo $cf['id']; ?>">
	<?php echo $label; ?>
</div>
