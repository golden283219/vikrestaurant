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
 * VikRestaurants take-away order cart management view.
 *
 * @since 	1.6
 */
class VikRestaurantsViewmanagetkrescart extends JViewVRE
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
		
		// set the toolbar
		$this->addToolBar();
		
		$id = $input->get('cid', array(0), 'uint');
		
		// load order details
		$order = VREOrderFactory::getOrder($id[0]);

		if (!$order)
		{
			// order not found, go back to orders list
			$app->redirect('index.php?option=com_vikrestaurants&view=tkreservations');
			exit;
		}

		$menus = array();
		$count = 0;

		// get all products

		$q = $dbo->getQuery(true);

		// get menus details
		$q->select($dbo->qn('m.id', 'menu_id'));
		$q->select($dbo->qn('m.title', 'menu_title'));
		$q->select($dbo->qn('m.published', 'menu_published'));
		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'));

		// get products details
		$q->select($dbo->qn('i.id', 'product_id'));
		$q->select($dbo->qn('i.name', 'product_name'));
		$q->select($dbo->qn('i.description', 'product_description'));
		$q->select($dbo->qn('i.price', 'product_price'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'i') . ' ON ' . $dbo->qn('i.id_takeaway_menu') . ' = ' . $dbo->qn('m.id'));

		$q->order($dbo->qn('m.ordering') . ' ASC');
		$q->order($dbo->qn('i.ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				if (!isset($menus[$row->menu_id]))
				{
					$menu = new stdClass;

					$menu->id        = $row->menu_id;
					$menu->title     = $row->menu_title;
					$menu->published = $row->menu_published;
					$menu->products  = array();

					$menus[$row->menu_id] = $menu;
				}

				if ($row->product_id)
				{
					$prod = new stdClass;

					$prod->id          = $row->product_id;
					$prod->name        = $row->product_name;
					$prod->description = $row->product_description;
					$prod->price       = $row->product_price;

					$menus[$row->menu_id]->products[] = $prod;

					$count++;
				}
			}
		}
		
		$this->order = &$order;
		$this->menus = &$menus;
		$this->count = &$count;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLETKORDERCART'), 'vikrestaurants');

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to order management page
			JToolbarHelper::apply('tkreservation.edit', JText::_('VRSAVE'));
			// back to orders list
			JToolbarHelper::save('tkreservation.cancel', JText::_('VRSAVEANDCLOSE'));
		}

		JToolbarHelper::cancel('tkreservation.cancel', JText::_('VRCANCEL'));
	}
}
