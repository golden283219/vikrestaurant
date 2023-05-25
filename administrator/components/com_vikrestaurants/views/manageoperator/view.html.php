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
 * VikRestaurants operator management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmanageoperator extends JViewVRE
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
		
		$operator = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_operator'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$operator = $dbo->loadObject();

				if ($operator->rooms)
				{
					$operator->rooms = explode(',', $operator->rooms);
				}
				else
				{
					$operator->rooms = array();
				}

				if ($operator->products)
				{
					$operator->products = explode(',', $operator->products);
				}
				else
				{
					$operator->products = array();
				}
			}
		}

		if (empty($operator))
		{
			$operator = (object) $this->getBlankItem();
		}

		// use default empty fields for user account
		$operator->usertype = array();
		$operator->username = '';

		// use operator data stored in user state
		$this->injectUserStateData($operator, 'vre.operator.data');
		
		// fetch users
		$users = VREApplication::getInstance()->getOperatorUsers($operator->jid);
		
		$this->operator = &$operator;
		$this->users    = &$users;

		// display the template
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
	protected function getBlankItem($group = null)
	{
		if (is_null($group))
		{
			$group = RestaurantsHelper::getDefaultGroup(array(1, 2));
		}

		return array(
			'id'                 => 0,
			'code'               => '',
			'firstname'          => '',
			'lastname'           => '',
			'phone_number'       => '',
			'email'              => '',
			'jid'                => '',
			'group'              => 0,
			'can_login'          => 0,
			'keep_track'         => 1,
			'mail_notifications' => 0,
			'allres'             => 0,
			'assign'             => 1,
			'rooms'              => array(),
			'products'           => array(),
			'manage_coupon'      => 0,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITOPERATOR'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWOPERATOR'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('operator.save', JText::_('VRSAVE'));
			JToolbarHelper::save('operator.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('operator.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('operator.cancel', JText::_('VRCANCEL'));
	}
}
