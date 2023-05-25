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
 * VikRestaurants take-away items stock overrides/refills view.
 *
 * @since 1.7
 */
class VikRestaurantsViewtkstocks extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('tkstocks', 'remaining', 1);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['id_menu']   = $app->getUserStateFromRequest('vre.tkstocks.id_menu', 'id_menu', 0, 'uint');
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.tkstocks.keysearch', 'keysearch', '', 'string');

		// get all menus
		$menus = JHtml::_('vikrestaurants.takeawaymenus');

		/**
		 * Do not proceed in case the menus list is empty because probably
		 * the customer is accessing a section without having configured
		 * any menus.
		 *
		 * @since 1.8
		 */
		if (!$menus)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
			$app->redirect('index.php?option=com_vikrestaurants&view=tkmenus');
			exit;
		}

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $this->buildStockQuery($filters, $ordering);

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		// assert limit used for list query
		$this->assertListQuery($lim0, $lim);
		
		if ($dbo->getNumRows())
		{
			$rows = $dbo->loadAssocList();

			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}

		$new_type = OrderingManager::getSwitchColumnType('tkstocks', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);
		
		$this->rows 	= &$rows;
		$this->menus 	= &$menus;
		$this->navbut 	= &$navbut;
		$this->filters 	= &$filters;
		$this->ordering = &$ordering;
		
		// display the template (default.php)
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
		JToolbarHelper::title(JText::_('VRTKSTOCKSOVERVIEW'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('tkstocks.save', JText::_('VRSAVE'));
			JToolbarHelper::save('tkstocks.saveclose', JText::_('VRSAVEANDCLOSE'));
			JToolbarHelper::divider();
		}

		JToolbarHelper::cancel('tkreservation.cancel', JText::_('VRCANCEL'));
	}

	/**
	 * Builds the query that will be used to retrieve the current items in stock.
	 *
	 * @param 	array 	$filters   An associative array of filters.
	 * @param 	array 	$ordering  An array containing ordering column and direction.
	 *
	 * @return 	string 	The database query.
	 */
	protected function buildStockQuery($filters, $ordering)
	{
		$dbo = JFactory::getDbo();

		$where = "";

		if (!empty($filters['id_menu']))
		{
			$where = " AND `e`.`id_takeaway_menu` = " . $filters['id_menu'];
		}

		if (!empty($filters['keysearch']))
		{
			$where .= " AND CONCAT_WS(' ', `e`.`name`, `o`.`name`) LIKE " . $dbo->q("%{$filters['keysearch']}%");
		}

		$order = '';

		$ordering_dir = $ordering['type'] == 2 ? 'DESC' : 'ASC';

		if ($ordering['column'] != 'remaining')
		{
			if ($ordering['column'] == 'concat_name')
			{
				// backward compatibility to avoid fatal errors
				$ordering['column'] = 'ename';
			}

			$order = $dbo->qn($ordering['column']) . ' ' . $ordering_dir;
		}
		else
		{
			$order = '(`products_in_stock` - `products_used`) ' . $ordering_dir;
		}

		$order .= ', ' . $dbo->qn('e.ordering') . ' ' . $ordering_dir;
		$order .= ', ' . $dbo->qn('o.ordering') . ' ' . $ordering_dir;

		/**
		 * Added support for linked stocks.
		 * The variations with stock disabled will use the
		 * stock value of the parent item.
		 *
		 * @since 1.8
		 */
		return "SELECT SQL_CALC_FOUND_ROWS `e`.`id` AS `eid`, `e`.`name` AS `ename`, `o`.`id` AS `oid`, `o`.`name` AS `oname`, `o`.`stock_enabled`,
			IF(`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, `e`.`notify_below`, `o`.`notify_below`) AS `product_notify_below`,
			IF(`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, `e`.`items_in_stock`, `o`.`items_in_stock`) AS `product_original_stock`,
			IF(`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 
				(
					IFNULL(
						(
							SELECT SUM(`so`.`items_available`) 
							FROM `#__vikrestaurants_takeaway_stock_override` AS `so` 
							WHERE `so`.`id_takeaway_entry` = `e`.`id`
							AND `so`.`id_takeaway_option` IS NULL
						), `e`.`items_in_stock`
					)
				), (
					IFNULL(
						(
							SELECT SUM(`so`.`items_available`) 
							FROM `#__vikrestaurants_takeaway_stock_override` AS `so` 
							WHERE `so`.`id_takeaway_entry` = `e`.`id` AND `so`.`id_takeaway_option` = `o`.`id`
						), `o`.`items_in_stock`
					)
				)
			) AS `products_in_stock`,
			IF(`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 
				(
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `io` ON `i`.`id_product_option` = `io`.`id`
							WHERE (`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING') AND `i`.`id_product` = `e`.`id`
							AND (`o`.`id` IS NULL OR `io`.`stock_enabled` = 0)
						), 0
					)
				), (
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							WHERE (`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING') AND `i`.`id_product` = `e`.`id` AND `i`.`id_product_option` = `o`.`id`
						), 0
					)
				)
			) AS `products_used`
			FROM `#__vikrestaurants_takeaway_menus_entry` AS `e`
			LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `o` ON `e`.`id` = `o`.`id_takeaway_menu_entry`
			WHERE 1 $where
			ORDER BY $order";
	}
}
