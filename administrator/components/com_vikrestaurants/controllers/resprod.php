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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants reservation product controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerResprod extends VREControllerAdmin
{
	/**
	 * AJAX end-point used to change the status code of a product.
	 *
	 * @return 	void
	 */
	public function changecodeajax()
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();
		
		$code = array();
		$code['group']      = 3;
		$code['id_order']   = $input->get('id', 0, 'uint');
		$code['id_rescode'] = $input->get('id_code', 0, 'uint');
		$code['notes'] 		= $input->get('notes', '', 'string');
		$code['id']         = 0;

		if (empty($notes))
		{
			// use NULL to avoid overwriting the notes
			$notes = null;
		}

		// check user permissions (abort in case the product ID is missing)
		if (!$user->authorise('core.edit.state', 'com_vikrestaurants') || !$code['id_order'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$args = array();
		$args['id']      = $code['id_order'];
		$args['rescode'] = $code['id_rescode'];

		// get reservation product table
		$resprod = JTableVRE::getInstance('resprod', 'VRETable');

		// update reservation product
		if (!$resprod->save($args))
		{
			// something went wrong while saving the product
			UIErrorFactory::raiseError(500, $resprod->getError(null, true));
		}

		if ($code['id_rescode'])
		{
			// get record table
			$rescodeorder = JTableVRE::getInstance('rescodeorder', 'VRETable');

			// try to save arguments
			$rescodeorder->save($code);
		}
		
		// get reservation code details
		$rescode = JHtml::_('vikrestaurants.rescode', $code['id_rescode'], $code['group']);

		echo json_encode($rescode);
		exit;
	}
}
