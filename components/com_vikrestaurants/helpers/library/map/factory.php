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

VRELoader::import('library.map.element');

/**
 * Factory class used to generate a tables map in SVG format.
 * 
 * @since 1.7.4
 */
class VREMapFactory
{
	/**
	 * The map room.
	 *
	 * @var object
	 */
	protected $room = null;

	/**
	 * The map tables.
	 *
	 * @var array
	 */
	protected $tables = array();

	/**
	 * The canvas style attributes.
	 *
	 * @var array
	 */
	protected $style = array();

	/**
	 * Flag used to check whether the interface should
	 * include administration commands.
	 *
	 * @var boolean
	 */
	protected $isAdmin = false;

	/**
	 * The configuration object.
	 *
	 * @var object
	 */
	protected $options;

	/**
	 * The reservation codes list.
	 *
	 * @var array
	 */
	protected $codesList = null;

	/**
	 * Proxy to instantiate a new object.
	 *
	 * @param 	mixed 	$options 	A configuration array/object.
	 *
	 * @return 	VREMapFactory
	 */
	public static function getInstance($options = array())
	{
		return new static($options);
	}

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	$options 	A configuration array/object.
	 */
	public function __construct($options = array())
	{
		$this->options = (object) $options;
	}

	/**
	 * Sets the map room.
	 *
	 * @param 	mixed 	$room 	The room object.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function setRoom($room)
	{
		if (is_array($room) || is_object($room))
		{
			$room = (object) $room;

			if (isset($room->graphics_properties))
			{
				if (is_string($room->graphics_properties))
				{
					// try decoding GP in case of string
					$room->graphics_properties = (object) json_decode($room->graphics_properties);
				}

				// recover canvas properties
				$canvas = isset($room->graphics_properties->canvas) ? $room->graphics_properties->canvas : array();
			}
			else
			{
				// use empty properties
				$canvas = array();
			}

			$this->room = new VREMapElement($canvas);
		}
		else if ($room instanceof VREMapElement)
		{
			$this->room = $room;
		}
		else
		{
			// do nothing
			return $this;
		}

		// set width and height
		$this->room->width  = $this->room->getData('width', 1024);
		$this->room->height = $this->room->getData('height', 1024);

		// get room background
		$background = $this->room->getData('background');

		if ($background == 'color')
		{
			// setup background color
			$this->room->addStyle('background-color', '#' . $this->room->getData('bgColor', 'ffffff'));
		}
		else if ($background == 'image')
		{
			// get image URI
			$imageUri = $this->room->getData('bgImage');

			// setup background image
			$this->room->addStyle('background-image', "url('$imageUri')");

			// fetch background repeat/size
			switch ($this->room->getData('bgImageMode'))
			{
				case 'repeat':
					$this->room->addStyle('background-repeat', 'repeat');
					break;

				case 'repeatx':
					$this->room->addStyle('background-repeat', 'repeat-x');
					break;

				case 'repeaty':
					$this->room->addStyle('background-repeat', 'repeat-y');
					break;

				case 'cover':
					$this->room->addStyle('background-size', 'cover');
					break;
			}
		}

		return $this;
	}

	/**
	 * Sets the map tables.
	 *
	 * @param 	array 	$tables  The tables list.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @uses 	addTable()
	 */
	public function setTables(array $tables)
	{
		foreach ($tables as $table)
		{
			$this->addTable($table);
		}

		return $this;
	}

	/**
	 * Adds a table into the map.
	 *
	 * @param 	mixed 	$table 	The table object.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function addTable($table)
	{
		if (is_array($table) || is_object($table))
		{
			$table = (array) $table;

			if (isset($table['design_data']) && is_string($table['design_data']))
			{
				// try decoding GP in case of string
				$table['design_data'] = (object) json_decode($table['design_data']);
			}

			// merge table properties with design data
			$options = array_merge($table, (array) @$table['design_data']);
			unset($options['design_data']);

			$table = new VREMapElement($options);
		}
		else if (!$table instanceof VREMapElement)
		{
			// do nothing
			return $this;
		}

		// setup table common attributes
		$table->x 		= $table->getData('posx', 0);
		$table->y 		= $table->getData('posy', 0);
		$table->width   = $table->getData('width', 0);
		$table->height  = $table->getData('height', 0);

		$table->transform = sprintf('rotate(%f %f %f)', $table->getData('rotate', 0), $table->x + $table->width / 2, $table->y + $table->height / 2);

		$class = $table->getData('class');

		if ($class == 'UIShapeCircle')
		{
			$table->radius = $table->getData('radius');
			$table->width  = $table->height = $table->radius * 2;
			$table->cx     = $table->x + $table->radius;
			$table->cy     = $table->y + $table->radius;
		}

		if ($class == 'UIShapeRect' || $class == 'UIShapeCircle')
		{
			$table->fill = $table->stroke = '#' . $table->getData('bgColor', 'ffffff');
		}
		else
		{
			$table->fill = $table->stroke = '';
		}

		$table->strokeWidth = 0;

		if ($class == 'UIShapeRect')
		{
			$table->rx = $table->ry = $table->getData('roundness', 0);
		}

		if ($class == 'UIShapeImage')
		{
			$table->href = $table->getData('bgHref', '');
		}

		// push table within the list
		$this->tables[] = $table;
		
		return $this;
	}

	/**
	 * Sets if the map interface should include management commands.
	 *
	 * @param 	boolean  $is 	True or false.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function admin($is = true)
	{
		$this->isAdmin = (bool) $is;

		return $this;
	}

	/**
	 * Builds the SVG map for the specified room and tables.
	 *
	 * @return 	string 	The map HTML string.
	 */
	public function build()
	{
		if (!$this->room instanceof VREMapElement)
		{
			throw new Exception('Missing room object', 400);
		}

		// instantiate svg layouts
		$roomLayout  = new JLayoutFile('map.room');
		$tableLayout = new JLayoutFile('map.table');
		$inspector   = new JLayoutFile('map.inspector');
		
		$shapeLayout = array();
		$shapeLayout['UIShapeRect']   = new JLayoutFile('map.shapes.rect');
		$shapeLayout['UIShapeCircle'] = new JLayoutFile('map.shapes.circle');
		$shapeLayout['UIShapeImage']  = new JLayoutFile('map.shapes.image');

		if (JFactory::getApplication()->isClient('administrator'))
		{
			// load front-end language as it may contain the layout translations
			VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag());

			$path = VREBASE . DIRECTORY_SEPARATOR . 'layouts';

			// in case of back-end, use the site layouts path
			$roomLayout->addIncludePath($path);
			$tableLayout->addIncludePath($path);
			$inspector->addIncludePath($path);

			foreach ($shapeLayout as &$shape)
			{
				$shape->addIncludePath($path);
			}
		}

		// build layout display data
		$data = array(
			'room'   		=> $this->room,
			'tables' 		=> $this->tables,
			'tableLayout'	=> $tableLayout,
			'shapeLayout'	=> $shapeLayout,
			'inspector'		=> $inspector,
			'admin' 		=> $this->isAdmin,
			'options'		=> $this->options,
			'codes' 		=> $this->getReservationCodes(),
		);

		if ($this->isAdmin)
		{
			foreach ($data['tables'] as &$table)
			{
				// check if available
				$available = (bool) $table->getData('available', false);

				if ($available)
				{
					if ((int) $table->getData('occurrency', 0) === 0)
					{
						// table is available
						$color = '27bc6e';
					}
					else
					{
						// shared table is available
						$color = 'e9b82c';
					}
				}
				else
				{
					// table is fully occupied
					$color = 'e94b37';
				}

				$table->strokeWidth = 2;
				$table->stroke 		= '#' . $color;
			}
		}

		/**
		 * Trigger event to allow the plugins to manipulate the display data that will be
		 * used by the layout that renders the map.
		 *
		 * @param 	array 	&$data 	The display data array.
		 *
		 * @return 	void
		 *
		 * @since 	1.7.4
		 */
		VREFactory::getEventDispatcher()->trigger('onBeforeBuildMap', array(&$data));

		// use layout to draw SVG map
		$html = $roomLayout->render($data);

		/**
		 * Trigger event to allow the plugins to manipulate the HTML generated by the
		 * layout that renders the map.
		 *
		 * @param 	string 	&$html 	The rendered HTML.
		 *
		 * @return 	void
		 *
		 * @since 	1.7.4
		 */
		VREFactory::getEventDispatcher()->trigger('onAfterBuildMap', array(&$html));

		return $html;
	}

	/**
	 * Returns the list containing all the available reservation codes.
	 *
	 * @return 	array 	The reservation codest list.
	 */
	protected function getReservationCodes()
	{
		if ($this->codesList === null)
		{
			$dbo = JFactory::getDbo();

			$this->codesList = array();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array(
					'id',
					'code',
					'icon',
				)))
				->from($dbo->qn('#__vikrestaurants_res_code'))
				->where($dbo->qn('type') . ' = 1')
				->order($dbo->qn('ordering') . ' ASC');
			
			$dbo->setQuery($q);
			$dbo->execute();
			
			if ($dbo->getNumRows())
			{
				$this->codesList = $dbo->loadObjectList();
			}
		}

		return $this->codesList;
	}
} 
