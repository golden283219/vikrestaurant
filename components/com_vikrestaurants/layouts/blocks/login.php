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

$canRegister 	= isset($displayData['register']) 	? $displayData['register'] 	: false;
$returnUrl 		= isset($displayData['return']) 	? $displayData['return'] 	: '';
$remember 		= isset($displayData['remember'])	? $displayData['remember']	: false;
$useCaptcha 	= isset($displayData['captcha']) 	? $displayData['captcha']	: null;
$gdpr 			= isset($displayData['gdpr'])		? $displayData['gdpr']		: null;
$footerLinks 	= isset($displayData['footer'])		? $displayData['footer'] 	: true;
$active			= isset($displayData['active'])		? $displayData['active']	: 'login';

$vik = VREApplication::getInstance();

if (is_null($useCaptcha))
{
	// check if 'recaptcha' is configured
	$useCaptcha = $vik->isCaptcha();
}

if (!$canRegister && $active == 'registration')
{
	// restore active tab to "login" as the registration is disabled
	$active = 'login';
}

if (is_null($gdpr))
{
	$config = VREFactory::getConfig();

	// gdpr setting not provided, get it from the global configuration
	$gdpr = $config->getBool('gdpr', false);

	/**
	 * Translate setting to support different URLs
	 * for several languages.
	 *
	 * @since 1.8
	 */
	$policy = VikRestaurants::translateSetting('policylink');
}

if ($footerLinks)
{
	// load com_users site language to display footer messages
	JFactory::getLanguage()->load('com_users', JPATH_SITE, JFactory::getLanguage()->getTag(), true);
}

if ($canRegister)
{
	?>

	<!-- REGISTRATION -->
	
	<script>

		var CAPTCHA_VALID = <?php echo $useCaptcha ? 0 : 1; ?>;

		jQuery(document).ready(function() {
			// register callback
			jQuery('#vre_dynamic_recaptcha_1').attr('data-callback', 'reCaptchaCallback');
		});

		function reCaptchaCallback() {
			CAPTCHA_VALID = 1;
		}

		function vrLoginValueChanged() {
			if (jQuery('input[name=loginradio]:checked').val() == 1) {
				jQuery('.vrregisterblock').hide();
				jQuery('.vrloginblock').show();
			} else {
				jQuery('.vrloginblock').hide()
				jQuery('.vrregisterblock').show();
			}
		}

		function vrValidateRegistrationFields() {
			var names = [
				"fname",
				"lname",
				"email",
				// "confemail",
				"username",
				"password",
				"confpassword",
			];

			var fields = {};

			var elem = null;
			var ok = true;

			for (var i = 0;  i < names.length; i++) {
				elem = jQuery('#vrregform input[name="' + names[i] + '"]');

				if (elem.length) {
					fields[names[i]] = elem.val();

					if (fields[names[i]].length > 0) {
						elem.removeClass('vrrequiredfield');
					} else {
						ok = false;
						elem.addClass('vrrequiredfield');
					}
				}
			}

			if (ok) {
				var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				if (!re.test(fields.email)) {
					ok = false;
					jQuery('#vrregform input[name="email"]').addClass('vrrequiredfield');
				}
			}

			if (ok) {
				if (fields.password !== fields.confpassword) {
					ok = false;
					jQuery('#vrregform input[name="password"], #vrregform input[name="confpassword"]').addClass('vrrequiredfield');
				}
			}

			if (CAPTCHA_VALID) {
				jQuery('#vre_dynamic_recaptcha_1').removeClass('vrinvalid');
			} else {
				jQuery('#vre_dynamic_recaptcha_1').addClass('vrinvalid');
				ok = false;
			}

			<?php if ($gdpr) { ?>

				if (jQuery('#gdpr-register').is(':checked')) {
					jQuery('#gdpr-register').next().removeClass('vrinvalid');
				} else {
					jQuery('#gdpr-register').next().addClass('vrinvalid');
					ok = false;
				}

			<?php } ?>

			return ok;
		}

	</script>

	<div class="vrloginradiobox" id="vrloginradiobox">
		<span class="vrloginradiosp">
			<label for="logradio1"><?php echo JText::_('VRLOGINRADIOCHOOSE1'); ?></label>
			<input type="radio" id="logradio1" name="loginradio" value="1" onChange="vrLoginValueChanged();" <?php echo $active == 'login' ? 'checked="checked"' : ''; ?> />
		</span>
		<span class="vrloginradiosp">
			<label for="logradio2"><?php echo JText::_('VRLOGINRADIOCHOOSE2'); ?></label>
			<input type="radio" id="logradio2" name="loginradio" value="2" onChange="vrLoginValueChanged();" <?php echo $active != 'login' ? 'checked="checked"' : ''; ?> />
		</span>
	</div>

	<div class="vrregisterblock" style="<?php echo $active != 'login' ? '' : 'display: none;'; ?>">
		<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants'); ?>" method="post" name="vrregform" id="vrregform">
			<h3><?php echo JText::_('VRREGISTRATIONTITLE'); ?></h3>
			
			<div class="vrloginfieldsdiv">

				<div class="vrloginfield">
					<span class="vrloginsplabel" id="vrfname">
						<label for="register-fname"><?php echo JText::_('VRREGNAME'); ?><sup>*</sup>:</label>
					</span>
					<span class="vrloginspinput">
						<input id="register-fname" type="text" name="fname" value="" size="20" class="vrinput" />
					</span>
				</div>

				<div class="vrloginfield">
					<span class="vrloginsplabel" id="vrlname">
						<label for="register-lname"><?php echo JText::_('VRREGLNAME'); ?><sup>*</sup>:</label>
					</span>
					<span class="vrloginspinput">
						<input id="register-lname" type="text" name="lname" value="" size="20" class="vrinput" />
					</span>
				</div>

				<div class="vrloginfield">
					<span class="vrloginsplabel" id="vremail">
						<label for="register-email"><?php echo JText::_('VRREGEMAIL'); ?><sup>*</sup>:</label>
					</span>
					<span class="vrloginspinput">
						<input id="register-email" type="text" name="email" value="" size="20" class="vrinput" />
					</span>
				</div>

				<div class="vrloginfield">
					<span class="vrloginsplabel" id="vrusername">
						<label for="register-username"><?php echo JText::_('VRREGUNAME'); ?><sup>*</sup>:</label>
					</span>
					<span class="vrloginspinput">
						<input id="register-username" type="text" name="username" value="" size="20" class="vrinput" />
					</span>
				</div>

				<div class="vrloginfield">
					<span class="vrloginsplabel" id="vrpassword">
						<label for="register-password"><?php echo JText::_('VRREGPWD'); ?><sup>*</sup>:</label>
					</span>
					<span class="vrloginspinput">
						<input id="register-password" type="password" name="password" value="" size="20" class="vrinput" />
					</span>
				</div>

				<div class="vrloginfield">
					<span class="vrloginsplabel" id="vrconfpassword">
						<label for="register-confpassword"><?php echo JText::_('VRREGCONFIRMPWD'); ?><sup>*</sup>:</label>
					</span>
					<span class="vrloginspinput">
						<input id="register-confpassword" type="password" name="confpassword" value="" size="20" class="vrinput" />
					</span>
				</div>

				<div class="vrloginfield">
					<?php
					if ($useCaptcha)
					{
						echo $vik->reCaptcha();
					}
					?>
				</div>

				<?php
				if ($gdpr)
				{
					?>
					<div class="vrloginfield">
						<!--<span class="vrloginsplabel" class="">&nbsp;</span>-->
						<span class="vrloginspinput">
							<input type="checkbox" class="required" id="gdpr-register" value="1" />
							<label for="gdpr-register" style="display: inline-block;">
								<?php
								if ($policy)
								{
									JHtml::_('vrehtml.assets.fancybox');

									// label with link to read the privacy policy
									echo JText::sprintf(
										'GDPR_POLICY_AUTH_LINK',
										'javascript: void(0);',
										'vreOpenPopup(\'' . $policy . '\');'
									);
								}
								else
								{
									// label without link
									echo JText::_('GDPR_POLICY_AUTH_NO_LINK');
								}
								?>
							</label>
						</span>
					</div>
					<?php
				}
				?>

				<div class="vrloginfield">
					<span class="vrloginsplabel">&nbsp;</span>
					<span class="vrloginspinput">
						<button type="submit" class="vrbooknow" name="registerbutton" onClick="return vrValidateRegistrationFields();"><?php echo JText::_('VRREGSIGNUPBTN'); ?></button>
					</span>
				</div>
			</div>
	
			<input type="hidden" name="option" value="com_vikrestaurants" />
			<input type="hidden" name="task" value="registeruser" />
			<input type="hidden" name="return" value="<?php echo base64_encode($returnUrl); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>

<?php } ?>

<!-- LOGIN -->

<div class="vrloginblock" style="<?php echo $active == 'login' ? '' : 'display: none;'; ?>">
	<?php
	/**
	 * The login form is displayed from the layout below:
	 * /components/com_vikrestaurants/layouts/blocks/login/[PLATFORM_NAME].php
	 * which depends on the current platform ("joomla" or "wordpress").
	 *
	 * @since 1.8
	 */
	echo $this->sublayout(VersionListener::getPlatform(), $displayData);
	?>
</div>
