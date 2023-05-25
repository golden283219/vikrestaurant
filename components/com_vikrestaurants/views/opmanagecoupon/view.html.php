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
 * VikRestaurants operator coupon management view.
 *
 * @since 1.6
 */
class VikRestaurantsViewopmanagecoupon extends JViewVRE
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
		
		////// LOGIN //////

		// get current operator
		$operator = VikRestaurants::getOperator();
		
		// make sure the user is an operator and it is
		// allowed to access the private area
		$access = $operator && $operator->canLogin() && $operator->canManage('coupon');
		
		if (!$access)
		{
			$itemid = $input->get('Itemid', 0, 'uint');

			$app->enqueueMessage(JText::_('VRLOGINUSERNOTFOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_vikrestaurants&view=oversight' . ($itemid ? '&Itemid=' . $itemid : ''), false));
			exit;
		}
		
		////// MANAGEMENT //////
		
		$ids  = $input->get('cid', array(), 'uint');
		$type = $ids ? 'edit' : 'new';
		
		$coupon = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_coupons'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			if ($operator->get('group') > 0)
			{
				$q->where($dbo->qn('group') . ' = ' . ($operator->get('group') - 1));
			}
			
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
		
		$this->operator = &$operator;
		$this->coupon   = &$coupon;

		VikRestaurants::loadLanguage(JFactory::getLanguage()->getTag(), JPATH_ADMINISTRATOR);

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @param 	mixed 	$operator  The operator instance.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem()
	{
		return array(
			'id'         => 0,
			'code'       => VikRestaurants::generateSerialCode(12, 'coupon'),
			'type'       => 1,
			'percentot'  => 1,
			'value'      => 0.0,
			'datevalid'  => '',
			'minvalue'   => null,
			'group'      => null,
		);
	}
}
