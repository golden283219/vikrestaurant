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
 * @var  VREWizardStep  $step  The wizard step instance.
 */
extract($displayData);

$vik = VREApplication::getInstance();

$id = $step->getID();

$config = VREFactory::getConfig();

if (!$step->isCompleted())
{
	// get list of currencies
	$currencies = $step->getCurrencies();

	?>
	<div class="wizard-form">

		<!-- RESTAURANT NAME - Text -->

		<?php echo $vik->openControl(JText::_('VRMANAGECONFIG0')); ?>
			<input type="text" name="wizard[<?php echo $id; ?>][restname]" value="<?php echo $config->get('restname'); ?>" class="required" />
		<?php echo $vik->closeControl(); ?>

		<!-- ADMIN E-MAIL - Email -->

		<?php echo $vik->openControl(JText::_('VRMANAGECONFIG1')); ?>
			<input type="email" name="wizard[<?php echo $id; ?>][adminemail]" value="<?php echo $config->get('adminemail'); ?>" class="required" />
		<?php echo $vik->closeControl(); ?>

		<!-- CURRENCY - Select -->

		<?php
		$options = array();

		$options[] = JHtml::_('select.option', '', JText::_('VRE_DELIVERY_LOCATION_TYPE_OTHER'));

		foreach ($currencies as $code => $format)
		{
			$options[] = JHtml::_('select.option', $code, $code . ' - ' . $format['currency']);
		}

		$code = $config->get('currencyname');
		$symb = $config->get('currencysymb');
		?>

		<?php echo $vik->openControl(JText::_('VRMANAGECONFIGGLOBSECTION3')); ?>
			<select name="wizard[<?php echo $id; ?>][currency]">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $code); ?>
			</select>

			<input type="text" name="wizard[<?php echo $id; ?>][currencyname]" value="<?php echo $code; ?>" size="6" placeholder="<?php echo $this->escape(JText::_('VRWIZARDCURRCODE')); ?>" style="margin-left:5px;max-width:80px;<?php echo !isset($currencies[$code]) ? '' : 'display:none;'; ?>" />
			<input type="text" name="wizard[<?php echo $id; ?>][currencysymb]" value="<?php echo $symb; ?>" size="6" placeholder="<?php echo $this->escape(JText::_('VRWIZARDCURRSYMB')); ?>" style="margin-left:5px;max-width:80px;<?php echo !isset($currencies[$code]) ? '' : 'display:none;'; ?>" />
		<?php echo $vik->closeControl(); ?>

		<!-- DATE FORMAT - Select -->

		<?php
		$options = array(
			JHtml::_('select.option', 'Y/m/d', 'VRCONFIGDATEFORMAT1'),
			JHtml::_('select.option', 'm/d/Y', 'VRCONFIGDATEFORMAT2'),
			JHtml::_('select.option', 'd/m/Y', 'VRCONFIGDATEFORMAT3'),
			JHtml::_('select.option', 'Y-m-d', 'VRCONFIGDATEFORMAT4'),
			JHtml::_('select.option', 'm-d-Y', 'VRCONFIGDATEFORMAT5'),
			JHtml::_('select.option', 'd-m-Y', 'VRCONFIGDATEFORMAT6'),
			JHtml::_('select.option', 'Y.m.d', 'VRCONFIGDATEFORMAT7'),
			JHtml::_('select.option', 'm.d.Y', 'VRCONFIGDATEFORMAT8'),
			JHtml::_('select.option', 'd.m.Y', 'VRCONFIGDATEFORMAT9'),
		);
		?>

		<?php echo $vik->openControl(JText::_('VRMANAGECONFIG5')); ?>
			<select name="wizard[<?php echo $id; ?>][dateformat]">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $config->get('dateformat'), true); ?>
			</select>
		<?php echo $vik->closeControl(); ?>

		<!-- TIME FORMAT - Checkbox -->

		<?php
		$yes = $vik->initRadioElement('', '', $config->get('timeformat') == 'H:i');
		$no  = $vik->initRadioElement('', '', $config->get('timeformat') == 'h:i A');

		echo $vik->openControl(JText::_('VRWIZARDFORMATH24'));
		echo $vik->radioYesNo("wizard[{$id}][timeformat]", $yes, $no);
		echo $vik->closeControl();
		?>

	</div>

	<script>

		jQuery(document).ready(function() {
			VikRenderer.chosen('[data-id="<?php echo $id; ?>"] select');

			jQuery('select[name="wizard[<?php echo $id; ?>][currency]"]').on('change', function() {
				if (jQuery(this).val().length) {
					jQuery('input[name="wizard[<?php echo $id; ?>][currencyname]"]').hide();
					jQuery('input[name="wizard[<?php echo $id; ?>][currencysymb]"]').hide();
				} else {
					jQuery('input[name="wizard[<?php echo $id; ?>][currencyname]"]').show();
					jQuery('input[name="wizard[<?php echo $id; ?>][currencysymb]"]').show();
				}
			});
		});

		VREWizard.addPreflight('<?php echo $id; ?>', function(role, step) {
			if (role != 'process') {
				return true;
			}

			// create form validator
			var validator = new VikFormValidator(step);

			if (jQuery('select[name="wizard[<?php echo $id; ?>][currency]"]').val().length == 0) {
				validator.registerFields('input[name="wizard[<?php echo $id; ?>][currencyname]"]');
				validator.registerFields('input[name="wizard[<?php echo $id; ?>][currencysymb]"]');
			}

			// validate form
			if (!validator.validate()) {
				// prevent request
				return false;
			}

			return true;
		});

	</script>
	<?php
}
else
{
	?>
	<ul class="wizard-step-summary">
		<li>
			<b><?php echo $config->get('restname'); ?></b>
		</li>
		<li>
			<b><?php echo $config->get('adminemail'); ?></b>
		</li>
		<li>
			<b><?php echo $config->get('currencyname'); ?></b>
			<span class="badge badge-success"><?php echo VREFactory::getCurrency()->format(1234.56); ?></span>
		</li>
		<li>
			<span class="badge badge-important"><?php echo date($config->get('dateformat'), VikRestaurants::now()); ?></span>
			<span class="badge badge-warning"><?php echo date($config->get('timeformat'), VikRestaurants::now()); ?></span>
		</li>
	</ul>
	<?php
}
?>
