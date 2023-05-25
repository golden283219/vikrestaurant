<?php

/**
 * @package     Joomla.Weblinks
 * @subpackage  Webservices.Weblinks
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_weblinks.
 *
 * @since  4.0.0
 */
class PlgWebservicesWeblinks extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_weblinks's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $router->createCRUDRoutes(
            'v1/weblinks',
            'weblinks',
            ['public' => true, 'component' => 'com_weblinks']
        );

        $router->createCRUDRoutes(
            'v1/weblinks/categories',
            'categories',
            ['public' => true, 'component' => 'com_categories', 'extension' => 'com_weblinks']
        );

        $router->createCRUDRoutes(
            'v1/fields/weblinks',
            'fields',
            ['public' => true, 'component' => 'com_fields', 'context' => 'com_weblinks.weblink']
        );

        $router->createCRUDRoutes(
            'v1/fields/groups/weblinks',
            'groups',
            ['public' => true, 'component' => 'com_fields', 'context' => 'com_weblinks.weblink']
        );

        $router->createCRUDRoutes(
            'v1/vikrestaurants/reservation',
            'clients',
            ['public' => true, 'component' => 'com_weblinks']
        );
    }

    protected function save($recordKey = null)
    {
        $data = (array) json_decode($this->input->json->getRaw(), true);

        foreach (FieldsHelper::getFields('com_weblinks.weblink') as $field)
        {
            if (isset($data[$field->name]))
            {
                !isset($data['com_fields']) && $data['com_fields'] = [];

                $data['com_fields'][$field->name] = $data[$field->name];
                unset($data[$field->name]);
            }
        }

        $this->input->set('data', $data);

        return parent::save($recordKey);
    }

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
