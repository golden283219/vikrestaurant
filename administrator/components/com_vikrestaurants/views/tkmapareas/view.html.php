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
 * VikRestaurants take-away delivery areas view.
 *
 * @since 1.7
 */
class VikRestaurantsViewtkmapareas extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{	
		VikRestaurants::loadGraphics2D();
		$shapes = VikRestaurants::getAllDeliveryAreas($published = true);
		
		$this->shapes = &$shapes;
		
		// display the template (default.php)
		parent::display($tpl);
	}
}
