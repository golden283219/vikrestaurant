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
 * VikRestaurants language take-away menu management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagelangtkmenu extends JViewVRE
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
		
		$id_menu = $input->get('id_menu', 0, 'uint');
		$ids     = $input->get('cid', array(), 'uint');
		$type    = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `m`.`id` AS `id_menu`, `m`.`title` AS `name`, `m`.`description`, `m`.`alias`,
			`ml`.`id` AS `id_lang`, `ml`.`name` AS `lang_name`, `ml`.`description` AS `lang_description`, `ml`.`alias` AS `lang_alias`, `ml`.`tag`
			
			FROM `#__vikrestaurants_takeaway_menus` AS `m` 
			LEFT JOIN `#__vikrestaurants_lang_takeaway_menus` AS `ml` ON `m`.`id` = `ml`.`id_menu`
			
			WHERE `ml`.`id` = {$ids[0]}";
		}
		else
		{
			$q = "SELECT `m`.`id` AS `id_menu`, `m`.`title` AS `name`, `m`.`description`, `m`.`alias`
			
			FROM `#__vikrestaurants_takeaway_menus` AS `m` 
			
			WHERE `m`.`id` = $id_menu";
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langtkmenus&id_menu=' . $id_menu);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGTKMENU'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGTKMENU'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langtkmenu.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langtkmenu.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langtkmenu.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langtkmenu.cancel', JText::_('VRCANCEL'));
	}
}
