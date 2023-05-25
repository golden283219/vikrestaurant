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
 * VikRestaurants menus list preview.
 *
 * @since 1.3
 */
class VikRestaurantsViewmenuslist extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();

		// force tmpl=component in request
		$input->set('tmpl', 'component');
		
		$id = $input->get('id', 0, 'uint');
		
		$menus = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('group'))
			->from($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('id') . ' = ' . $id);
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			throw new Exception(sprintf('Menu [%d] not found', $id), 404);
		}

		$group = $dbo->loadResult();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('m.id'))
			->from($dbo->qn('#__vikrestaurants_sd_menus', 'a'))
			->where($dbo->qn('id_spday') . ' = ' . $id);

		if ($group == 1)
		{
			$q->select($dbo->qn('m.name'));
			$q->select($dbo->qn('m.image'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_menus', 'm') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('a.id_menu'));
		}
		else
		{
			$q->select($dbo->qn('m.title', 'name'));
			// take-away menus don't support an image, always use NULL
			$q->select('NULL AS ' . $dbo->qn('image'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('a.id_menu'));
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$menus = $dbo->loadObjectList();
		}
		
		$this->menus = &$menus;
		$this->group = &$group;

		// display the template
		parent::display($tpl);
	}
}
