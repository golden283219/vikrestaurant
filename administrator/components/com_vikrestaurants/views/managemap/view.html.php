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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants map managment view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagemap extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();

		$canvasData = array();
		
		// set the toolbar
		$this->addToolBar();

		// disable platform main menu
		$input->set('hidemainmenu', 1);
		
		$id = $input->get('selectedroom', 0, 'uint');
		
		$allRoomTables = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_room'))
			->where($dbo->qn('id') . ' = ' . $id);
		
		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			$app->redirect('index.php?option=com_vikrestaurants&view=maps');
			exit;
		}

		$selectedRoom = $dbo->loadAssoc();

		/**
		 * Backward compatibility. Make sure that the design
		 * data stored within the room record are compatible
		 * with the new map framework. Otherwise, try to adapt it.
		 *
		 * @since 1.7.4
		 */

		$json = json_decode($selectedRoom['graphics_properties'], true);

		if (!isset($json['canvas']))
		{
			$reg = new JRegistry($json);

			// adjust canvas to the current structure
			$canvasData['canvas'] = array();
			$canvasData['canvas']['width']  = $reg->get('mapwidth');
			$canvasData['canvas']['height'] = $reg->get('mapheight');

			if (!empty($selectedRoom['image']))
			{
				$canvasData['canvas']['background']  = 'image';
				$canvasData['canvas']['bgImage']	 = VREMEDIA_URI . $selectedRoom['image'];
				$canvasData['canvas']['bgImageMode'] = 'repeat';
			}

			// adjust commands to the current structure
			$canvasData['commands'] = array();
			$canvasData['commands']['UICommandShape'] = array(
				'shapeType' 			=> 'rect',
				'shapeDefaultBgColor' 	=> preg_replace("/^#/", '', $reg->get('color')),
				'shapeDefaultWidth' 	=> $reg->get('minwidth'),
				'shapeDefaultHeight' 	=> $reg->get('minheight'),
			);
		}
		else
		{
			// otherwise use current data
			$canvasData = $json;

			// always overwrite background image, if any
			if (!empty($selectedRoom['image']))
			{
				$canvasData['canvas']['bgImage'] = VREMEDIA_URI . $selectedRoom['image'];
			}
		}

		// get room tables
		
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id_room') . ' = ' . $id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$allRoomTables = $dbo->loadAssocList();
		}

		$canvasData['shapes'] = array();

		foreach ($allRoomTables as &$table)
		{
			/**
			 * Backward compatibility. Make sure that the design
			 * data stored within the table record are compatible
			 * with the new map framework. Otherwise, try to adapt it.
			 *
			 * @since 1.7.4
			 */
			$json = json_decode($table['design_data'], true);

			// check for a specific class
			if (empty($json['class']))
			{
				// adapt JSON array to the new structure
				$json = array(
					'bgColor'	=> $json['bgcolor'],
					'posx' 		=> (int) $json['pos']['left'],
					'posy' 		=> (int) $json['pos']['top'],
					'width'		=> (int) $json['size']['width'],
					'height'	=> (int) $json['size']['height'],
					'rotate' 	=> (int) $json['rotation'],
					'roundness' => 0,
					'class' 	=> 'UIShapeRect',
				);
			}

			// append table information
			$json['tableId'] 			= (int) $table['id'];
			$json['tableName'] 			= $table['name'];
			$json['tableMinCapacity'] 	= (int) $table['min_capacity'];
			$json['tableMaxCapacity'] 	= (int) $table['max_capacity'];
			$json['tableCanBeShared'] 	= (int) $table['multi_res'];
			$json['tablePublished']		= (int) $table['published'];

			// push table array within pool
			$canvasData['shapes'][] = $json;
		}

		// encode canvas data
		$canvasData = json_encode($canvasData);

		/**
		 * Get all media.
		 *
		 * @since 1.7.4
		 */
		$media = RestaurantsHelper::getAllMedia();

		$mediaURI = array();

		// exclude images that have a width lower than 64px
		$media = array_filter($media, function($path) use (&$mediaURI)
		{
			// get file properties
			$details = RestaurantsHelper::getFileProperties($path);

			// take only the images wider than 64 pixel
			if ((int) @$details['width'] >= 64)
			{
				// keep media URI
				$mediaURI[] = $details['uri'];

				return $path;
			}

			// exclude image if not supported
			return false;
		});

		// reset keys
		$media = array_values($media);

		// create media manager
		$media_manager = new MediaManagerHTML($media, '', null, 'vre');

		// debug
		$debug = false;
		
		$this->room   	 	= &$selectedRoom;
		$this->tables 	 	= &$allRoomTables;
		$this->canvasData 	= &$canvasData;
		$this->mediaList 	= &$mediaURI;
		$this->mediaManager = &$media_manager;
		$this->debug 		= &$debug;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar. 
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEEDITMAP'), 'vikrestaurants');
		
		JToolbarHelper::cancel('map.cancel', JText::_('VRCANCEL'));
	}
}
