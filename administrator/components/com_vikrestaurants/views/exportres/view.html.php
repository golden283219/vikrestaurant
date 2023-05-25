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
 * VikRestaurants reservations/orders export view.
 *
 * @since 1.2
 */
class VikRestaurantsViewexportres extends JViewVRE
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

		// get type set in request
		$data = new stdClass;
		$data->type     = $input->get('type', null, 'string');
		$data->cid      = $input->get('cid', array(), 'uint');
		$data->fromdate = $input->get('fromdate', '', 'string');
		$data->todate   = $input->get('todate', '', 'string');

		// retrieve data from user state
		$this->injectUserStateData($data, 'vre.exportres.data');

		// make sure the group is supported
		$data->type = JHtml::_('vrehtml.admin.getgroup', $data->type, array('restaurant', 'takeaway'));
		
		// set the toolbar
		$this->addToolBar($data->type);

		VRELoader::import('library.order.export.factory');

		// get supported drivers
		$drivers = VREOrderExportFactory::getSupportedDrivers($data->type);
		
		$this->data    = &$data;
		$this->drivers = &$drivers;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string 	$type  The export type ('restaurant' or 'takeaway').
	 *
	 * @return 	void
	 */
	private function addToolBar($type)
	{
		if ($type == 'restaurant')
		{
			$title = JText::_('VRMAINTITLEEXPORTRES');
			$acl   = 'reservations';
		}
		else
		{
			$title = JText::_('VRMAINTITLETKEXPORTRES');
			$acl   = 'tkorders';
		}
		
		JToolbarHelper::title($title, 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.access.' . $acl, 'com_vikrestaurants'))
		{
			JToolbarHelper::custom('exportres.save', 'download', 'download', JText::_('VRDOWNLOAD'), false);
			JToolbarHelper::divider();
		}
		
		JToolbarHelper::cancel('exportres.cancel', JText::_('VRCANCEL'));
	}
}
