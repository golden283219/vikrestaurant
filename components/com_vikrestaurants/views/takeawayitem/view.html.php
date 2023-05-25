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
 * VikRestaurants take-away item details view.
 * It is possible to purchase the selected product
 * from here.
 *
 * @since 1.7
 */
class VikRestaurantsViewtakeawayitem extends JViewVRE
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
		
		VikRestaurants::loadCartLibrary();
		
		$cart = TakeAwayCart::getInstance();
		
		$id_item   = $input->get('takeaway_item', 0, 'uint');
		$date      = $input->get('takeaway_date', null, 'string');
		$id_option = $input->get('id_option', 0, 'uint');

		/**
		 * Change check-in date if specified.
		 *
		 * @since 1.8
		 */
		if (!empty($date) && VikRestaurants::isTakeAwayDateAllowed())
		{
			$checkin_ts = VikRestaurants::createTimestamp($date, 0, 0);

			if ($checkin_ts != $cart->getCheckinTimestamp())
			{
				// update check-in date
				$cart->setCheckinTimestamp($checkin_ts);
				$cart->setCheckinTime(null);

				// check for deals
				VikRestaurants::resetDealsInCart($cart, $cart->getCheckinTime(true));
				VikRestaurants::checkForDeals($cart);

				// commit changes
				$cart->store();
			}
		}

		// compose request
		$request = new stdClass;
		$request->idEntry  = $id_item;
		$request->idOption = $id_option;
		$request->quantity = $input->get('quantity', 1, 'uint');
		$request->notes    = $input->get('notes', '', 'string');
		$request->toppings = $input->get('topping', array(), 'array');
		$request->units    = $input->get('topping_units', array(), 'array');

		$filters = array();
		$filters['date'] = date(VREFactory::getConfig()->get('dateformat'), $cart->getCheckinTimestamp());

		// get all attributes
		$attributes = JHtml::_('vikrestaurants.takeawayattributes');

		// get discount deals
		VikRestaurants::loadDealsLibrary();
		$discount_deals = DealsHandler::getAvailableFullDeals($cart->getCheckinTimestamp(), 2);

		// build item object
		$item = $this->buildTakeawayProduct($request, $attributes, $discount_deals);

		// fetch status of the menu
		VikRestaurants::fetchMenusStatus($item->menu, $cart->getCheckinTimestamp());

		// check if the form should be submitted when
		// the variation changes, as there might be
		// other toppings groups available
		$q = $dbo->getQuery(true)
			->select(1)
			->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc'))
			->where($dbo->qn('id_entry') . ' = ' . $item->id)
			->where($dbo->qn('id_variation') . ' > 0');
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		$to_submit = (bool) $dbo->getNumRows();

		// get reviews
		VRELoader::import('library.reviews.handler');

		$reviewsHandler = new ReviewsHandler();

		$reviews = $reviewsHandler->takeaway()
			->setOrdering('rating', 2)
			->addOrdering('timestamp', 2)
			->getReviews($item->id);

		$reviews_stats = $reviewsHandler->takeaway()->getAverageRatio($item->id);
		
		/**
		 * An object containing the details of
		 * the selected product.
		 *
		 * @var object
		 */
		$this->item = &$item;

		/**
		 * The current cart instance.
		 *
		 * @var TakeawayCart
		 */
		$this->cart = &$cart;

		/**
		 * A list of published food attributes.
		 *
		 * @var array
		 */
		$this->attributes = &$attributes;

		/**
		 * A list of deals related to the products discounts.
		 *
		 * @var array
		 */
		$this->discountDeals = &$discount_deals;

		/**
		 * A list of reviews made for the selected product.
		 *
		 * @var array
		 */
		$this->reviews = &$reviews;

		/**
		 * An object containing a statistics summary of the
		 * reviews left for the selected product.
		 *
		 * @var object
		 */
		$this->reviewsStats = &$reviews_stats;

		/**
		 * An object containing the requested information.
		 *
		 * @var object
		 */
		$this->request = &$request;

		/**
		 * Flag used to check whether the form should be
		 * submitted every time the variation changes.
		 *
		 * @var boolean
		 */
		$this->isToSubmit = &$to_submit;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}
	
	/**
	 * Builds the take-away item object.
	 *
	 * @param 	object  $request 	 An object containing the request details.
	 * @param 	array 	$attributes  A list of attributes.
	 * @param 	array 	$deals       A list of discounts.
	 *
	 * @return 	array 	The resulting tree.
	 *
	 * @since 	1.8
	 */
	private function buildTakeawayProduct($request, array $attributes, array $deals)
	{
		$id_entry  = $request->idEntry;
		$id_option = $request->idOption;
		$toppings  = $request->toppings;
		$units     = $request->units;

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('e.*');

		$q->select($dbo->qn('o.id', 'oid'));
		$q->select($dbo->qn('o.name', 'oname'));
		$q->select($dbo->qn('o.inc_price', 'oprice'));

		$q->select($dbo->qn('m.id', 'mid'));
		$q->select($dbo->qn('m.title', 'mtitle'));
		$q->select($dbo->qn('m.description', 'mdesc'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu') . ' AND ' . $dbo->qn('e.published') . ' = 1');
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('o.id_takeaway_menu_entry'));
		
		$q->where($dbo->qn('m.published') . ' = 1');
		$q->where($dbo->qn('e.id') . ' = ' . (int) $id_entry);

		$q->order($dbo->qn('o.ordering') . ' ASC');		
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// product not found, raise error
			throw new Exception(JText::_('VRTKCARTROWNOTFOUND'), 404);
		}

		$rows = $dbo->loadObjectList();

		$item = new stdClass;
		$item->id          = $rows[0]->id;
		$item->name        = $rows[0]->name;
		$item->description = $rows[0]->description;
		$item->price       = $rows[0]->price;
		$item->image       = $rows[0]->img_path;
		$item->totalPrice  = $item->price;

		/**
		 * Build images gallery.
		 * 
		 * @since 1.8.2
		 */
		$item->images = array();

		/**
		 * Add image to list only if specified.
		 *
		 * @since 1.8.3
		 */
		if ($item->image)
		{
			$item->images[] = $item->image;
		}

		if ($rows[0]->img_extra)
		{
			// merge main image with extra images
			$item->images = array_merge($item->images, json_decode($rows[0]->img_extra));
		}

		// add URI to images
		$item->images = array_map(function($image)
		{
			return VREMEDIA_URI . $image;
		}, $item->images);

		// check global discount
		$is_discounted = DealsHandler::isProductInDeals(array(
			'id_product' => $item->id,
			'id_option'  => 0,
			'quantity'   => 1,
		), $deals);

		if ($is_discounted !== false)
		{
			if ($deals[$is_discounted]['percentot'] == 1)
			{
				$item->totalPrice -= $item->totalPrice * $deals[$is_discounted]['amount'] / 100.0;
			}
			else
			{
				$item->totalPrice -= $deals[$is_discounted]['amount'];
			}
		}

		$item->totalBasePrice = $item->totalPrice;

		// build menu object
		$item->menu = new stdClass;
		$item->menu->id          = $rows[0]->mid;
		$item->menu->title       = $rows[0]->mtitle;
		$item->menu->description = $rows[0]->mdesc;

		// build options

		$item->options = array();

		foreach ($rows as $row)
		{
			if ($row->oid)
			{
				$option = new stdClass;
				$option->id    = $row->oid;
				$option->name  = $row->oname;
				$option->price = $row->oprice;

				// check variation discount
				$is_discounted = DealsHandler::isProductInDeals(array(
					'id_product' => $item->id,
					'id_option'  => $option->id,
					'quantity'   => 1
				), $deals);

				$option->totalPrice = $item->price + $option->price;

				if ($is_discounted !== false)
				{
					if ($deals[$is_discounted]['percentot'] == 1)
					{
						$option->totalPrice -= $option->totalPrice * $deals[$is_discounted]['amount'] / 100.0;
					}
					else
					{
						$option->totalPrice -= $deals[$is_discounted]['amount'];
					}
				}

				if ($option->id == $id_option)
				{
					// increase total cost
					$item->totalPrice = $option->totalPrice;
				}

				$item->options[] = $option;
			}
		}

		// apply menu translation
		VikRestaurants::translateTakeawayMenus($item->menu);

		// apply product translation
		VikRestaurants::translateTakeawayProducts($item);

		// apply variation translation
		VikRestaurants::translateTakeawayProductOptions($item->options);

		// get all products attributes

		$item->attributes = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_attribute'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'))
			->where($dbo->qn('id_menuentry') . ' = ' . (int) $item->id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$tmp = $dbo->loadColumn();

			// iterate all attributes
			foreach ($attributes as $attr)
			{
				// check if the product is assigned to this attribute
				if (in_array($attr->id, $tmp))
				{
					// copy attribute details
					$item->attributes[] = $attr;
				}
			}
		}

		// fetch toppings groups

		$item->toppings = array();

		$q = $dbo->getQuery(true);

		$q->select('g.*');
		$q->select($dbo->qn('a.id', 'topping_group_assoc_id'));
		$q->select($dbo->qn('a.id_topping'));
		$q->select($dbo->qn('a.rate', 'topping_rate'));
		$q->select($dbo->qn('t.name', 'topping_name'));
		$q->select($dbo->qn('t.description', 'topping_desc'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc', 'g'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc', 'a') . ' ON ' . $dbo->qn('a.id_group') . ' = ' . $dbo->qn('g.id'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_topping', 't') . ' ON ' . $dbo->qn('a.id_topping') . ' = ' . $dbo->qn('t.id'));

		$q->where($dbo->qn('g.id_entry') . ' = ' . $item->id);
		$q->where($dbo->qn('t.published') . ' = 1');

		$q->andWhere(array(
			$dbo->qn('g.id_variation') . ' <= 0',
			$dbo->qn('g.id_variation') . ' = ' . (int) $id_option,
		), 'OR');
		
		$q->order($dbo->qn('g.ordering') . ' ASC');
		$q->order($dbo->qn('a.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			// no toppings available
			return $item;
		}
		
		foreach ($dbo->loadObjectList() as $group)
		{
			if (!isset($item->toppings[$group->id]))
			{
				$tmp = new stdClass;
				$tmp->id           = $group->id;
				$tmp->title        = $group->title;
				$tmp->description  = $group->description ? $group->description : $group->title;
				$tmp->multiple     = $group->multiple;
				$tmp->min_toppings = $group->min_toppings;
				$tmp->max_toppings = $group->max_toppings;
				$tmp->use_quantity = $group->use_quantity;
				$tmp->list         = array();

				$item->toppings[$group->id] = $tmp;
			}
			
			if (!empty($group->topping_group_assoc_id))
			{
				$topping = new stdClass;
				$topping->id          = $group->id_topping;
				$topping->assoc_id    = $group->topping_group_assoc_id;
				$topping->name        = $group->topping_name;
				$topping->description = $group->topping_desc;
				$topping->rate        = $group->topping_rate;
				$topping->checked     = isset($toppings[$group->id]) && in_array($topping->assoc_id, $toppings[$group->id]);
				$topping->units       = 0;

				if ($topping->checked)
				{
					/**
					 * Check whether the customer specified the units
					 * for this topping.
					 *
					 * @since 1.8.2
					 */
					if (isset($units[$group->id][$topping->assoc_id]))
					{
						$topping->units = $units[$group->id][$topping->assoc_id];
					}
					else
					{
						$topping->units = 1;
					}

					// increase total price if topping was checked
					$item->totalPrice += $topping->rate * $topping->units;
				}

				$item->toppings[$group->id]->list[] = $topping;
			}
		}
		
		// apply toppings and groups translation
		VikRestaurants::translateTakeawayToppingsGroups($item->toppings);

		return $item;
	}
}
