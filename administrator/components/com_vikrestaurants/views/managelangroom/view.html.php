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
 * VikRestaurants language room management view.
 *
 * @since 1.8
 */
class VikRestaurantsViewmanagelangroom extends JViewVRE
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
		
		$id_room = $input->get('id_room', 0, 'uint');
		$ids     = $input->get('cid', array(), 'uint');
		$type    = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
			
		if ($type == 'edit')
		{
			$q = "SELECT `r`.`id` AS `id_room`, `r`.`name`, `r`.`description`,
			`rl`.`id` AS `id_lang`, `rl`.`name` AS `lang_name`, `rl`.`description` AS `lang_description`, `rl`.`tag`
			
			FROM `#__vikrestaurants_room` AS `r` 
			LEFT JOIN `#__vikrestaurants_lang_room` AS `rl` ON `r`.`id` = `rl`.`id_room`
			
			WHERE `rl`.`id` = {$ids[0]}";
		}
		else
		{	
			$q = "SELECT `r`.`id` AS `id_room`, `r`.`name`, `r`.`description`
			
			FROM `#__vikrestaurants_room` AS `r` 
			
			WHERE `r`.`id` = $id_room";
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langrooms&id_room=' . $id_room);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGROOM'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGROOM'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langroom.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langroom.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langroom.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langroom.cancel', JText::_('VRCANCEL'));
	}
}
