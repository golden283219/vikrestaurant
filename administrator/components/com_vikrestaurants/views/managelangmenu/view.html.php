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
 * VikRestaurants language menu management view.
 *
 * @since 1.5
 */
class VikRestaurantsViewmanagelangmenu extends JViewVRE
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
			/**
			 * The query is now based on the translations in order to avoid
			 * retrieveing always the default translations.
			 *
			 * @since 1.7.5
			 */
			$q = "SELECT `m`.`id` AS `id_menu`, `m`.`name` AS `menu_name`, `m`.`description` AS `menu_description`, `m`.`alias` AS `menu_alias`,
			`ml`.`id` AS `id_lang_menu`, `ml`.`name` AS `menu_lang_name`, `ml`.`description` AS `menu_lang_description`, `ml`.`alias` AS `menu_lang_alias`, `ml`.`tag`,
			
			`s`.`id` AS `id_section`, `s`.`name` AS `section_name`, `s`.`description` AS `section_description`,
			`sl`.`id` AS `id_lang_section`, `sl`.`name` AS `section_lang_name`, `sl`.`description` AS `section_lang_description`
			
			FROM `#__vikrestaurants_lang_menus` AS `ml` 
			LEFT JOIN `#__vikrestaurants_menus` AS `m` ON `m`.`id` = `ml`.`id_menu` 
			
			LEFT JOIN `#__vikrestaurants_menus_section` AS `s` ON `m`.`id` = `s`.`id_menu`
			LEFT JOIN `#__vikrestaurants_lang_menus_section` AS `sl` ON `s`.`id` = `sl`.`id_section` AND `ml`.`id` = `sl`.`id_parent`
			
			WHERE `ml`.`id` = {$ids[0]}

			ORDER BY `m`.`ordering` ASC, `s`.`ordering` ASC";
		}
		else
		{
			$q = "SELECT `m`.`id` AS `id_menu`, `m`.`name` AS `menu_name`, `m`.`description` AS `menu_description`, `m`.`alias` AS `menu_alias`,
			
			`s`.`id` AS `id_section`, `s`.`name` AS `section_name`, `s`.`description` AS `section_description`
			
			FROM `#__vikrestaurants_menus` AS `m`
			LEFT JOIN `#__vikrestaurants_menus_section` AS `s` ON `s`.`id_menu` = `m`.`id`
			
			WHERE `m`.`id` = {$id_menu}

			ORDER BY `m`.`ordering` ASC, `s`.`ordering` ASC";
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langmenus&id_menu=' . $id_menu);
			exit;
		}
		
		$struct = $this->fetchMenuStructure($dbo->loadObjectList());
		
		$this->struct = &$struct;
		$this->type   = &$type;

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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGMENU'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGMENU'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langmenu.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langmenu.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langmenu.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langmenu.cancel', JText::_('VRCANCEL'));
	}
	
	/**
	 * Fetches the results retrieved from the database.
	 *
	 * @param 	array  arr  A list of records.
	 *
	 * @return 	The resulting tree.
	 */
	private function fetchMenuStructure($arr)
	{
		$struct = new stdClass;
		$struct->id               = $arr[0]->id_menu;
		$struct->id_lang          = empty($arr[0]->id_lang_menu) ? 0 : $arr[0]->id_lang_menu;
		$struct->name             = $arr[0]->menu_name;
		$struct->alias            = $arr[0]->menu_alias;
		$struct->description      = $arr[0]->menu_description;
		$struct->lang_name        = empty($arr[0]->menu_lang_name) ? '' : $arr[0]->menu_lang_name;
		$struct->lang_alias       = empty($arr[0]->menu_lang_alias) ? '' : $arr[0]->menu_lang_alias;
		$struct->lang_description = empty($arr[0]->menu_lang_description) ? '' : $arr[0]->menu_lang_description;
		$struct->tag              = empty($arr[0]->tag) ? '' : $arr[0]->tag;
		$struct->sections         = array();
		
		$section = $product = null;
		
		foreach ($arr as $row)
		{
			// check if we have a section
			if (!empty($row->id_section))
			{
				$section = new stdClass;
				$section->id               = $row->id_section;
				$section->id_lang          = empty($row->id_lang_section) ? 0 : $row->id_lang_section;
				$section->name             = $row->section_name;
				$section->description      = $row->section_description;
				$section->lang_name        = empty($row->section_lang_name) ? '' : $row->section_lang_name;
				$section->lang_description = empty($row->section_lang_description) ? '' : $row->section_lang_description;
				
				$struct->sections[] = $section;
			}
		}

		return $struct;
	}
}
