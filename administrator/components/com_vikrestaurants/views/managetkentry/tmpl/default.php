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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.select2');

$vik = VREApplication::getInstance();

// always use default tab while creating a new record
$active_tab = $this->entry->id ? $this->getActiveTab('tkentry_details', $this->entry->id) : 'tkentry_details';

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	
	<?php echo $vik->bootStartTabSet('tkentry', array('active' => $active_tab, 'cookie' => $this->getCookieTab($this->entry->id)->name)); ?>

		<!-- ENTRY -->
			
		<?php echo $vik->bootAddTab('tkentry', 'tkentry_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<?php echo $this->loadTemplate('entry'); ?>

		<?php echo $vik->bootEndTab(); ?>

		<!-- TOPPINGS -->

		<?php
		$options = array(
			'badge' => count($this->entry->groups),
		);

		echo $vik->bootAddTab('tkentry', 'tkentry_toppings', JText::_('VRMANAGETKENTRYFIELDSET3'), $options); ?>

			<?php echo $this->loadTemplate('toppings'); ?>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $this->entry->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	
</form>

<?php
$footer  = '<button type="button" class="btn btn-success" id="entry-option-save">' . JText::_('JAPPLY') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="entry-option-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

// render inspector to manage entry variations
echo JHtml::_(
	'vrehtml.inspector.render',
	'entry-option-inspector',
	array(
		'title'       => JText::_('VRE_ADD_VARIATION'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
		'width'       => 400,
	),
	$this->loadTemplate('variation_modal')
);

$footer  = '<button type="button" class="btn btn-success" id="entry-group-save">' . JText::_('JAPPLY') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="entry-group-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

// render inspector to manage entry toppings groups
echo JHtml::_(
	'vrehtml.inspector.render',
	'entry-group-inspector',
	array(
		'title'       => JText::_('VRE_ADD_TOPPING_GROUP'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
	),
	$this->loadTemplate('group_modal')
);
?>

<script>

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
