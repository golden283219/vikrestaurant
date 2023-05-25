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

$user = $this->user;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->bootStartTabSet('apiuser', array('active' => $this->getActiveTab('apiuser_details'), 'cookie' => $this->getCookieTab()->name)); ?>

		<!-- DETAILS -->
			
		<?php echo $vik->bootAddTab('apiuser', 'apiuser_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<div class="row-fluid">

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMANAGEAPIUSER8')); ?>
						
						<!-- APPLICATION NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEAPIUSER2')); ?>
							<input type="text" name="application" value="<?php echo $this->escape($user->application); ?>" size="40" />
						<?php echo $vik->closeControl(); ?>

						<!-- USERNAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEAPIUSER3') . '*'); ?>
							<input type="text" name="username" class="required" value="<?php echo $user->username; ?>" size="40" />
						<?php echo $vik->closeControl(); ?>

						<!-- USERNAME REGEX - Label -->
						<?php
						$control = array();
						$control['idparent'] = 'user-regex';
						$control['style']    = 'display:none;';

						echo $vik->openControl('', 'vr-user-regex', $control); ?>
							<span style="color:#900;font-size:95%;"><?php echo JText::_('VRAPIUSERUSERNAMEREGEX'); ?></span>
						<?php echo $vik->closeControl(); ?>

						<!-- PASSWORD - Password -->
						<?php echo $vik->openControl(JText::_('VRMANAGEAPIUSER4') . '*'); ?>
							<div class="input-append">
								<input type="password" name="password" class="required" value="<?php echo $user->password; ?>" size="40" />

								<button type="button" class="btn" id="pwd-reveal-btn">
									<i class="fas fa-eye"></i>
								</button>
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- PASSWORD REGEX - Label -->
						<?php
						$control = array();
						$control['idparent'] = 'pwd-regex';
						$control['style']    = 'display:none;';

						echo $vik->openControl('', 'vr-pwd-regex', $control); ?>
							<span style="color:#900;font-size:95%;"><?php echo JText::_('VRAPIUSERPASSWORDREGEX'); ?></span>
						<?php echo $vik->closeControl(); ?>

						<!-- GENERATE PASSWORD - Button -->
						<?php echo $vik->openControl(''); ?>
							<button type="button" class="btn" onclick="generatePassword();"><?php echo JText::_('VRMANAGECUSTOMER17'); ?></button>
						<?php echo $vik->closeControl(); ?>
						
						<!-- ACTIVE - Number -->
						<?php
						$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $user->active);
						$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$user->active);
						
						echo $vik->openControl(JText::_('VRMANAGEAPIUSER6'));
						echo $vik->radioYesNo('active', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>
					
					<?php echo $vik->closeFieldset(); ?>
				</div>

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMANAGEAPIUSER5')); ?>

						<div class="vr-ips-container" id="ips-container">

							<?php
							foreach ($user->ips as $k => $ip)
							{
								?>
								<div class="control-group ip-address" id="ipaddr<?php echo $k; ?>">
									<?php
									$parts = explode('.', $ip);

									for ($i = 0; $i < 4; $i++)
									{
										if ($i > 0)
										{
											?><span class="ip-dot">.</span><?php
										}
										?>
										<input type="text" name="ip[<?php echo $k; ?>][]" value="<?php echo intval($parts[$i]); ?>" size="3" maxlength="3" onkeypress="return handleTypeEventIP(event);" onchange="checkBoxIP(this);" />
										<?php
									}
									?>

									<a href="javascript: void(0);" onclick="removeIP(<?php echo $k; ?>);">
										<i class="fas fa-times big" style="margin-left: 10px;"></i>
									</a>
								</div>
								<?php
							}
							?>

						</div>

						<?php
						echo $vik->alert(
							JText::_('VRE_APIUSER_EMPTY_IPS_NOTICE'),
							'info',
							$dismissible = false,
							array(
								'id'    => 'no-ip-notice',
								'style' => $user->ips ? 'display:none;' : '',
							)
						);
						?>

						<div class="control-group" id="ips-container">
							<button type="button" class="btn" onclick="addIP();"><?php echo JText::_('VRMANAGEAPIUSER9'); ?></button>
						</div>

					<?php echo $vik->closeFieldset(); ?>
				</div>

				<?php
				/**
				 * Trigger event to display custom HTML.
				 * In case it is needed to include any additional fields,
				 * it is possible to create a plugin and attach it to an event
				 * called "onDisplayViewApiuser". The event method receives the
				 * view instance as argument.
				 *
				 * @since 1.8
				 */
				$custom = $this->onDisplayManageView();

				if ($custom)
				{
					?>
					<div class="span6">
						<?php
						echo $vik->openFieldset(JText::_('VRE_CUSTOM_FIELDSET'));
						echo $custom;
						echo $vik->closeFieldset();
						?>
					</div>
					<?php
				}
				?>

			</div>

		<?php echo $vik->bootEndTab(); ?>

		<!-- PLUGINS -->
			
		<?php echo $vik->bootAddTab('apiuser', 'apiuser_plugins', JText::_('VRMANAGEAPIUSER21')); ?>

			<div class="row-fluid">

				<div class="span12">
					<?php echo $vik->openEmptyFieldset(); ?>

						<?php echo $vik->openControl(''); ?>

							<button type="button" class="btn" onclick="allowAllRules(1);"><?php echo JText::_('VRINVSELECTALL'); ?></button>
							<button type="button" class="btn" onclick="allowAllRules(0);"><?php echo JText::_('VRINVSELECTNONE'); ?></button>

						<?php echo $vik->closeControl(); ?>

						<?php
						foreach ($this->plugins as $plugin)
						{
							?>
							<!-- PLUGIN - Dropdown -->
							<?php
							$is_allowed = (int) ($plugin->alwaysAllowed() || !in_array($plugin->getName(), $user->denied));

							$elements = array(
								JHtml::_('select.option', 1, 'VRALLOWED'),
								JHtml::_('select.option', 0, 'VRDENIED'),
							);
							
							echo $vik->openControl($plugin->getTitle(), 'multi-field with-icon'); ?>
								<select name="plugin[<?php echo $plugin->getName(); ?>]" class="vr-plugin-rules" <?php echo $plugin->alwaysAllowed() ? 'disabled="disabled"' : ''; ?>>
									<?php echo JHtml::_('select.options', $elements, 'value', 'text', $is_allowed, true); ?>
								</select>

								&nbsp;<i class="fas fa-<?php echo ($is_allowed ? 'check' : 'ban'); ?> big" style="color:#<?php echo ($is_allowed ? '090' : '900'); ?>;"></i>
							<?php echo $vik->closeControl(); ?>

							<?php
						}
						?>

					<?php echo $vik->closeEmptyFieldset(); ?>
				</div>

			</div>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>
	

	<input type="hidden" name="id" value="<?php echo $user->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">

	jQuery(document).ready(function(){

		jQuery('.vr-plugin-rules').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 200,
		});

		jQuery('#pwd-reveal-btn').on('click', function() {
			revealPassword(this);
		});

		jQuery('#ips-container .ip-address input').on('paste', handlePasteEventIP);

	});

	var IP_COUNT = <?php echo count($user->ips); ?>;

	function addIP() {

		var html = '';
		for (var i = 0; i < 4; i++) {
			if (i > 0) {
				html += '<span class="ip-dot">.</span>\n';
			}

			html += '<input type="text" name="ip[' + IP_COUNT + '][]" class="form-control" value="" size="3" maxlength="3" onkeypress="return handleTypeEventIP(event);" onchange="checkBoxIP(this);" />\n';
		}

		jQuery('#ips-container').append(
			'<div class="control-group ip-address" id="ipaddr' + IP_COUNT + '">\n' + html + '\n'+
				'<a href="javascript: void(0);" onclick="removeIP(' + IP_COUNT + ');">\n'+
					'<i class="fas fa-times big" style="margin-left: 10px;"></i>\n'+
				'</a>\n'+
			'</div>\n'
		);

		jQuery('#ipaddr' + IP_COUNT).find('input').on('paste', handlePasteEventIP);

		// hide notice when adding an IP
		jQuery('#no-ip-notice').hide();

		IP_COUNT++;
	}

	function removeIP(id) {
		jQuery('#ipaddr' + id).remove();

		// show notice again when deleting the last IP
		if (jQuery('.ip-address').length == 0) {
			jQuery('#no-ip-notice').show();
		}
	}

	function checkBoxIP(input) {
		var val = parseInt(jQuery(input).val());

		if (val < 0) {
			jQuery(input).val(0);
		} else if (val > 255) {
			jQuery(input).val(255);
		}
	}

	function revealPassword(btn) {
		var icon = jQuery(btn).find('i');

		if (icon.hasClass('fa-eye')) {
			jQuery('input[name="password"]').attr('type', 'text');

			icon.removeClass('fa-eye').addClass('fa-eye-slash');
		} else {
			jQuery('input[name="password"]').attr('type', 'password');

			icon.removeClass('fa-eye-slash').addClass('fa-eye');
		}
	}

	function generatePassword() {
		var password = buildPassword(8, 128, 1, 1, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');

		jQuery('input[name="password"]').val(password);

		if (jQuery('input[name="password"]').attr('type') == 'password') {
			jQuery('#pwd-reveal-btn').trigger('click');
		}

		jQuery('#pwd-regex').hide();
	}

	function buildPassword(min_length, max_length, min_digits, min_uppercase, chars_str) {
		var pwd = '';

		var len = Math.min(24, ( min_length + max_length ) / 2); 

		var i;

		for (i = 0; i < min_digits; i++) {
			pwd += '' + Math.floor(Math.random() * 10);
		}

		for (i = 0; i < min_uppercase; i++) {
			pwd += String.fromCharCode(65 + Math.floor(Math.random() * 26));
		}

		for (i = pwd.length; i < len; i++) {
			pwd += chars_str.charAt(Math.floor(Math.random() * chars_str.length));
		}

		return pwd.shuffle();
	}

	String.prototype.shuffle = function () {
		var a = this.split("");

		for (var i = a.length - 1, j = 0, tmp = 0; i >= 0; i--) {
			j = Math.floor(Math.random() * (i + 1));
			
			tmp = a[i];
			a[i] = a[j];
			a[j] = tmp;
		}

		return a.join('');
	}

	function matchPassword() {
		var pwdInput = jQuery('input[name="password"]');
		var pwd      = pwdInput.val();

		if (typeof pwd !== 'string') {
			pwd = '';
		}

		if (pwd.match(/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!?@#$%_.\-{\[()\]}]{8,128}$/)) {
			validator.unsetInvalid(pwdInput);
			jQuery('#pwd-regex').hide();
			return true;
		}

		validator.setInvalid(pwdInput);
		jQuery('#pwd-regex').show();
		return false;
	}

	function matchUsername() {
		var userInput = jQuery('input[name="username"]');
		var user      = userInput.val();

		if (typeof user !== 'string') {
			user = '';
		}

		if (user.match(/^[0-9A-Za-z._]{3,128}$/)) {
			validator.unsetInvalid(userInput);
			jQuery('#user-regex').hide();
			return true;
		}

		validator.setInvalid(userInput);
		jQuery('#user-regex').show();
		return false;
	}

	function allowAllRules(is) {
		jQuery('select.vr-plugin-rules:not(:disabled)').val(is).trigger('change');
	}

	function handlePasteEventIP(event) {
		var text = event.originalEvent.clipboardData.getData('text');
			
		var chunks = text.split(/\./g);

		var _input = this;

		if (chunks.length == 4) {
			jQuery(_input).parent().find('input').each(function() {
				jQuery(this).val(chunks.shift());
			});
		}

		if (!text.match(/^\d+$/)) {
			// prevent copy in case of non integer
			event.preventDefault();
			event.stopPropagation();

			return false;
		}
	}

	function handleTypeEventIP(event) {
		if (event.ctrlKey || event.metaKey) {
			// always accept in case of CTRL or CMD
			return true;
		}

		if (event.keyCode == 46) {
			// "." clicked, go to next input
			jQuery(event.target).nextAll('input').first().focus();
		}

		return event.keyCode >= 48 && event.keyCode <= 57;
	}

	// validate

	var validator = new VikFormValidator('#adminForm');
	validator.addCallback(matchUsername);
	validator.addCallback(matchPassword);

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
