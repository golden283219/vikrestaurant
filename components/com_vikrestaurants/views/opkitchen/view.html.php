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
 * VikRestaurants operator kitchen view.
 *
 * @since 1.8
 */
class VikRestaurantsViewopkitchen extends JViewVRE
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

		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin() && $operator->isRestaurantAllowed();
		
		if (!$access)
		{
			$itemid = $input->get('Itemid', 0, 'uint');

			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}

		$this->operator = &$operator;

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

		// display the template
		parent::display($tpl);
	}	
}
