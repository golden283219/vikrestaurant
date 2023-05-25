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
 * Implement the wizard step used to choose the active sections
 * of the program: restaurant, take-away or both.
 *
 * @since 1.8.3
 */
class VREWizardStepSections extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRE_WIZARD_STEP_SECTIONS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_SECTIONS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-sliders-h"></i>';
	}

	/**
	 * Return the group to which the step belongs.
	 *
	 * @return 	string  The group name.
	 */
	public function getGroup()
	{
		// belongs to GLOBAL group
		return JText::_('VRMENUTITLEHEADER4');
	}

	/**
	 * Implements the step execution.
	 *
	 * @param 	JRegistry  $data  The request data.
	 *
	 * @return 	boolean
	 */
	protected function doExecute($data)
	{
		$config = VREFactory::getConfig();

		// enable restaurant section according to the given value
		$config->set('enablerestaurant', (int) $data->get('restaurant'));
		// enable take-away section according to the given value
		$config->set('enabletakeaway', (int) $data->get('takeaway'));

		return true;
	}

	/**
	 * Checks whether the restaurant has been enabled.
	 *
	 * @return 	boolean  True if enabled, false otherwise
	 */
	public function isRestaurant()
	{
		return VikRestaurants::isRestaurantEnabled();
	}

	/**
	 * Checks whether the take-away has been enabled.
	 *
	 * @return 	boolean  True if enabled, false otherwise
	 */
	public function isTakeAway()
	{
		return VikRestaurants::isTakeAwayEnabled();
	}
}
