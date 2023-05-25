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
JHtml::_('vrehtml.assets.intltel', '[name="phone_number"]');
JHtml::_('vrehtml.assets.fontawesome');

$operator = $this->operator;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->bootStartTabSet('operator', array('active' => $this->getActiveTab('operator_details'), 'cookie' => $this->getCookieTab()->name)); ?>

		<!-- DETAILS -->

		<?php echo $vik->bootAddTab('operator', 'operator_details', JText::_('VRMAPDETAILSBUTTON')); ?>
	
			<div class="row-fluid">

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VROPERATORFIELDSET1'), 'form-horizontal'); ?>
						
						<!-- CODE - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR1')); ?>
							<input type="text" name="code" value="<?php echo $operator->code; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>
				
						<!-- FIRST NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR2')); ?>
							<input class="required" type="text" name="firstname" value="<?php echo $operator->firstname; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>
						
						<!-- LAST NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR3')); ?>
							<input class="required" type="text" name="lastname" value="<?php echo $operator->lastname; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>
						
						<!-- EMAIL - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR5')); ?>
							<input class="required" type="email" name="email" value="<?php echo $operator->email; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>

						<!-- PHONE NUMBER - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR4')); ?>
							<input type="tel" name="phone_number" value="<?php echo $operator->phone_number; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>

						<!-- GROUP - Dropdown -->
						<?php
						$groups = JHtml::_('vrehtml.admin.groups', array(1, 2), true, '');

						echo $vik->openControl(JText::_('VRMANAGECUSTOMF7')); ?>
							<select name="group" id="vr-group-sel">
								<?php echo JHtml::_('select.options', $groups, 'value', 'text', $operator->group, true); ?>
							</select>
						<?php echo $vik->closeControl(); ?>
						
					<?php echo $vik->closeFieldset(); ?>
				</div>
			
				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VROPERATORFIELDSET2'), 'form-horizontal'); ?>
						
						<!-- JOOMLA USER - Dropdown -->
						<?php
						$options = array();
						$options[0] = array(JHtml::_('select.option', '', ''));

						foreach ($this->users as $user)
						{
							if (!isset($options[$user->title]))
							{
								$options[$user->title] = array();
							}

							$options[$user->title][] = JHtml::_('select.option', $user->id, $user->name);
						}

						$args = array(
							'id' 			=> 'vr-users-sel',
							'group.items' 	=> null,
							'list.select'	=> $operator->jid,
						);

						echo $vik->openControl(JText::_('VRMANAGEOPERATOR7'));
						echo JHtml::_('select.groupedList', $options, 'jid', $args);
						echo $vik->closeControl();
						?>
				
						<!-- USER GROUP - Dropdown -->
						<?php
						$groups = array();
						$groups = array_merge($groups, JHtml::_('user.groups', true));

						// remove hiphens used to create the tree structure of the groups
						$groups = array_map(function($group)
						{
							$group->text = preg_replace("/^(-\s?)+/", "", $group->text);

							return $group;
						}, $groups);

						$control = array();
						$control['style'] = empty($operator->jid) ? '' : 'display:none;';
						
						echo $vik->openControl(JText::_('VRMANAGEOPERATOR10'), 'vruserfield', $control); ?>
							<select name="usertype[]" id="vr-usertypes-sel" multiple>
								<?php echo JHtml::_('select.options', $groups, 'value', 'text', $operator->usertype); ?>
							</select>
						<?php echo $vik->closeControl(); ?>
						
						<!-- USERNAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR11'), 'vruserfield', $control); ?>
							<input class="maybe-required <?php echo (empty($operator->jid) ? 'required' : ''); ?>" type="text" name="username" value="<?php echo $operator->username; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>
						
						<!-- PASSWORD - Password -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR12'), 'vruserfield', $control); ?>
							<input class="maybe-required <?php echo (empty($operator->jid) ? 'required' : ''); ?>" type="password" name="password" value="" size="40" />
						<?php echo $vik->closeControl(); ?>
						
						<!-- CONFIRM PASSWORD - Password -->
						<?php echo $vik->openControl(JText::_('VRMANAGEOPERATOR13'), 'vruserfield', $control); ?>
							<input class="maybe-required <?php echo (empty($operator->jid) ? 'required' : ''); ?>" type="password" name="confpassword" value="" size="40" />
						<?php echo $vik->closeControl(); ?>
						
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<!-- ACTIONS -->

		<?php echo $vik->bootAddTab('operator', 'operator_actions', JText::_('VRMAPACTIONSBUTTON')); ?>

			<div class="row-fluid">

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VROPERATORFIELDSET3'), 'form-horizontal'); ?>

						<!-- CAN LOGIN - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $operator->can_login, 'onclick="canLoginValueChanged(1);"');
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$operator->can_login, 'onclick="canLoginValueChanged(0);"');
						
						echo $vik->openControl(JText::_('VRMANAGEOPERATOR6'));
						echo $vik->radioYesNo('can_login', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>
						
						<!-- KEEP TRACK - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $operator->keep_track);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$operator->keep_track);
						
						echo $vik->openControl(JText::_('VRMANAGEOPERATOR16'));
						echo $vik->radioYesNo('keep_track', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>
						
						<!-- MAIL NOTIFICATIONS - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $operator->mail_notifications);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$operator->mail_notifications);
						
						echo $vik->openControl(JText::_('VRMANAGEOPERATOR15'));
						echo $vik->radioYesNo('mail_notifications', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

				<div class="span6 livemap-block" style="<?php echo ($operator->can_login ? '' : 'display:none;"'); ?>">
					<?php echo $vik->openFieldset(JText::_('VROPERATORFIELDSET4'), 'form-horizontal'); ?>

						<!-- SEE ALL RESERVATIONS - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $operator->allres);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$operator->allres);
						
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGEOPERATOR18'),
							'content' => JText::_('VRMANAGEOPERATOR18_DESC'),
						));

						echo $vik->openControl(JText::_('VRMANAGEOPERATOR18') . $help);
						echo $vik->radioYesNo('allres', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- SELF ASSIGNMENT - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $operator->assign);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$operator->assign);
						
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGEOPERATOR19'),
							'content' => JText::_('VRMANAGEOPERATOR19_DESC'),
						));

						echo $vik->openControl(JText::_('VRMANAGEOPERATOR19') . $help);
						echo $vik->radioYesNo('assign', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- ASSIGNED ROOMS - Select -->
						<?php
						$options = JHtml::_('vikrestaurants.rooms', true);
						
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGEOPERATOR20'),
							'content' => JText::_('VRMANAGEOPERATOR20_DESC'),
						));

						echo $vik->openControl(JText::_('VRMANAGEOPERATOR20') . $help); ?>					
							<select name="rooms[]" id="vr-rooms-sel" multiple>
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $operator->rooms); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- ASSIGNED Products - Select -->
						<?php
						$options = JHtml::_('vikrestaurants.tags', 'products');
						
						$help = $vik->createPopover(array(
							'title'   => JText::_('VRMANAGEOPERATOR21'),
							'content' => JText::_('VRMANAGEOPERATOR21_DESC'),
						));

						echo $vik->openControl(JText::_('VRMANAGEOPERATOR21') . $help); ?>					
							<select name="products[]" id="vr-products-sel" multiple>
								<?php echo JHtml::_('select.options', $options, 'name', 'name', $operator->products); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

						<!-- MANAGE COUPON - Radio Button -->
						<?php
						$options = array(
							JHtml::_('select.option', 0, 'VROPCOUPONOPT0'),
							JHtml::_('select.option', 1, 'VROPCOUPONOPT1'),
							JHtml::_('select.option', 2, 'VROPCOUPONOPT2'),
						);
						
						echo $vik->openControl(JText::_('VRMANAGEOPERATOR17')); ?>
							<select name="manage_coupon" id="vr-managecoupon-sel">
								<?php echo JHtml::_('select.options', $options, 'value', 'text', $operator->manage_coupon, true); ?>
							</select>
						<?php echo $vik->closeControl(); ?>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<?php
		/**
		 * Trigger event to display custom HTML.
		 * In case it is needed to include any additional fields,
		 * it is possible to create a plugin and attach it to an event
		 * called "onDisplayViewOperator". The event method receives the
		 * view instance as argument.
		 *
		 * @since 1.8
		 */
		$custom = $this->onDisplayManageView();

		if ($custom)
		{
			echo $vik->bootAddTab('operator', 'operator_custom', JText::_('VRE_CUSTOM_FIELDSET'));
			echo $custom;
			echo $vik->bootEndTab();
		}
		?>

	<?php echo $vik->bootEndTabSet(); ?>
	
	<input type="hidden" name="id" value="<?php echo $operator->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRE_FILTER_SELECT_GROUP');
JText::script('VRMANAGEOPERATOR9');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		jQuery('#vr-group-sel').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_GROUP'),
			allowClear: true,
			width: 200,
		});

		jQuery('#vr-users-sel').select2({
			placeholder: Joomla.JText._('VRMANAGEOPERATOR9'),
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-rooms-sel, #vr-products-sel').select2({
			placeholder: false,
			allowClear: true,
			width: 300,
		});

		jQuery('#vr-usertypes-sel, #vr-managecoupon-sel').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 300,
		});

		jQuery('#vr-users-sel').on('change', function() {
			if (jQuery('#vr-users-sel').val().length == 0) {
			
				jQuery('.maybe-required').each(function() {
					if (!jQuery(this).hasClass('required')) {
						jQuery(this).addClass('required');
					}
				});

				jQuery('.vruserfield').show();
			} else {

				jQuery('.maybe-required').each(function() {
					if (jQuery(this).hasClass('required')) {
						jQuery(this).removeClass('required');
					}
				});

				jQuery('.vruserfield').hide();
			}
		});
	});

	function canLoginValueChanged(is) {
		if (is) {
			jQuery('.livemap-block').show();
		} else {
			jQuery('.livemap-block').hide();
		}
	}

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate(vrValidatePassword)) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

	function vrValidatePassword() {
		var pass = [];
		pass[0] = jQuery('input[name="password"]');
		pass[1] = jQuery('input[name="confpassword"]');

		if (pass[0].hasClass('required') && pass[0].val() != pass[1].val()) {
			validator.setInvalid(pass[0]);
			validator.setInvalid(pass[1]);

			return false;
		}

		validator.unsetInvalid(pass[0]);
		validator.unsetInvalid(pass[1]);

		return true;
	}
	
</script>
