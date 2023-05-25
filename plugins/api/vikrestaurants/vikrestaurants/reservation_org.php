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
class VikRestaurantsApiResourceReservation extends ApiResource
{
	/**
	 * Function get for tkreservation record.
	 *
	 * @return void
	 */
	public function get()
	{
		$input = JFactory::getApplication()->input;

		// query parameters
        $page = $input->get('page', 0, 'INT');
        $pageSize = $input->get('pagesize', 10, 'INT');
        $id = $input->get('id', 0, 'INT');
        $status = $input->get('status', '', 'STRING');
        $need_notif = $input->get('need_notif', -1, 'INT');
        $from = $input->get('from', 0, 'INT');

        $type = $input->get('type', '');

        if ($type == 'restaurant')
        {
            $table = '#__vikrestaurants_reservation';
        }
        else
        {
            $table = '#__vikrestaurants_takeaway_reservation';
        }

        // build where clause
        $where = "where 1 ";
        if($id != 0) {
            $where .= " AND id=$id";
        }
        if($status != '') {
            $where .= " AND status='$status'";
        }
        if($need_notif != -1) {
            $where .= " AND need_notif=$need_notif";
        }
        if($from != 0) {
            $where .= " AND created_on > $from";
        }

        $dbo = JFactory::getDBO();

        // get total count
        $q = "SELECT COUNT(*) 
		FROM `$table` " . $where;

        $dbo->setQuery( $q );
        $totalCount = $dbo->loadResult();

        // get limited records
        $latest_tk_orders = array();
        $q = "SELECT `r`.* 
		FROM `$table` AS `r` " . $where ." 
		ORDER BY `r`.`id` DESC";

        $dbo->setQuery($q, $page * $pageSize, $pageSize);
        $dbo->execute();

        if( $dbo->getNumRows() > 0 ) {
            $latest_tk_orders = $dbo->loadAssocList();
        }

        $cur_ts = VikRestaurants::now();

        $result = ['count'=>$totalCount, 'cur_ts'=>$cur_ts, 'data'=>$latest_tk_orders];
        $this->plugin->setResponse($result);
	}

	public function put()
    {
        $input = JFactory::getApplication()->input;

        // get parameters
        $id = $input->get('id', 0, 'INT');
        $type = $input->get('type', '');
        $action = $input->get('action', '');

        if ($type == 'restaurant')
        {
            $table = '#__vikrestaurants_reservation';
        }
        else
        {
            $table = '#__vikrestaurants_takeaway_reservation';
        }

        $dbo   = JFactory::getDbo();

        if($action == 'confirm') {
            $q = $dbo->getQuery(true)
                ->update($dbo->qn($table))
                ->set($dbo->qn('status') . ' = ' . $dbo->q('CONFIRMED'))
                ->set($dbo->qn('need_notif') . ' = 0')
                ->where($dbo->qn('id') . ' = ' . $id);

            $dbo->setQuery($q);
            $dbo->execute();

            // send notification
            if ($type == 'restaurant')
            {
                // fetch reservation details
                $order_details = VikRestaurants::fetchOrderDetails($id);
                // send e-mail for restaurant reservation
                VikRestaurants::sendCustomerEmail($order_details);
            }
            else
            {
                // fetch order details
                $order_details = VikRestaurants::fetchTakeawayOrderDetails($id);
                // send e-mail for take-away order
                VikRestaurants::sendCustomerEmailTakeAway($order_details);
            }

            $this->plugin->setResponse(['success'=>1]);
        } else if($action == 'printed') {
            // the record doesn't need to be notified anymore
            $q = $dbo->getQuery(true)
                ->update($dbo->qn($table))
                ->set($dbo->qn('need_notif') . ' = 2')
                ->where($dbo->qn('id') . ' = ' . $id);

            $dbo->setQuery($q);
            $dbo->execute();

            $this->plugin->setResponse(['success'=>1]);
        } else {
            $this->plugin->setResponse(['success'=>0]);
        }
    }

}
