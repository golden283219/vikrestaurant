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
 * Template file used to display a login/registration form
 * to allow the users to access their accounts.
 *
 * @since 1.7.4
 */

// take only the needed arguments
$args = array();

foreach ($this->args as $k => $v)
{
	if (in_array($k, array('date', 'hourmin', 'people', 'table')))
	{
		$args[$k] = $v;
	}
}

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$data = array(
	/**
	 * @param 	boolean  register 	True to enable the registration form, otherwise false.
	 * 								If not provided, the registration is disabled by default.
	 */
	'register' => VREFactory::getConfig()->getBool('enablereg', false),

	/**
	 * @param 	string  return 		The return URL used after the login.
	 *								The URL must be plain (non-routed).
	 */
	'return' => 'index.php?option=com_vikrestaurants&view=confirmres&' . http_build_query($this->args) . ($itemid ? '&Itemid=' . $itemid : ''),

	/**
	 * @param 	boolean  remember 	True to remember the user after the login (an authentication
	 * 								cookie will be created to avoid re-logging in from the browser used).
	 * 								False to allow the customers to choose to remember the login or not.
	 * 								If not provided, the remember option is disabled by default.
	 */
	'remember' => false,

	/**
	 * @param 	boolean  captcha 	True to use the reCAPTCHA within the registration form 
	 * 								to prevent bots to create mass accounts. False to disable captcha.
	 * 								If not provided, it will be used the value specified 
	 * 								in the configuration of com_users.
	 */
	// 'captcha' => true,

	/**
	 * @param 	boolean  gdpr 	  	True to place a disclaimer for GDPR European law, otherwise false.
	 * 								If not provided, the value will be retrived from the global configuration.
	 */
	// 'gdpr' => false,

	/**
	 * @param 	boolean  footer 	True to display the footer links to allow the users
	 * 								to recover the password and the name of the account.
	 * 								If not provided, the links are not displayed.
	 */
	'footer' => true,

	/**
	 * @param 	string  active 		The name of the active tab.
	 *								The accepted values are: "login" and "registration".
	 * 								If not provided, the login form will be active by default.
	 *								In case this value is set to "registration" and the "register"
	 * 								field is disabled, the active value will be reset to "login".
	 */
	// 'active' => 'login',
);

/**
 * The login form is displayed from the layout below:
 * /components/com_vikrestaurants/layouts/blocks/login.php
 * 
 * If you need to change something from this layout, just create
 * an override of this layout by following the instructions below:
 * - open the back-end of your Joomla
 * - visit the Extensions > Templates > Templates page
 * - edit the active template
 * - access the "Create Overrides" tab
 * - select Layouts > com_vikrestaurants > blocks
 * - start editing the login.php file on your template to create your own layout
 *
 * @since 1.7.4
 */
echo JLayoutHelper::render('blocks.login', $data);
