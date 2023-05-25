<?php
/** 
 * @package     VikRestaurants
 * @subpackage  mod_vikrestaurants_takeaway_map
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Form field override to display a custom header.
 *
 * @since 1.0
 */
class JFormFieldVikadmin extends JFormField
{
	/**
	 * The type of the field.
	 * MUST be equals to the definition in the XML file.
	 *
	 * @var string 
	 */
	protected $type = 'vikadmin';

	/**
	 * Build the HTML form field to display.
	 *
	 * @return 	string 	The HTML form field.
	 */
	public function getInput()
	{
		if (defined('JPATH_SITE') && JPATH_SITE !== 'JPATH_SITE')
		{
			// autoload VRE
			$success = require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));
		}
		else
		{
			// WP platform, VRE should be already loaded
			$success = defined('VIKRESTAURANTS_SOFTWARE_VERSION');

			// it might be required to auto-load the HTML helpers, because while accessing the
			// block editor the client always results to be "site"
			JHtml::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');
		}

		if (!$success)
		{
			throw new Exception('VikRestaurants is not installed!', 404);
		}

		// // autoload module helper
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'helper.php';

		$vik = VREApplication::getInstance();

		// // build the URL of the landing page
		$url = $vik->adminUrl('index.php?option=com_vikrestaurants&view=origins');

		// // load list of take-away addresses
		$locations = VikRestaurantsTakeAwayMapHelper::getLocations();

		// // explain where the locations management has been moved
		$message = '<p>' . JText::_('VRTKXMLLOCATIONSDESC') . '</p>';

		if ($locations)
		{
			// create LI tags
			$ul = implode("\n", array_map(function($l)
			{
				return '<li>' . $l->address . '</li>';
			}, $locations));

			// in case of locations, display the available ones
			$message .= '<p>' . JText::_('VRTKXMLLOCATIONSAVAIL') . '</p><ul>' . $ul . '</ul>';
		}

		// add button to reach the management page
		$message .= '<p><a href="' . $url . '" target="_blank" class="btn btn-primary">' . JText::_('VRTKXMLLOCATIONSMANAGE') . '</a>';

		return $vik->alert($message, 'info');
	}
}
