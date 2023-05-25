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
 * VikRestaurants menus list view.
 * The menus of restaurant will be filtered by date
 * in case the Search Bar is turned on.
 *
 * @since 1.0
 */
class VikRestaurantsViewmenuslist extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		$config = VREFactory::getConfig();
		
		$filters = array();
		$filters['date']  = $input->get('date', '', 'string');
		$filters['shift'] = $input->get('shift', 0, 'uint');
		$filters['hour']  = $input->get('hour', null, 'uint');
		
		if (!$filters['date'] || $filters['date'] == $dbo->getNullDate())
		{
			$filters['date'] = date($config->get('dateformat'), VikRestaurants::now());
		}

		// check if the search bar should be displayed
		$show_search_form = $app->getUserStateFromRequest('vre.menuslist.showsearchbar', 'show_search_bar', false, 'bool');
		$show_search_form = $input->get('tmpl') != 'component' && $show_search_form;
		
		$menus = array();

		$hour = $filters['hour'];

		if (is_null($hour) && $filters['shift'])
		{
			$time = JHtml::_('vikrestaurants.timeofshift', (int) $filters['shift']);

			if ($time)
			{
				// use from hour of selected working shift
				$hour = $time->fromhour;
			}
		}
			
		/**
		 * Do not use the date filter if search bar is turned off.
		 * This will retrieve all the menus also for closing days.
		 *
		 * Do not use time filter in case the hour/shift was not specified.
		 *
		 * @since  1.8
		 */
		$args = array(
			'date'    => $show_search_form ? $filters['date'] : null,
			'hourmin' => $show_search_form && $hour ? $hour . ':0' : null,
		);
		
		$menus = VikRestaurants::getAllAvailableMenusOn($args);

		/**
		 * Filter the menus to display only the selected ones.
		 *
		 * @since 1.7.4
		 */
		$ids = $input->get('id_menus', array(), 'uint');

		if (count($ids))
		{
			// unset the menus that are not within the list
			$menus = array_filter($menus, function($menu) use ($ids)
			{
				return in_array($menu->id, $ids);
			});

			// do not preserve the keys
			$menus = array_values($menus);
		}

		/**
		 * Set printable menus setting in user state for
		 * being re-used in other views.
		 *
		 * @since 1.8
		 */
		$app->getUserStateFromRequest('vre.menuslist.printable', 'printable_menus', false, 'bool');

		// translate menus in case multi-lingual is supported
		VikRestaurants::translateMenus($menus);

		/**
		 * A list of menus to display.
		 *
		 * @var array
		 */
		$this->menus = &$menus;

		/**
		 * The selected filters.
		 *
		 * @var array
		 */
		$this->filters = &$filters;

		/**
		 * Flag used to check whether it is possible
		 * to filter the menus by date and time.
		 *
		 * @var boolean
		 */
		$this->showSearchForm = &$show_search_form;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}
}
