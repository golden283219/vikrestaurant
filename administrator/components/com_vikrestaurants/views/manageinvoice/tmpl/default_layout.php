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

$vik = VREApplication::getInstance();

$invoice = $this->invoice;

?>

<div class="row-fluid">

	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('VRINVOICEFIELDSET4')); ?>

			<!-- FONT FAMILY - Dropdown -->

			<?php
			$options = array(
				JHtml::_('select.option', 'courier', 'Courier'),
				JHtml::_('select.option', 'helvetica', 'Helvetica'),
				JHtml::_('select.option', 'dejavusans', 'DejavuSans'),
			);

			foreach ($options as &$opt)
			{
				// make option disabled in case it is not supported
				$opt->disable = $this->handler->isFontSupported($opt->value) ? false : true;
			}

			echo $vik->openControl(JText::_('VRMANAGEINVOICE12')); ?>
				<select name="font" id="vre-font-sel">
					<?php echo JHtml::_('select.options', $options, 'value', 'text', $invoice->settings->font); ?>
				</select>
			<?php echo $vik->closeControl(); ?>

			<!-- BODY FONT SIZE - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE13')); ?>
				<div class="input-append">
					<input type="number" name="fontsizes[body]" value="<?php echo $invoice->settings->fontSizes->body; ?>" min="1" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- SHOW HEADER - Checkbox -->

			<?php
			$has_title = (bool) $invoice->settings->headerTitle;

			$yes = $vik->initRadioElement('', JText::_('JYES'), $has_title, 'onclick="showHeaderParams(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$has_title, 'onclick="showHeaderParams(0);"');

			echo $vik->openControl(JText::_('VRMANAGEINVOICE14'));
			echo $vik->radioYesNo('showheader', $yes, $no, false);
			echo $vik->closeControl();

			$head_param_attrs = array();

			if (!$has_title)
			{
				$head_param_attrs['style'] = 'display:none;';
			}
			?>

			<!-- HEADER TITLE - Text -->

			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGEINVOICE15'),
				'content' => JText::_('VRMANAGEINVOICE15_HELP'),
			));

			echo $vik->openControl(JText::_('VRMANAGEINVOICE15') . '*' . $help, 'header-param', $head_param_attrs); ?>
				<input type="text" name="headertitle" size="48" value="<?php echo $invoice->settings->headerTitle; ?>" class="<?php echo $has_title ? 'required' : ''; ?>" />
			<?php echo $vik->closeControl(); ?>

			<!-- HEADER FONT SIZE - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE16'), 'header-param', $head_param_attrs); ?>
				<div class="input-append">
					<input type="number" name="fontsizes[header]" value="<?php echo $invoice->settings->fontSizes->header; ?>" min="1" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- SHOW FOOTER - Checkbox -->

			<?php
			$has_footer = (bool) $invoice->settings->showFooter;

			$yes = $vik->initRadioElement('', JText::_('JYES'), $has_footer, 'onclick="showFooterParams(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$has_footer, 'onclick="showFooterParams(0);"');

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGEINVOICE17'),
				'content' => JText::_('VRMANAGEINVOICE17_HELP'),
			));

			echo $vik->openControl(JText::_('VRMANAGEINVOICE17') . $help);
			echo $vik->radioYesNo('showfooter', $yes, $no, false);
			echo $vik->closeControl();

			$foot_param_attrs = array();

			if (!$has_footer)
			{
				$foot_param_attrs['style'] = 'display:none;';
			}
			?>

			<!-- FOOTER FONT SIZE - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE18'), 'footer-param', $foot_param_attrs); ?>
				<div class="input-append">
					<input type="number" name="fontsizes[footer]" value="<?php echo $invoice->settings->fontSizes->footer; ?>" min="1" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

		<?php echo $vik->closeFieldset(); ?>
	</div>

	<div class="span6">
		<?php echo $vik->openFieldset(JText::_('VRINVOICEFIELDSET5')); ?>

			<!-- MARGIN TOP - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE19')); ?>
				<div class="input-append">
					<input type="number" name="margins[top]" value="<?php echo $invoice->settings->margins->top; ?>" min="0" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- MARGIN BOTTOM - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE20')); ?>
				<div class="input-append">
					<input type="number" name="margins[bottom]" value="<?php echo $invoice->settings->margins->bottom; ?>" min="0" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- MARGIN LEFT - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE21')); ?>
				<div class="input-append">
					<input type="number" name="margins[left]" value="<?php echo $invoice->settings->margins->left; ?>" min="0" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- MARGIN RIGHT - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE22')); ?>
				<div class="input-append">
					<input type="number" name="margins[right]" value="<?php echo $invoice->settings->margins->right; ?>" min="0" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- MARGIN HEADER - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE23'), 'header-param', $head_param_attrs); ?>
				<div class="input-append">
					<input type="number" name="margins[header]" value="<?php echo $invoice->settings->margins->header; ?>" min="0" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

			<!-- MARGIN FOOTER - Number -->

			<?php echo $vik->openControl(JText::_('VRMANAGEINVOICE24'), 'footer-param', $foot_param_attrs); ?>
				<div class="input-append">
					<input type="number" name="margins[footer]" value="<?php echo $invoice->settings->margins->footer; ?>" min="0" max="9999" step="any" />

					<button type="button" class="btn">pt.</button>
				</div>
			<?php echo $vik->closeControl(); ?>

		<?php echo $vik->closeFieldset(); ?>
	</div>

</div>

<script>

	jQuery(document).ready(function() {

		jQuery('#vre-font-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

	});

	function showHeaderParams(is) {
		if (is) {
			jQuery('.header-param').show();

			// make header title required
			validator.registerFields('input[name="headertitle"]');
		} else {
			jQuery('.header-param').hide();

			// make header title optional
			validator.unregisterFields('input[name="headertitle"]');
		}
	}

	function showFooterParams(is) {
		if (is) {
			jQuery('.footer-param').show();
		} else {
			jQuery('.footer-param').hide();
		}
	}

</script>
