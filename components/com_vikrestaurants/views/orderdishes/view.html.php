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
 * VikRestaurants dishes ordering view.
 * Here a owner of a reservation is able to self-order
 * the dishes for the whole group.
 *
 * @since 1.8
 */
class VikRestaurantsVieworderdishes extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$dbo    = JFactory::getDbo();
		$config = VREFactory::getConfig();

		$oid = $input->get('ordnum', 0, 'uint');
		$sid = $input->get('ordkey', '', 'alnum');

		if (empty($oid) || empty($sid))
		{
			// missing required fields
			throw new Exception(JText::_('VRORDERRESERVATIONERROR'), 400);
		}

		// Get reservation details.
		// In case the reservation doesn't exist, an exception will be thrown.
		$reservation = VREOrderFactory::getReservation($oid, null, array('sid' => $sid));

		if ($reservation->menus)
		{
			// use menus assigned to the reservation
			$menus = array();

			// extract IDs from menus list
			$menu_ids = array_map(function($m) {
				return (int) $m->id;
			}, $reservation->menus);

			// recover menu details from DB
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_menus'))
				->where($dbo->qn('id') . ' IN (' . implode(',', $menu_ids) . ')')
				->order($dbo->qn('ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$menus = $dbo->loadObjectList();
			}
		}
		else
		{
			$args = array(
				'date'    => date($config->get('dateformat'), $reservation->checkin_ts),
				'hourmin' => date('H:i', $reservation->checkin_ts),
			);

			// recover all the menus available for the reservation check-in
			$menus = VikRestaurants::getAllAvailableMenusOn($args);
		}

		// iterate all menus to obtain the related sections and products
		for ($i = 0; $i < count($menus); $i++)
		{
			// load all available sections and products
			$menus[$i]->sections = $this->loadSections($menus[$i]->id);
		}

		// filter the menu to exclude all the menus without sections
		$menus = array_values(array_filter($menus, function($m)
		{
			return count($m->sections) > 0;
		}));

		if ($menus)
		{
			// translate all the menus and the related children
			$this->translate($menus);
		}
		else
		{
			// no menus available, raise warning
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
		}

		// build payment URL
		$pay_url = 'index.php?option=com_vikrestaurants&view=reservation&ordnum=' . $reservation->id . '&ordkey=' . $reservation->sid . '#payment';
		$pay_url = JRoute::_($pay_url, false);

		// check if the user is allowed to reserve dishes
		$can_order = VikRestaurants::canUserOrderFood($reservation, $error);

		if (!$can_order && $error)
		{
			// display error as a system message
			$app->enqueueMessage($error, 'error');

			if ($reservation->bill_closed && $reservation->id_payment)
			{
				// back to summary page in case the payment method
				// has been already selected
				$app->redirect($pay_url);
				exit;
			}
		}

		VRELoader::import('library.dishes.cart');

		// get cart instance
		$cart = VREDishesCart::getInstance($reservation->id);

		// get payment gateways (1: restaurant group)
		$payments = VikRestaurants::getAvailablePayments(1);
		
		/**
		 * An object containing the details of the specified
		 * restaurant reservation.
		 * 
		 * @var VREOrderRestaurant
		 */
		$this->reservation = &$reservation;

		/**
		 * The instance of the cart that contains all the
		 * dishes that have been selected.
		 *
		 * @var VREDishesCart
		 */
		$this->cart = &$cart;

		/**
		 * A list containing all the menus that can be accessed
		 * by the owner of this reservation.
		 * 
		 * @var array
		 */
		$this->menus = &$menus;

		/**
		 * Flag used to check whether the customer is currently
		 * allowed to order the dishes for its reservation.
		 *
		 * @var boolean
		 */
		$this->canOrder = &$can_order;

		/**
		 * The URL needed to reach to complete the payment.
		 *
		 * @var string
		 *
		 * @since 1.8.1
		 */
		$this->paymentURL = &$pay_url;

		/**
		 * A list of available payment gateways.
		 *
		 * @var array
		 *
		 * @since 1.8.1
		 */
		$this->payments = &$payments;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}

	/**
	 * Loads all the published sections and products that belong
	 * to the specified menu ID.
	 *
	 * @param 	integer  $id  The menu ID.
	 *
	 * @return 	array
	 */
	protected function loadSections($id)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('s.*');
		$q->select($dbo->qn('p.id', 'pid'));
		$q->select($dbo->qn('p.name', 'pname'));
		$q->select($dbo->qn('p.image', 'pimage'));
		$q->select($dbo->qn('p.description', 'pdesc'));
		$q->select(sprintf('(%s + %s) AS %s', $dbo->qn('p.price'), $dbo->qn('a.charge'), $dbo->qn('pcharge')));
		$q->select($dbo->qn('a.id', 'aid'));
		$q->select($dbo->qn('o.id', 'oid'));
		$q->select($dbo->qn('o.name', 'oname'));
		$q->select($dbo->qn('o.inc_price', 'oprice'));

		$q->from($dbo->qn('#__vikrestaurants_menus_section', 's'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('s.id') . ' = ' . $dbo->qn('a.id_section'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_section_product', 'p') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('a.id_product') . ' AND ' . $dbo->qn('p.published') . ' = 1');
		$q->leftjoin($dbo->qn('#__vikrestaurants_section_product_option', 'o') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('o.id_product'));
		
		$q->where($dbo->qn('s.id_menu') . ' = ' . (int) $id);
		$q->andWhere(array(
			$dbo->qn('s.published') . ' = 1',
			$dbo->qn('s.orderdishes') . ' = 1',
		), 'OR');

		$q->order($dbo->qn('s.ordering') . ' ASC');
		$q->order($dbo->qn('a.ordering') . ' ASC');
		$q->order($dbo->qn('o.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			return array();
		}

		$sections = array();

		foreach ($dbo->loadObjectList() as $r)
		{
			if ($r->id && !isset($sections[$r->id]))
			{
				$section = new stdClass;
				$section->id          = $r->id;
				$section->name        = $r->name;
				$section->description = $r->description;
				$section->image       = $r->image;
				$section->highlight   = $r->highlight;
				$section->products    = array();

				$sections[$r->id] = $section;
			}
			
			if ($r->pid && !isset($sections[$r->id]->products[$r->pid]))
			{
				$prod = new stdClass;
				$prod->id          = $r->pid;
				$prod->idAssoc     = $r->aid;
				$prod->name        = $r->pname;
				$prod->description = $r->pdesc;
				$prod->image       = $r->pimage;
				$prod->price       = $r->pcharge;
				$prod->options     = array();

				$sections[$r->id]->products[$r->pid] = $prod;
			}
			
			if ($r->oid)
			{
				$opt = new stdClass;
				$opt->id    = $r->oid;
				$opt->name  = $r->oname;
				$opt->price = $r->oprice;
				
				$sections[$r->id]->products[$r->pid]->options[$r->oid] = $opt;
			}
		}

		// do not take sections without products
		$sections = array_filter($sections, function($s)
		{
			return count($s->products) > 0;
		});

		return array_values($sections);
	}

	/**
	 * Translates the menu details.
	 *
	 * @param 	object 	&$menu   The menus to translate.
	 *
	 * @return 	void
	 */
	private function translate(&$menus)
	{
		// make sure multi-language is supported
		if (!VikRestaurants::isMultilanguage())
		{
			return;
		}

		// get language tage
		$langtag = JFactory::getLanguage()->getTag();

		// get translator
		$translator = VREFactory::getTranslator();

		$lookup = array();

		// recover all menus IDs
		foreach ($menus as $menu)
		{
			if (!isset($lookup['menu']))
			{
				$lookup['menu'] = array();
			}

			$lookup['menu'][] = $menu->id;

			// recover all sections IDs
			foreach ($menu->sections as $section)
			{
				if (!isset($lookup['menusection']))
				{
					$lookup['menusection'] = array();
				}

				$lookup['menusection'][] = $section->id;

				// recover all products IDs
				foreach ($section->products as $product)
				{
					if (!isset($lookup['menusproduct']))
					{
						$lookup['menusproduct'] = array();
					}

					$lookup['menusproduct'][] = $product->id;

					// recover all variations IDs
					foreach ($product->options as $option)
					{
						if (!isset($lookup['productoption']))
						{
							$lookup['productoption'] = array();
						}

						$lookup['productoption'][] = $option->id;
					}
				}
			}
		}
		
		// preload translations
		foreach ($lookup as $table => $ids)
		{
			// preload translations for current table
			$lookup[$table] = $translator->load($table, array_unique($ids), $langtag);
		}

		// iterate menus
		foreach ($menus as &$menu)
		{
			// get translation of current menu
			$menu_tx = $lookup['menu']->getTranslation($menu->id, $langtag);

			if ($menu_tx)
			{
				$menu->name        = $menu_tx->name;
				$menu->description = $menu_tx->description;
			}

			// iterate menu sections
			foreach ($menu->sections as &$section)
			{
				// get translation of current section
				$section_tx = $lookup['menusection']->getTranslation($section->id, $langtag);

				if ($section_tx)
				{
					$section->name        = $section_tx->name;
					$section->description = $section_tx->description;
				}

				// iterate section products
				foreach ($section->products as &$product)
				{
					// get translation of current product
					$prod_tx = $lookup['menusproduct']->getTranslation($product->id, $langtag);

					if ($prod_tx)
					{
						$product->name        = $prod_tx->name;
						$product->description = $prod_tx->description;
					}

					// iterate product options
					foreach ($product->options as &$option)
					{
						// get translation of current option
						$opt_tx = $lookup['productoption']->getTranslation($option->id, $langtag);

						if ($opt_tx)
						{
							$option->name = $opt_tx->name;
						}
					}
					// end option
				}
				// end product
			}
			// end section
		}
		// end menu
	}
}
