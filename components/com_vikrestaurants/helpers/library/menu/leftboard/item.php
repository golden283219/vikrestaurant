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

VRELoader::import('library.menu.item');

/**
 * Extends the MenuItemShape class to handle a menu item.
 *
 * @since 1.7
 * @since 1.8 Renamed from LeftBoardMenuItem to LeftboardMenuItemShape.
 */
class LeftboardMenuItemShape extends MenuItemShape
{
	/**
	 * @override
	 * Builds and returns the html structure of the menu item.
	 * This method must be implemented to define a specific graphic of the menu item.
	 *
	 * @return  string 		The html of the menu item.
	 */
	public function buildHtml()
	{
		$data = array(
			'selected'  => $this->isSelected(),
			'href'      => $this->getHref(),
			'icon'      => $this->getCustom(),
			'title'     => $this->getTitle(),
		);

		$layout = new JLayoutFile('menu.leftboard.item');

		return $layout->render($data);
	}
}
