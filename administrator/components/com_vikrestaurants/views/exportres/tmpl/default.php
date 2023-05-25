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

JHtml::_('bootstrap.popover');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRMAPDETAILSBUTTON')); ?>
			
				<!-- NAME - Text -->

				<?php echo $vik->openControl(JText::_('VREXPORTRES1')); ?>
					<input type="text" name="filename" value="" size="32" />
				<?php echo $vik->closeControl(); ?>
			
				<!-- EXPORT CLASS - Select -->

				<?php
				$elements = array();
				$elements[] = JHtml::_('select.option', '', '');

				foreach ($this->drivers as $k => $v)
				{
					$elements[] = JHtml::_('select.option', $k, $v);
				}

				echo $vik->openControl(JText::_('VREXPORTRES2') . '*'); ?>
					<select name="driver" class="required" id="vr-driver-sel">
						<?php echo JHtml::_('select.options', $elements); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- DATE FROM - Calendar -->

				<?php
				echo $vik->openControl(JText::_('VREXPORTRES3'));
				echo $vik->calendar($this->data->fromdate, 'fromdate', 'vr-date-from');
				echo $vik->closeControl();
				?>

				<!-- DATE TO - Calendar -->

				<?php
				echo $vik->openControl(JText::_('VREXPORTRES4'));
				echo $vik->calendar($this->data->todate, 'todate', 'vr-date-from');
				echo $vik->closeControl();
				?>

			<?php echo $vik->closeFieldset(); ?>
		</div>

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRMANAGEPAYMENT8')); ?>

			<div class="vikpayparamdiv">
				<?php echo $vik->alert(JText::_('VRMANAGEPAYMENT9')); ?>
			</div>

			<div id="vikparamerr" style="display: none;">
				<?php echo $vik->alert(JText::_('VRE_AJAX_GENERIC_ERROR'), 'error'); ?>
			</div>

			<?php echo $vik->closeFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<?php
	foreach ($this->data->cid as $id)
	{
		?> 
		<input type="hidden" name="cid[]" value="<?php echo $id; ?>" />
		<?php
	}
	?>
	
	<input type="hidden" name="type" value="<?php echo $this->data->type; ?>" />

	<input type="hidden" name="view" value="exportres" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_FILTER_SELECT_DRIVER');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('#vr-driver-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_DRIVER'),
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-driver-sel').on('change', function() {
			// destroy select2 
			jQuery('.vikpayparamdiv select').select2('destroy');
			// unregister form fields
			validator.unregisterFields('.vikpayparamdiv .required');
			
			jQuery('.vikpayparamdiv').html('');
			jQuery('#vikparamerr').hide();

			// fetch driver form
			UIAjax.do(
				'index.php?option=com_vikrestaurants&task=exportres.getdriverformajax&tmpl=component',
				{
					driver: jQuery(this).val(),
					type:   jQuery('input[name="type"]').val(),
				},
				function(resp) {
					var obj = jQuery.parseJSON(resp);

					if (!obj) {
						jQuery('#vikparamerr').show();
						return false;
					}

					jQuery('.vikpayparamdiv').html(obj[0]);

					// render select
					jQuery('.vikpayparamdiv select').each(function() {
						jQuery(this).select2({
							// disable search for select with 3 or lower options
							minimumResultsForSearch: jQuery(this).find('option').length > 3 ? 0 : -1,
							allowClear: false,
							width: 285,
						});
					});

					// register form fields for validation
					validator.registerFields('.vikpayparamdiv .required');

					// init helpers
					jQuery('.vikpayparamdiv .vr-quest-popover').popover({sanitize: false, container: 'body'});
				},
				function(error) {
					jQuery('#vikparamerr').show();
				}
			);
		});

	});

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
