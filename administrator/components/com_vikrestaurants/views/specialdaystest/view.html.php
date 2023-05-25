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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants special days test view.
 *
 * @since 1.8.2
 */
class VikRestaurantsViewspecialdaystest extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app    = JFactory::getApplication();
		$config = VREFactory::getConfig();

		$args = array();

		$args['group'] = $app->getUserStateFromRequest('vre.specialdaystest.group', 'group', '', 'string');
		$args['date']  = $app->getUserStateFromRequest('vre.specialdaystest.date', 'date', '', 'string');

		// make sure the group is supported
		$args['group'] = JHtml::_('vrehtml.admin.getgroup', $args['group'], array('restaurant', 'takeaway'));

		// if not specified, use the current date
		if (!$args['date'])
		{
			$args['date'] = date($config->get('dateformat'), VikRestaurants::now());
		}

		$this->args = &$args;
		
		// display the template (default.php)
		parent::display($tpl);
	}
}
