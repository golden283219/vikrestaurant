<?php

/**
 * @package     Joomla.API
 * @subpackage  com_weblinks
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Weblinks\Api\View\Weblinks;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The weblinks view
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
        // 'parent_id',
        // 'level',
        // 'lft',
        // 'rgt',
        // 'alias',
        // 'typeAlias',
        // 'path',
        // 'title',
        // 'note',
        // 'description',
        // 'published',
        // 'checked_out',
        // 'checked_out_time',
        // 'access',
        // 'params',
        // 'metadesc',
        // 'metakey',
        // 'metadata',
        // 'created_user_id',
        // 'created_time',
        // 'created_by_alias',
        // 'modified_user_id',
        // 'modified_time',
        // 'images',
        // 'urls',
        // 'hits',
        // 'language',
        // 'version',
        // 'publish_up',
        // 'publish_down',
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
        // 'title',
        // 'alias',
        // 'note',
        // 'published',
        // 'access',
        // 'description',
        // 'checked_out',
        // 'checked_out_time',
        // 'created_user_id',
        // 'path',
        // 'parent_id',
        // 'level',
        // 'lft',
        // 'rgt',
        // 'language',
        // 'language_title',
        // 'language_image',
        // 'editor',
        // 'author_name',
        // 'access_title',
    ];

    public function displayList(array $items = null)
    {
        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field)
        {
            $this->fieldsToRenderList[] = $field->name;
        }

        return parent::displayList();
    }

    public function displayItem($item = null)
    {
        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field)
        {
            $this->fieldsToRenderItem[] = $field->name;
        }

        return parent::displayItem();
    }

    protected function prepareItem($item)
    {
        foreach (FieldsHelper::getFields('com_weblinks.weblink', $item, true) as $field)
        {
            $item->{$field->name} = isset($field->apivalue) ? $field->apivalue : $field->rawvalue;
        }

        return parent::prepareItem($item);
    }
}
