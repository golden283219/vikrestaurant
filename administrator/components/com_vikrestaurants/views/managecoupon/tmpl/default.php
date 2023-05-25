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

$coupon = $this->coupon;

if ($coupon->group == 0)
{
	$coupon->minvalue = round($coupon->minvalue);
}

$dates_exp = array('', '');

if (strlen($coupon->datevalid))
{
	$dates_exp = explode('-', $coupon->datevalid);
}

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>

				<!-- GROUP - Dropdown -->
				<?php
				$groups = JHtml::_('vrehtml.admin.groups');

				echo $vik->openControl(JText::_('VRMANAGECOUPON10')); ?>
					<select name="group" id="vr-group-sel" class="medium">
						<?php echo JHtml::_('select.options', $groups, 'value', 'text', $coupon->group, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
			
				<!-- CODE - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGECOUPON1') . '*'); ?>
					<input type="text" name="code" class="required" value="<?php echo $coupon->code; ?>" size="32" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- TYPE - Dropdown -->
				<?php
				$options = array(
					JHtml::_('select.option', 1, 'VRCOUPONTYPEOPTION1'),
					JHtml::_('select.option', 2, 'VRCOUPONTYPEOPTION2'),
				);
				
				echo $vik->openControl(JText::_('VRMANAGECOUPON2')); ?>
					<select name="type" class="medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $coupon->type, true); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- PERCENT OR TOTAL - Dropdown -->
				<?php
				$options = array(
					JHtml::_('select.option', 1, '%'),
					JHtml::_('select.option', 2, $currency->getSymbol()),
				);

				echo $vik->openControl(JText::_('VRMANAGECOUPON3')); ?>
					<select name="percentot" id="vr-percentot-sel" class="medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $coupon->percentot); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- VALUE - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGECOUPON4') . '*'); ?>
					<div class="input-prepend currency-field" id="value-currency">
						<button type="button" class="btn"><?php echo $coupon->percentot == 1 ? '%' : $currency->getSymbol(); ?></button>

						<input type="number" name="value" class="required" value="<?php echo $coupon->value; ?>" min="0" max="99999999" step="any" />
					</div>
				<?php echo $vik->closeControl(); ?>
				
				<!-- DATE START - Calendar -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGECOUPON5'));
				echo $vik->calendar($dates_exp[0], 'dstart', 'dstart');
				echo $vik->closeControl();
				?>
				
				<!-- DATE END - Calendar -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGECOUPON6'));
				echo $vik->calendar($dates_exp[1], 'dend', 'dend');
				echo $vik->closeControl();
				?>
				
				<!-- MIN PEOPLE/MIN ORDER - Number -->
				<?php echo $vik->openControl(JText::_('VRMANAGECOUPON' . ($coupon->group == 0 ? '8' : '9')), 'vr-minvalue-row'); ?>

					<div class="input-prepend currency-field" id="min-field">
						<button type="button" class="btn"><?php echo $coupon->group == 0 ? '<i class="fas fa-user"></i>' : $currency->getSymbol(); ?></button>

						<input type="number" name="minvalue" value="<?php echo $coupon->minvalue; ?>" size="40" min="<?php echo ($coupon->group == 0 ? '1' : '0'); ?>" step="<?php echo ($coupon->group == 0 ? '1' : 'any'); ?>" />
					</div>

				<?php echo $vik->closeControl(); ?>
				
			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<div class="span6">
			<?php echo $vik->openEmptyFieldset(); ?>

				<!-- USAGES - Number -->
				<?php
				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGECOUPON14'),
					'content' => JText::_('VRMANAGECOUPON14_DESC'),
				));
				
				echo $vik->openControl(JText::_('VRMANAGECOUPON14') . $help); ?>

					<input type="number" name="usages" value="<?php echo $coupon->usages; ?>" size="40" min="0" step="1" />

				<?php echo $vik->closeControl(); ?>

				<!-- MAX USAGES - Number -->
				<?php
				$options = array(
					JHtml::_('select.option', 0, 'VRPEOPLEALLOPT1'),
					JHtml::_('select.option', 1, 'VRPEOPLEALLOPT2'),
				);

				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGECOUPON12'),
					'content' => JText::_('VRMANAGECOUPON12_DESC'),
				));

				$max = $coupon->maxusages == 0 ? 0 : 1;
				
				echo $vik->openControl(JText::_('VRMANAGECOUPON12') . $help, 'multi-field'); ?>

					<select id="maxusages-sel" class="medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $max, true); ?>
					</select>

					<input type="number" name="maxusages" value="<?php echo $coupon->maxusages; ?>" size="40" min="<?php echo $max; ?>" step="1" max="999999" style="<?php echo $max ? '' : 'display:none;'; ?>width:80px !important;" />

				<?php echo $vik->closeControl(); ?>

				<!-- MAX USAGES PER CUSTOMER - Number -->
				<?php
				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGECOUPON13'),
					'content' => JText::_('VRMANAGECOUPON13_DESC'),
				));

				$max = $coupon->maxperuser == 0 ? 0 : 1;
				
				echo $vik->openControl(JText::_('VRMANAGECOUPON13') . $help, 'multi-field'); ?>

					<select id="maxperuser-sel" class="medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $max, true); ?>
					</select>

					<input type="number" name="maxperuser" value="<?php echo $coupon->maxperuser; ?>" size="40" min="<?php echo $max; ?>" step="1" max="999999" style="<?php echo $max ? '' : 'display:none;'; ?>width:80px !important;" />

				<?php echo $vik->closeControl(); ?>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewCoupon". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				$custom = $this->onDisplayManageView();
				?>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $coupon->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRMANAGECOUPON8');
JText::script('VRMANAGECOUPON9');
?>

<script type="text/javascript">

	jQuery(document).ready(function(){
		
		jQuery('select.medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150
		});

		jQuery('#vr-group-sel').on('change', function(){

			var input = jQuery('input[name="minvalue"]');

			if (jQuery(this).val() == '0') {
				input.prop('step', 1);
				input.prop('min', 1);

				input.val(Math.max(1, parseInt(input.val())));

				jQuery('.vr-minvalue-row b').text(Joomla.JText._('VRMANAGECOUPON8'));

				jQuery('#min-field').find('button').html('<i class="fas fa-user"></i>');
			} else {
				input.prop('step', 'any');
				input.prop('min', 0);

				jQuery('.vr-minvalue-row b').text(Joomla.JText._('VRMANAGECOUPON9'));

				jQuery('#min-field').find('button').text('<?php echo $currency->getSymbol(); ?>');
			}

		});

		jQuery('#vr-percentot-sel').on('change', function() {
			var text = jQuery(this).find('option:selected').text();

			jQuery('#value-currency').find('button').text(text);
		});

		jQuery('#maxusages-sel,#maxperuser-sel').on('change', function() {
			// get selected value
			var sel = parseInt(jQuery(this).val());
			// get input
			var input = jQuery(this).nextAll('input').first();

			if (sel == 0) {
				// hide input and update value
				input.attr('min', 0).val(0).hide();
			} else {
				// show input and update value
				input.attr('min', 1).val(1).show();
			}

			input.trigger('change');
		});

		jQuery('input[name="maxusages"]').on('change', function() {
			// get selected value
			var max = parseInt(jQuery(this).val());
			// get linked input
			var input = jQuery('input[name="maxperuser"]');

			if (max == 0) {
				// set maximum to (almost) unlimited
				max = 999999;
			}

			// update max attribute
			input.attr('max', max);

			// change value in case it exceeds the ceil
			if (parseInt(input.val()) > max) {
				input.val(max);
			}
		}).trigger('change');

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
