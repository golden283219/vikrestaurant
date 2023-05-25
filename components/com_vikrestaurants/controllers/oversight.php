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
 * VikRestaurants oversight controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerOversight extends VREControllerAdmin
{
	/**
	 * Disconnects the current logged-in user.
	 *
	 * @return 	void
	 */
	public function logout()
	{
		$app = JFactory::getApplication();

		// disconnect current user
		$app->logout(JFactory::getUser()->id);

		$url = 'index.php?option=com_vikrestaurants&view=oversight';

		$itemid = $app->input->get('Itemid', 0, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}

		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * Task used to switch table for the given reservation.
	 *
	 * @return 	boolean
	 */
	public function changetable()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		$args = array();
		$args['id_table'] = $input->get('newid', 0, 'uint');
		$args['id']       = $input->get('id_order', 0, 'uint');

		// check user permissions (do not allow creation of new reservations here)
		if (!$operator->isRestaurantAllowed() || !$args['id'])
		{
			// back to main list, not authorised to create/edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		// get search arguments from request
		$date    = $input->get('date', '', 'string');
		$hourmin = $input->get('hourmin', '', 'string');
		$table   = $args['id_table'];

		// recover number of people from reservation details
		$q = $dbo->getQuery(true)
			->select($dbo->qn('people'))
			->from($dbo->qn('#__vikrestaurants_reservation'))
			->where($dbo->qn('id') . ' = ' . $args['id']);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			throw new Exception('Unable to find the reservation [' . $args['id'] . ']', 404);
		}

		$people = (int) $dbo->loadResult();

		// create search parameters
		$search = new VREAvailabilitySearch($date, $hourmin, $people);

		// check if the specified table is available
		if ($search->isTableAvailable($table, $args['id']))
		{
			// get record table
			JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
			$reservation = JTableVRE::getInstance('reservation', 'VRETable');

			// update reservation
			if ($reservation->save($args))
			{
				$app->enqueueMessage(JText::_('VRMAPTABLECHANGEDSUCCESS'));
			}
			else
			{
				// get string error
				$error = $reservation->getError(null, true);

				// display error message
				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error), 'error');
			}
		}
		else
		{
			// table already occupied
			$app->enqueueMessage(JText::_('VRMAPTABLENOTCHANGED'), 'error');
		}

		$this->cancel();

		return true;
	}

	/**
	 * AJAX end-point used to change the status code of a reservation.
	 *
	 * @return 	void
	 */
	public function changecodeajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();
		
		$code = array();
		$code['group']      = $input->get('group', 1, 'uint');
		$code['id_order']   = $input->get('id', 0, 'uint');
		$code['id_rescode'] = $input->get('id_code', 0, 'uint');
		$code['notes'] 		= $input->get('notes', '', 'string');
		$code['id']         = 0;

		if (empty($notes))
		{
			// use NULL to avoid overwriting the notes
			$notes = null;
		}

		// check user permissions (abort in case the order ID is missing)
		if (!$code['id_order'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$args = array();
		$args['id']      = $code['id_order'];
		$args['rescode'] = $code['id_rescode'];

		// for restaurant only
		if ($code['group'] == 1)
		{
			// check if the operator can edit the order
			if (!$operator->canSeeAll() && !$operator->canAssign($code['id_order']))
			{
				// raise AJAX error, not authorised to edit records
				UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}

			// do not update operator in case of master account
			if (!$operator->canSeeAll())
			{
				$args['id_operator'] = $operator->get('id');
			}
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance($code['group'] == 1 ? 'reservation' : 'tkreservation', 'VRETable');

		// update reservation
		$reservation->save($args);

		if ($code['id_rescode'])
		{
			// get record table
			$rescodeorder = JTableVRE::getInstance('rescodeorder', 'VRETable');

			// try to save arguments
			$rescodeorder->save($code);
		}
		
		// get reservation codes details
		$rescode = JHtml::_('vikrestaurants.rescode', $code['id_rescode'], $code['group']);

		echo json_encode($rescode);
		exit;
	}

	/**
	 * AJAX end-point used to change the reservation notes.
	 *
	 * @return 	void
	 */
	public function savenotesajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$args = array();
		$args['id']    = $input->get('id', 0, 'uint');
		$args['notes'] = $input->get('notes', '', 'string');

		// check user permissions (abort in case the order ID is missing)
		if (!$args['id'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// update reservation
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to assign an operator to the reservation.
	 *
	 * @return 	void
	 */
	public function assignoperatorajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		$args = array();
		$args['id']          = $input->get('id', 0, 'uint');
		$args['id_operator'] = $input->get('id_operator', 0, 'uint');

		// check user permissions (abort in case the order ID is missing)
		if (!$operator->canSeeAll() || !$args['id'])
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// update reservation
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		echo 1;
		exit;
	}

	/**
	 * Task used to save a reservation closure.
	 *
	 * @param 	boolean  $ajax  True if the request has been made via AJAX.
	 *
	 * @return 	boolean
	 */
	public function saveclosure($ajax = false)
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator || !$operator->isRestaurantAllowed())
		{
			if ($ajax)
			{
				// raise error
				UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			}
			else
			{
				// raise error, not authorised to access private area
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$this->cancel();

				return false;
			}
		}

		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		// get record table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');
		
		$args = array();
		$args['id'] = $input->get('id', 0, 'uint');

		if ($input->getBool('reopen'))
		{
			// permanently delete closure in case "RE-OPEN" checkbox was checked
			$reservation->delete(array($args['id']));
			$this->cancel();
			
			return false;
		}

		// load closure data from request
		$args['date']      = $input->get('date', '', 'string');
		$args['hourmin']   = $input->get('hourmin', '', 'string');
		$args['hour']	   = $input->get('hour', '', 'string');
		$args['min']	   = $input->get('min', '', 'string');
		$args['id_table']  = $input->get('id_table', 0, 'uint');
		$args['notes']     = $input->get('notes', '', 'raw');
		$args['stay_time'] = $input->get('stay_time', 0, 'uint');

		if (empty($args['id_table']))
		{
			// try to retrieve table from a different variable
			$args['id_table'] = $input->get('idt', 0, 'uint');

			if (empty($args['id_table']))
			{
				if ($ajax)
				{
					// raise error
					UIErrorFactory::raiseError(400, 'Missing table ID');
				}
				else
				{
					// display error message
					$app->enqueueMessage('Missing table ID', 'error');
					$this->cancel();

					return false;
				}
			}
		}

		if (empty($args['stay_time']))
		{
			// use default amount if time of stay was not specified
			$args['stay_time'] = VikRestaurants::getAverageTimeStay();
		}

		// get table details
		$q = $dbo->getQuery(true)
			->select($dbo->qn('max_capacity'))
			->from($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id') . ' = ' . $args['id_table']);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			if ($ajax)
			{
				// raise error
				UIErrorFactory::raiseError(404, 'Table ID [' . $args['id_table'] . '] not found');
			}
			else
			{
				// display error message
				$app->enqueueMessage('Table ID [' . $args['id_table'] . '] not found', 'error');
				$this->cancel();

				return false;
			}
		}

		// Always use the maximum capacity supported by the table.
		// This avoids to receive other reservations in case the
		// table is shared
		$args['people'] = (int) $dbo->loadResult();

		$args['closure']              = 1;
		$args['status']               = 'CONFIRMED';
		$args['purchaser_nominative'] = 'CLOSURE';

		// try to save arguments
		if (!$reservation->save($args))
		{
			// get string error
			$error = $reservation->getError(null, true);
			$error = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error);

			if ($ajax)
			{
				// raise error
				UIErrorFactory::raiseError(500, $error);
			}
			else
			{
				// display error message
				$app->enqueueMessage($error, 'error');

				$this->cancel();
					
				return false;
			}
		}

		if ($ajax)
		{
			echo $reservation->id;
			exit;
		}

		$this->cancel();

		return true;
	}

	/**
	 * AJAX end-point used to save a reservation closure.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.1
	 */
	public function saveclosureajax()
	{
		$this->saveclosure(true);
	}

	/**
	 * AJAX end-point used to obtain the widget contents
	 * or datasets.
	 *
	 * @return 	void
	 */
	public function loadwidgetdata()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$app   = JFactory::getApplication();
		$input = $app->input;

		// first of all, get selected group
		$group = $input->get('group', 'restaurant', 'string');

		// fetch ACL rule based on group
		$rule = $group == 'restaurant' ? 'isRestaurantAllowed' : 'isTakeawayAllowed';

		// check user permissions
		if (!$operator->$rule())
		{
			// raise error, not authorised to access statistics
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get widget name and ID
		$widget = $input->get('widget', '', 'string');
		$id     = $input->get('id', 0, 'uint');

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

		VRELoader::import('library.statistics.factory');

		try
		{
			// try to instantiate the widget
			$widget = VREStatisticsFactory::getWidget($widget, $group);

			// set up widget ID
			$widget->setID($id);

			// fetch widget data
			$data = $widget->getData();
		}
		catch (Exception $e)
		{
			// an error occurred while trying to access the widget
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// encode data in JSON and return them
		echo json_encode($data);
		exit;
	}

	/**
	 * AJAX end-point used to confirm a reservation.
	 *
	 * @return 	void
	 */
	public function confirmajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;

		$ids   = $input->get('cid', array(), 'uint');
		$group = $input->get('group', 1, 'uint');

		// filter empty IDs to avoid inserting them
		$ids = array_filter($ids);

		// check user permissions (abort in case the order ID is missing)
		if (!$operator->isGroupAllowed($group) || count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance($group == 1 ? 'reservation' : 'tkreservation', 'VRETable');

		foreach ($ids as $id)
		{
			$args = array(
				'id'         => $id,
				'status'     => 'CONFIRMED',
				'need_notif' => 1,
			);

			// update reservation
			if (!$reservation->save($args))
			{
				// get string error
				$error = $reservation->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to decline a reservation.
	 *
	 * @return 	void
	 */
	public function refuseajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;

		$ids   = $input->get('cid', array(), 'uint');
		$group = $input->get('group', 1, 'uint');

		// filter empty IDs to avoid inserting them
		$ids = array_filter($ids);

		// check user permissions (abort in case the order ID is missing)
		if (!$operator->isGroupAllowed($group) || count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance($group == 1 ? 'reservation' : 'tkreservation', 'VRETable');

		foreach ($ids as $id)
		{
			$args = array(
				'id'     => $id,
				'status' => 'REMOVED',
			);

			// update reservation
			if (!$reservation->save($args))
			{
				// get string error
				$error = $reservation->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}
		}

		echo 1;
		exit;
	}

	/**
	 * AJAX end-point used to notify a reservation.
	 *
	 * @return 	void
	 */
	public function notifyajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		if (!$operator)
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;

		$ids   = $input->get('cid', array(), 'uint');
		$group = $input->get('group', 1, 'uint');

		// filter empty IDs to avoid inserting them
		$ids = array_filter($ids);

		// check user permissions (abort in case the order ID is missing)
		if (!$operator->isGroupAllowed($group) || count($ids) == 0)
		{
			// raise AJAX error, not authorised to edit records
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// get reservation table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance($group == 1 ? 'reservation' : 'tkreservation', 'VRETable');

		VRELoader::import('library.mail.factory');

		foreach ($ids as $id)
		{
			$args = array(
				'id'         => $id,
				'need_notif' => 0,
			);

			// update reservation
			if (!$reservation->save($args))
			{
				// get string error
				$error = $reservation->getError(null, true);
				
				// raise returned error while saving the record
				UIErrorFactory::raiseError(500, $error);
			}

			// instantiate mail provider
			$mail = VREMailFactory::getInstance($group == 1 ? 'restaurant' : 'takeaway', 'customer', $id);

			// send e-mail notification to customer
			$mail->send();
		}

		echo 1;
		exit;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$input = JFactory::getApplication()->input;

		$query = array();
		$query['datefilter'] = $input->getString('date', '');
		$query['hourmin']    = $input->getString('hourmin', '');
		$query['people']     = $input->getUint('people', 0);

		$query['Itemid'] = $input->getUint('Itemid', 0);

		// remove empty attributes
		$query = array_filter($query);

		$from = $input->get('from', null);

		// build return URL
		$url = 'index.php?option=com_vikrestaurants&view=' . ($from ? $from : 'opreservations'). '&' . http_build_query($query);

		$this->setRedirect(JRoute::_($url, false));
	}
}
