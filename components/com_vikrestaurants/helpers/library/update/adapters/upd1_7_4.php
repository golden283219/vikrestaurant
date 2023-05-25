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

/**
 * Update adapter for com_vikrestaurants 1.7.4 version.
 *
 * This class can include update() and finalise().
 *
 * NOTE. do not call exit() or die() because the update won't be finalised correctly.
 * Return false instead to stop in anytime the flow without errors.
 *
 * @since 1.7.4
 */
abstract class VikRestaurantsUpdateAdapter1_7_4
{
	/**
	 * Method run during postflight process.
	 *
	 * @param 	object 	 $parent  The parent that calls this method.
	 *
	 * @return 	boolean  True on success, otherwise false to stop the flow.
	 */
	public static function finalise($parent)
	{
		// adapt rooms canvas
		self::adaptRoomsDesignData();

		// adapt tables canvas
		self::adaptTablesDesignData();

		return true;
	}

	/**
	 * Tries to adapt the design data stored within the rooms records
	 * with the new map framework.
	 *
	 * @return 	void
	 */
	protected static function adaptRoomsDesignData()
	{
		$dbo = JFactory::getDbo();

		// get all rooms

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'image', 'graphics_properties')))
			->from($dbo->qn('#__vikrestaurants_room'));

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no room found
			return;
		}

		$uri = JUri::root() . 'components/com_vikrestaurants/assets/media/';

		// iterate the rooms
		foreach ($dbo->loadObjectList() as $room)
		{
			// decode deprecated graphics properties
			$json = json_decode($room->graphics_properties);

			if (!isset($json->canvas))
			{
				$data = new stdClass;
				
				// adjust canvas to the current structure
				$data->canvas = new stdClass;
				$data->canvas->width  = $json->mapwidth;
				$data->canvas->height = $json->mapheight;

				// adjust background image, if any
				if (!empty($room->image))
				{
					$data->canvas->background  = 'image';
					$data->canvas->bgImage	   = $uri . $room->image;
					$data->canvas->bgImageMode = 'repeat';
				}

				// adjust commands to the current structure
				$data->commands = array();
				$data->commands['UICommandShape'] = new stdClass;
				$data->commands['UICommandShape']->shapeType 			= 'rect';
				$data->commands['UICommandShape']->shapeDefaultBgColor 	= preg_replace("/^#/", '', $json->color);
				$data->commands['UICommandShape']->shapeDefaultWidth 	= $json->minwidth;
				$data->commands['UICommandShape']->shapeDefaultHeight 	= $json->minheight;

				// update graphics properties with new JSON
				$room->graphics_properties = json_encode($data);

				// update room record
				$dbo->updateObject('#__vikrestaurants_room', $room, 'id');
			}
		}
	}

	/**
	 * Tries to adapt the design data stored within the tables records
	 * with the new map framework.
	 *
	 * @return 	void
	 */
	protected static function adaptTablesDesignData()
	{
		$dbo = JFactory::getDbo();

		// get all tables

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'design_data')))
			->from($dbo->qn('#__vikrestaurants_table'));

		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// no table found
			return;
		}

		// iterate the tables
		foreach ($dbo->loadObjectList() as $table)
		{
			// decode deprecated design data
			$json = json_decode($table->design_data);

			if (!isset($json->class))
			{
				$data = new stdClass;

				// adjust appearance
				$data->bgColor 	 = $json->bgcolor;
				$data->posx 	 = (int) $json->pos->left;
				$data->posy 	 = (int) $json->pos->top;
				$data->width 	 = (int) $json->size->width;
				$data->height 	 = (int) $json->size->height;
				$data->rotate 	 = (int) $json->rotation;
				$data->roundness = 0;
				$data->class 	 = 'UIShapeRect';

				// make safe background color
				if ($data->bgColor && preg_match("/^#?[0-9a-f]{6,6}/i", $data->bgColor))
				{
					// strip initial #, if any
					$data->bgColor = preg_replace("/^#/", '', $data->bgColor);
				}
				else
				{
					// use default color
					$data->bgColor = 'a1988d';
				}

				// update design data with new JSON
				$table->design_data = json_encode($data);

				// update table record
				$dbo->updateObject('#__vikrestaurants_table', $table, 'id');
			}
		}
	}
}
