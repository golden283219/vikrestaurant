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
 * Implement the wizard step used to configure the
 * payment gateways.
 *
 * @since 1.8.3
 */
class VREWizardStepPayments extends VREWizardStep
{
	/**
	 * Returns the step title.
	 * Used as a very-short description.
	 *
	 * @return 	string  The step title.
	 */
	public function getTitle()
	{
		return JText::_('VRMENUPAYMENTS');
	}

	/**
	 * Returns the step description.
	 *
	 * @return 	string  The step description.
	 */
	public function getDescription()
	{
		return JText::_('VRE_WIZARD_STEP_PAYMENTS_DESC');
	}

	/**
	 * Returns an optional step icon.
	 *
	 * @return 	string  The step icon.
	 */
	public function getIcon()
	{
		return '<i class="fas fa-credit-card"></i>';
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
	 * Checks whether the step has been completed.
	 *
	 * @return 	boolean  True if completed, false otherwise.
	 */
	public function isCompleted()
	{
		// the step is completed after publishing at least a payment
		foreach ($this->getPayments() as $payment)
		{
			if ($payment->published)
			{
				// payment published
				return true;
			}
		}

		// no published payments
		return false;
	}

	/**
	 * Returns the button used to process the step.
	 *
	 * @return 	string  The HTML of the button.
	 */
	public function getExecuteButton()
	{
		// get payments list
		$payments = $this->getPayments();

		if ($payments)
		{
			// point to the controller for editing an existing payment
			return '<a href="index.php?option=com_vikrestaurants&task=payment.edit&cid[]=' . $payments[0]->id . '" class="btn btn-success">' . JText::_('VREDIT') . '</a>';
		}

		// point to the controller for creating a new payment
		return '<a href="index.php?option=com_vikrestaurants&task=payment.add" class="btn btn-success">' . JText::_('VRNEW') . '</a>';
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
	 * Returns a list of created payments.
	 *
	 * @return 	array  A list of payments.
	 */
	public function getPayments()
	{
		static $payments = null;

		// get payments only once
		if (is_null($payments))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select($dbo->qn(array('id', 'name', 'file', 'group', 'published')))
				->from($dbo->qn('#__vikrestaurants_gpayments'))
				->order($dbo->qn('ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$payments = $dbo->loadObjectList();
			}
			else
			{
				$payments = array();
			}
		}

		return $payments;
	}
}
