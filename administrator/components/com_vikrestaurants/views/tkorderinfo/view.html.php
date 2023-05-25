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
 * VikRestaurants take-away order summary view.
 *
 * @since 1.0
 */
class VikRestaurantsViewtkorderinfo extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$input = JFactory::getApplication()->input;

		// force blank component layout
		$input->set('tmpl', 'component');
		
		$id = $input->get('id', 0, 'uint');

		// check the status of the reservation first
		VikRestaurants::removeTakeAwayOrdersOutOfTime($id);
		
		// get order details
		$order = VREOrderFactory::getOrder($id, JFactory::getLanguage()->getTag());

		if (!$order)
		{
			throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
		}
		
		$this->order = &$order;

		// display the template
		parent::display($tpl);
	}
}
