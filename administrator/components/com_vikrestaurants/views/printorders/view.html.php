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
 * VikRestaurants print orders view.
 *
 * @since 1.0
 */
class VikRestaurantsViewprintorders extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		$input = JFactory::getApplication()->input;
		$input->set('tmpl', 'component');

		// Retrieve print header and footer from request.
		// If not specified, the default ones will be used.
		$print_orders_attr = $input->get('printorders', VikRestaurants::getPrintOrdersText(), 'array');

		if (!empty($print_orders_attr['update']))
		{
			// update header and footer texts
			VREFactory::getConfig()->set('printorderstext', $print_orders_attr);
		}
		
		$type = $input->get('type', 0, 'uint');
		$ids  = $input->get('cid', array(), 'uint');

		$tag = JFactory::getLanguage()->getTag();

		/**
		 * Loads the site language file according to the current langtag.
		 *
		 * @since 1.8
		 */
		VikRestaurants::loadLanguage($tag);
		
		$rows = array();

		foreach ($ids as $id)
		{	
			if ($type == 1)
			{
				// get take-away order
				$order = VREOrderFactory::getOrder($id, $tag);
			}
			else
			{
				// get restaurant reservation
				$order = VREOrderFactory::getReservation($id, $tag);	
			}

			if ($order)
			{
				$rows[] = $order;
			}	
		}
		
		$this->type = &$type;
		$this->rows = &$rows;
		$this->text = &$print_orders_attr;

		// display the template
		parent::display($tpl);
	}
}
