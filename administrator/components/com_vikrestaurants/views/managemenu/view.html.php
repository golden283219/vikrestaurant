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
 * VikRestaurants menu management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagemenu extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$ids  = $input->getUint('cid', array());
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$menu = array();

		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_menus'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$menu = $dbo->loadObject();

				$menu->working_shifts = array_filter(preg_split("/,\s*/", $menu->working_shifts));
				$menu->days_filter    = array_filter(preg_split("/,\s*/", $menu->days_filter), 'strlen');

				$q = $dbo->getQuery(true)
					->select($dbo->qn(array(
						's.id', 's.name', 's.description', 's.published', 's.highlight', 's.orderdishes', 's.image',
					)))
					->select(array(
						$dbo->qn('p.id', 'id_product'),
						$dbo->qn('p.name', 'prod_name'),
						$dbo->qn('p.price', 'prod_price'),
						$dbo->qn('a.charge', 'prod_charge'),
						$dbo->qn('a.id', 'id_assoc'),
					))
					->from($dbo->qn('#__vikrestaurants_menus_section', 's'))
					->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('s.id') . ' = ' . $dbo->qn('a.id_section'))
					->leftjoin($dbo->qn('#__vikrestaurants_section_product', 'p') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('a.id_product'))
					->where($dbo->qn('s.id_menu') . ' = ' . $menu->id)
					->order(array(
						$dbo->qn('s.ordering') . ' ASC',
						$dbo->qn('a.ordering') . ' ASC',
					));
				
				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$menu->sections = array();

					foreach ($dbo->loadObjectList() as $tmp)
					{
						if (!isset($menu->sections[$tmp->id]))
						{
							$section = new stdClass;
							$section->id          = $tmp->id;
							$section->name        = $tmp->name;
							$section->image       = $tmp->image;
							$section->description = $tmp->description;
							$section->published   = $tmp->published;
							$section->highlight   = $tmp->highlight;
							$section->orderdishes = $tmp->orderdishes;
							$section->products    = array();

							$menu->sections[$tmp->id] = $section;
						}

						if ($tmp->id_product)
						{
							$prod = new stdClass;
							$prod->id        = $tmp->id_assoc;
							$prod->name      = $tmp->prod_name;
							$prod->price     = $tmp->prod_price;
							$prod->charge    = $tmp->prod_charge;
							$prod->idProduct = $tmp->id_product;
							$prod->idSection = $tmp->id;

							$menu->sections[$tmp->id]->products[] = $prod;
						}
					}
				}
				else
				{
					$menu->sections = array();
				}
			}
		}

		if (empty($menu))
		{
			$menu = (object) $this->getBlankItem();
		}

		// use menu data stored in user state
		$this->injectUserStateData($menu, 'vre.menu.data');
		
		// get products

		$products = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name', 'image', 'description', 'price')))
			->from($dbo->qn('#__vikrestaurants_section_product'))
			->where($dbo->qn('hidden') . ' = 0')
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$products = $dbo->loadObjectList();
		}
		
		$this->menu     = &$menu;
		$this->products = &$products;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @param 	integer  $status  The status to pre-select.
	 *
	 * @return 	array 	 A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem()
	{
		return array(
			'id'             => 0,
			'name'           => '',
			'alias'          => '',
			'description'    => '',
			'cost'           => 0.0,
			'published'      => 0,
			'choosable'      => 0,
			'special_day'    => 0,
			'working_shifts' => array(),
			'days_filter'    => array(),
			'image'          => '',
			'sections'       => array(),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITMENU'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWMENU'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('menu.save', JText::_('VRSAVE'));
			JToolbarHelper::save('menu.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('menu.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('menu.cancel', JText::_('VRCANCEL'));
	}
}
