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

$invoice = $this->invoice;

$vik = VREApplication::getInstance();

$date_format = VREFactory::getConfig()->get('dateformat');

?>

<div class="row-fluid">

	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('VRINVOICEFIELDSET1')); ?>

			<?php
			// invoice management
			if ($invoice->id)
			{
				?>
				<!-- GROUP - Hidden -->
				<input type="hidden" name="group" value="<?php echo $invoice->group; ?>" />

				<!-- OVERWRITE - Hidden -->
				<input type="hidden" name="overwrite" value="1" />
				<?php
			}
			// invoice creation
			else
			{
				?>
				<!-- GROUP - Dropdown -->
				<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE1')); ?>
					<select name="group" id="vr-group-sel" class="medium-large">
						<?php echo JHtml::_('select.options', JHtml::_('vrehtml.admin.groups'), 'value', 'text', $invoice->group, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- MONTH AND YEAR - Dropdowns -->
				<?php
				$help = $vik->createPopover(array(
					'title'   => JText::_('VRSTATISTICSTH1'),
					'content' => JText::_('VRMANAGEINVOICE2_HELP'),
				));

				echo $vik->openControl(JText::_('VRSTATISTICSTH1') . $help, 'multi-field'); ?>
					<select name="month" id="vr-order-month" class="short-medium">
						<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.months'), 'value', 'text', $invoice->month); ?>
					</select>

					<select name="year" id="vr-order-year" class="short">
						<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.years', -10, 10), 'value', 'text', $invoice->year); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- OVERWRITE - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', '', false);
				$elem_no  = $vik->initRadioElement('', '', true);

				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGEINVOICE3'),
					'content' => JText::_('VRMANAGEINVOICE3_HELP'),
				));
				
				echo $vik->openControl(JText::_('VRMANAGEINVOICE3') . $help);
				echo $vik->radioYesNo('overwrite', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
			}
			?>

			<!-- NOTIFY CUSTOMERS - Radio Button -->
			<?php
			$elem_yes = $vik->initRadioElement('', '', false);
			$elem_no  = $vik->initRadioElement('', '', true);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGEINVOICE7'),
				'content' => JText::_('VRMANAGEINVOICE7_HELP'),
			));
			
			echo $vik->openControl(JText::_('VRMANAGEINVOICE7') . $help);
			echo $vik->radioYesNo('notifycust', $elem_yes, $elem_no, false);
			echo $vik->closeControl();
			?>

		<?php echo $vik->closeFieldset(); ?>
	</div>

	<div class="span6">

		<div class="row-fluid">

			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRINVOICEFIELDSET2')); ?>

					<!-- INVOICE NUMBER - Text -->
					<?php
					$help = $vik->createPopover(array(
						'title'   => JText::_('VRMANAGEINVOICE4'),
						'content' => JText::_('VRMANAGEINVOICE4_HELP'),
					));

					echo $vik->openControl(JText::_('VRMANAGEINVOICE4') . '*' . $help, 'multi-field'); ?>
						<input type="number" name="inv_number[]" value="<?php echo $invoice->number; ?>" min="1" max="99999999" value="" class="required" style="text-align:right;" step="1"/>
						&nbsp;/&nbsp;
						<input type="text" name="inv_number[]" value="<?php echo $invoice->suffix; ?>" size="10" value="" />
					<?php echo $vik->closeControl(); ?>

					<!-- INVOICE DATE - Dropdown -->
					<?php
					$help = $vik->createPopover(array(
						'title'   => JText::_('VRMANAGEINVOICE5'),
						'content' => JText::_('VRMANAGEINVOICE5_HELP'),
					));

					echo $vik->openControl(JText::_('VRMANAGEINVOICE5') . '*' . $help);

					// invoice management
					if ($invoice->id)
					{
						echo $vik->calendar(date($date_format, $invoice->inv_date), 'inv_date', 'vr-invdate-cal', null, array('class' => 'required'));
					}
					// invoice creation
					else
					{
						$today = date($date_format, VikRestaurants::now());

						$options = array(
							JHtml::_('select.option', 1, JText::sprintf('VRINVOICEDATEOPT1', $today)),
							JHtml::_('select.option', 2, JText::_('VRINVOICEDATEOPT2')),
						);
						
						?>
						<select name="inv_date" id="vr-invdate-sel" class="medium">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $invoice->datetype); ?>
						</select>
						<?php
						
					}
					echo $vik->closeControl();
					?>

					<!-- LEGAL INFO - Textarea -->
					<?php
					$help = $vik->createPopover(array(
						'title'   => JText::_('VRMANAGEINVOICE6'),
						'content' => JText::_('VRMANAGEINVOICE6_HELP'),
					));

					echo $vik->openControl(JText::_('VRMANAGEINVOICE6') . $help); ?>
						<textarea name="legalinfo" class="full-width" style="height: 150px;resize: vertical;"><?php echo $invoice->legalinfo; ?></textarea>
					<?php echo $vik->closeControl(); ?>

				<?php echo $vik->closeFieldset(); ?>
			</div>

		</div>

		<div class="row-fluid">

			<div class="span12">
				<?php echo $vik->openFieldset(JText::_('VRINVOICEFIELDSET3')); ?>

					<!-- PAGE ORIENTATION - Dropdown -->
					<?php
					$options = array(
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::PAGE_ORIENTATION_PORTRAIT, JText::_('VRINVOICEPAGEORIOPT1')),
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::PAGE_ORIENTATION_LANDSCAPE, JText::_('VRINVOICEPAGEORIOPT2')),
					);
					
					echo $vik->openControl(JText::_('VRMANAGEINVOICE8')); ?>
						<select name="pageorientation" id="vr-pageori-sel" class="medium">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $invoice->settings->pageOrientation); ?>
						</select>
					<?php echo $vik->closeControl(); ?>

					<!-- PAGE FORMAT - Dropdown -->
					<?php
					$options = array(
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::PAGE_FORMAT_A4, VikRestaurantsConstraintsPDF::PAGE_FORMAT_A4),
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::PAGE_FORMAT_A5, VikRestaurantsConstraintsPDF::PAGE_FORMAT_A5),
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::PAGE_FORMAT_A6, VikRestaurantsConstraintsPDF::PAGE_FORMAT_A6),
					);
					
					echo $vik->openControl(JText::_('VRMANAGEINVOICE9')); ?>
						<select name="pageformat" id="vr-pageformat-sel" class="medium">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $invoice->settings->pageFormat); ?>
						</select>
					<?php echo $vik->closeControl(); ?>

					<!-- UNIT - Dropdown -->
					<?php
					$options = array(
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::UNIT_POINT, JText::_('VRINVOICEUNITOPT1')),
						JHtml::_('select.option', VikRestaurantsConstraintsPDF::UNIT_MILLIMETER, JText::_('VRINVOICEUNITOPT2')),
						// JHtml::_('select.option', VikRestaurantsConstraintsPDF::UNIT_CENTIMETER, JText::_('VRINVOICEUNITOPT3')),
						// JHtml::_('select.option', VikRestaurantsConstraintsPDF::UNIT_INCH, JText::_('VRINVOICEUNITOPT4')),
					);
					
					echo $vik->openControl(JText::_('VRMANAGEINVOICE10')); ?>
						<select name="unit" id="vr-unit-sel" class="medium">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $invoice->settings->unit); ?>
						</select>
					<?php echo $vik->closeControl(); ?>

					<!-- SCALE RATIO - Number -->
					<?php
					$help = $vik->createPopover(array(
						'title'   => JText::_('VRMANAGEINVOICE11'),
						'content' => JText::_('VRMANAGEINVOICE11_HELP'),
					));

					echo $vik->openControl(JText::_('VRMANAGEINVOICE11') . $help); ?>
						<div class="input-append">
							<input type="number" name="scale" value="<?php echo ($invoice->settings->imageScaleRatio * 100); ?>" min="10" step="1" />

							<button type="button" class="btn">%</button>
						</div>
					<?php echo $vik->closeControl(); ?>

				<?php echo $vik->closeFieldset(); ?>
			</div>

		</div>

	</div>

</div>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('select.short').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 100,
		});

		jQuery('select.short-medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150,
		});

		jQuery('select.medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#vr-group-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 254,
		});

		validator.addCallback(function() {
			var scaleInput = jQuery('input[name="scale"]');
			var scaleRatio = parseFloat(scaleInput.val());

			if (!isNaN(scaleRatio) && scaleRatio <= 10) {
				validator.setInvalid(scaleInput);
				return false;
			}

			validator.unsetInvalid(scaleInput);
			return true;
		});

	});

</script>
