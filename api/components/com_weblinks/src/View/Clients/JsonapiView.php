<?php

/**
 * @package     Joomla.API
 * @subpackage  com_weblinks
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Api\View\Clients;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The clients view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'id_table',
        'id_payment',
        'coupon_str',
        'checkin_ts',
        'stay_time',
        'people',
        'purchaser_nominative',
        'purchaser_mail',
        'purchaser_phone',
        'purchaser_prefix',
        'purchaser_country',
        'langtag',
        'custom_f',
        'bill_closed',
        'bill_value',
        'deposit',
        'tot_paid',
        'discount_val',
        'tip_amount',
        'status',
        'rescode',
        'arrived',
        'locked_until',
        'sid',
        'notes',
        'created_on',
        'created_by',
        'modified_on',
        'id_user',
        'conf_key',
        'need_notif',
        'id_operator',
        'closure',
        'id_parent',
        'payment_log',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'id_table',
        'id_payment',
        'coupon_str',
        'checkin_ts',
        'stay_time',
        'people',
        'purchaser_nominative',
        'purchaser_mail',
        'purchaser_phone',
        'purchaser_prefix',
        'purchaser_country',
        'langtag',
        'custom_f',
        'bill_closed',
        'bill_value',
        'deposit',
        'tot_paid',
        'discount_val',
        'tip_amount',
        'status',
        'rescode',
        'arrived',
        'locked_until',
        'sid',
        'notes',
        'created_on',
        'created_by',
        'modified_on',
        'id_user',
        'conf_key',
        'need_notif',
        'id_operator',
        'closure',
        'id_parent',
        'payment_log',
    ];
}
