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
 * VikRestaurants language take-away topping management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagelangtktopping extends JViewVRE
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
		
		$id_topping = $input->get('id_topping', 0, 'uint');
		$ids        = $input->get('cid', array(), 'uint');
		$type       = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `t`.`id` AS `id_topping`, `t`.`name`, `t`.`description`,
			`tl`.`id` AS `id_lang`, `tl`.`name` AS `lang_name`, `tl`.`description` AS `lang_description`, `tl`.`tag`
			
			FROM `#__vikrestaurants_takeaway_topping` AS `t` 
			LEFT JOIN `#__vikrestaurants_lang_takeaway_topping` AS `tl` ON `t`.`id` = `tl`.`id_topping`
			
			WHERE `tl`.`id` = {$ids[0]}";
		}
		else
		{
			$q = "SELECT `t`.`id` AS `id_topping`, `t`.`name`, `t`.`description`
			
			FROM `#__vikrestaurants_takeaway_topping` AS `t` 
			
			WHERE `t`.`id` = $id_topping";
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langtktoppings&id_topping=' . $id_topping);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGTKTOPPING'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGTKTOPPING'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langtktopping.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langtktopping.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langtktopping.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langtktopping.cancel', JText::_('VRCANCEL'));
	}
}
