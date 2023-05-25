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
 * Implement the wizard step used to create the tables
 * of the restaurant.
 *
 * @since 1.8.3
 */
class VREWizardStepTables extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRMENUTABLES');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_TABLES_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-th"></i>';
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
		// the step is completed after creating at least a table
		return (bool) $this->getTables();
	}

	/**
	 * Returns the button used to process the step.
	 *
	 * @return 	string  The HTML of the button.
	 */
	public function getExecuteButton()
	{
		// get room dependency
		$dep = $this->getDependency('rooms');

		if ($dep)
		{
			// get rooms
			$rooms = $dep->getRooms();
		}
		else
		{
			$rooms = array();
		}

		// use by default the standard save button
		return '<a href="index.php?option=com_vikrestaurants&task=map.edit' . ($rooms ? '&selectedroom=' . $rooms[0]->id : '') . '&wizard=1" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
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
	 * Returns a list of created tables.
	 *
	 * @return 	array  A list of tables.
	 */
	public function getTables()
	{
		static $tables = null;

		// get tables only once
		if (is_null($tables))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('name', 'published')))
				->from($dbo->qn('#__vikrestaurants_table'))
				->order($dbo->qn('id') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$tables = $dbo->loadObjectList();
			}
			else
			{
				$tables = array();
			}
		}

		return $tables;
	}
}
