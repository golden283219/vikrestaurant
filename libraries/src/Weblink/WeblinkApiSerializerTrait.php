<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Weblink;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for implementing weblinks in an API Serializer
 *
 * @since  4.0.0
 */
trait WeblinkApiSerializerTrait
{
    /**
     * Build weblinks relationship
     *
     * @param   \stdClass  $model  Item model
     *
     * @return  Relationship
     *
     * @since 4.0.0
     */
    public function weblinks($model)
    {
        $resources = [];

        $serializer = new JoomlaSerializer('weblinks');

        foreach ($model->weblinks as $id => $weblinkName) {
            $resources[] = (new Resource($id, $serializer))
                ->addLink('self', Route::link('site', Uri::root() . 'api/index.php/v1/weblinks/' . $id));
        }

        $collection = new Collection($resources, $serializer);

        return new Relationship($collection);
    }
}
