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
 * VikRestaurants coupon management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagecoupon extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$dbo   = JFactory::getDbo();
		
		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$coupon = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_coupons'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);
			
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$coupon = $dbo->loadObject();
			}
		}

		if (empty($coupon))
		{
			$coupon = (object) $this->getBlankItem();
		}

		// use coupon data stored in user state
		$this->injectUserStateData($coupon, 'vre.coupon.data');

		if (is_null($coupon->minvalue))
		{
			$coupon->minvalue = $coupon->group == 0 ? 1 : 0;
		}
		
		$this->coupon = &$coupon;

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
			$group = RestaurantsHelper::getDefaultGroup();
		}

		return array(
			'id'         => 0,
			'code'       => VikRestaurants::generateSerialCode(12, 'coupon'),
			'type'       => 1,
			'percentot'  => 1,
			'value'      => 0.0,
			'datevalid'  => '',
			'minvalue'   => null,
			'usages'     => 0,
			'maxusages'  => 0,
			'maxperuser' => 0,
			'group'      => $group,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITCOUPON'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWCOUPON'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('coupon.save', JText::_('VRSAVE'));
			JToolbarHelper::save('coupon.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('coupon.savenew', JText::_('VRSAVEANDNEW'));
		}

		JToolbarHelper::cancel('coupon.cancel', JText::_('VRCANCEL'));
	}
}
