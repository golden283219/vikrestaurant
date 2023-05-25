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
 * VikRestaurants language take-away deal management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagelangtkdeal extends JViewVRE
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
		
		$id_deal = $input->get('id_deal', 0, 'uint');
		$ids     = $input->get('cid', array(), 'uint');
		$type    = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = "SELECT `d`.`id` AS `id_deal`, `d`.`name`, `d`.`description`,
			`dl`.`id` AS `id_lang`, `dl`.`name` AS `lang_name`, `dl`.`description` AS `lang_description`, `dl`.`tag`
			
			FROM `#__vikrestaurants_takeaway_deal` AS `d` 
			LEFT JOIN `#__vikrestaurants_lang_takeaway_deal` AS `dl` ON `d`.`id` = `dl`.`id_deal`
			
			WHERE `dl`.`id` = {$ids[0]}";
		}
		else
		{	
			$q = "SELECT `d`.`id` AS `id_deal`, `d`.`name`, `d`.`description`
			
			FROM `#__vikrestaurants_takeaway_deal` AS `d` 
			
			WHERE `d`.`id` = $id_deal";
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langtkdeals&id_deal=' . $id_deal);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGTKDEAL'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGTKDEAL'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langtkdeal.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langtkdeal.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langtkdeal.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langtkdeal.cancel', JText::_('VRCANCEL'));
	}
}
