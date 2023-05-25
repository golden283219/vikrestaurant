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
 * VikRestaurants restaurant reservation form view.
 * Within this view is displayed the form to start
 * the table booking process.
 *
 * @since 1.0
 */
class VikRestaurantsViewrestaurants extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$config = VREFactory::getConfig();

		$args = array();
		$args['date']    = $input->get('date', '', 'string');
		$args['hourmin'] = $input->get('hourmin', '', 'string');
		$args['people']  = $input->get('people', 0, 'uint');

		// check if we have arguments set in request
		if ($args['date'])
		{
			if (empty($args['hourmin']))
			{
				/**
				 * Find first available time for the given date.
				 *
				 * @since 1.7.4
				 */
				$args['hourmin'] = VikRestaurants::getClosestTime($args['date'], $next = true);
			}
		}
		else
		{
			/**
			 * Find first available time.
			 * The $date argument is passed by reference and it will
			 * be modified by the method, if needed.
			 *
			 * @since 1.7.4
			 */
			$args['date']    = null;
			$args['hourmin'] = VikRestaurants::getClosestTime($args['date'], $next = true);
		}

		if (is_integer($args['date']))
		{
			// convert date timestamp to string
			$args['date'] = date($config->get('dateformat'), $args['date']);
		}
		
		/**
		 * An associative array containing the check-in details,
		 * such as: date, hourmin and people.
		 * 
		 * @var array
		 */
		$this->args = &$args;

		/**
		 * Flag used to check whether the customer already agreed
		 * that all the guests belong to the same family.
		 *
		 * @var   boolean
		 * @since 1.8
		 *
		 * @see   COVID-19
		 */
		$this->family = $app->getUserState('vre.search.family', false);

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}
}
