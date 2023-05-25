<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_grid
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Take-Away Grid module.
 *
 * @since 1.3.1
 */
class VikRestaurantsTakeAwayGridHelper
{
	/**
	 * Fetch the list of the products selected from the admin.
	 *
	 * @param 	JRegistry 	$param 	The configuration object.
	 *
	 * @return 	array 	The list containing the products.
	 */
	public static function getProducts($params)
	{
		// fetch products list

		$dbo = JFactory::getDbo();

		$products = array();

		$menus_ids 	 = array();
		$entries_ids = array();
		$options_ids = array();
		$attrs_ids 	 = array();

		$list = $params->get('products', array());

		if (!count($list))
		{
			/**
			 * When the products parameter is empty, get all the published products.
			 *
			 * @since 1.3
			 */
			$q = $dbo->getQuery(true)
				->select(array($dbo->qn('e.id', 'eid'), $dbo->qn('o.id', 'oid')))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'))
				->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'))
				->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id_takeaway_menu_entry') . ' = ' . $dbo->qn('e.id'))
				->where(array(
					$dbo->qn('m.published') . ' = 1',
					$dbo->qn('e.published') . ' = 1',
				))
				->order(array(
					$dbo->qn('m.ordering') . ' ASC',
					$dbo->qn('e.ordering') . ' ASC',
					$dbo->qn('o.ordering') . ' ASC',
				));

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				foreach ($dbo->loadObjectList() as $p)
				{
					$list[] = $p->eid . '-' . (int) $p->oid;
				}
			}
		}

		foreach ($list as $id)
		{
			list($id_entry, $id_option) = explode('-', $id);

			$q = $dbo->getQuery(true);

			/**
			 * The query now supports the product description,
			 * which could be used in the module overrides.
			 *
			 * @since 1.3.1
			 */

			$q->select(array(
				$dbo->qn('e.id', 'eid'),
				$dbo->qn('e.name', 'ename'),
				$dbo->qn('e.description', 'edesc'),
				$dbo->qn('e.price', 'eprice'),
				$dbo->qn('e.img_path', 'eimage'),
			));
			$q->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'));

			$q->select(array(
				$dbo->qn('o.id', 'oid'),
				$dbo->qn('o.name', 'oname'),
				$dbo->qn('o.inc_price', 'oprice'),
			));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id_takeaway_menu_entry') . ' = ' . $dbo->qn('e.id'));

			$q->select(array(
				$dbo->qn('m.id', 'mid'), 
				$dbo->qn('m.title', 'mtitle'),
			));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus', 'm') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'));

			$q->select(array(
				$dbo->qn('a.id', 'aid'),
				$dbo->qn('a.name', 'aname'),
				$dbo->qn('a.icon', 'aicon'),
			));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc', 'assoc') . ' ON ' . $dbo->qn('assoc.id_menuentry') . ' = ' . $dbo->qn('e.id'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_attribute', 'a') . ' ON ' . $dbo->qn('a.id') . ' = ' . $dbo->qn('assoc.id_attribute'));

			$q->where($dbo->qn('e.id') . ' = ' . (int) $id_entry);

			if ((int) $id_option)
			{
				$q->where($dbo->qn('o.id') . ' = ' . (int) $id_option);
			}

			/**
			 * Ignore the products that are not published.
			 *
			 * @since 1.2
			 */
			$q->where(array(
				$dbo->qn('m.published') . ' = 1',
				$dbo->qn('e.published') . ' = 1',
			));

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$last_entry = '0-0';

				foreach ($dbo->loadObjectList() as $obj)
				{
					if ($last_entry != $obj->eid . '-' . $obj->oid)
					{
						$std = new stdClass;

						$std->idEntry    = $obj->eid;
						$std->entryName  = $obj->ename;
						$std->entryDesc  = $obj->edesc;
						$std->entryPrice = $obj->eprice;
						$std->entryImage = $obj->eimage;

						$std->idOption    = (int) $obj->oid;
						$std->optionName  = $obj->oname;
						$std->optionPrice = (float) $obj->oprice;

						$std->idMenu    = $obj->mid;
						$std->menuTitle	= $obj->mtitle;	

						$std->fullName  = $std->entryName . (empty($std->optionName) ? '' : ' - ' . $std->optionName); 
						$std->fullPrice = $std->entryPrice + $std->optionPrice;

						$std->attributes = array();

						$products[] = $std;

						$last_entry = $obj->eid . '-' . $obj->oid;

						$entries_ids[] = $std->idEntry;
						$options_ids[] = $std->idOption;
						$menus_ids[]   = $std->idMenu;
					}

					if (!empty($obj->aid))
					{
						$attr = new stdClass;

						$attr->id   = $obj->aid;
						$attr->name = $obj->aname;
						$attr->icon = $obj->aicon;

						end($products)->attributes[] = $attr;

						$attrs_ids[] = $attr->id;
					}
				}
			}
		}

		/**
		 * Provides items translations.
		 *
		 * @since 1.1
		 */
		if (VikRestaurants::isMultilanguage())
		{
			// get current language tag
			$tag = JFactory::getLanguage()->getTag();

			// get translator
			$translator = VREFactory::getTranslator();

			// preload table translations
			$menuLang = $translator->load('tkmenu', array_unique($menus_ids), $tag);
			$prodLang = $translator->load('tkentry', array_unique($entries_ids), $tag);
			$optLang  = $translator->load('tkentryoption', array_unique($options_ids), $tag);
			$attrLang = $translator->load('tkattr', array_unique($attrs_ids), $tag);

			foreach ($products as &$prod)
			{
				// translate menu for the given language
				$tx = $menuLang->getTranslation($prod->idMenu, $tag);

				if ($tx)
				{
					// apply menu translation
					$prod->menuTitle = $tx->title;
				}

				// translate product for the given language
				$tx = $prodLang->getTranslation($prod->idEntry, $tag);

				if ($tx)
				{
					// apply product translation
					$prod->entryName = $tx->name;
					$prod->entryDesc = $tx->description;
				}

				// translate option for the given language
				$tx = $optLang->getTranslation($prod->idOption, $tag);

				if ($tx)
				{
					// apply option translation
					$prod->optionName = $tx->name;
				}

				foreach ($prod->attributes as &$attr)
				{
					// translate attribute for the given language
					$tx = $attrLang->getTranslation($attr->id, $tag);

					if ($tx)
					{
						// apply attribute translation
						$attr->name = $tx->name;
					}
				}

				// refactor full name
				$prod->fullName = $prod->entryName . (empty($prod->optionName) ? '' : ' - ' . $prod->optionName); 
			}
		}

		return $products;
	}

	/**
	 * Get all the available take-away menus.
	 *
	 * @param 	array 	$products 	The list of products to filter the menus.
	 * 								The array MUST be returned from the getProducts() function.
	 * 								Do not provide this param in case you need to display
	 * 								all the menus you have.
	 *
	 * @return 	array 	A list of menus.
	 *
	 * @see 	getProducts() 	Get a list of products.
	 */
	public static function getAllMenus(array $products = null)
	{
		if ($products === null || !count($products) || !isset($products[0]->idMenu))
		{
			return $menus;
		}

		$menus = array();

		foreach ($products as $p)
		{
			if (!isset($menus[$p->idMenu]))
			{
				$std = new stdClass;

				$std->id 	= $p->idMenu;
				$std->title = $p->menuTitle;

				$menus[$p->idMenu] = $std;
			}
		}

		return $menus;
	}
}
