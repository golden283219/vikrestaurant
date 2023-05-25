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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants payment management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagepayment extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		
		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';

		// set the toolbar
		$this->addToolBar($type);
		
		$payment = null;
	
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_gpayments'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$payment = $dbo->loadObject();
			}
		}

		if (empty($payment))
		{
			$payment = (object) $this->getBlankItem();
		}

		// use payment data stored in user state
		$this->injectUserStateData($payment, 'vre.payment.data');

		// get rid of file extension to properly select the correct driver
		$payment->file = preg_replace("/\.php$/", '', $payment->file);

		if ($payment->selfconfirm)
		{
			// always turn on auto-confirm in case of self-confirmation
			$payment->setconfirmed = 1;
		}
		
		$this->payment = &$payment;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @param 	mixed 	$group  The default group to use.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem($group = 0)
	{
		return array(
			'id'           => 0,
			'name'         => '',
			'file'         => '',
			'published'    => 0,
			'note'         => '',
			'prenote'      => '',
			'charge'       => 0.0,
			'percentot'    => 2,
			'setconfirmed' => 0,
			'selfconfirm'  => 0,
			'group'        => $group,
			'icontype'     => 0,
			'icon'         => '',
			'enablecost'   => 0,
			'trust'        => 0,
			'position'     => '',
		);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @param 	string  $type  The view type ('edit' or 'new').
	 *
	 * @return 	void
	 */
	private function addToolBar($type)
	{
		// add menu title and some buttons to the page
		if ($type == 'edit')
		{
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITPAYMENT'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWPAYMENT'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('payment.save', JText::_('VRSAVE'));
			JToolbarHelper::save('payment.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('payment.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('payment.cancel', JText::_('VRCANCEL'));
	}
}
