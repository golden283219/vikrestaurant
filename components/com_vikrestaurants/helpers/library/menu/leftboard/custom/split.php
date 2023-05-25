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

VRELoader::import('library.menu.custom');

/**
 * Extends the CustomShape class to display a button to collapse the menu.
 *
 * @since 1.7
 * @since 1.8 Renamed from LeftBoardMenuSplit to LeftboardCustomShapeSplit.
 */
class LeftboardCustomShapeSplit extends CustomShape
{
	/**
	 * @override
	 * Builds and returns the html structure of the custom menu item.
	 * This method must be implemented to define a specific graphic of the custom item.
	 *
	 * @return 	string 	The html of the custom item.
	 */
	public function buildHtml()
	{
		$layout = new JLayoutFile('menu.leftboard.custom.split');

		return $layout->render();
	}
}
