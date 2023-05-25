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

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of VikRestaurants component.
 *
 * @since 1.0
 */
class VikRestaurantsController extends JControllerVRE
{
	/**
	 * Display task.
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// set default view if not set

		$input = JFactory::getApplication()->input;

		$view = $input->get('view');

		if (empty($view))
		{
			$input->set('view', $view = RestaurantsHelper::getDefaultView());
		}
		
		/**
		 * Fetch here whether to display the menu or not.
		 *
		 * @since 1.8
		 */
		if ($this->shouldDisplayMenu($view))
		{
			RestaurantsHelper::printMenu();
		}

		// call parent behavior
		parent::display();

		/**
		 * Fetch here whether to display the footer or not.
		 *
		 * @since 1.8
		 */
		if ($this->shouldDisplayMenu($view))
		{
			RestaurantsHelper::printFooter();
		}
	}

	////////////////////////
	////// AJAX UTILS //////
	////////////////////////

	/**
	 * AJAX end-point to obtain a list of users beloning
	 * to the current platform (CMS).
	 *
	 * @return 	void
	 *
	 * @since 	1.2
	 */
	public function search_jusers()
	{	
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$search = $input->getString('term');
		$id 	= $input->getUint('id');

		// create inner query to fetch enabled/disabled status
		$inner = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_users', 'a'))
			->where($dbo->qn('a.jid') . ' = ' . $dbo->qn('u.id'));

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('u.id'));
		$q->select($dbo->qn('u.name'));
		$q->select($dbo->qn('u.email'));
		$q->select($dbo->qn('u.username'));
		$q->select('(' . $dbo->qn('id') . ' <> ' . (int) $id . ' AND EXISTS (' . $inner . ')) AS ' . $dbo->qn('disabled'));

		$q->from($dbo->qn('#__users', 'u'));
		
		/**
		 * Reverse the search key in order to try finding
		 * users by name even if it was wront in the opposite way.
		 * If we searched by "John Smith", the system will search
		 * for "Smith John" too.
		 *
		 * @since 1.8
		 */
		$reverse = preg_split("/\s+/", $search);
		$reverse = array_reverse($reverse);
		$reverse = implode(' ', $reverse);

		$q->where(array(
			$dbo->qn('u.name') . ' LIKE ' . $dbo->q("%$search%"),
			$dbo->qn('u.name') . ' LIKE ' . $dbo->q("%$reverse%"),
			$dbo->qn('u.username') . ' LIKE ' . $dbo->q("%$search%"),
			$dbo->qn('u.email') . ' LIKE ' . $dbo->q("%$search%"),
		), 'OR');

		$q->order($dbo->qn('u.name') . ' ASC');
		$q->order($dbo->qn('u.username') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			echo "[]";
			exit;
		}

		$users = array();

		/**
		 * Reverse lookup used to check whether there is already
		 * a user with the same name.
		 * 
		 * @since 1.8
		 */
		$namesakesLookup = array();

		foreach ($dbo->loadObjectList() as $u)
		{
			$u->text = $u->name;

			$users[$u->id] = $u;

			// insert name-id relation within the lookup
			if (!isset($namesakesLookup[$u->name]))
			{
				$namesakesLookup[$u->name] = array();
			}

			$namesakesLookup[$u->name][] = $u->id;
		}

		// iterate names lookup
		foreach ($namesakesLookup as $name => $ids)
		{
			// in case a name owns more than 1 ID, we have a homonym
			if (count($ids) > 1)
			{
				// iterate the list of IDS and append the e-mail to the name
				foreach ($ids as $id)
				{
					$users[$id]->text .= ' : ' . $users[$id]->username;
				}
			}
		}

		echo json_encode(array_values($users));
		exit;
	}

	/**
	 * AJAX end-point used to search the customers by ID or QUERY.
	 * The task expects to receive at least one of these 2 values
	 * in query string: id (integer) or term (string).
	 *
	 * The ID has higher priority and it will be always used in 
	 * case it is specified.
	 *
	 * The TERM will be used to search by name, e-mail or phone.
	 *
	 * The task echoes a stringified (JSON) list of customers.
	 *
	 * @return 	void
	 *
	 * @since 	1.4
	 */
	public function search_users()
	{	
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$search = $input->getString('term');
		$id 	= $input->getUint('id');
		
		if ($id > 0)
		{
			// return user data in case a single ID was specified
			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('id', 'billing_name')))
				->from($dbo->qn('#__vikrestaurants_users'))
				->where($dbo->qn('id'));

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				echo json_encode($dbo->loadObject());
			}
			else
			{
				echo "[]";
			}

			exit;
		}

		// fetch list based on search key

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('c.id'));
		$q->select($dbo->qn('c.billing_name'));
		$q->select($dbo->qn('c.billing_mail'));
		$q->select($dbo->qn('c.billing_phone'));
		$q->select($dbo->qn('c.country_code'));
		$q->select($dbo->qn('c.fields'));
		$q->select($dbo->qn('c.tkfields'));
		$q->select($dbo->qn('d.id', 'id_delivery'));
		$q->select($dbo->qn('d.country', 'delivery_country'));
		$q->select($dbo->qn('d.state'));
		$q->select($dbo->qn('d.city'));
		$q->select($dbo->qn('d.address'));
		$q->select($dbo->qn('d.address_2'));
		$q->select($dbo->qn('d.zip'));
		$q->select($dbo->qn('d.latitude'));
		$q->select($dbo->qn('d.longitude'));

		$q->from($dbo->qn('#__vikrestaurants_users', 'c'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_user_delivery', 'd') . ' ON ' . $dbo->qn('c.id') . ' = ' . $dbo->qn('d.id_user'));

		/**
		 * Reverse the search key in order to try finding
		 * users by name even if it was wront in the opposite way.
		 * If we searched by "John Smith", the system will search
		 * for "Smith John" too.
		 *
		 * @since 1.8
		 */
		$reverse = preg_split("/\s+/", $search);
		$reverse = array_reverse($reverse);
		$reverse = implode(' ', $reverse);

		$q->where(array(
			$dbo->qn('c.billing_name') . ' LIKE ' . $dbo->q("%$search%"),
			$dbo->qn('c.billing_name') . ' LIKE ' . $dbo->q("%$reverse%"),
			$dbo->qn('c.billing_mail') . ' LIKE ' . $dbo->q("%$search%"),
			$dbo->qn('c.billing_phone') . ' LIKE ' . $dbo->q("%$search%"),
		), 'OR');

		$q->order($dbo->qn('c.billing_name') . ' ASC');
		$q->order($dbo->qn('c.billing_mail') . ' ASC');
		$q->order($dbo->qn('d.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			echo "[]";
			exit;
		}

		$users = array();

		/**
		 * Reverse lookup used to check whether there is already
		 * a user with the same name.
		 * 
		 * @since 1.8
		 */
		$namesakesLookup = array();

		foreach ($dbo->loadObjectList() as $u)
		{
			if (!isset($users[$u->id]))
			{
				$tmp = new stdClass;
				$tmp->id            = $u->id;
				$tmp->text          = $u->billing_name;
				$tmp->billing_name  = $u->billing_name;
				$tmp->billing_mail  = $u->billing_mail;
				$tmp->billing_phone = $u->billing_phone;
				$tmp->country_code  = $u->country_code;
				$tmp->fields        = strlen($u->fields)   ? json_decode($u->fields)   : array();
				$tmp->tkfields      = strlen($u->tkfields) ? json_decode($u->tkfields) : array();
				$tmp->delivery      = array();

				$users[$u->id] = $tmp;

				// insert name-id relation within the lookup
				if (!isset($namesakesLookup[$u->billing_name]))
				{
					$namesakesLookup[$u->billing_name] = array();
				}

				$namesakesLookup[$u->billing_name][] = $u->id;
			}

			if (!empty($u->address) && !empty($u->zip))
			{
				$addr = new stdClass;
				$addr->country     = $u->delivery_country;
				$addr->state       = $u->state;
				$addr->city        = $u->city;
				$addr->address     = $u->address;
				$addr->address_2   = $u->address_2;
				$addr->zip         = $u->zip;
				$addr->latitude    = $u->latitude;
				$addr->longitude   = $u->longitude;

				$addr->fullString = VikRestaurants::deliveryAddressToStr($u);

				$users[$u->id]->delivery[$u->id_delivery] = $addr;
			}
		}

		// iterate names lookup
		foreach ($namesakesLookup as $name => $ids)
		{
			// in case a name owns more than 1 ID, we have a homonym
			if (count($ids) > 1)
			{
				// iterate the list of IDS and append the e-mail to the name
				foreach ($ids as $id)
				{
					$users[$id]->text .= ' : ' . $users[$id]->billing_mail;
				}
			}
		}

		echo json_encode(array_values($users));
		exit;
	}

	/**
	 * AJAX end-point to obtain a list of available working
	 * shifts for the given date and group (1: restaurant, 2: take-away).
	 *
	 * @return 	void
	 *
	 * @since 	1.5
	 */
	public function get_working_shifts()
	{
		$input = JFactory::getApplication()->input;
		
		$date 	= $input->get('date', '', 'string');
		$group  = $input->get('group', 1, 'uint');
		
		$shifts = JHtml::_('vikrestaurants.times', $group, $date);

		$html = '';
		
		foreach ($shifts as $optgroup => $options)
		{
			if ($optgroup)
			{
				$html .= '<optgroup label="' . $optgroup . '">';
			}

			foreach ($options as $opt)
			{
				$html .= '<option value="' . $opt->value . '">' . $opt->text . '</option>';
			}

			if ($optgroup)
			{
				$html .= '</optgroup>';
			}
		}
		
		echo json_encode(array(1, $html));
		exit;
	}

	/**
	 * AJAX end-point to fetch a Google image for the specified location.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public function get_googlemaps_image()
	{
		$input = JFactory::getApplication()->input;

		$lat  = $input->get('lat', null, 'float');
		$lng  = $input->get('lng', null, 'float');
		$size = $input->get('size', null, 'string');
		$attr = $input->get('imageattr', null, 'array');

		$options = array(
			// define image center
			'center' => array(
				'lat' => $lat,
				'lng' => $lng,
			),
			// define image size
			'size' => $size,
		);

		echo json_encode(JHtml::_('vrehtml.site.googlemapsimage', $options));
		exit;
	}

	/**
	 * AJAX end-point used to fetch a list of suggestions based on
	 * the translations that have been already made for the given
	 * language tag.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	public function get_suggested_translations()
	{
		$input = JFactory::getApplication()->input;

		// do not filter term in order to support HTML
		$term = $input->get('term', '', 'raw');
		$tag  = $input->get('tag', null, 'string');

		$translator = VREFactory::getTranslator();

		// fetch suggestions
		$suggestions = $translator->getSuggestions($term, $tag);

		if ($suggestions)
		{
			// map suggestions to always encode HTML special characters
			$suggestions = array_map(function($hint)
			{
				return htmlentities($hint);
			}, $suggestions);
		}
		else
		{
			$suggestions = array();
		}

		echo json_encode($suggestions);
		exit;
	}

	/**
	 * Checks whether the specified view should display the menu.
	 *
	 * @param 	string 	$view  The view to check.
	 * @param 	array 	$list  An additional list of supported views.
	 *
	 * @return 	boolean
	 *
	 * @since 	1.8
	 */
	protected function shouldDisplayMenu($view, array $list = array())
	{
		$tmpl = JFactory::getApplication()->input->get('tmpl');

		// do not display in case of tmpl=component
		if (!strcmp((string) $tmpl, 'component'))
		{
			return false;
		}

		// defines list of views that supports menu and footer
		$views = array(
			'coupons',
			'customers',
			'customf',
			'editconfig',
			'invoices',
			'shifts',
			'specialdays',
			'maps',
			'media',
			'menus',
			'menusproducts',
			'operators',
			'operatorlogs',
			'payments',
			'rescodes',
			'reservations',
			'restaurant',
			'reviews',
			'roomclosures',
			'rooms',
			'tables',
			'tkareas',
			'tkdeals',
			'tkmenuattr',
			'tkmenus',
			'tkproducts',
			'tkreservations',
			'tktoppings',
			'tktopseparators',
		);

		// merge lookup with overrides
		$views = array_merge($views, $list);

		// check whether the view is in the list
		return in_array($view, $views);
	}
}
