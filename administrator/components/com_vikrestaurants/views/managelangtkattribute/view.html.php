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
 * VikRestaurants language take-away menus attribute management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagelangtkattribute extends JViewVRE
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
		
		$id_attribute = $input->get('id_attribute', 0, 'uint');
		$ids          = $input->get('cid', array(), 'uint');
		$type         = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `a`.`id` AS `id_attribute`, `a`.`name`,
			`al`.`id` AS `id_lang`, `al`.`name` AS `lang_name`, `al`.`tag`
			
			FROM `#__vikrestaurants_takeaway_menus_attribute` AS `a` 
			LEFT JOIN `#__vikrestaurants_lang_takeaway_menus_attribute` AS `al` ON `a`.`id` = `al`.`id_attribute`
			
			WHERE `al`.`id` = {$ids[0]}";
		}
		else
		{
			$q = "SELECT `a`.`id` AS `id_attribute`, `a`.`name`
			
			FROM `#__vikrestaurants_takeaway_menus_attribute` AS `a` 
			
			WHERE `a`.`id` = $id_attribute";
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langtkattributes&id_attribute=' . $id_attribute);
			exit;
		}
		
		$struct = $dbo->loadObject();
		
		$this->struct = &$struct;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string  $type  The view type ('edit' or 'new').
	 *
	 * @return 	void
	 */
	private function addToolBar($type)
	{
		// add menu title and some buttons to the page
		if ($type == 'edit')
		{
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGTKATTRIBUTE'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGTKATTRIBUTE'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langtkattribute.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langtkattribute.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langtkattribute.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langtkattribute.cancel', JText::_('VRCANCEL'));
	}
}
