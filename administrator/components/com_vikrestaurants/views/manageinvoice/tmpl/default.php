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

$invoice = $this->invoice;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->bootStartTabSet('invoice', array('active' => 'invoice_details')); ?>

		<!-- INVOICE -->
			
		<?php echo $vik->bootAddTab('invoice', 'invoice_details', JText::_('VRINVOICE')); ?>

			<?php echo $this->loadTemplate('invoice'); ?>

		<?php echo $vik->bootEndTab(); ?>

		<!-- LAYOUT -->

		<?php echo $vik->bootAddTab('invoice', 'invoice_layout', JText::_('VRE_UISVG_LAYOUT')); ?>

			<?php echo $this->loadTemplate('layout'); ?>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $invoice->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate()) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

</script>
