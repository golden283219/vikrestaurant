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
 * Implement the wizard step used to choose the active services
 * of the take-away: delivery, pickup or both.
 *
 * @since 1.8.3
 */
class VREWizardStepTkservices extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRMANAGETKRES4');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_TKSERVICES_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-truck fa-flip-horizontal"></i>';
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
	 * Implements the step execution.
	 *
	 * @param 	JRegistry  $data  The request data.
	 *
	 * @return 	boolean
	 */
	protected function doExecute($data)
	{
		$config = VREFactory::getConfig();

		$delivery = (int) $data->get('delivery');
		$pickup   = (int) $data->get('pickup');

		if ($delivery && $pickup)
		{
			// activate both services
			$service = 2;
		}
		else if ($delivery)
		{
			// activate delivery only
			$service = 1;
		}
		else
		{
			// activate pickup only
			$service = 0;
		}

		// update configuration value
		$config->set('deliveryservice', $service);

		return true;
	}

	/**
	 * Checks whether the delivery service has been disabled.
	 *
	 * @return 	boolean  True if enabled, false otherwise.
	 */
	public function isDelivery()
	{
		return VREFactory::getConfig()->getUint('deliveryservice') != 0;
	}

	/**
	 * Checks whether the pickup service has been disabled.
	 *
	 * @return 	boolean  True if enabled, false otherwise.
	 */
	public function isPickup()
	{
		return VREFactory::getConfig()->getUint('deliveryservice') != 1;
	}
}
