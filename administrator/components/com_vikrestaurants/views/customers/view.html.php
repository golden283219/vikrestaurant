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
 * VikRestaurants customers view.
 *
 * @since 1.3
 */
class VikRestaurantsViewcustomers extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$ordering = OrderingManager::getColumnToOrder('customers', 'u.id', 1);

		// set the toolbar
		$this->addToolBar();
		
		$app = JFactory::getApplication();
		$dbo = JFactory::getDbo();
		
		$filters = array();
		$filters['keysearch'] = $app->getUserStateFromRequest('vrcustomers.keysearch', 'keysearch', '', 'string');

		$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
		$lim0 	= $this->getListLimitStart($filters);
		$navbut	= "";

		$rows = array();

		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS `u`.*')
			->from($dbo->qn('#__vikrestaurants_users', 'u'))
			// hide customers with empty name
			->where($dbo->qn('u.billing_name') . ' <> ' . $dbo->q(''))
			->order($dbo->qn($ordering['column']) . ' ' . ($ordering['type'] == 2 ? 'DESC' : 'ASC'));

		// calculate reservations count
		$resCount = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikrestaurants_reservation', 'r'))
			->where($dbo->qn('r.id_user') . ' <> -1')
			->where($dbo->qn('r.id_user') . ' = ' . $dbo->qn('u.id'))
			->where($dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'));

		$q->select('(' . $resCount . ') AS ' . $dbo->qn('rescount'));

		// calculate orders count
		$resCount->clear('from')->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'));

		$q->select('(' . $resCount . ') AS ' . $dbo->qn('ordcount'));

		if ($filters['keysearch'])
		{
			/**
			 * Reverse the search key in order to try finding
			 * users by name even if it was wrote in the opposite way.
			 * If we searched by "John Smith", the system will search
			 * for "Smith John" too.
			 *
			 * @since 1.8
			 */
			$reverse = preg_split("/\s+/", $filters['keysearch']);
			$reverse = array_reverse($reverse);
			$reverse = implode(' ', $reverse);

			$q->andWhere(array(
				$dbo->qn('u.billing_name') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('u.billing_name') . ' LIKE ' . $dbo->q("%{$reverse}%"),
				$dbo->qn('u.billing_mail') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('u.billing_phone') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
				$dbo->qn('u.billing_address') . ' LIKE ' . $dbo->q("%{$filters['keysearch']}%"),
			), 'OR');
		}
		
		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		// assert limit used for list query
		$this->assertListQuery($lim0, $lim);
		
		if ($dbo->getNumRows())
		{
			$rows = $dbo->loadAssocList();
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}
		
		$new_type = OrderingManager::getSwitchColumnType('customers', $ordering['column'], $ordering['type'], array(1, 2));
		$ordering = array($ordering['column'] => $new_type);
		
		$is_sms = $this->isApiSmsConfigured();
		
		$this->rows 		= &$rows;
		$this->navbut 		= &$navbut;
		$this->ordering 	= &$ordering;
		$this->filters 		= &$filters;
		$this->isSms 		= &$is_sms;
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void 	
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWCUSTOMERS'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('customer.add', JText::_('VRNEW'));
			JToolbarHelper::divider();  
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('customer.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'customer.delete', JText::_('VRDELETE'));
		}
	}
	
	/**
	 * Checks whether the SMS provider has been configured.
	 *
	 * @return 	boolean
	 */
	protected function isApiSmsConfigured()
	{
		// first of all, check ACL
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants'))
		{
			return false;
		}

		try
		{
			// try to instantiate the SMS API provider
			$provider = VREApplication::getInstance()->getSmsInstance();
		}
		catch (Exception $e)
		{
			// SMS provider not configured
			return false;
		}

		// provider (probably) configured
		return true;
	}
}
