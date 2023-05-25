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
 * VikRestaurants language menus product management view.
 *
 * @since 1.8
 */
class VikRestaurantsViewmanagelangmenusproduct extends JViewVRE
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
		
		$id_product = $input->get('id_product', 0, 'uint');
		$ids        = $input->get('cid', array(), 'uint');
		$type       = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `p`.`id` AS `id_product`, `p`.`name` AS `product_name`, `p`.`description` AS `product_description`,
			`pl`.`id` AS `id_lang_product`, `pl`.`name` AS `product_lang_name`, `pl`.`description` AS `product_lang_description`, `pl`.`tag`,
			
			`o`.`id` AS `id_option`, `o`.`name` AS `option_name`,
			`ol`.`id` AS `id_lang_option`, `ol`.`name` AS `option_lang_name`
			
			FROM `#__vikrestaurants_section_product` AS `p`
			LEFT JOIN `#__vikrestaurants_lang_section_product` AS `pl` ON `p`.`id` = `pl`.`id_product`
			
			LEFT JOIN `#__vikrestaurants_section_product_option` AS `o` ON `p`.`id` = `o`.`id_product`
			LEFT JOIN `#__vikrestaurants_lang_section_product_option` AS `ol` ON `o`.`id` = `ol`.`id_option` AND `pl`.`id` = `ol`.`id_parent`
			
			WHERE `pl`.`id` = {$ids[0]}

			ORDER BY `p`.`ordering` ASC, `o`.`ordering` ASC";
		}
		else
		{
			$q = "SELECT `p`.`id` AS `id_product`, `p`.`name` AS `product_name`, `p`.`description` AS `product_description`,
			
			`o`.`id` AS `id_option`, `o`.`name` AS `option_name`
			
			FROM `#__vikrestaurants_section_product` AS `p`
			LEFT JOIN `#__vikrestaurants_section_product_option` AS `o` ON `o`.`id_product` = `p`.`id`
			
			WHERE `p`.`id` = {$id_product}

			ORDER BY `p`.`ordering` ASC, `o`.`ordering` ASC";
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langmenusproducts&id_product=' . $id_product);
			exit;
		}
		
		$struct = $this->fetchProductStructure($dbo->loadObjectList());
		
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGMENUSPROD'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGMENUSPROD'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langmenusproduct.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langmenusproduct.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langmenusproduct.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langmenusproduct.cancel', JText::_('VRCANCEL'));
	}
	
	/**
	 * Fetches the results retrieved from the database.
	 *
	 * @param 	array  arr  A list of records.
	 *
	 * @return 	The resulting tree.
	 */
	private function fetchProductStructure($arr)
	{
		$struct = new stdClass;
		$struct->id               = $arr[0]->id_product;
		$struct->id_lang          = empty($arr[0]->id_lang_product) ? '' : $arr[0]->id_lang_product;
		$struct->name             = $arr[0]->product_name;
		$struct->description      = $arr[0]->product_description;
		$struct->lang_name        = empty($arr[0]->product_lang_name) ? '' : $arr[0]->product_lang_name;
		$struct->lang_description = empty($arr[0]->product_lang_description) ? '' : $arr[0]->product_lang_description;
		$struct->tag              = empty($arr[0]->tag) ? null : $arr[0]->tag;
		$struct->options          = array();
		
		foreach ($arr as $row)
		{
			// check if we have an option
			if (!empty($row->id_option))
			{
				$option = new stdClass;
				$option->id        = $row->id_option;
				$option->id_lang   = empty($row->id_lang_option) ? '' : $row->id_lang_option;
				$option->name      = $row->option_name;
				$option->lang_name = empty($row->option_lang_name) ? '' : $row->option_lang_name;

				$struct->options[] = $option;
			}
		}

		return $struct;
	}
}
