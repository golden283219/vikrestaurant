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
 * Implement the wizard step used to define the openings.
 *
 * @since 1.8.3
 */
class VREWizardStepOpenings extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRE_WIZARD_STEP_OPENINGS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_OPENINGS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-clock"></i>';
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
	 * Returns the completion progress in percentage.
	 *
	 * @return 	integer  The percentage progress (always rounded).
	 */
	public function getProgress()
	{
		$progress = 100;

		if ($this->needShift('restaurant'))
		{
			// missing shift for restaurant section, decrease progress
			$progress -= 50;
		}

		if ($this->needShift('takeaway'))
		{
			// missing shift for take-away section, decrease progress
			$progress -= 50;
		}

		return $progress;
	}

	/**
	 * Checks whether the step has been completed.
	 *
	 * @return 	boolean  True if completed, false otherwise.
	 */
	public function isCompleted()
	{
		// look for 100% completion progress
		return $this->getProgress() == 100;
	}

	/**
	 * Returns the button used to process the step.
	 *
	 * @return 	string  The HTML of the button.
	 */
	public function getExecuteButton()
	{
		if ($this->needShift('restaurant'))
		{
			$group = 1;
		}
		else if ($this->needShift('takeaway'))
		{
			$group = 2;
		}
		else
		{
			$group = null;
		}

		// use by default the standard save button
		return '<a href="index.php?option=com_vikrestaurants&task=shift.add' . ($group ? '&group=' . $group : '') . '" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
	}

	/**
	 * Returns a list of created shifts.
	 *
	 * @return 	array  A list of shifts.
	 */
	public function getShifts()
	{
		static $shifts = null;

		// get shifts only once
		if (is_null($shifts))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('name', 'group')))
				->from($dbo->qn('#__vikrestaurants_shifts'))
				->order($dbo->qn('id') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$shifts = $dbo->loadObjectList();
			}
			else
			{
				$shifts = array();
			}
		}

		return $shifts;
	}

	/**
	 * Checks whether the specified group needs the
	 * creation of a working shift.
	 *
	 * @param 	mixed    $group  Either the group ID or its alias.
	 *
	 * @return 	boolean  True if a shift is needed, false otherwise.
	 */
	public function needShift($group)
	{
		// the step is completed after creating at least a shift
		// for each active section
		$groups = array_map(function($shift)
		{
			return $shift->group;
		}, $this->getShifts());

		$lookup = array(
			'restaurant' => 1,
			'takeaway'   => 2,
		);

		// try to route alias
		$group = isset($lookup[$group]) ? $lookup[$group] : $group;

		// get sections dependency
		$sections = $this->getDependency('sections');

		// check if the group is enabled
		switch ($group)
		{
			case 1:
				$enabled = $sections && $sections->isRestaurant();
				break;

			case 2:
				$enabled = $sections && $sections->isTakeAway();
				break;

			default:
				$enabled = false;
		}

		// in case the group is active, check whether the list
		// contains at list a shift for the specified group
		return $enabled && !in_array($group, $groups);
	}
}
