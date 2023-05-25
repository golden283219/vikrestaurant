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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants map (room) controller.
 *
 * @since 1.8
 */
class VikRestaurantsControllerMap extends VREControllerAdmin
{
	/**
	 * Task used to access the management page of an existing record.
	 *
	 * @return 	boolean
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// check user permissions
		if (!JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			// back to main list, not authorised to edit records
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->cancel();

			return false;
		}

		$id = $app->input->getUint('selectedroom', 0);

		$this->setRedirect('index.php?option=com_vikrestaurants&view=managemap&selectedroom=' . $id);

		return true;
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the main list.
	 *
	 * @return 	void
	 */
	public function saveclose()
	{
		if ($this->save())
		{
			$this->setRedirect('index.php?option=com_vikrestaurants&view=maps');
		}
	}

	/**
	 * Task used to save the record data set in the request.
	 * After saving, the user is redirected to the management
	 * page of the record that has been saved.
	 *
	 * @return 	boolean
	 */
	public function save()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		$user  = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.edit', 'com_vikrestaurants'))
		{
			// not authorised to edit the map
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$id_room = $input->getUint('id', 0);
		$json    = $input->getString('json', '');

		$response = new stdClass;

		if (!$id_room)
		{
			throw new Exception('Missing room ID', 400);
		}

		$json = json_decode($json);

		// handle shapes
		if (isset($json->shapes))
		{
			$shapes = $json->shapes;
			unset($json->shapes);
		}
		else
		{
			$shapes = array();
		}

		// get all tables ID
		$table_ids = array();

		foreach ($shapes as $shape)
		{
			if (!empty($shape->tableId))
			{
				$table_ids[] = $shape->tableId;
			}
		}

		// Delete all tables (that belong to this room) that 
		// have not been passed through the request. In case a 
		// table is not available here, it means that it has 
		// been deleted from the map.
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id_room') . ' = ' . $id_room);

		if ($table_ids)
		{
			$q->where($dbo->qn('id') . ' NOT IN (' . implode(',', array_map('intval', $table_ids)) . ')');
		}

		$dbo->setQuery($q);
		$dbo->execute();

		$response->deleted = $dbo->getAffectedRows();

		$response->newMap = array();

		// define TABLE-SHAPE lookup
		$lookup = array(
			'id' 			=> 'tableId',
			'name' 			=> 'tableName',
			'min_capacity' 	=> 'tableMinCapacity',
			'max_capacity' 	=> 'tableMaxCapacity',
			'multi_res' 	=> 'tableCanBeShared',
			'published' 	=> 'tablePublished',
		);

		$response->created = 0;
		$response->updated = 0;
		$response->failed  = array();

		$_table = JTable::getInstance('table', 'VRETable');

		// iterate shapes to update tables
		foreach ($shapes as $shape)
		{
			// create table object
			$table = array();
			
			foreach ($lookup as $k => $v)
			{
				$table[$k] = $shape->{$v};

				if ($k != 'name')
				{
					// cast all properties to int (except for name)
					$table[$k] = (int) $table[$k];
				}

				unset($shape->{$v});
			}

			// keep temporary id without saving it
			$tmpId = $shape->tmpId;
			unset($shape->tmpId);

			// inject shape design data
			$table['design_data'] = $shape;

			if ($table['id'] == 0)
			{
				// inject room id for this new table
				$table['id_room'] = $id_room;

				// create new table
				$created = $_table->save($table);
				$table['id'] = $_table->id;

				// update response object
				if ($created)
				{
					$response->created++;

					// create assoc between temporary ID and new ID
					$response->newMap[$tmpId] = $table['id'];
				}
				else
				{
					$response->failed[] = $table;
				}
			}
			else
			{
				// update existing table
				$_table->save($table);

				// update response object
				$response->updated++;
			}

			$_table->reset();
		}

		$room = array();
		$room['id'] = $id_room;

		// update canvas
		if (isset($json->canvas))
		{
			// if we have a background, update the room image too
			if (@$json->canvas->background == 'image' && !empty($json->canvas->bgImage))
			{
				// use given URI
				$room['image'] = $json->canvas->bgImage;
				// keep only image name
				$room['image'] = substr($room['image'], strrpos($room['image'], '/') + 1);
			}
		}

		// inject canvas data
		$room['graphics_properties'] = $json;

		JTable::getInstance('room', 'VRETable')->save($room);

		echo json_encode($response);
		exit;
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants&view=maps');
	}
}
