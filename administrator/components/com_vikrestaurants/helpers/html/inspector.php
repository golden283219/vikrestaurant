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
 * VikRestaurants inspector HTML helper.
 *
 * @since 1.8
 */
abstract class VREHtmlInspector
{
	/**
	 * Renders a HTML inspector.
	 *
	 * @param 	string  $id          The inspector ID.
	 * @param 	array   $attributes  An array of attributes for the inspector.
	 * 								 - title 		An optional inspector title;
	 * 								 - closeButton  True to display the close button;
	 * 								 - keyboard		True to dismiss the inspector by pressing ESC;
	 * 								 - width		The width of the sidebar (in pixel or percentage).
	 * 								 - placement 	Where the inspector should be placed (left or right);
	 * 								 - class 		Either a string or a list of additional classes.
	 * 								 - url 			An optional URL to render the body within a <iframe>;
	 * 								 - footer 		An optional footer to be placed at bottom.
	 * @param 	string  $body 		 An optional HTML string to be placed in the body.
	 *
	 * @return  string  The inspector HTML.
	 *
	 * @uses 	script()
	 */
	public static function render($id, array $attributes = array(), $body = null)
	{
		// raise error in case ID is empty
		if (!$id)
		{
			throw new RuntimeException('Missing Inspector ID', 404);
		}

		$data = array();

		// fetch layout data
		$data['id']          = $id;
		$data['title']       = !empty($attributes['title'])       ? $attributes['title'] : false;
		$data['closeButton'] = !empty($attributes['closeButton']) ? true : false;
		$data['keyboard']    = !empty($attributes['keyboard'])    ? true : false;
		$data['url']         = !empty($attributes['url'])         ? $attributes['url'] : false;
		$data['width']       = !empty($attributes['width'])       ? $attributes['width'] : '';
		$data['placement']   = !empty($attributes['placement'])   ? $attributes['placement'] : 'right';
		$data['class']       = !empty($attributes['class'])       ? (array) $attributes['class'] : array();
		$data['footer']      = !empty($attributes['footer'])      ? $attributes['footer'] : false;
		$data['body']        = (string) $body;

		// santitize width
		if (preg_match("/^[\d.]+$/", (string) $data['width']))
		{
			// append "px" because we received an amount
			$data['width'] .= 'px';
		}

		// append custom class in case of blank template
		if (JFactory::getApplication()->input->get('tmpl') === 'component')
		{
			$data['class'][] = 'blank-template';
		}

		// join the classes
		$data['class'] = implode(' ', $data['class']);

		// create layout
		$layout = new JLayoutFile('inspector.sidebar');

		// return HTML of the field
		return $layout->render($data);
	}
}
