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
 * VikRestaurants take-away deal management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanagetkdeal extends JViewVRE
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
			
		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_deal'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$deal = $dbo->loadObject();

				// decode saved shifts
				$deal->shifts = (array) json_decode($deal->shifts);

				// get days
				$q = $dbo->getQuery(true)
					->select($dbo->qn('id_weekday'))
					->from($dbo->qn('#__vikrestaurants_takeaway_deal_day_assoc'))
					->where($dbo->qn('id_deal') . ' = ' . $deal->id)
					->order($dbo->qn('id_weekday') . ' ASC');

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$deal->days = $dbo->loadColumn();
				}
				else
				{
					$deal->days = array();
				}

				// get target products
				$q = $dbo->getQuery(true)
					->select('d.*')
					->select($dbo->qn('e.name', 'product_name'))
					->select($dbo->qn('o.name', 'option_name'))
					->from($dbo->qn('#__vikrestaurants_takeaway_deal_product_assoc', 'd'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('d.id_product'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('d.id_option'))
					->where($dbo->qn('id_deal') . ' = ' . $deal->id);

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$deal->products = $dbo->loadObjectList();
				}
				else
				{
					$deal->products = array();
				}

				// get free products
				$q = $dbo->getQuery(true)
					->select('d.*')
					->select($dbo->qn('e.name', 'product_name'))
					->select($dbo->qn('o.name', 'option_name'))
					->from($dbo->qn('#__vikrestaurants_takeaway_deal_free_assoc', 'd'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('d.id_product'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('o.id') . ' = ' . $dbo->qn('d.id_option'))
					->where($dbo->qn('id_deal') . ' = ' . $deal->id);

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$deal->gifts = $dbo->loadObjectList();
				}
				else
				{
					$deal->gifts = array();
				}
			}
		}

		if (empty($deal))
		{
			$deal = (object) $this->getBlankItem();
		}

		// use deal data stored in user state
		$this->injectUserStateData($deal, 'vre.tkdeal.data');

		// get all products
		$menus = array();

		$q = $dbo->getQuery(true);

		$q->select(array(
			$dbo->qn('m.id', 'id_menu'),
			$dbo->qn('m.title', 'menu_title'),
		));

		$q->select(array(
			$dbo->qn('e.id', 'id_product'),
			$dbo->qn('e.name', 'product_name'),
		));

		$q->select(array(
			$dbo->qn('o.id', 'id_option'),
			$dbo->qn('o.name', 'option_name'),
		));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('o.id_takeaway_menu_entry'));

		$q->order($dbo->qn('m.ordering') . ' ASC');
		$q->order($dbo->qn('e.ordering') . ' ASC');
		$q->order($dbo->qn('o.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $r)
			{
				if (!isset($menus[$r->id_menu]))
				{
					$menu = new stdClass;
					$menu->id       = $r->id_menu;
					$menu->title    = $r->menu_title;
					$menu->products = array();

					$menus[$r->id_menu] = $menu;
				}

				if ($r->id_product && !isset($menus[$r->id_menu]->products[$r->id_product]))
				{
					$prod = new stdClass;
					$prod->id      = $r->id_product;
					$prod->name    = $r->product_name;
					$prod->options = array();

					$menus[$r->id_menu]->products[$r->id_product] = $prod;
				}

				if ($r->id_option)
				{
					$opt = new stdClass;
					$opt->id   = $r->id_option;
					$opt->name = $r->option_name;

					$menus[$r->id_menu]->products[$r->id_product]->options[] = $opt;
				}
			}
		}

		/**
		 * Returns a list of supported deals.
		 *
		 * @since 1.8
		 */
		VRELoader::import('library.deals.handler');
		$this->deals = DealsHandler::getSupportedDeals();
		
		$this->deal  = &$deal;
		$this->menus = &$menus;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem()
	{
		return array(
			'id'           => 0,
			'name'         => '',
			'description'  => '',
			'start_ts'     => -1,
			'end_ts'       => -1,
			'shifts'       => array(),
			'service'      => 2,
			'max_quantity' => 1,
			'published'    => 0,
			'type'         => 0,
			'amount'       => 0.0,
			'percentot'    => 2,
			'auto_insert'  => 0,
			'min_quantity' => 1,
			'cart_tcost'   => 0.0,
			'days'         => array(),
			'products'     => array(),
			'gifts'        => array(),
		);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string  $type  The view type ('edit' or 'new').
	 *
	 * @return 	void
	 */
	private function addToolBar($type)
	{
		// add menu title and some buttons to the page
		if ($type == 'edit') 
		{
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITTKDEAL'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWTKDEAL'), 'vikrestaurants');
		}

		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('tkdeal.save', JText::_('VRSAVE'));
			JToolbarHelper::save('tkdeal.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('tkdeal.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('tkdeal.cancel', JText::_('VRCANCEL'));
	}
}
