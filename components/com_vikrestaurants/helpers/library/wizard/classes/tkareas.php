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
 * Implement the wizard step used to create the
 * available delivery areas.
 *
 * @since 1.8.3
 */
class VREWizardStepTkareas extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRMENUTAKEAWAYDELIVERYAREAS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_TKAREAS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-map-marker-alt"></i>';
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
	 * Returns the completion progress in percentage.
	 *
	 * @return 	integer  The percentage progress (always rounded).
	 */
	public function getProgress()
	{
		$progress = 100;

		if (!$this->getGoogleAK())
		{
			// missing API Key, decrease progress
			$progress -= 50;
		}

		if (!$this->getAreas())
		{
			// missing delivery areas, decrease progress
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
		if ($this->getGoogleAK())
		{
			// point to controller to create a new delivery area
			return '<a href="index.php?option=com_vikrestaurants&task=tkarea.add" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
		}

		// use the default save button otherwise
		return parent::getExecuteButton();
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

		// update configuration value
		$config->set('googleapikey', $data->get('googleapikey'));

		return true;
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

		// make sure the take-away section is enabled
		if ($sections && $sections->isCompleted() && $sections->isTakeAway() == false)
		{
			// take-away disabled, auto-ignore this step
			return true;
		}

		// get services dependency
		$services = $this->getDependency('tkservices');

		// make sure the delivery service is enabled
		if ($services && $services->isCompleted() && $services->isDelivery() == false)
		{
			// delivery service disabled, auto-ignore this step
			return true;
		}

		// otherwise lean on parent method
		return parent::isIgnored();
	}

	/**
	 * Returns a list of created delivery areas.
	 *
	 * @return 	array  A list of delivery areas.
	 */
	public function getAreas()
	{
		static $areas = null;

		// get delivery areas only once
		if (is_null($areas))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('id', 'name', 'published')))
				->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
				->order($dbo->qn('ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$areas = $dbo->loadObjectList();
			}
			else
			{
				$areas = array();
			}
		}

		return $areas;
	}

	/**
	 * Returns the configured Google API Key.
	 *
	 * @return 	string
	 */
	public function getGoogleAK()
	{
		return VREFactory::getConfig()->get('googleapikey');
	}
}
