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

$returnUrl 		= isset($displayData['return']) 	? $displayData['return'] 	: '';
$remember 		= isset($displayData['remember'])	? $displayData['remember']	: false;
$footerLinks 	= isset($displayData['footer'])		? $displayData['footer'] 	: true;

$vik = VREApplication::getInstance();

?>

<form action="<?php echo JRoute::_('index.php?option=com_users'); ?>" method="post" name="vrloginform" id="vrloginform">
	<h3><?php echo JText::_('VRLOGINTITLE'); ?></h3>

	<div class="vrloginfieldsdiv">

		<div class="vrloginfield">
			<span class="vrloginsplabel">
				<label for="login-username"><?php echo JText::_('VRLOGINUSERNAME'); ?></label>
			</span>
			<span class="vrloginspinput">
				<input id="login-username" type="text" name="username" value="" size="20" class="vrlogininput" />
			</span>
		</div>

		<div class="vrloginfield">
			<span class="vrloginsplabel">
				<label for="login-password"><?php echo JText::_('VRLOGINPASSWORD'); ?></label>
			</span>
			<span class="vrloginspinput">
				<input id="login-password" type="password" name="password" value="" size="20" class="vrlogininput" />
			</span>
		</div>

		<?php if ($remember) { ?>

			<input type="hidden" name="remember" id="remember" value="yes" />

		<?php } else { ?>

			<div class="login-fields-rem">
				<label for="login-remember"><?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME'); ?></label>
				<input id="login-remember" type="checkbox" name="remember" class="inputbox" value="yes" alt="<?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME'); ?>" />
			</div>

		<?php } ?>

		<div class="vrloginfield">
			<span class="vrloginsplabel">&nbsp;</span>
			<span class="vrloginspinput">
				<button type="submit" class="vrloginbutton" name="Login"><?php echo JText::_('VRLOGINSUBMIT'); ?></button>
			</span>
		</div>
	</div>

	
	<?php if ($footerLinks) { ?>

		<div class="vr-login-footer-links">
			<div>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" target="_blank">
					<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>
				</a>
			</div>
			<div>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" target="_blank">
					<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?>
				</a>
			</div>
		</div>

	<?php } ?>

	<input type="hidden" name="return" value="<?php echo base64_encode($vik->routeForExternalUse($returnUrl, false)); ?>" />
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<?php echo JHtml::_('form.token'); ?>
</form>
