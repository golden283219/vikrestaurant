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
 * VikRestaurants reservation bill management view.
 *
 * @since 1.7
 */
class VikRestaurantsViewtkdiscord extends JViewVRE
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

		$ids = $input->get('cid', array(0), 'uint');

		// set the toolbar
		$this->addToolBar();

		$order = VREOrderFactory::getOrder($ids[0]);

		if (!$order)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
			$app->redirect('index.php?option=com_vikrestaurants&view=tkreservations');
			exit;
		}

		// get all coupons

		$coupons = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_coupons'))
			->where($dbo->qn('group') . ' = 1');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$coupons = $dbo->loadObjectList();
		}
		
		$this->order   = &$order;
		$this->coupons = &$coupons;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWTKDISCORD'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('tkreservation.savebill', JText::_('VRSAVE'));
			JToolbarHelper::save('tkreservation.saveclosebill', JText::_('VRSAVEANDCLOSE'));
			JToolbarHelper::divider();
		}

		JToolbarHelper::cancel('tkreservation.cancel', JText::_('VRCANCEL'));
	}
}
