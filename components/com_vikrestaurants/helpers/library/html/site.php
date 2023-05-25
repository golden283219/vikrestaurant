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
 * VikRestaurants HTML site helper.
 *
 * @since 1.8
 */
abstract class VREHtmlSite
{
	/**
	 * Returns the HTML used to display a dropdown of available times.
	 *
	 * @param 	string   $name     The select name.
	 * @param 	mixed 	 $value    The select value.
	 * @param 	mixed    $options  The times options or the group for which we should
	 * 							   fetch the times.
	 * @param 	array    $attrs    A list of tag attributes.
	 *
	 * @return 	string 	The dropdown HTML.
	 */
	public static function timeselect($name, $value = null, $options = null, array $attrs = array())
	{
		if (!is_array($options))
		{
			// fetch options (treat argument as section group)
			$options = JHtml::_('vikrestaurants.times', (int) $options);
		}

		if ($value)
		{
			// extract hour and minutes from value string
			$value = explode(':', $value);
			// cast values to INT
			$value = array_map('intval', $value);
			// create hour:min string again
			$value = implode(':', $value);
		}

		$args = array(
			'group.items' 	=> null,
			'list.select'	=> $value,
		);

		if (isset($attrs['id']))
		{
			$args['id'] = $attrs['id'];
			unset($attrs['id']);
		}

		$args['list.attr'] = $attrs;

		return JHtml::_('select.groupedList', $options, $name, $args);
	}

	/**
	 * Returns the HTML used to display a dropdown of available times.
	 *
	 * @param 	string  $name   The select name.
	 * @param 	mixed 	$value  The select value.
	 * @param 	array   $attrs  A list of tag attributes.
	 *
	 * @return 	string 	The dropdown HTML.
	 */
	public static function peopleselect($name, $value = null, array $attrs = array())
	{
		if (!empty($attrs['id']))
		{
			$selector = '#' . $attrs['id'];
		}
		else
		{
			$selector = 'select[name="' + $name + '"]';
		}

		// include event to handle "more" option click
		JHtml::_('vrehtml.sitescripts.morepeople', $selector);

		// inject name in attributes
		$attrs['name'] = $name;

		$attrs_str = '';

		// fetch tag attributes
		foreach ($attrs as $k => $v)
		{
			// skip in case of false
			if ($v !== false)
			{
				$attrs_str .= ' ' . $k;

				if ($v !== true)
				{
					$attrs_str .= '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
				}
			}
		}

		// fetch people options list
		$people = JHtml::_('vikrestaurants.people');

		// fetch options HTML (always preselect 2 options, if value is not specified)
		$options = JHtml::_('select.options', $people, 'value', 'text', $value ? $value : 2);

		// build select tag
		return '<select ' . $attrs_str . '>' . $options . '</select>';
	}

	/**
	 * Returns an image rendered by using Google Maps Static API services.
	 * The specified options will render an image of the related map.
	 *
	 * @param 	mixed 	$options  A configuration array.
	 * 							  - center     Either a string or an array (e.g. [lat],[lng]);
	 * 							  - zoom       The zoom level (1-18);
	 * 							  - size       Either a string or an array (e.g. "1200x800");
	 * 							  - markers    Either a string or a list of markers (optional);
	 * 							  - key        An optional Google API Key;
	 * 							  - urlparams  A string to append to the image source;
	 *                            - default    A fallback image in case of loading error;
	 * 							  - imageattr  An associative array of image attributes.
	 *
	 * @return 	string 	The HTML of the image.
	 * 
	 * @link 	https://developers.google.com/maps/documentation/maps-static/intro
	 */
	public static function googlemapsimage($options = array())
	{
		$options = (array) $options;

		// do not proceed in case the Maps Static API is turned off
		if (!VikRestaurants::isGoogleMapsApiEnabled('staticmap'))
		{
			// immediately return default image, if set
			return isset($options['default']) ? $options['default'] : '';
		}

		// do not proceed in case of missing center
		if (!isset($options['center']))
		{
			// immediately return default image, if set
			return isset($options['default']) ? $options['default'] : '';
		}

		// join the values if an array was passed
		if (!is_scalar($options['center']))
		{
			$options['center'] = implode(',', array_values((array) $options['center']));
		}

		// use default zoom if not passed
		if (empty($options['zoom']))
		{
			$options['zoom'] = 15;
		}

		// check if the size was passed
		if (isset($options['size']))
		{
			// the size is not a string
			if (!is_scalar($options['size']))
			{
				// cast property to an array and extract with and height
				$options['size'] = (array) $options['size'];
				$options['size'] = $options['size']['width'] . 'x' . $options['size']['height'];
			}
		}
		else
		{
			// use default size
			$options['size'] = '1280x800';
		}

		// fetch markers list
		if (isset($options['markers']))
		{
			if (!is_scalar($options['markers']))
			{
				$options['markers'] = implode('|', array_values((array) $options['markers']));
			}
		}
		else
		{
			$options['markers'] = $options['center'];
		}

		// use global API key if not specified
		if (!isset($options['key']))
		{
			$options['key'] = VREFactory::getConfig()->get('googleapikey');
		}

		// build URL
		$url = sprintf(
			'https://maps.googleapis.com/maps/api/staticmap?center=%s&zoom=%d&size=%s&markers=%s&key=%s',
			$options['center'],
			$options['zoom'],
			$options['size'],
			$options['markers'],
			$options['key']
		);

		// append URL parameters to image SRC, if any
		if (isset($options['urlparams']))
		{
			$url .= $options['urlparams'];
		}

		// use a fallback image if specified
		if (isset($options['default']))
		{
			$default_img = ' onerror="this.src = \'' . $options['default'] . '\';"';
		}
		else
		{
			$default_img = '';
		}

		$attrs = '';

		// fetch any image attributes
		if (!empty($options['imageattr']))
		{
			foreach ((array) $options['imageattr'] as $k => $v)
			{
				// always skip "src" attribute
				if (strcasecmp($k, 'src'))
				{
					$attrs .= ' ' . $k . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
				}
			}
		}

		// return image HTML
		return '<img src="' . $url . '"' . $default_img . $attrs . ' />';
	}

	/**
	 * Returns the image with the requested flag.
	 *
	 * @param 	string 	$code 	 The county code or a langtag.
	 * @param   array   $config  An array of configuration options.
	 *                           This array can contain a list of key/value pairs where values are boolean.
	 *
	 * @return  string  The HTML image tag.
	 */
	public static function flag($code, $config = array())
	{
		if (preg_match("/^[a-z]{2,3}-([a-z]{2,2})$/i", $code, $match))
		{
			// we have a langtag, find the last match
			$code2 = end($match);
		}
		else
		{
			// use the given code (only 2 chars)
			$code2 = substr($code, 0, 2);
		}

		// find country name
		$country = JHtml::_('vrehtml.countries.withcode', $code2);

		if (!$country)
		{
			// build a list of exceptions
			$unitags = array(
				'AA' => 'Arabic Unitag',
			);

			if (isset($unitags[$code2]))
			{
				$country = $unitags[$code2];
			}
			else
			{
				// country not found, return given code
				return $code;
			}
		}
		else
		{
			// use name only
			$country = $country->name;
		}

		$attrs = array();
		$attrs['src'] 	= VREASSETS_URI . 'css/flags/' . strtolower($code2) . '.png';
		$attrs['title'] = $country . " ($code)";
		$attrs['alt'] 	= $attrs['title'];
		$attrs['style'] = array();
		$attrs['class'] = array('flag');

		if (isset($config['width']))
		{
			$attrs['style'][] = 'width: ' . $config['width'] . (is_int($config['width']) ? 'px' : '') . ';';
		}

		if (isset($config['height']))
		{
			$attrs['style'][] = 'height: ' . $config['height'] . (is_int($config['height']) ? 'px' : '') . ';';
		}

		if (isset($config['class']))
		{
			if (is_array($config['class']))
			{
				$attrs['class'] = array_merge($attrs['class'], $config['class']);
			}
			else
			{
				$attrs['class'][] = $config['class'];
			}
		}

		// make attributes HTML compatible
		foreach ($attrs as $k => &$attr)
		{
			if (is_array($attr))
			{
				$attr = implode(' ', $attr);
			}

			$attr = " {$k}=\"" . htmlspecialchars($attr, ENT_QUOTES, 'UTF-8') . "\"";
		}

		// convert attributes list in HTML string
		$attrs = implode(' ', $attrs);

		// create IMG tag
		return "<img{$attrs}/>";
	}

	/**
	 * Method to sort a column in a grid.
	 *
	 * @param   string  $title          The link title.
	 * @param   string  $order          The order field for the column.
	 * @param   string  $direction      The current direction.
	 * @param   string  $selected       The selected ordering.
	 * @param   string  $task           An optional task override.
	 * @param   string  $new_direction  An optional direction for the new column.
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title.
	 * @param   string  $form           An optional form selector.
	 *
	 * @return  string
	 */
	public static function sort($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc', $tip = '', $form = null)
	{
		// render grid HTML
		$html = JHtml::_('grid.sort', $title, $order, $direction, $selected, $task, $new_direction, $tip, $form);

		// turn off tooltip or popover
		$html = preg_replace("/\bhas(?:Tooltip|Popover)\b/", '', $html);

		// replace IcoMoon with FontAwesome
		$html = preg_replace_callback("/<(?:span|i).*?class=\"([a-zA-Z0-9-_\s]+)\".*?>.*?<\/(?:span|i)>/i", function($match)
		{
			if (preg_match("/\bicon-arrow-up-3\b/", end($match)))
			{
				$icon = 'sort-up';
			}
			else
			{
				$icon = 'sort-down';
			}

			return '<i class="fas fa-' . $icon . '"></i>';
		}, $html);

		return $html;
	}

	/**
	 * Returns the HTML used to display a tag.
	 *
	 * @param 	mixed 	$tag    Either a ID or a tag name or the object/array itself.
	 * @param 	array 	$attrs  A list of tag attributes.
	 *
	 * @return  string  The HTML of the tag.
	 */
	public static function tag($tag, array $attrs = array())
	{
		if (is_scalar($tag))
		{
			$dbo = JFactory::getDbo();

			// recover tag details from database
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_tag'));

			if (preg_match("/^\d+$/", $tag))
			{
				// an ID of the tag was specified
				$q->where($dbo->qn('id') . ' = ' . (int) $tag);
			}
			else
			{
				// the tag name was specified
				$q->where($dbo->qn('name') . ' = ' . $dbo->q($tag));
			}

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows() == 0)
			{
				// tag not found
				return '';
			}

			$tag = $dbo->loadObject();
		}
		else
		{
			// cast to object
			$tag = (object) $tag;
		}

		if (!isset($attrs['style']))
		{
			$attrs['style'] = '';
		}

		// check if the tag owns a color
		if ($tag->color)
		{
			// overwrite default color of the tag
			$attrs['style'] .= 'background-color: #' . $tag->color . ';';

			// check if the tag color is bright or dark
			if (JHtml::_('vrehtml.color.light', $tag->color))
			{
				// we have a light background, so we need to use a darker foreground
				$attrs['style'] .= 'color: #333;';
			}
			else
			{
				// we have a dark background, so we need to use a lighter foreground
				$attrs['style'] .= 'color: #fff;';
			}
		}

		$app = JFactory::getApplication();

		// search in site folder if we are in the back-end
		if ($app->isClient('administrator'))
		{
			$base = VREBASE . DIRECTORY_SEPARATOR . 'layouts';
		}
		else
		{
			$base = null;
		}

		// register script to handle cookie alert
		JHtml::_('vrehtml.scripts.cookiealert');

		// instantiate layout file
		$layout = new JLayoutFile('blocks.badge', $base);

		// build layout data
		$data = array(
			'text'  => $tag->name,
			'type'  => '',
			'attrs' => $attrs,
		);

		// display layout
		return $layout->render($data);
	}
}
