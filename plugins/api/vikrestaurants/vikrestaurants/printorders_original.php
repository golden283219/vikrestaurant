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
class VikRestaurantsApiResourcePrintOrders extends ApiResource
{
    public function __construct( &$ubject, $config = array()) {
        parent::__construct( $ubject, $config = array() );

//        $mainframe =&JFactory::getApplication('site');
//        $mainframe->initialise();
//
//        $lang =&JFactory::getLanguage();
//        $lang->load('com_vikrestaurants',JPATH_ROOT);
    }

    /**
     * Function get for print order.
     *
     * @return void
     */
    public function get()
    {
        // parse input data
        $input 	= JFactory::getApplication()->input;

        $print_orders_attr = $input->get('printorders', VikRestaurants::getPrintOrdersText(true), 'array');

        if (!empty($print_orders_attr['update']))
        {
            UIFactory::getConfig()->set('printorderstext', $print_orders_attr);
        }

        $type = $input->get('type', '', 'STRING');
        $ids  = $input->get('cid', array(), 'uint');

        $rows = array();

        foreach ($ids as $id)
        {
            $order = null;

            if ($type == 'restaurant')
            {
                $order = VikRestaurants::fetchOrderDetails($id);

                if ($order !== null)
                {
                    $order['items'] = VikRestaurants::getFoodFromReservation($id);
                }
                // generate formatting string
            }
            else
            {
                $order = VikRestaurants::fetchTakeawayOrderDetails($id);
				$tkmenuids = $input->get('tkmenuids', array(), 'uint');
				
				if (count($tkmenuids) != 0)
				{
					foreach ($order['items'] as $key => $item)
					{
						$found = false;
						foreach ($tkmenuids as $tkmenuid)
						{
							if ($item['id_tkmenu'] == $tkmenuid)
							{
								$found = true;
							}
						}
						if (!$found)
						{
							unset($order['items'][$key]);
						}
					}
				}
				
            }

            if ($order !== null)
            {
                array_push($rows, $order);
            }
        }

        $this->type = &$type;
        $this->rows = &$rows;
        $this->text = &$print_orders_attr;

        ob_start();
        include "print_tmpl.php";
        $output = ob_get_contents();
        ob_end_clean();

        JLog::add($output, JLog::ERROR);

		$this->plugin->setResponse(['output'=>$output]);
    }

}
