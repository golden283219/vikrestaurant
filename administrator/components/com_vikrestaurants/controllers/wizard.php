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

VRELoader::import('library.mvc.controllers.admin');

/**
 * VikRestaurants wizard controller.
 *
 * @since 1.8.2
 */
class VikRestaurantsControllerWizard extends VREControllerAdmin
{
	/**
	 * Task used to dismiss the wizard.
	 *
	 * @return 	void
	 */
	public function done()
	{
		// check user permissions
		if (JFactory::getUser()->authorise('core.admin', 'com_vikrestaurants'))
		{
			// get wizard instance
			$wizard = VREFactory::getWizard();

			// dismiss the wizard
			$wizard->done();
		}
		else
		{
			// not authorised to dismiss the wizard
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// back to dashboard
		$this->cancel();
	}

	/**
	 * Task used to restore the wizard.
	 *
	 * @return 	void
	 */
	public function restore()
	{
		// check user permissions
		if (JFactory::getUser()->authorise('core.admin', 'com_vikrestaurants'))
		{
			// get wizard instance
			$wizard = VREFactory::getWizard();

			// restore the wizard
			$wizard->restore();
		}
		else
		{
			// not authorised to dismiss the wizard
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// back to dashboard
		$this->cancel();
	}

	/**
	 * Redirects the users to the main records list.
	 *
	 * @return 	void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_vikrestaurants');
	}

	/**
	 * AJAX end-point used to process the wizard step.
	 *
	 * @return 	void
	 */
	public function process()
	{
		$this->executeRole('process');
	}

	/**
	 * AJAX end-point used to dismiss a step.
	 *
	 * @return 	void
	 */
	public function dismiss()
	{
		$this->executeRole('dismiss');
	}

	/**
	 * AJAX end-point used to ignore a step.
	 *
	 * @return 	void
	 */
	public function ignore()
	{
		$this->executeRole('ignore');
	}

	/**
	 * Helper method used to execute the given role.
	 *
	 * @return 	void
	 */
	protected function executeRole($role)
	{
		$input = JFactory::getApplication()->input;	
		$user  = JFactory::getUser();

		// check user permissions
		if (!$user->authorise('core.admin', 'com_vikrestaurants'))
		{
			// not authorised, raise AJAX error
			UIErrorFactory::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// instantiate wizard
		$wizard = VREFactory::getWizard();

		// recover step from request
		$id = $input->get('id', '', 'string');

		// find registered step 
		$index = $wizard->indexOf($id);

		if ($index === false)
		{
			// step not found, raise AJAX error
			UIErrorFactory::raiseError(404, sprintf('Wizard step [%s] not found.', $id));
		}

		// get step at index
		$step = $wizard->getStep($index);

		// recover step configuration
		$config = $input->get('wizard', array(), 'array');

		// take only the parameters related to this steo
		$params = isset($config[$id]) ? $config[$id] : array();

		try
		{
			if ($role == 'process')
			{
				// try to execute the step
				$step->execute($params);
			}
			else if ($role == 'ignore')
			{
				// ignore the step
				$step->ignore();
			}
			else if ($role == 'dismiss')
			{
				// dismiss the step
				$step->dismiss();
			}
		}
		catch (Exception $e)
		{
			// raise AJAX error
			UIErrorFactory::raiseError($e->getCode(), $e->getMessage());
		}

		// prepare response
		$response = array();
		$response['steps'] = array();

		// create step layout file
		$layout = new JLayoutFile('wizard.step');

		if ($step->isVisible())
		{
			// reload the layout of the step
			$response['steps'][$id] = $layout->render(array('step' => $step));
		}
		else
		{
			// hide step
			$response['steps'][$id] = false;
		}

		// iterate all the steps
		foreach ($wizard as $dep)
		{
			// check if this step depends on the previous one
			if ($dep !== $step && $dep->hasDependency($step))
			{
				if ($dep->isVisible())
				{
					// reload the layout of the dependency too
					$response['steps'][$dep->getID()] = $layout->render(array('step' => $dep));
				}
				else
				{
					// dependency not visible
					$response['steps'][$dep->getID()] = false;
				}
			}
		}

		// calculate overall progress
		$response['progress'] = $wizard->getProgress();

		echo json_encode($response);
		exit;
	}
}
