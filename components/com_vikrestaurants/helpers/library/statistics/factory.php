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

VRELoader::import('library.statistics.widget');

/**
 * Factory class used to obtain and instantiate the
 * supported statistics widgets.
 *
 * @since 1.8
 */
abstract class VREStatisticsFactory
{
	/**
	 * Returns an associative array represeting the complete
	 * dashboard of widgets.
	 *
	 * @param 	string   $group     The section to which the reservations
	 * 							    belong ('restaurant' or 'takeaway').
	 * @param 	string 	 $location  The location in which the widgets have
	 * 								been published ('dashboard' or 'statistics').
	 * @param 	mixed 	 $user      The user that configured the widgets. If not
	 * 					            specified, the current one will be used.
	 *
	 * @return 	array 	 An associative array of widgets at the assigned positions.
	 *
	 * @uses 	getActiveWidgets()
	 */
	public static function getDashboard($group, $location = 'statistics', $user = null)
	{
		// get list of active widgets
		$widgets = static::getActiveWidgets($group, $location, $user);

		$dashboard = array();

		// iterate widgets
		foreach ($widgets as $w)
		{
			// check if the position already exists
			if (!isset($dashboard[$w->getPosition()]))
			{
				// create position
				$dashboard[$w->getPosition()] = array();
			}

			// Register widget within the position.
			// Do not use an associative array because the same
			// widget could be used more than once.
			$dashboard[$w->getPosition()][] = $w;
		}

		return $dashboard;
	}

	/**
	 * Returns a list containing all the active widgets.
	 *
	 * @param 	string   $group     The section to which the reservations
	 * 							    belong ('restaurant' or 'takeaway').
	 * @param 	string 	 $location  The location in which the widgets have
	 * 								been published ('dashboard' or 'statistics').
	 * @param 	mixed 	 $user      The user that configured the widgets. If not
	 * 					            specified, the current one will be used.
	 *
	 * @return 	array 	 An array of widgets.
	 *
	 * @uses 	getSupportedWidgets()
	 */
	public static function getActiveWidgets($group, $location = 'statistics', $user = null)
	{
		$dbo = JFactory::getDbo();

		if (!$user instanceof JUser)
		{
			// load specified/current user
			$user = JFactory::getUser($user);
		}

		// get list of active widgets
		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_stats_widget'))
			->where($dbo->qn('group') . ' = ' . $dbo->q($group))
			->where($dbo->qn('location') . ' = ' . $dbo->q($location))
			->where($dbo->qn('id_user') . ' IN (0, ' . (int) $user->id . ')')
			->order($dbo->qn('id_user') . ' DESC')
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// load active widgets
			$widgets = $dbo->loadObjectList();

			/**
			 * In case the first widget is not global, take only
			 * the widgets that are assigned to the specified user.
			 *
			 * @since 1.8.3
			 */
			if ($widgets[0]->id_user > 0)
			{
				// filter the array and exclude widgets with `id_user` equals to "0"
				$widgets = array_values(array_filter($widgets, function($widget)
				{
					return $widget->id_user > 0;
				}));
			}
		}
		else
		{
			// no widgets loaded
			$widgets = array();
		}

		// get all supported widgets
		$supported = static::getSupportedWidgets($group);

		$list = array();

		// iterate active widgets
		foreach ($widgets as $data)
		{
			// make sure the widget is supported
			if (isset($supported[$data->widget]))
			{
				// assign object to a temporary variable
				// to avoid altering the list reference
				$widget = clone $supported[$data->widget];

				// set widget ID
				$widget->setID($data->id);

				// set widget user ID
				$widget->setUserID($data->id_user);

				// set widget title
				$widget->setTitle($data->name);

				// assign the widget to its position
				$widget->setPosition($data->position);

				// set widget size
				$widget->setSize($data->size);

				// Register using the default creation ordering.
				// Do not use an associative array because the same
				// widget could be used more than once.
				$list[] = $widget;
			}
		}

		return $list;
	}

	/**
	 * Returns a list containing all the supported widgets.
	 *
	 * @param 	string   $group   The section to which the reservations
	 * 							  belong ('restaurant' or 'takeaway').
	 *
	 * @return 	array 	 An associative array of widgets.
	 *
	 * @uses 	getInstance()
	 */
	public static function getSupportedWidgets($group)
	{
		$widgets = array();

		// get default widgets
		$files = glob(VRELIB . DIRECTORY_SEPARATOR . 'statistics' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR . '*.php');

		// iterate files
		foreach ($files as $i => $file)
		{
			// keep the filename only
			$files[$i] = basename($file);
		}

		/**
		 * Trigger event to let the plugins register external widgets
		 * to extend the statistics dashboard without editing any core files.
		 *
		 * @return 	array 	A list of supported widgets.
		 *
		 * @since 	1.8
		 */
		$list = VREFactory::getEventDispatcher()->trigger('onFetchSupportedStatisticsWidgets');

		foreach ($list as $chunk)
		{
			// merge default files with specified ones
			$files = array_merge($files, (array) $chunk);
		}

		// iterate files
		foreach ($files as $file)
		{
			try
			{
				// try to instantiate the widget
				$widget = static::getInstance($file, $group);

				// instantiation went fine, make sure the widget
				// supports the requested group
				if ($widget->isSupported($group))
				{
					// group supported, register widget within the list
					$widgets[$widget->getName()] = $widget;
				}
			}
			catch (Exception $e)
			{
				// widget not supported
			}
		}

		// sort widgets alphabetically
		uasort($widgets, function($a, $b)
		{
			// compare as normal strings
			return strcasecmp($a->getTitle(), $b->getTitle());
		});

		return $widgets;
	}

	/**
	 * Returns the statistics widget instance, ready for the usage.
	 * Searches for any arguments set in the request required
	 * by the widget.
	 *
	 * @param 	string 	$widget   The widget name/filename.
	 * @param 	string  $group    The section to which the reservations
	 * 							  belong ('restaurant' or 'takeaway').
	 * @param 	mixed 	$options  Either an array or an object of options to be passed 
	 * 							  to the order instance.
	 *
	 * @return 	VREStatisticsWidget
	 *
	 * @throws 	Exception
	 *
	 * @uses 	getInstance()
	 */
	public static function getWidget($widget, $group, $options = array())
	{
		// get widget first
		$widget = static::getInstance($widget, $group, $options);

		$input = JFactory::getApplication()->input;

		// iterate form arguments
		foreach ($widget->getForm() as $k => $field)
		{
			// use registry for ease of use
			$field = new JRegistry($field);

			// validate field filter
			if ($field->get('multiple') == 1)
			{
				// retrieve an array
				$filter = 'array';
			}
			else
			{
				// retrieve a string otherwise
				$filter = 'string';
			}

			// get value from request
			$value = $input->get($k, null, $filter);

			// cast to integer in case of checkbox, 
			// so that we can use "0" instead of NULL
			if ($field->get('type') == 'checkbox')
			{
				$value = (int) $value;
			}

			// make sure we have a value in the request
			if ($value !== null)
			{
				// Push value within the options.
				// Any value previously set will be overwritten.
				$widget->setOption($k, $value);
			}

			// check if the field was mandatory
			if ($field->get('required') == 1)
			{
				// get value from widget configuration
				$value = $widget->getOption($k, null);

				// make sure the value exists
				if ($value === null || $value === array())
				{
					// missing field, throw exception
					throw new Exception(sprintf('Statistics widget missing required [%s] field', $k), 400);
				}
			}
		}

		return $widget;
	}

	/**
	 * Returns the statistics widget instance.
	 *
	 * @param 	string 	$widget   The widget name/filename.
	 * @param 	string  $group    The section to which the reservations
	 * 							  belong ('restaurant' or 'takeaway').
	 * @param 	mixed 	$options  Either an array or an object of options to be passed 
	 * 							  to the order instance.
	 *
	 * @return 	VREStatisticsWidget
	 *
	 * @throws 	Exception
	 */
	public static function getInstance($widget, $group, $options = array())
	{
		// remove file extension if provided
		$widget = preg_replace("/\.php$/", '', $widget);

		/**
		 * Trigger event to let the plugins include external widgets.
		 * The plugins MUST include the resources needed, otherwise
		 * it wouldn't be possible to instantiate the returned classes.
		 *
		 * @param 	string  $widget  The name of the widget to include.
		 *
		 * @return 	string 	The classname of the widget.
		 *
		 * @since 	1.8
		 */
		$classname = VREFactory::getEventDispatcher()->triggerOnce('onFetchStatisticsWidgetClassname', array($widget));

		if (!$classname)
		{
			// load handler class from default folder
			if (!VRELoader::import('library.statistics.widgets.' . $widget))
			{
				throw new Exception(sprintf('Statistics widget [%s] not found', $widget), 404);
			}

			// create class name
			$classname = 'VREStatisticsWidget' . ucfirst($widget);
		}

		// make sure the class handler exists
		if (!class_exists($classname))
		{
			throw new Exception(sprintf('Statistics widget class [%s] does not exist', $classname), 404);
		}

		// instantiate handler
		$widget = new $classname($group, $options);

		// make sure the widget is a valid instance
		if (!$widget instanceof VREStatisticsWidget)
		{
			throw new Exception(sprintf('Statistics widget class [%s] is not a valid instance', $classname), 500);
		}

		return $widget;
	}

	/**
	 * Returns a list containing all the supported positions in which
	 * the widget can be placed.
	 *
	 * @param 	string   $group     The section to which the reservations
	 * 							    belong ('restaurant' or 'takeaway').
	 * @param 	string 	 $location  The location in which the widgets have
	 * 								been published ('dashboard' or 'statistics').
	 * @param 	mixed 	 $user      The user that configured the widgets. If not
	 * 					            specified, the current one will be used.
	 *
	 * @return 	array 	 A list of positions
	 */
	public static function getSupportedPositions($group = null, $location = null, $user = null)
	{
		$dbo = JFactory::getDbo();

		if (!$user instanceof JUser)
		{
			// load specified/current user
			$user = JFactory::getUser($user);
		}

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('position', 'id_user')))
			->from($dbo->qn('#__vikrestaurants_stats_widget'))
			->group($dbo->qn('position'))
			->group($dbo->qn('id_user'))
			->order($dbo->qn('id_user') . ' DESC')
			->order($dbo->qn('ordering') . ' ASC');

		if ($group)
		{
			$q->where($dbo->qn('group') . ' = ' . $dbo->q($group));
		}

		if ($location)
		{
			$q->where($dbo->qn('location') . ' = ' . $dbo->q($location));
		}

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$widgets = $dbo->loadObjectList();

			/**
			 * In case the first widget is not global, take only
			 * the widgets that are assigned to the specified user.
			 *
			 * @since 1.8.3
			 */
			if ($widgets[0]->id_user > 0)
			{
				// filter the array and exclude widgets with `id_user` equals to "0"
				$widgets = array_values(array_filter($widgets, function($widget)
				{
					return $widget->id_user > 0;
				}));
			}

			// return only the position
			return array_map(function($widget)
			{
				return $widget->position;
			}, $widgets);
		}

		return array();
	}
}
