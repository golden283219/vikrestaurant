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
 * VikRestaurants language custom field management view.
 *
 * @since 1.7
 */
class VikRestaurantsViewmanagelangcustomf extends JViewVRE
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
		
		$id_customf = $input->get('id_customf', 0, 'uint');
		$ids        = $input->get('cid', array(), 'uint');
		$type       = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `c`.`id` AS `id_customf`, `c`.`name`, `c`.`choose`, `c`.`poplink`, `c`.`type`, `c`.`rule`,
			`cl`.`id` AS `id_lang`, `cl`.`name` AS `lang_name`, `cl`.`choose` AS `lang_choose`, `cl`.`poplink` AS `lang_poplink`, `cl`.`tag`
			
			FROM `#__vikrestaurants_custfields` AS `c` 
			LEFT JOIN `#__vikrestaurants_lang_customf` AS `cl` ON `c`.`id`=`cl`.`id_customf`
			
			WHERE `cl`.`id` = {$ids[0]}";
		}
		else
		{	
			$q = "SELECT `c`.`id` AS `id_customf`, `c`.`name`, `c`.`choose`, `c`.`poplink`, `c`.`type`, `c`.`rule`
			
			FROM `#__vikrestaurants_custfields` AS `c` 
			
			WHERE `c`.`id` = $id_customf";
		}
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langcustomf&id_customf=' . $id_customf);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGCUSTOMF'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGCUSTOMF'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langcustomf.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langcustomf.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langcustomf.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langcustomf.cancel', JText::_('VRCANCEL'));
	}
}
