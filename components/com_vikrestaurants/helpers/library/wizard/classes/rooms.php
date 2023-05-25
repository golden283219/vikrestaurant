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
 * Implement the wizard step used to create the rooms
 * of the restaurant.
 *
 * @since 1.8.3
 */
class VREWizardStepRooms extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRMENUROOMS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_ROOMS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-home"></i>';
	}

	/**
	 * Return the group to which the step belongs.
	 *
	 * @return 	string  The group name.
	 */
	public function getGroup()
	{
		// belongs to RESTAURANT group
		return JText::_('VRMENUTITLEHEADER1');
	}

	/**
	 * Checks whether the step has been completed.
	 *
	 * @return 	boolean  True if completed, false otherwise.
	 */
	public function isCompleted()
	{
		// the step is completed after creating at least a room
		return (bool) $this->getRooms();
	}

	/**
	 * Returns the button used to process the step.
	 *
	 * @return 	string  The HTML of the button.
	 */
	public function getExecuteButton()
	{
		// use by default the standard save button
		return '<a href="index.php?option=com_vikrestaurants&task=room.add" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
	}

	/**
	 * Checks whether the step has been ignored.
	 *
	 * @return 	boolean  True if ignored, false otherwise.
	 */
	public function isIgnored()
	{
		// get sections dependency
		$sections = $this->getDependency('sections');

		// make sure the restaurant section is enabled
		if ($sections && $sections->isRestaurant() == false)
		{
			// restaurant disabled, auto-ignore this step
			return true;
		}

		// otherwise lean on parent method
		return parent::isIgnored();
	}

	/**
	 * Returns a list of created rooms.
	 *
	 * @return 	array  A list of rooms.
	 */
	public function getRooms()
	{
		static $rooms = null;

		// get rooms only once
		if (is_null($rooms))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('id', 'name', 'published')))
				->from($dbo->qn('#__vikrestaurants_room'))
				->order($dbo->qn('ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$rooms = $dbo->loadObjectList();
			}
			else
			{
				$rooms = array();
			}
		}

		return $rooms;
	}
}
