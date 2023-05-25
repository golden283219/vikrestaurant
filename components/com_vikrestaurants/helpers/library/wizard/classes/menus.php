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
 * of the restaurant.
 *
 * @since 1.8.3
 */
class VREWizardStepMenus extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRMENUMENUS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_MENUS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-bars"></i>';
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
		// the step is completed after creating at least a menu,
		// which must be assigned at least to a section
		return (bool) $this->getMenus();
	}

	/**
	 * Returns the button used to process the step.
	 *
	 * @return 	string  The HTML of the button.
	 */
	public function getExecuteButton()
	{
		// use by default the standard save button
		return '<a href="index.php?option=com_vikrestaurants&task=menu.add" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
	}

	/**
	 * Checks whether the specified step can be skipped.
	 * By default, all the steps are mandatory.
	 * 
	 * @return 	boolean  True if skippable, false otherwise.
	 */
	public function canIgnore()
	{
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
				->select($dbo->qn(array('m.id', 'm.name', 'm.published')))
				->from($dbo->qn('#__vikrestaurants_menus', 'm'))
				->from($dbo->qn('#__vikrestaurants_menus_section', 's'))
				->where($dbo->qn('m.id') . ' = ' . $dbo->qn('s.id_menu'))
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
