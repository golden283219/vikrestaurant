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
 * VikRestaurants take-away menus view.
 * This view displays a list of available menus
 * with all the related products. Here it is 
 * possible to start ordering some food.
 *
 * @since 1.2
 */
class VikRestaurantsViewtakeaway extends JViewVRE
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

		$config = VREFactory::getConfig();
		
		VikRestaurants::loadCartLibrary();
		
		$cart = TakeAwayCart::getInstance();
		
		$filters = array();
		$filters['menu']    = $input->get('takeaway_menu', 0, 'int');
		$filters['date']    = $input->get('takeaway_date', '', 'string');
		$filters['hourmin'] = $input->get('takeaway_time', '', 'string');

		$reset_deals = false;

		// only if date is set and date can be changed
		if (!empty($filters['date']) && VikRestaurants::isTakeAwayDateAllowed())
		{
			$checkin_ts = VikRestaurants::createTimestamp($filters['date'], 0, 0);

			// update check-in date
			$cart->setCheckinTimestamp($checkin_ts);

			$reset_deals = true;
		}
		else
		{
			// use cart date
			$filters['date'] = date($config->get('dateformat'), $cart->getCheckinTimestamp());
		}

		// obtain all the available times for pick-up and delivery
		$times = JHtml::_('vikrestaurants.takeawaytimes', $filters['date'], $cart, array('show_asap' => false));

		/**
		 * Make sure the selected time is supported.
		 *
		 * @since 1.8.3
		 */
		if ($filters['hourmin'] && !JHtml::_('vikrestaurants.hastime', $filters['hourmin'], $times))
		{
			// time not supported, unset it
			$filters['hourmin'] = null;
		}

		if ($filters['hourmin'])
		{
			// reset deals in case the time changed
			$reset_deals = true;
		}
		else if ($times)
		{
			// get time saved in cart
			$filters['hourmin'] = $cart->getCheckinTime();

			if (!$filters['hourmin'])
			{
				// use first time available in case there is no selected time
				$sh = reset($times);
				$filters['hourmin'] = $sh[0]->value;
			}
		}

		// validate the time against the available ones,
		// because the selected time might be not available
		// and the next one could be on a different shift
		if (!VikRestaurants::validateTakeAwayTime($filters['hourmin'], $times))
		{
			// invalid time, reset deals
			$reset_deals = true;
		}

		// always refresh check-in time
		$cart->setCheckinTime($filters['hourmin']);

		if ($reset_deals)
		{
			// check for deals
			VikRestaurants::resetDealsInCart($cart, $filters['hourmin']);
			VikRestaurants::checkForDeals($cart);
		}

		// save cart changes
		$cart->store();

		// get all take-away menus available for the specified date
		$available_menus = VikRestaurants::getAllTakeawayMenusOn($filters);

		// get all attributes
		$attributes = JHtml::_('vikrestaurants.takeawayattributes');
		
		// get menu items
		$menus = array();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn('e.id', 'eid'));
		$q->select($dbo->qn('e.name', 'ename'));
		$q->select($dbo->qn('e.description', 'edesc'));
		$q->select($dbo->qn('e.price', 'eprice'));
		$q->select($dbo->qn('e.ready', 'eready'));
		$q->select($dbo->qn('e.img_path', 'eimg'));
		$q->select($dbo->qn('e.img_extra', 'eimgextra'));

		$q->select($dbo->qn('o.id', 'oid'));
		$q->select($dbo->qn('o.name', 'oname'));
		$q->select($dbo->qn('o.inc_price', 'oprice'));

		$q->select($dbo->qn('m.id', 'mid'));
		$q->select($dbo->qn('m.title', 'mtitle'));
		$q->select($dbo->qn('m.description', 'mdesc'));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu') . ' AND ' . $dbo->qn('e.published') . ' = 1');
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('o.id_takeaway_menu_entry') . ' AND ' . $dbo->qn('o.published') . ' = 1');
		
		$q->where($dbo->qn('m.published') . ' = 1');

		if ($filters['menu'])
		{
			$q->where($dbo->qn('m.id') . ' = ' . $filters['menu']);
		}

		/**
		 * Take all the menus with a valid/empty start publishing.
		 *
		 * @since 1.8.3
		 */
		$q->andWhere(array(
			$dbo->qn('m.publish_up') . ' = -1',
			$dbo->qn('m.publish_up') . ' IS NULL',
			$dbo->qn('m.publish_up') . ' <= ' . $cart->getCheckinTimestamp(), 
		));

		/**
		 * Take all the menus with a valid/empty finish publishing.
		 *
		 * @since 1.8.3
		 */
		$q->andWhere(array(
			$dbo->qn('m.publish_down') . ' = -1',
			$dbo->qn('m.publish_down') . ' IS NULL',
			$dbo->qn('m.publish_down') . ' >= ' . $cart->getCheckinTimestamp(), 
		));

		$q->order($dbo->qn('m.ordering') . ' ASC');
		$q->order($dbo->qn('e.ordering') . ' ASC');
		$q->order($dbo->qn('o.ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$menus = $this->parseTakeawayMenus($dbo->loadObjectList(), $attributes);
		}

		// fetch status of the menus
		VikRestaurants::fetchMenusStatus($menus, $cart->getCheckinTimestamp(), $available_menus);

		// fetch all products that offers a discount for the given date
		VikRestaurants::loadDealsLibrary();
		$discount_deals = DealsHandler::getAvailableFullDeals($cart->getCheckinTimestamp(), 2);

		/**
		 * A list containing all the published menus.
		 * Each menu contains a list of products.
		 *
		 * @var array
		 */
		$this->menus = &$menus;
		
		/**
		 * A list of menus available for the selected day.
		 *
		 * @var array
		 */
		$this->availableMenus = &$available_menus;

		/**
		 * A list of published food attributes.
		 *
		 * @var array
		 */
		$this->attributes = &$attributes;

		/**
		 * An associative array containing a few
		 * search filters.
		 *
		 * @var array
		 */
		$this->filters = &$filters;

		/**
		 * A list of deals related to the products discounts.
		 *
		 * @var array
		 */
		$this->discountDeals = &$discount_deals;

		/**
		 * The user cart instance.
		 *
		 * @var TakeawayCart
		 */
		$this->cart = &$cart;

		/**
		 * A list of available times.
		 *
		 * @var array
		 *
		 * @since 1.8
		 */
		$this->times = &$times;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);
	}
	
	/**
	 * Builds the take-away menus tree.
	 *
	 * @param 	array 	$rows 	     A list of records.
	 * @param 	array 	$attributes  A list of attributes.
	 *
	 * @return 	array 	The resulting tree.
	 */
	private function parseTakeawayMenus($rows, array $attributes)
	{
		$dbo = JFactory::getDbo();

		$attrLookup = array();

		// get all products attributes
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id_menuentry'))
			->select($dbo->qn('id_attribute'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $attr)
			{
				if (!isset($attrLookup[$attr->id_menuentry]))
				{
					$attrLookup[$attr->id_menuentry] = array();
				}

				$attrLookup[$attr->id_menuentry][] = $attr->id_attribute;
			}
		}

		// translations lookup ID
		$trxLookup = array(
			'tkmenu'        => array(),
			'tkentry'       => array(),
			'tkentryoption' => array(),
		);

		$menus = array();
		
		foreach ($rows as $r)
		{
			if (!isset($menus[$r->mid]))
			{
				$menu = new stdClass;
				$menu->id          = $r->mid;
				$menu->title       = $r->mtitle;
				$menu->description = $r->mdesc;
				$menu->products    = array();

				$menus[$r->mid] = $menu;

				$trxLookup['tkmenu'][] = $menu->id;
			}

			if ($r->eid && !isset($menus[$r->mid]->products[$r->eid]))
			{
				$prod = new stdClass;
				$prod->id          = $r->eid;
				$prod->name        = $r->ename;
				$prod->description = $r->edesc;
				$prod->price       = $r->eprice;
				$prod->ready       = $r->eready;
				$prod->image       = $r->eimg;
				$prod->options     = array();
				$prod->attributes  = array();

				/**
				 * Build images gallery.
				 * 
				 * @since 1.8.2
				 */
				$prod->images = array();

				/**
				 * Add image to list only if specified.
				 *
				 * @since 1.8.3
				 */
				if ($prod->image)
				{
					$prod->images[] = $prod->image;
				}

				if ($r->eimgextra)
				{
					// merge main image with extra images
					$prod->images = array_merge($prod->images, json_decode($r->eimgextra));
				}

				// search for product attributes
				if (isset($attrLookup[$r->eid]))
				{
					// iterate all attributes
					foreach ($attributes as $attr)
					{
						// check if the product is assigned to this attribute
						if (in_array($attr->id, $attrLookup[$r->eid]))
						{
							// copy attribute details
							$prod->attributes[] = $attr;
						}
					}
				}

				$menus[$r->mid]->products[$r->eid] = $prod;

				$trxLookup['tkentry'][] = $prod->id;
			}
			
			if ($r->oid)
			{
				$opt = new stdClass;
				$opt->id    = $r->oid;
				$opt->name  = $r->oname;
				$opt->price = $r->oprice;

				$menus[$r->mid]->products[$r->eid]->options[$r->oid] = $opt;

				$trxLookup['tkentryoption'][] = $opt->id;
			}
		}

		// translate records
		$this->translate($menus, $trxLookup);
		
		return $menus;
	}

	/**
	 * Translates the menus details.
	 *
	 * @param 	object 	&$menus  The menus to translate.
	 * @param 	array 	$lookup  A lookup of IDs to preload.
	 *
	 * @return 	void
	 *
	 * @since 	1.8
	 */
	private function translate(&$menus, array $lookup)
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
		
		// preload translations
		foreach ($lookup as $table => $ids)
		{
			// preload translations for current table
			$lookup[$table] = $translator->load($table, $ids, $langtag);
		}

		// iterate menus
		foreach ($menus as &$menu)
		{
			// get translation of current menu
			$menu_tx = $lookup['tkmenu']->getTranslation($menu->id, $langtag);

			if ($menu_tx)
			{
				$menu->title       = $menu_tx->title;
				$menu->description = $menu_tx->description;
			}

			// iterate menu products
			foreach ($menu->products as &$product)
			{
				// get translation of current product
				$prod_tx = $lookup['tkentry']->getTranslation($product->id, $langtag);

				if ($prod_tx)
				{
					$product->name        = $prod_tx->name;
					$product->description = $prod_tx->description;
				}

				// iterate product options
				foreach ($product->options as &$option)
				{
					// get translation of current option
					$opt_tx = $lookup['tkentryoption']->getTranslation($option->id, $langtag);

					if ($opt_tx)
					{
						$option->name = $opt_tx->name;
					}
				}
				// end option
			}
			// end product
		}
		// end menu
	}

	/**
	 * Returns the data needed to setup a gallery of images.
	 *
	 * @return 	object
	 *
	 * @since 	1.8.2
	 */
	protected function getGalleryData()
	{
		$gallery = new stdClass;
		$gallery->groupBy = 'menu';
		$gallery->images  = array();

		foreach ($this->menus as $menu)
		{
			$gallery->images[$menu->id] = array();

			foreach ($menu->products as $prod)
			{
				// check if the product owns more than one image
				if (count($prod->images) > 1)
				{
					// we should group the images by product, so that
					// the gallery can show only the images of the
					// selected item
					$gallery->groupBy = 'product';
				}

				// iterate all images
				foreach ($prod->images as $i => $image)
				{
					// fetch gallery data
					$data = new stdClass;
					$data->thumb   = VREMEDIA_SMALL_URI . $image;
					$data->uri     = VREMEDIA_URI . $image;
					$data->caption = $prod->name;
					$data->id      = $prod->id;
					$data->idMenu  = $menu->id;

					// group by menu
					$gallery->images[$menu->id][] = $data;
				}
			}
		}

		if ($gallery->groupBy == 'menu')
		{
			return $gallery;
		}

		// assign gallery to a temporary variable
		$list = $gallery->images;

		// reset gallery
		$gallery->images = array();

		// iterate all menus
		foreach ($list as $id_menu => $images)
		{
			// iterate menu images
			foreach ($images as $image)
			{
				// create repository for current product if doesn't exist
				if (!isset($gallery->images[$image->id]))
				{
					$gallery->images[$image->id] = array();
				}

				// add data to gallery
				$gallery->images[$image->id][] = $image;
			}
		}

		return $gallery;
	}
}
