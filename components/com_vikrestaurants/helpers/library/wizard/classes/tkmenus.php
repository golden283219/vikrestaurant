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
 * Implement the wizard step used to create the menus
 * and the products of the take-away.
 *
 * @since 1.8.3
 */
class VREWizardStepTkmenus extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRE_WIZARD_STEP_TKMENUS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_TKMENUS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-pizza-slice"></i>';
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
		// the step is completed after creating at least a menu,
		// which must own at least a product
		foreach ($this->getMenus() as $menu)
		{
			if ($menu->products)
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
		$menus = $this->getMenus();

		if ($menus)
		{
			// create product
			$task = 'tkentry.add&id_takeaway_menu=' . $menus[0]->id;
		}
		else
		{
			// create menu
			$task = 'tkmenu.add';
		}

		// use by default the standard save button
		return '<a href="index.php?option=com_vikrestaurants&task=' . $task . '" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
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
	 * Returns a list of created menus.
	 *
	 * @return 	array  A list of menus.
	 */
	public function getMenus()
	{
		static $menus = null;

		// get menus only once
		if (is_null($menus))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('m.id', 'm.title', 'm.published')))
				->select('COUNT(1) AS ' . $dbo->qn('products'))
				->from($dbo->qn('#__vikrestaurants_takeaway_menus', 'm'))
				->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('m.id') . ' = ' . $dbo->qn('e.id_takeaway_menu'))
				->group($dbo->qn('m.id'))
				->order($dbo->qn('m.ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$menus = $dbo->loadObjectList();
			}
			else
			{
				$menus = array();
			}
		}

		return $menus;
	}
}
