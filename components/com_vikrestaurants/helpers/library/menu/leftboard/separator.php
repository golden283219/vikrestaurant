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

VRELoader::import('library.menu.separator');

/**
 * Extends the SeparatorItemShape class to handle a menu group.
 *
 * @since 1.7
 * @since 1.8 Renamed from LeftBoardMenuSeparator to LeftboardSeparatorItemShape.
 */
class LeftboardSeparatorItemShape extends SeparatorItemShape
{
	/**
	 * @override
	 * Builds and returns the html structure of the separator that wraps the children.
	 * This method must be implemented to define a specific graphic of the separator.
	 *
	 * @param 	string 	$html 	The full structure of the children of the separator.
	 *
	 * @return 	string 	The html of the separator.
	 */
	protected function buildHtml($html)
	{
		$data = array(
			'selected'  => $this->isSelected(),
			'collapsed' => $this->isCollapsed(),
			'href'      => $this->getHref(),
			'icon'      => $this->getCustom(),
			'title'     => $this->getTitle(),
			'children'  => $this->children(),
			'html'      => $html,
		);

		$layout = new JLayoutFile('menu.leftboard.separator');

		return $layout->render($data);
	}
}
