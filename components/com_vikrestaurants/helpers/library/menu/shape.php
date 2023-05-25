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
 * Class used to represent the shape of a generic menu.
 *
 * @since 	1.7
 * 
 * @see 	MenuAbstractList
 * @see 	SeparatorItemShape
 * @see 	CustomShape
 */
abstract class MenuShape extends MenuAbstractList
{
	/**
	 * Builds and returns the html structure of the menu and its children.
	 *
	 * @return 	string 	The html structure.
	 *
	 * @uses 	buildHtml()
	 * @uses 	SeparatorItemShape::build()
	 * @uses 	CustomShape::buildHtml()
	 */
	public function build()
	{
		$html = "";

		// get the HTML structure from each child of the menu
		foreach ($this->menu as $separator)
		{
			if ($separator instanceof SeparatorItemShape)
			{
				// get the HTML if the child is a separator
				$html .= $separator->build();
			}
			else if ($separator instanceof CustomShape)
			{
				// get the HTML if the child is a custom shape
				$html .= $separator->buildHtml();
			}
		}

		// build the structure of the menu, which will contain the evaluated $html
		return $this->buildHtml($html);
	}

	/**
	 * Builds and returns the html structure of the menu that wraps the children.
	 * This method must be implemented to define a specific graphic of the menu.
	 *
	 * @param 	string 	$html 	The full structure of the children of the menu.
	 *
	 * @return 	string 	The html of the menu.
	 */
	abstract protected function buildHtml($html);

	/**
	 * Builds and returns the html opening that will wrap the body contents.
	 * This html will be displayed after the menu.
	 *
	 * @return 	string 	The body opening html.
	 *
	 * @since 	1.8
	 */
	abstract public function openBody();

	/**
	 * Builds and returns the html closing that will wrap the body contents.
	 * This html will be displayed after the menu and the body opening.
	 *
	 * @return 	string 	The body closing html.
	 *
	 * @since 	1.8
	 */
	abstract public function closeBody();
}
