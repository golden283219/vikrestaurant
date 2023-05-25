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

JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.googlemaps', null, 'places');

$origin = $this->origin;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php
			echo $vik->openFieldset(JText::_('VRMAPDETAILSBUTTON'));
			echo $this->loadTemplate('details');
			echo $vik->closeFieldset();
			?>
		</div>

		<div class="span6">
			<?php
			echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET4'));
			echo $this->loadTemplate('map');
			echo $vik->closeFieldset();
			?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo (int) $origin->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script>

	(function($) {
		'use strict';

		const validator = new VikFormValidator('#adminForm');

		Joomla.submitbutton = (task) => {
			if (task.indexOf('save') === -1 || validator.validate()) {
				Joomla.submitform(task, document.adminForm);
			}
		}
	})(jQuery);

</script>
