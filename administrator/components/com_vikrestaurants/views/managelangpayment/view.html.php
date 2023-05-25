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
 * VikRestaurants language payment management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagelangpayment extends JViewVRE
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
		
		$id_payment = $input->get('id_payment', 0, 'uint');
		$ids        = $input->get('cid', array(), 'uint');
		$type       = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
			
		if ($type == 'edit')
		{
			$q = "SELECT `p`.`id` AS `id_payment`, `p`.`name`, `p`.`note`, `p`.`prenote`,
			`pl`.`id` AS `id_lang`, `pl`.`name` AS `lang_name`, `pl`.`note` AS `lang_note`, `pl`.`prenote` AS `lang_prenote`, `pl`.`tag`
			
			FROM `#__vikrestaurants_gpayments` AS `p` 
			LEFT JOIN `#__vikrestaurants_lang_payments` AS `pl` ON `p`.`id` = `pl`.`id_payment`
			
			WHERE `pl`.`id` = {$ids[0]}";
		}
		else
		{	
			$q = "SELECT `p`.`id` AS `id_payment`, `p`.`name`, `p`.`note`, `p`.`prenote`
			
			FROM `#__vikrestaurants_gpayments` AS `p` 
			
			WHERE `p`.`id` = $id_payment";
		}
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=langpayments&id_payment=' . $id_payment);
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITLANGPAYMENT'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWLANGPAYMENT'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('langpayment.save', JText::_('VRSAVE'));
			JToolbarHelper::save('langpayment.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('langpayment.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('langpayment.cancel', JText::_('VRCANCEL'));
	}
}
