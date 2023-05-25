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
 * VikRestaurants operator reservation controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerOpreservation extends VREControllerAdmin
{
	/**
	 * Task used to access the creation page of a new record.
	 *
	 * @return 	boolean
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		$data = array();

		// use the checkin date, if specified
		$checkin_date = $app->input->getString('date', '');

		if ($checkin_date)
		{
			$data['date'] = $checkin_date;
		}

		// use the checkin time, if specified
		$checkin_time = $app->input->getString('hourmin', '');

		if ($checkin_time)
		{
			$data['hourmin'] = $checkin_time;
		}

		// use the table, if specified
		$id_table = $app->input->getUint('idt', 0);

		if ($id_table)
		{
			$data['id_table'] = $id_table;
		}

		// use the number of participants, if specified
		$people = $app->input->getUint('people', 0);

		if ($people)
		{
			$data['people'] = $people;
		}

		// unset user state for being recovered again
		$app->setUserState('vre.reservation.data', $data);

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}

		$url = 'index.php?option=com_vikrestaurants&view=opmanageres';

		$from = $app->input->get('from');

		if ($from)
		{
			$url .= '&from=' . $from;
		}

		$itemid = $app->input->get('Itemid', 0, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}

		$this->setRedirect(JRoute::_($url, false));

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.reservation.data', array());

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}

		$cid = $app->input->getUint('cid', array(0));

		$url = 'index.php?option=com_vikrestaurants&view=opmanageres&cid[]=' . $cid[0];

		$from = $app->input->get('from');

		if ($from)
		{
			$url .= '&from=' . $from;
		}

		$itemid = $app->input->get('Itemid', 0, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}

		$this->setRedirect(JRoute::_($url, false));

		return true;
	}

	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function editbill()
	{
		$app = JFactory::getApplication();

		// unset user state for being recovered again
		$app->setUserState('vre.bill.data', array());

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}

		$cid = $app->input->getUint('cid', array());

		if (empty($cid))
		{
			// try to recover ID from a different var
			$cid = array($app->input->getUint('id', 0));
		}

		$url = 'index.php?option=com_vikrestaurants&view=opeditbill&cid[]=' . $cid[0];

		$from = $app->input->get('bill_from');

		if ($from)
		{
			// use direct redirect
			$url .= '&bill_from=' . $from;
		}
		else
		{
			// fallback to default redirect
			$from = $app->input->get('from');

			if ($from)
			{
				$url .= '&from=' . $from;
			}
		}

		$itemid = $app->input->get('Itemid', 0, 'uint');

		if ($itemid)
		{
			$url .= '&Itemid=' . $itemid;
		}

		$this->setRedirect(JRoute::_($url, false));

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->cancel();
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the creation
	 * page of a new record.
	 *
	 * @return 	void
	 */
	public function savenew()
	{
		if ($this->save())
		{
			$input = JFactory::getApplication()->input;

			$itemid = $input->get('Itemid', 0, 'uint');

			$url = 'index.php?option=com_vikrestaurants&task=opreservation.add' . ($itemid ? '&Itemid=' . $itemid : '');

			$from = $app->input->get('bill_from');

			if ($from)
			{
				// use direct redirect
				$url .= '&bill_from=' . $from;
			}
			else
			{
				// fallback to default redirect
				$from = $app->input->get('from');

				if ($from)
				{
					$url .= '&from=' . $from;
				}
			}

			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// back to main list, not authorised to create records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel('oversight');

			return false;
		}
		
		$args = array();
		$args['date']        = $input->getString('date', '');
		$args['hourmin']     = $input->getString('hourmin', '');
		$args['id_table']    = $input->getUint('id_table', 0);
		$args['people']      = $input->getUint('people', 0);
		$args['status']      = $input->getString('status', '');
		$args['rescode']     = $input->getUint('rescode', 0);
		$args['stay_time']   = $input->getUint('stay_time', 0);
		$args['id_operator'] = $input->getUint('id_operator', 0);
		$args['id']          = $input->getInt('id', 0);

		if ($args['id'])
		{
			// make sure the operator is allowed to edit the reservation details
			if (!$operator->canSeeAll() && !$operator->canAssign($args['id']))
			{
				// reservation already assigned to someone else
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$this->cancel();

				return false;
			}
		}

		if ($args['stay_time'] == VREFactory::getConfig()->getUint('averagetimestay'))
		{
			// unset stay time in case it is equals to the default amount
			unset($args['stay_time']);
		}

		// get restaurant custom fields
		$args['custom_f'] = VRCustomFields::loadFromRequest(VRCustomFields::GROUP_RESTAURANT, $match, $strict = false);

		// auto-fill purchaser nominative if specified only as custom fields
		if (!empty($match['purchaser_nominative']))
		{
			$args['purchaser_nominative'] = $match['purchaser_nominative'];
		}

		// auto-fill purchaser e-mail if specified only as custom fields
		if (!empty($match['purchaser_mail']))
		{
			$args['purchaser_mail'] = $match['purchaser_mail'];
		}

		// auto-fill purchaser phone if specified only as custom fields
		if (!empty($match['purchaser_phone']))
		{
			$args['purchaser_phone'] = $match['purchaser_phone'];
		}

		$itemid   = $input->get('Itemid', 0, 'uint');
		$billfrom = $input->get('bill_from');
		$from     = $input->get('from');

		// get record table
		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// bind reservation arguments
		$reservation->bind($args);

		// validate table availability
		$search = new VREAvailabilitySearch($args['date'], $args['hourmin'], $args['people']);

		/**
		 * Force usage of the specified time of stay.
		 *
		 * @since 1.8.2
		 */
		$search->setStayTime($reservation->stay_time);
		
		$avail = $search->isTableAvailable($args['id_table'], $args['id']);

		// Try to save arguments.
		// Pass an empty array because the save method of JTable
		// expects at least an argument. Obviously, binded data
		// won't be replaced.
		if (!$avail || !$reservation->save($args))
		{
			if ($avail)
			{
				// get string error
				$error = $reservation->getError(null, true);
				$error = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error);
			}
			else
			{
				// table no more available
				$error = JText::_('VRERRTABNOLONGAV');
			}

			$app->enqueueMessage($error, 'error');

			$url = 'index.php?option=com_vikrestaurants&view=opmanageres' . ($itemid ? '&Itemid=' . $itemid : '');

			if ($reservation->id)
			{
				$url .= '&cid[]=' . $reservation->id;
			}

			if ($billfrom)
			{
				$url .= '&bill_from=' . $billfrom;
			}
			else if ($from)
			{
				$url .= '&from=' . $from;
			}

			// redirect to new/edit page
			$this->setRedirect(JRoute::_($url, false));
				
			return false;
		}

		// display generic successful message
		$app->enqueueMessage(JText::_('JLIB_APPLICATION_SAVE_SUCCESS'));

		// check if the reservation code has changed
		if ($reservation->rescode && $reservation->rescode != $input->get('prevrescode', 0, 'uint'))
		{
			$code = array();
			$code['group']      = 1;
			$code['id_order']   = $reservation->id;
			$code['id_rescode'] = $reservation->rescode;
			$code['id']         = 0;

			// get record table
			$rescodeorder = JTableVRE::getInstance('rescodeorder', 'VRETable');

			// try to save arguments
			$rescodeorder->save($code);
		}

		// check if we should send a notification e-mail to the customer
		if ($input->getBool('notifycust'))
		{
			// import mail factory class
			VRELoader::import('library.mail.factory');
			// instantiate mail provider
			$mail = VREMailFactory::getInstance('restaurant', 'customer', $reservation->id);
			// send e-mail to customer
			$mail->send();
		}

		$url = 'index.php?option=com_vikrestaurants&task=opreservation.edit&cid[]=' . $reservation->id . ($itemid ? '&Itemid=' . $itemid : '');

		if ($billfrom)
		{
			$url .= '&bill_from=' . $billfrom;
		}
		else if ($from)
		{
			$url .= '&from=' . $from;
		}

		// redirect to edit page
		$this->setRedirect(JRoute::_($url, false));

		return true;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @param 	string  $view  The return view.
	 *
	 * @return 	void
	 */
	public function cancel($view = null)
	{
		$input = JFactory::getApplication()->input;

		$itemid = $input->get('Itemid', 0, 'uint');

		$url = 'index.php?option=com_vikrestaurants' . ($itemid ? '&Itemid=' . $itemid : '');

		if (is_null($view))
		{
			$from = $input->get('from', null);

			$url .= '&view=' . ($from ? $from : 'opreservations');
		}
		else
		{
			$url .= '&view=' . $view;
		}

		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * AJAX end-point used to search the products.
	 *
	 * @return 	void
	 */
	public function searchproductajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$products = array();

		$search = $input->getString('term');

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->select($dbo->qn('name'))
			->from($dbo->qn('#__vikrestaurants_section_product'))
			->where($dbo->qn('name') . ' LIKE ' . $dbo->q("%{$search}%"))
			->order($dbo->qn('ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$products = $dbo->loadObjectList();
		}
		
		echo json_encode($products);
		exit;
	}

	/**
	 * AJAX end-point used to return a list of sections
	 * that belong to the specified menu.
	 *
	 * @return 	void
	 */
	public function menusectionsajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		$sections = array();

		$id_menu = $input->getUint('id_menu', 0);

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_menus_section'))
			->where($dbo->qn('id_menu') . ' = ' . $id_menu)
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$sections = $dbo->loadObjectList();
		}

		echo json_encode($sections);
		exit;
	}

	/**
	 * AJAX end-point used to return a list of products
	 * that belong to the specified section.
	 *
	 * @return 	void
	 */
	public function sectionproductsajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();

		$products = array();

		$id_section = $input->getUint('id_section', 0);

		$q = $dbo->getQuery(true)
			->select('p.*')
			->from($dbo->qn('#__vikrestaurants_section_product', 'p'))
			->order($dbo->qn('p.ordering') . ' ASC');

		if ($id_section)
		{
			$q->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('a.id_product') . ' = ' . $dbo->qn('p.id'));
			$q->where($dbo->qn('a.id_section') . ' = ' . $id_section);
		}
		else
		{
			$q->where($dbo->qn('p.hidden') . ' = 1');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$products = $dbo->loadObjectList();
		}

		echo json_encode($products);
		exit;
	}

	/**
	 * Returns the HTML used to insert a product within the bill.
	 *
	 * @return 	void
	 */
	public function getproducthtml()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;

		$id_product = $input->getUint('id_product', 0);
		$id_assoc	= $input->getUint('id_assoc', 0);

		$item = null;

		// get item

		if ($id_product > 0)
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn('p.id'))
				->select($dbo->qn('p.name'))
				->select($dbo->qn('p.price'))
				->select($dbo->qn('o.id', 'oid'))
				->select($dbo->qn('o.name', 'oname'))
				->select($dbo->qn('o.inc_price', 'oprice'))
				->from($dbo->qn('#__vikrestaurants_section_product', 'p'))
				->leftjoin($dbo->qn('#__vikrestaurants_section_product_option', 'o') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('o.id_product'))
				->where($dbo->qn('p.id') . ' = ' . $id_product)
				->order($dbo->qn('o.ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows() > 0)
			{
				// build default item
				$rows = $dbo->loadObjectList();

				$item = new stdClass;

				$item->id         = $rows[0]->id;
				$item->name       = $rows[0]->name;
				$item->price      = $rows[0]->price;
				$item->quantity   = 1;
				$item->id_var     = 0;
				$item->notes      = '';
				$item->variations = array();

				foreach ($rows as $r)
				{
					if (!empty($r->oid))
					{
						$var = new stdClass;
						$var->id    = $r->oid;
						$var->name  = $r->oname;
						$var->price = $r->oprice;

						$item->variations[] = $var;
					}
				}

				// build assoc item

				if ($id_assoc > 0)
				{
					$q = $dbo->getQuery(true)
						->select($dbo->qn('i.quantity'))
						->select($dbo->qn('i.price'))
						->select($dbo->qn('i.id_product_option', 'id_var'))
						->select($dbo->qn('i.notes'))
						->from($dbo->qn('#__vikrestaurants_res_prod_assoc', 'i'))
						->where($dbo->qn('i.id') . ' = ' . $id_assoc);

					$dbo->setQuery($q, 0, 1);
					$dbo->execute();

					if ($dbo->getNumRows())
					{	
						foreach ($dbo->loadObject() as $k => $v)
						{
							$item->{$k} = $v;
						}
					}
				}
			}
		}

		$data = array(
			'item'       => $item,
			'id_assoc'   => $id_assoc,
			'id_product' => $id_product,
		);

		/**
		 * Generate item form with a layout.
		 *
		 * @since 1.8
		 */
		$html = JLayoutHelper::render('oversight.billitem', $data);

		echo json_encode(array('status' => 1, 'html' => $html));
		exit;
	}

	/**
	 * AJAX end-point used to add an item to the bill.
	 *
	 * @return 	void
	 */
	public function additemajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;

		$args = array();
		$args['id_reservation']    = $input->get('id', 0, 'uint');
		$args['id_product']        = $input->get('id_entry', 0, 'uint');
		$args['id_product_option'] = $input->get('id_option', 0, 'uint');
		$args['quantity']          = $input->get('quantity', 1, 'uint');
		$args['notes']             = $input->get('notes', '', 'string');
		$args['id']                = $input->get('item_index', 0, 'uint');

		// make sure the operator is allowed to edit the reservation details
		if (!$operator->canSeeAll() && !$operator->canAssign($args['id']))
		{
			// the reservation is assigned to someone else
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');

		// fetch the ID of the section to which the product belongs
		$id_section = $input->getUint('id_section');

		if (!$args['id_product'])
		{
			// create produt first
			$prod = array(
				'id'        => 0,
				'name'      => $input->get('name', '', 'string'),
				'price'     => $input->get('price', 0, 'float'),
				'published' => 0,
				'hidden'    => 1,
			);

			// get product table
			$prodTable = JTableVRE::getInstance('menusproduct', 'VRETable');
			// save product
			if (!$prodTable->save($prod))
			{
				// get string error
				$error = $prod->getError(null, true);

				// abort with the occurred error
				UIErrorFactory::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $error));
			}

			$args['id_product'] = $prodTable->id;
		}
		else if ($id_section)
		{
			$sectionProd = JTableVRE::getInstance('sectionproduct', 'VRETable');

			// search for the association between the product and the parent section,
			// needed to retrieve any applied charge/discount
			$loaded = $sectionProd->load([
				'id_product' => $args['id_product'],
				'id_section' => $id_section,
			]);

			if ($loaded)
			{
				// item found, set the charge
				$args['charge'] = $sectionProd->charge;
			}
		}

		// get reservation product table
		$resprod = JTableVRE::getInstance('resprod', 'VRETable');

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// cache order so that it is possible to detect any added products (0: restaurant)
		VREOperatorLogger::getInstance()->cache($args['id_reservation'], 0);

		// get current price stored in database
		$old_price = $resprod->getPrice($args['id']);

		// try to save arguments
		if (!$resprod->save($args))
		{
			// get string error
			$error = $resprod->getError(null, true);
			
			// raise returned error while saving the record
			UIErrorFactory::raiseError(500, $error);
		}

		// update bill by the price of the added item (subtract the previous price from total)
		$reservation->updateBill($resprod->id_reservation, $resprod->price - $old_price);
		
		// get saved item
		$item = $resprod->getProperties();

		$response = new stdClass;
		$response->status = 1;
		$response->id = $resprod->id;
		$response->grand_total = $reservation->bill_value;

		$response->object = new stdClass;
		$response->object->item_id   = $resprod->id_product;
		$response->object->item_name = $resprod->name;
		$response->object->price     = $resprod->price;
		$response->object->quantity  = $resprod->quantity;

		echo json_encode($response);
		exit;
	}

	/**
	 * AJAX end-point used to add an item to the bill.
	 *
	 * @return 	void
	 */
	public function removeitemajax()
	{
		// get current operator
		$operator = VikRestaurants::getOperator();

		// make sure the user is an operator and it is
		// allowed to access the private area
		if (!$operator || !$operator->canLogin() || !$operator->isRestaurantAllowed())
		{
			// raise error, not authorised to access private area
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$input = JFactory::getApplication()->input;

		$id_assoc = $input->get('id_assoc', 0, 'uint');
		$id_res   = $input->get('id_res', 0, 'uint');

		// make sure the operator is allowed to edit the reservation details
		if (!$operator->canSeeAll() && !$operator->canAssign($id_res))
		{
			// the reservation is assigned to someone else
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');

		// get reservation product table
		$resprod = JTableVRE::getInstance('resprod', 'VRETable');

		// get reservation table
		$reservation = JTableVRE::getInstance('reservation', 'VRETable');

		// cache order so that it is possible to detect any deleted products (0: restaurant)
		VREOperatorLogger::getInstance()->cache($id_res, 0);

		// get product price
		$price = $resprod->getPrice($id_assoc);

		// delete record
		if (!$resprod->delete($id_assoc))
		{
			// an error occurred while trying to delete the record
			UIErrorFactory::raiseError(403, JText::_('VRE_AJAX_GENERIC_ERROR'));
		}

		// update bill by subtracting the price of the removed product
		$reservation->updateBill($id_res, $price * -1);
		
		echo json_encode($reservation->bill_value);
		exit;
	}
}
