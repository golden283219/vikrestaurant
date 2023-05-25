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
 * VikRestaurants API plugins view.
 *
 * @since 1.5
 */
class VikRestaurantsViewapiplugins extends JViewVRE
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

		// set the toolbar
		$this->addToolBar();
		
		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.apiplugins.keysearch', 'keysearch', '', 'string');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$apis = VREFactory::getApis();
		$rows = $apis->getPluginsList();

		if (strlen($filters['keysearch']))
		{
			// filter plugins by search keyword
			$rows = array_filter($rows, function($plugin) use ($filters)
			{
				return stripos(strtolower($plugin->getName()), $filters['keysearch']) !== false || stripos(strtolower($plugin->getTitle()), $filters['keysearch']) !== false;
			});

			// do not keep the assoc keys
			$rows = array_values($rows);
		}

		if (($count = count($rows)) > $lim)
		{	
			$rows = array_slice($rows, $lim0, $lim);

			jimport('joomla.html.pagination');
			$pageNav = new JPagination($count, $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}
		
		$this->rows    = &$rows;
		$this->navbut  = &$navbut;
		$this->filters = &$filters;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWAPIPLUGINS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'apiplugin.delete', JText::_('VRDELETE'));
		}

		JToolbarHelper::cancel('configuration.cancel', JText::_('VRCANCEL'));
	}
}
