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
 * VikRestaurants language take-away menu entry management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagelangtkproduct extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();
		
		$id_entry = $input->get('id_entry', 0, 'uint');
		$id_menu  = $input->get('id_takeaway_menu', 0, 'uint');
		$ids      = $input->get('cid', array(), 'uint');
		$type     = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == "edit")
		{
			/**
			 * The query is now based on the variations and groups, instead joining them to the translation records.
			 * In this way, while creating/deleting variations and groups, it will be possible to have those records
			 * updated by default.
			 *
			 * @since 1.7.5
			 */
			$q = "SELECT
				`p`.`id` AS `id_product`, `p`.`name` AS `product_name`, `p`.`description` AS `product_description`, `p`.`alias` AS `product_alias`,
				`pl`.`id` AS `id_lang_product`, `pl`.`name` AS `product_lang_name`, `pl`.`description` AS `product_lang_description`, `pl`.`alias` AS `product_lang_alias`, `pl`.`tag`,
				`pl`.`id_parent` AS `id_takeaway_menu`,

				`o`.`id` AS `id_option`, `o`.`name` AS `option_name`, `o`.`alias` AS `option_alias`,
				`ol`.`id` AS `id_lang_option`, `ol`.`name` AS `option_lang_name`, `ol`.`alias` AS `option_lang_alias`,

				`g`.`id` AS `id_group`, `g`.`title` AS `group_name`, `g`.`description` AS `group_description`,
				`gl`.`id` AS `id_lang_group`, `gl`.`name` AS `group_lang_name`, `gl`.`description` AS `group_lang_description`

			FROM 	  `#__vikrestaurants_lang_takeaway_menus_entry` AS `pl`
			LEFT JOIN `#__vikrestaurants_takeaway_menus_entry` AS `p` ON `p`.`id` = `pl`.`id_entry`

			LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `o` ON `o`.`id_takeaway_menu_entry` = `p`.`id`
			LEFT JOIN `#__vikrestaurants_lang_takeaway_menus_entry_option` AS `ol` ON `o`.`id` = `ol`.`id_option` AND `pl`.`id` = `ol`.`id_parent`

			LEFT JOIN `#__vikrestaurants_takeaway_entry_group_assoc` AS `g` ON `g`.`id_entry` = `p`.`id`
			LEFT JOIN `#__vikrestaurants_lang_takeaway_menus_entry_topping_group` AS `gl` ON `g`.`id` = `gl`.`id_group` AND `pl`.`id` = `gl`.`id_parent`

			WHERE `pl`.`id` = {$ids[0]}

			ORDER BY `o`.`ordering` ASC, `g`.`ordering` ASC";
		}
		else
		{
			$q = "SELECT
				`p`.`id` AS `id_product`, `p`.`name` AS `product_name`, `p`.`description` AS `product_description`, `p`.`alias` AS `product_alias`,
			
				`o`.`id` AS `id_option`, `o`.`name` AS `option_name`, `o`.`alias` AS `option_alias`,
			
				`g`.`id` AS `id_group`, `g`.`title` AS `group_name`, `g`.`description` AS `group_description`
			
			FROM `#__vikrestaurants_takeaway_menus_entry` AS `p`
			
			LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `o` ON `o`.`id_takeaway_menu_entry` = `p`.`id`
			
			LEFT JOIN `#__vikrestaurants_takeaway_entry_group_assoc` AS `g` ON `g`.`id_entry` = `p`.`id`
			
			WHERE `p`.`id` = $id_entry

			ORDER BY `o`.`ordering` ASC, `g`.`ordering` ASC";
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&task=langtkproducts&id_entry=' . $id_entry . '&id_takeaway_menu=' . $id_menu);
			exit;
		}

		$this->idMenu = &$id_menu;
		
		$struct = $this->fetchMenuStructure($dbo->loadObjectList());
		
		$this->struct 	= &$struct;
		$this->type 	= &$type;

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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGTKPRODUCT'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGTKPRODUCT'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langtkentry.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langtkentry.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langtkentry.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langtkentry.cancel', JText::_('VRCANCEL'));
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
		if (empty($this->idMenu) && !empty($arr[0]->id_takeaway_menu))
		{
			// overwrite parent ID when editing a product
			$this->idMenu = $arr[0]->id_takeaway_menu;
		}

		$struct = new stdClass;
		$struct->id               = $arr[0]->id_product;
		$struct->id_lang          = empty($arr[0]->id_lang_product) ? '' : $arr[0]->id_lang_product;
		$struct->name             = $arr[0]->product_name;
		$struct->alias            = $arr[0]->product_alias;
		$struct->description      = $arr[0]->product_description;
		$struct->lang_name        = empty($arr[0]->product_lang_name) ? '' : $arr[0]->product_lang_name;
		$struct->lang_alias       = empty($arr[0]->product_lang_alias) ? '' : $arr[0]->product_lang_alias;
		$struct->lang_description = empty($arr[0]->product_lang_description) ? '' : $arr[0]->product_lang_description;
		$struct->tag              = empty($arr[0]->tag) ? null : $arr[0]->tag;
		$struct->options          = array();
		$struct->groups           = array();
		
		foreach ($arr as $row)
		{
			if (!empty($row->id_option) && !isset($struct->options[$row->id_option]))
			{
				$option = new stdClass;
				$option->id         = $row->id_option;
				$option->id_lang    = empty($row->id_lang_option) ? '' : $row->id_lang_option;
				$option->name       = $row->option_name;
				$option->alias      = $row->option_alias;
				$option->lang_name  = empty($row->option_lang_name) ? '' : $row->option_lang_name;
				$option->lang_alias = empty($row->option_lang_alias) ? '' : $row->option_lang_alias;

				$struct->options[$row->id_option] = $option;
			}
			
			if (!empty($row->id_group) && !isset($struct->groups[$row->id_group]))
			{
				$group = new stdClass;
				$group->id               = $row->id_group;
				$group->id_lang          = empty($row->id_lang_group) ? '' : $row->id_lang_group;
				$group->name             = $row->group_name;
				$group->lang_name        = empty($row->group_lang_name) ? '' : $row->group_lang_name;
				$group->description      = $row->group_description;
				$group->lang_description = empty($row->group_lang_description) ? '' : $row->group_lang_description;
				
				$struct->groups[$row->id_group] = $group;
			}
		}
		
		return $struct;
	}
}
