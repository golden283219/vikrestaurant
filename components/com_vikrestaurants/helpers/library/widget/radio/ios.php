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

// load parent class
VRELoader::import('library.widget.radio.radio');

/**
 * This class provides the construction of a radio button (toggle) using the Apple iOS style.
 * The radio button built from this class MUST have only 2 states: 1 and 0.
 *
 * @since 	1.7
 * @since 	1.8.2 Renamed from UIRadioIOS
 */
class VRERadioIOS extends VRERadio
{
	/**
	 * Insert a radio button element.
	 * @override
	 *
	 * @param 	object 	$element  The radio button element.
	 *
	 * @return 	self  	This object to support chaining.
	 */
	public function addElement($element)
	{
		if (count(parent::getElements()) < 2)
		{
			parent::addElement($element);
		}

		return $this;
	}

	/**
	 * Adapt the given element.
	 *
	 * @param 	object 	$element 	The radio button element.
	 *
	 * @return 	object 	The adapted element.
	 */
	protected function bind($element)
	{
		if (isset($element->htmlAttr) && stripos($element->htmlAttr, 'onclick') !== false)
		{
			// strip onclick and trim ending double quotes
			$function = rtrim($element->htmlAttr, '"');
			$function = str_replace('onClick="', '', $function);
			$function = str_replace('onclick="', '', $function);

			// the javascript function to call
			$element->htmlAttr = $function;
		}

		if (empty($element->id))
		{
			$element->id = preg_replace("/[^a-zA-Z0-9\-_]+/", '_', parent::getName()) . '1';
		}

		if (!isset($element->value))
		{
			$element->value = 1;
		}

		return $element;
	}

	/**
	 * Call this method to build and return the HTML of the input.
	 *
	 * @return 	string 	The input HTML.
	 */
	public function display()
	{
		$elements = parent::getElements();

		if (count($elements) != 2)
		{
			return '';
		}

		list($yes, $no) = $elements;

		$checked = '';
		if (!empty($yes->checked))
		{
			$checked = 'checked="checked"';
		}

		$onchange = '';
		if (!empty($yes->htmlAttr) || !empty($no->htmlAttr))
		{
			$onchange = 'onchange="jQuery(this).is(\':checked\') ? function(){'.$yes->htmlAttr.'}() : function(){'.$no->htmlAttr.'}()"';
		}

		$html = '<span class="switch-ios">
			<input type="checkbox" name="'.parent::getName().'" value="'.$yes->value.'" id="'.$yes->id.'" class="ios-toggle ios-toggle-round" '.$checked.' '.$onchange.'/>
			<label for="'.$yes->id.'"></label>
		</span>';

		return $html;
	}
}
