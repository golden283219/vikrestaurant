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
 * VikRestaurants menus preview page.
 *
 * @since 1.5
 */
class VikRestaurantsViewsneakmenu extends JViewVRE
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
		
		// force tmpl=component
		$input->set('tmpl', 'component');

		$id = $input->get('id', 0, 'uint');
		
		$sections = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('s.id', 's.name', 's.published', 's.orderdishes', 's.image')))
			->select(array(
				$dbo->qn('p.id', 'pid'),
				$dbo->qn('p.name', 'pname'),
				$dbo->qn('p.price', 'pprice'),
				$dbo->qn('p.image', 'pimage'),
				$dbo->qn('p.published', 'ppublished'),
			))
			->select(array(
				$dbo->qn('a.id', 'aid'),
				$dbo->qn('a.charge', 'acharge'),
			))
			->from($dbo->qn('#__vikrestaurants_menus_section', 's'))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product_assoc', 'a') . ' ON ' . $dbo->qn('s.id') . ' = ' . $dbo->qn('a.id_section'))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product', 'p') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('a.id_product'))
			->where($dbo->qn('s.id_menu') . ' = ' . $id)
			->order(array(
				$dbo->qn('s.ordering') . ' ASC',
				$dbo->qn('a.ordering') . ' ASC',
			));
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				if (!isset($sections[$row->id]))
				{
					$section = new stdClass;
					$section->id          = $row->id;
					$section->name        = $row->name;
					$section->image       = $row->image;
					$section->published   = $row->published;
					$section->orderdishes = $row->orderdishes;
					$section->products    = array();

					$sections[$row->id] = $section;
				}

				if ($row->pid)
				{
					$prod = new stdClass;
					$prod->id        = $row->pid;
					$prod->name      = $row->pname;
					$prod->image     = $row->pimage;
					$prod->price     = $row->pprice + $row->acharge;
					$prod->charge    = $row->acharge;
					$prod->published = $row->ppublished;
					$prod->idAssoc   = $row->aid;

					$sections[$row->id]->products[] = $prod;
				}
			}
		}
		
		$this->sections = &$sections;

		// display the template
		parent::display($tpl);
	}
}
