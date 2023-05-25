<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_trading
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.helper');
jimport('joomla.application.component.model');

require_once JPATH_SITE . '/libraries/src/Filesystem/Folder.php';
require_once JPATH_SITE . '/components/com_vikrestaurants/helpers/library/loader/autoload.php';


/**
 * tkreservation Api.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_api
 *
 * @since       1.0
 */
class VikRestaurantsApiResourceTkmenus extends ApiResource
{
	/**
	 * Function get for tkreservation record.
	 *
	 * @return void
	 */
	public function get()
	{
		$input 	= JFactory::getApplication()->input;
		$id = $input->get('id', '');
		if ($id == '') {
			$dbo = JFactory::getDBO();

			$table = '#__vikrestaurants_takeaway_menus';
			$latest_tk_orders = array();
			$q = "SELECT * FROM `$table`";
	
			$dbo->setQuery($q);
			$dbo->execute();
	
			if( $dbo->getNumRows() > 0 ) {
				$latest_tk_orders = $dbo->loadAssocList();
			}
	
			$cur_ts = VikRestaurants::now();
	
			$result = ['count'=>$totalCount, 'cur_ts'=>$cur_ts, 'data'=>$latest_tk_orders];
			$this->plugin->setResponse($result);
		} else {
			$order = VikRestaurants::fetchTakeawayOrderDetails($id);
			$ids = array();
			foreach($order['items'] as $item) {
				array_push($ids, $item['id_tkmenu']);
			}
			$this->plugin->setResponse(['data' => $ids]);
			// $this->plugin->setResponse(['data' => $order]);
		}
	}
}
