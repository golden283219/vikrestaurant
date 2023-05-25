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

JHtml::_('behavior.core');
JHtml::_('vrehtml.sitescripts.calendar', '#vrdatestart,#vrdateend');

$coupon = $this->coupon;

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

$config = VREFactory::getConfig();

if ($coupon->datevalid)
{
	$coupon->datevalid = explode('-', $coupon->datevalid);

	$coupon->datevalid[0] = date($config->get('dateformat'), $coupon->datevalid[0]);
	$coupon->datevalid[1] = date($config->get('dateformat'), $coupon->datevalid[1]);
}
else
{
	$coupon->datevalid = array('', '');
}

$vik = VREApplication::getInstance();

?> 

<form name="managecouponform" action="index.php" method="post" id="vrmanageform">
	
	<div class="vrfront-manage-headerdiv">
		<div class="vrfront-manage-titlediv">
			<h2><?php echo JText::_($coupon->id ? 'VROPUPDATECOUPON' : 'VROPCREATECOUPON'); ?></h2>
		</div>
		
		<div class="vrfront-manage-actionsdiv">
			
			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrSaveCoupon(0);" id="vrfront-manage-btnsave" class="vrfront-manage-button">
					<?php echo JText::_('VRSAVE'); ?>
				</button>
			</div>

			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrSaveCoupon(1);" id="vrfront-manage-btnsaveclose" class="vrfront-manage-button">
					<?php echo JText::_('VRSAVEANDCLOSE'); ?>
				</button>
			</div>
			
			<div class="vrfront-manage-btn">
				<button type="button" onClick="vrCloseCoupon();" id="vrfront-manage-btnclose" class="vrfront-manage-button">
					<?php echo JText::_('VRCLOSE'); ?>
				</button>
			</div>

		</div>
	</div> 
	
	<div class="vrfront-manage-form">
		<?php echo $vik->openEmptyFieldset(); ?>
		
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON1') . '*'); ?>
				<input type="text" name="code" size="20" value="<?php echo $coupon->code; ?>" class="required" />
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON2')); ?>
				<div class="vre-select-wrapper">
					<select name="type" class="vre-select">
						<option value="1" <?php echo ($coupon->type == 1 ? 'selected="selected"' : ''); ?>><?php echo JText::_('VRCOUPONTYPEOPTION1'); ?></option>
						<option value="2" <?php echo ($coupon->type == 2 ? 'selected="selected"' : ''); ?>><?php echo JText::_('VRCOUPONTYPEOPTION2'); ?></option>
					</select>
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON3')); ?>
				<div class="vre-select-wrapper">
					<select name="percentot" class="vre-select">
						<option value="1" <?php echo ($coupon->percentot == 1 ? 'selected="selected"' : ''); ?>>%</option>
						<option value="2" <?php echo ($coupon->percentot == 2 ? 'selected="selected"' : ''); ?>><?php echo VREFactory::getCurrency()->getSymbol(); ?></option>
					</select>
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON4')); ?>
				<div class="field-value currency">
					<input type="number" name="value" size="20" value="<?php echo $coupon->value; ?>" min="0" step="any" />

					<span><?php echo VREFactory::getCurrency()->getSymbol(); ?></span>
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON5')); ?>
				<div class="vre-calendar-wrapper">
					<input type="text" name="datestart" id="vrdatestart" class="vre-calendar" size="20" value="<?php echo $coupon->datevalid[0]; ?>" />
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON6')); ?>
				<div class="vre-calendar-wrapper">
					<input type="text" name="dateend" id="vrdateend" class="vre-calendar" size="20" value="<?php echo $coupon->datevalid[1]; ?>" />
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<?php echo $vik->openControl(JText::_('VRMANAGECOUPON' . ($coupon->group == 0 ? '8' : '9')), 'vr-minvalue-row'); ?>
				<input type="number" name="minvalue" size="20" value="<?php echo (float) $coupon->minvalue; ?>" min="<?php echo ($coupon->group == 0 ? '1' : '0'); ?>" step="<?php echo ($coupon->group == 0 ? '1' : 'any'); ?>" />
			<?php echo $vik->closeControl(); ?>

			<?php if ($this->operator->get('group') == 0) { ?>

				<?php echo $vik->openControl(JText::_('VRMANAGECOUPON10')); ?>
					<div class="vre-select-wrapper">
						<select name="group" class="vre-select" id="vr-group-sel">
							<?php
							if ($config->getBool('enablerestaurant'))
							{
								?>
								<option value="0" <?php echo ($coupon->group == 0 ? 'selected="selected"' : ''); ?>><?php echo JText::_('VRORDERRESTAURANT'); ?></option>
								<?php
							}

							if ($config->getBool('enabletakeaway'))
							{
								?>
								<option value="1" <?php echo ($coupon->group == 1 ? 'selected="selected"' : ''); ?>><?php echo JText::_('VRORDERTAKEAWAY'); ?></option>
								<?php
							}
							?>
						</select>
					</div>
				<?php echo $vik->closeControl(); ?>

			<?php } else { ?>

				<input type="hidden" name="group" value="<?php echo ($this->operator->get('group') - 1); ?>" />

			<?php } ?>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
	
	<input type="hidden" name="id" value="<?php echo $coupon->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="opmanagecoupon" />
	<input type="hidden" name="option" value="com_vikrestaurants"/>
	<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
</form>

<?php
JText::script('VRMANAGECOUPON8');
JText::script('VRMANAGECOUPON9');
?>

<script>

	jQuery(document).ready(function() {
	
		jQuery('#vr-group-sel').on('change', function(){

			var input = jQuery('input[name="minvalue"]');

			if (jQuery(this).val() == '0') {
				input.prop('step', 1);
				input.prop('min', 1);

				input.val(Math.max(1, parseInt(input.val())));

				jQuery('.vr-minvalue-row b').text(Joomla.JText._('VRMANAGECOUPON8'));
			} else {
				input.prop('step', 'any');
				input.prop('min', 0);

				jQuery('.vr-minvalue-row b').text(Joomla.JText._('VRMANAGECOUPON9'));
			}

		});

	});

	var validator = new VikFormValidator('#vrmanageform', 'vrinvalid');

	function vrCloseCoupon() {
		Joomla.submitform('opcoupon.cancel', document.managecouponform);
	}
	
	function vrSaveCoupon(close) {
		if (validator.validate()) {
			var task = 'save';
			
			if (close) {
				task = 'saveclose';
			}

			Joomla.submitform('opcoupon.' + task, document.managecouponform);
		}
	}
	
</script>
