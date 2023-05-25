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
 * VikRestaurants menu detais view.
 * Displays the details of the menus
 * that have been selected.
 *
 * @since 1.5
 */
class VikRestaurantsViewmenudetails extends JViewVRE
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
		
		// get menu ID from request
		$id_menu = $input->get('id', 0, 'uint');
		
		$q = $dbo->getQuery(true);

		$q->select('s.*');
		$q->select($dbo->qn('p.id', 'pid'));
		$q->select($dbo->qn('p.name', 'pname'));
		$q->select($dbo->qn('p.image', 'pimage'));
		$q->select($dbo->qn('p.description', 'pdesc'));
		$q->select(sprintf('(%s + %s) AS %s', $dbo->qn('p.price'), $dbo->qn('a.charge'), $dbo->qn('pcharge')));
		$q->select($dbo->qn('a.id', 'aid'));
		$q->select($dbo->qn('m.name', 'mname'));
		$q->select($dbo->qn('m.description', 'mdesc'));
		$q->select($dbo->qn('m.image', 'mimage'));
		$q->select($dbo->qn('o.id', 'oid'));
		$q->select($dbo->qn('o.name', 'oname'));
		$q->select($dbo->qn('o.inc_price', 'oprice'));

		$q->from($dbo->qn('#__vikrestaurants_menus', 'm'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_menus_section', 's') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('s.id_menu'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('s.id') . ' = ' . $dbo->qn('a.id_section'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_section_product', 'p') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('a.id_product') . ' AND ' . $dbo->qn('p.published') . ' = 1');
		$q->leftjoin($dbo->qn('#__vikrestaurants_section_product_option', 'o') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('o.id_product'));
		
		$q->where($dbo->qn('m.id') . ' = ' . $id_menu);
		$q->where($dbo->qn('m.published') . ' = 1');
		$q->where($dbo->qn('s.published') . ' = 1');

		$q->order($dbo->qn('s.ordering') . ' ASC');
		$q->order($dbo->qn('a.ordering') . ' ASC');
		$q->order($dbo->qn('o.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			/**
			 * Raise an exception as it wasn't possible to find the specified menu.
			 *
			 * @since 1.7.4
			 */
			throw new Exception(JText::_('JERROR_PAGE_NOT_FOUND'), 404);
		}

		$menu = null;

		$ids = array(
			'menusection'   => array(),
			'menusproduct'  => array(),
			'productoption' => array(),
		);

		foreach ($dbo->loadObjectList() as $r)
		{		
			if (!$menu)
			{
				$menu = new stdClass;
				$menu->id          = $r->id_menu;
				$menu->name        = $r->mname;
				$menu->description = $r->mdesc;
				$menu->image       = $r->mimage;
				$menu->sections    = array();
			}
			
			if ($r->id && !isset($menu->sections[$r->id]))
			{
				$section = new stdClass;
				$section->id          = $r->id;
				$section->name        = $r->name;
				$section->description = $r->description;
				$section->image       = $r->image;
				$section->highlight   = $r->highlight;
				$section->products    = array();

				$menu->sections[$r->id] = $section;

				$ids['menusection'][] = $r->id;
			}
			
			if ($r->pid && !isset($menu->sections[$r->id]->products[$r->pid]))
			{
				$prod = new stdClass;
				$prod->id          = $r->pid;
				$prod->name        = $r->pname;
				$prod->description = $r->pdesc;
				$prod->image       = $r->pimage;
				$prod->price       = $r->pcharge;
				$prod->options     = array();

				$menu->sections[$r->id]->products[$r->pid] = $prod;

				$ids['menusproduct'][] = $r->pid;
			}
			
			if ($r->oid)
			{
				$opt = new stdClass;
				$opt->id    = $r->oid;
				$opt->name  = $r->oname;
				$opt->price = $r->oprice;
				
				$menu->sections[$r->id]->products[$r->pid]->options[$r->oid] = $opt;

				$ids['productoption'][] = $r->oid;
			}
		}

		// translate menus
		$this->translate($menu, $ids);

		/**
		 * Check if the menu is printable.
		 *
		 * @since 1.7.4
		 */
		$is_printable = $input->getUint('printable_menu', null);

		if (is_null($is_printable) || $is_printable == -1)
		{
			// check for parent argument
			$is_printable = $app->getUserState('vre.menuslist.printable', false);
		}
		else
		{
			$is_printable = (bool) $is_printable;
		}

		// auto-print the menu details in case it is possible to
		// print them and in case of blank template
		if ($is_printable && $input->get('tmpl') == 'component')
		{
			JFactory::getDocument()->addScriptDeclaration('jQuery(document).ready(function() { window.print(); });');
		}
		
		/**
		 * An object containing the menu details.
		 *
		 * @var object
		 */
		$this->menu = &$menu;
		
		/**
		 * Flag used to check whether to display
		 * a button to print the menu details page.
		 *
		 * @var boolean
		 */
		$this->isPrintable = $is_printable;

		// prepare page content
		VikRestaurants::prepareContent($this);
		
		// display the template
		parent::display($tpl);

	}
	
	/**
	 * Translates the menu details.
	 *
	 * @param 	object 	&$menu   The menu to translate.
	 * @param 	array 	$lookup  A lookup of IDs to preload.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	private function translate(&$menu, array $lookup)
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

		// get menu translation
		$menu_tx = $translator->translate('menu', $menu->id, $langtag);

		if ($menu_tx)
		{
			$menu->name        = $menu_tx->name;
			$menu->description = $menu_tx->description;
		}
		
		// preload translations
		foreach ($lookup as $table => $ids)
		{
			// preload translations for current table
			$lookup[$table] = $translator->load($table, $ids, $langtag);
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
}
