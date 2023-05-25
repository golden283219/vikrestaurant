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

JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');

$plugin = $this->plugin;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span12">
			<?php echo $vik->openFieldset($plugin->getTitle() . ' : ' . $plugin->getName() . '.php'); ?>

				<div><?php echo $plugin->getDescription(); ?></div>

			<?php echo $vik->closeFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />

</form>
