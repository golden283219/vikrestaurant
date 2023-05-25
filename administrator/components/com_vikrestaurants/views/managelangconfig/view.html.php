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
 * VikRestaurants language configuration management view.
 *
 * @since 1.8
 */
class VikRestaurantsViewmanagelangconfig extends JViewVRE
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
		
		$param = $input->get('param', 0, 'string');
		$ids   = $input->get('cid', array(), 'uint');
		$type  = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `c`.`id` AS `id_config`, `c`.`param`, `c`.`setting`,
			`cl`.`id` AS `id_lang`, `cl`.`param` AS `lang_param`, `cl`.`setting` AS `lang_setting`, `cl`.`tag`
			
			FROM `#__vikrestaurants_config` AS `c` 
			LEFT JOIN `#__vikrestaurants_lang_config` AS `cl` ON `c`.`param` = `cl`.`param`
			
			WHERE `cl`.`id` = {$ids[0]}";
		}
		else
		{	
			$q = "SELECT `c`.`id` AS `id_config`, `c`.`param`, `c`.`setting`
			
			FROM `#__vikrestaurants_config` AS `c` 
			
			WHERE `c`.`param` = " . $dbo->q($param);
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langconfig&param=' . $param);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGCONFIG'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGCONFIG'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langconfig.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langconfig.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langconfig.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langconfig.cancel', JText::_('VRCANCEL'));
	}
}
