<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_items
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for VikRestaurants Items module.
 *
 * @since 1.2
 */
class VikRestaurantsItemsHelper
{	
	/**
	 * Return the products selected from the customer.
	 *
	 * @param 	JRegistry 	$param 	The configuration object.
	 *
	 * @return 	array 	The products list.
	 *
	 * @since 	1.1 	Renamed by getProduct()
	 */
	public static function getProducts($params)
	{	
		$dbo = JFactory::getDbo();
		
		$ids = $params->get('product');

		if (!count($ids))
		{
			return array();
		}

		$q = $dbo->getQuery(true);

		$q->select('*')
			->from($dbo->qn('#__vikrestaurants_section_product'))
			->where($dbo->qn('id') . ' IN (' . implode(',', array_map('intval', $ids)) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return array();
		}
		
		$rows = $dbo->loadAssocList();

		/**
		 * Provides items translations.
		 *
		 * @since 1.3
		 */
		VikRestaurants::translateMenusProducts($rows);

		// shuffle rows
		shuffle($rows);
		return $rows;
	}	
}
