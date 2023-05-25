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
 * VikRestaurants take-away items stock statistics view.
 *
 * @since 1.7
 */
class VikRestaurantsViewtkstatstocks extends JViewVRE
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

		$ordering = OrderingManager::getColumnToOrder('tkstatstocks', 'products_used', 2);

		// set the toolbar
		$this->addToolBar();

		$filters = array();
		$filters['start_day'] = $app->getUserStateFromRequest('vre.tkstatstocks.start_day', 'start_day', '', 'string');
		$filters['end_day']   = $app->getUserStateFromRequest('vre.tkstatstocks.end_day', 'end_day', '', 'string');
		$filters['id_menu']   = $app->getUserStateFromRequest('vre.tkstatstocks.id_menu', 'id_menu', 0, 'uint');
		$filters['keysearch'] = $app->getUserStateFromRequest('vre.tkstatstocks.keysearch', 'keysearch', '', 'string');

		// fetch dates range

		$filters['start_ts'] = VikRestaurants::createTimestamp($filters['start_day'], 0, 0);
		$filters['end_ts']   = VikRestaurants::createTimestamp($filters['end_day'], 23, 59);

		if (empty($filters['start_ts']) || $filters['start_ts'] == -1)
		{
			$filters['start_ts'] = VikRestaurants::now();
		}

		if (empty($filters['end_ts']) || $filters['end_ts'] == -1)
		{
			$filters['end_ts'] = VikRestaurants::now();
		}

		if ($filters['start_ts'] >= $filters['end_ts'])
		{
			$arr = getdate();

			$filters['start_ts'] = mktime(0, 0, 0, $arr['mon'], 1, $arr['year']);
			$filters['end_ts']   = mktime(0, 0, 0, $arr['mon'] + 1, 1, $arr['year']) - 1;
		}

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

		$new_type = OrderingManager::getSwitchColumnType('tkstatstocks', $ordering['column'], $ordering['type'], array(1, 2));
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
		JToolbarHelper::title(JText::_('VRTKSTATSTOCKS'), 'vikrestaurants');

		JToolbarHelper::cancel('tkreservation.cancel', JText::_('VRCANCEL'));
	}

	/**
	 * Builds the query that will be used to retrieve total number of used items.
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

		if ($ordering['column'] == 'concat_name')
		{
			// backward compatibility to avoid fatal errors
			$ordering['column'] = 'ename';
		}

		$order = $dbo->qn($ordering['column']) . ' ' . $ordering_dir;

		if ($ordering['column'] == 'products_used')
		{
			// always use ASC direction to keep items with the same value
			// properly sorted according to natual ordering
			$ordering_dir = 'ASC';
		}

		$order .= ', ' . $dbo->qn('m.ordering') . ' ' . $ordering_dir;
		$order .= ', ' . $dbo->qn('e.ordering') . ' ' . $ordering_dir;
		$order .= ', ' . $dbo->qn('o.ordering') . ' ' . $ordering_dir;

		return "SELECT SQL_CALC_FOUND_ROWS `e`.`id` AS `eid`, `e`.`name` AS `ename`, `o`.`id` AS `oid`, `o`.`name` AS `oname`,
			IF(`o`.`id` IS NULL, 
				(
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							WHERE
								(`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING')
								AND `r`.`checkin_ts` BETWEEN {$filters['start_ts']} AND {$filters['end_ts']}
								AND `i`.`id_product` = `e`.`id`
								AND `o`.`id` IS NULL
						), 0
					)
				), (
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							WHERE
								(`r`.`status`='CONFIRMED' OR `r`.`status` = 'PENDING')
								AND `r`.`checkin_ts` BETWEEN {$filters['start_ts']} AND {$filters['end_ts']}
								AND `i`.`id_product` = `e`.`id`
								AND `i`.`id_product_option` = `o`.`id`
						), 0
					)
				)
			) AS `products_used`
			FROM `#__vikrestaurants_takeaway_menus_entry` AS `e`
			LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `o` ON `e`.`id` = `o`.`id_takeaway_menu_entry`
			LEFT JOIN `#__vikrestaurants_takeaway_menus` AS `m` ON `m`.`id` = `e`.`id_takeaway_menu`
			WHERE 1 $where
			HAVING `products_used` > 0
			ORDER BY $order";
	}
}
