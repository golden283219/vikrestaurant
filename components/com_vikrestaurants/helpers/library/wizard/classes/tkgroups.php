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
 * Implement the wizard step used to create the groups
 * ot toppings for take-away products.
 *
 * @since 1.8.3
 */
class VREWizardStepTkgroups extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRE_WIZARD_STEP_TKGROUPS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_TKGROUPS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-layer-group"></i>';
	}

	/**
	 * Return the group to which the step belongs.
	 *
	 * @return 	string  The group name.
	 */
	public function getGroup()
	{
		// belongs to TAKEAWAY group
		return JText::_('VRMENUTITLEHEADER5');
	}

	/**
	 * Checks whether the step has been completed.
	 *
	 * @return 	boolean  True if completed, false otherwise.
	 */
	public function isCompleted()
	{
		// the step is completed after creating at least a group,
		// which must own at least a toppings
		foreach ($this->getToppingsGroups() as $group)
		{
			if ($group->toppings)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the button used to process the step.
	 *
	 * @return 	string  The HTML of the button.
	 */
	public function getExecuteButton()
	{
		// get menus dependency
		$menus = $this->getDependency('tkmenus');

		if ($menus)
		{
			// get menus list
			$menus = $menus->getMenus();

			// go to products list
			$task = '&view=tkproducts&id_takeaway_menu=' . $menus[0]->id;
		}
		else
		{
			$task = '';
		}

		// use by default the standard save button
		return '<a href="index.php?option=com_vikrestaurants' . $task . '" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
	}

	/**
	 * Checks whether the specified step can be skipped.
	 * By default, all the steps are mandatory.
	 * 
	 * @return 	boolean  True if skippable, false otherwise.
	 */
	public function canIgnore()
	{
		// get toppings dependency
		$toppings = $this->getDependency('tktoppings');

		if ($toppings)
		{
			// step can be ignored only in case of no toppings
			return count($toppings->getToppings()) == 0;
		}

		return true;
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

		// make sure the take-away section is enabled
		if ($sections && $sections->isTakeAway() == false)
		{
			// take-away disabled, auto-ignore this step
			return true;
		}

		// otherwise lean on parent method
		return parent::isIgnored();
	}

	/**
	 * Returns a list of created groups.
	 *
	 * @return 	array  A list of groups.
	 */
	public function getToppingsGroups()
	{
		static $groups = null;

		// get groups only once
		if (is_null($groups))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('g.id', 'g.title')))
				->select('COUNT(1) AS ' . $dbo->qn('toppings'))
				->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc', 'g'))
				->leftjoin($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc', 'a') . ' ON ' . $dbo->qn('g.id') . ' = ' . $dbo->qn('a.id_group'))
				->group($dbo->qn('g.id'))
				->order($dbo->qn('g.id') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$groups = $dbo->loadObjectList();
			}
			else
			{
				$groups = array();
			}
		}

		return $groups;
	}
}
