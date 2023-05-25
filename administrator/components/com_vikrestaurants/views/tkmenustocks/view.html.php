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
 * VikRestaurants take-away menu stocks view.
 *
 * @since 1.7
 */
class VikRestaurantsViewtkmenustocks extends JViewVRE
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

		$filters = array();
		$filters['id_menu']   = $app->getUserStateFromRequest('vre.tkmenustocks.id_menu', 'id_menu', 0, 'uint');
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.tkmenustocks.keysearch', 'keysearch', '', 'string');

		// get all menus
		$menus = JHtml::_('vikrestaurants.takeawaymenus');

		/**
		 * Do not proceed in case the menus list is empty because probably
		 * the customer is accessing a section without having configured
		 * any menus.
		 *
		 * @since 1.7.4
		 */
		if (!$menus)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
			$app->redirect('index.php?option=com_vikrestaurants&view=tkmenus');
			exit;
		}

		// get first available menu if not specified
		if (empty($filters['id_menu']))
		{
			$filters['id_menu'] = $menus[0]->value;
		}

		$products = array();

		// fetch stock
		$q = $dbo->getQuery(true);

		$q->select(array(
			$dbo->qn('e.id', 'entry_id'),
			$dbo->qn('e.name', 'entry_name'),
			$dbo->qn('e.img_path', 'entry_image'),
			$dbo->qn('e.items_in_stock', 'entry_stock'),
			$dbo->qn('e.notify_below', 'entry_notify'),
		));

		$q->select(array(
			$dbo->qn('o.id', 'option_id'),
			$dbo->qn('o.name', 'option_name'),
			$dbo->qn('o.stock_enabled', 'option_stock_enabled'),
			$dbo->qn('o.items_in_stock', 'option_stock'),
			$dbo->qn('o.notify_below', 'option_notify'),
		));

		$q->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('e.id') . ' = ' . $dbo->qn('o.id_takeaway_menu_entry'));

		$q->where($dbo->qn('e.id_takeaway_menu') . ' = ' . $filters['id_menu']);

		if ($filters['keysearch'])
		{
			$q->where(sprintf('CONCAT_WS(\' \', %s, %s) LIKE %s', $dbo->qn('e.name'), $dbo->qn('o.name'), $dbo->q("%{$filters['keysearch']}%")));
		}

		$q->order($dbo->qn('e.ordering') . ' ASC');
		$q->order($dbo->qn('o.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				if (!isset($products[$row->entry_id]))
				{
					$entry = new stdClass;
					$entry->id             = $row->entry_id;
					$entry->name           = $row->entry_name;
					$entry->image          = $row->entry_image;
					$entry->items_in_stock = $row->entry_stock;
					$entry->notify_below   = $row->entry_notify;
					$entry->options        = array();

					$products[$entry->id] = $entry;
				}

				if ($row->option_id)
				{
					$option = new stdClass;
					$option->id             = $row->option_id;
					$option->name           = $row->option_name;
					$option->stock_enabled  = $row->option_stock_enabled;
					$option->items_in_stock = $row->option_stock;
					$option->notify_below   = $row->option_notify;

					$products[$entry->id]->options[] = $option;
				}
			}
		}
		
		$this->products = &$products;
		$this->filters 	= &$filters;
		$this->menus 	= &$menus;
		
		// Display the template (default.php)
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
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWTKMENUSTOCKS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants')) 
		{
			JToolbarHelper::apply('tkmenustocks.save', JText::_('VRSAVE'));
			JToolbarHelper::save('tkmenustocks.saveclose', JText::_('VRSAVEANDCLOSE'));
			JToolbarHelper::divider();
		}
		
		JToolbarHelper::cancel('tkmenu.cancel', JText::_('VRCANCEL'));
	}
}
