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
 * VikRestaurants room closure management view.
 *
 * @since 1.5
 */
class VikRestaurantsViewmanageroomclosure extends JViewVRE
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
		
		$ids  = $input->getUint('cid', array());
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$closure = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_room_closure'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$closure = $dbo->loadObject();
			}
		}

		if (empty($closure))
		{
			$closure = (object) $this->getBlankItem();
		}

		// use closure data stored in user state
		$this->injectUserStateData($closure, 'vre.roomclosure.data');

		// get rooms

		$rooms = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name')))
			->from($dbo->qn('#__vikrestaurants_room'))
			->order($dbo->qn('ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$rooms = $dbo->loadObjectList();
		}
		
		$this->closure = &$closure;
		$this->rooms   = &$rooms;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @return 	array 	 A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem()
	{
		return array(
			'id'         => 0,
			'id_room'    => -1,
			'start_ts'   => '',
			'end_ts'     => '',
			'start_date' => '',
			'end_date'   => '',
			'start_hour' => 0,
			'start_min'  => 0,
			'end_hour'   => 0,
			'end_min'    => 0,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITROOMCLOSURE'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWROOMCLOSURE'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('roomclosure.save', JText::_('VRSAVE'));
			JToolbarHelper::save('roomclosure.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('roomclosure.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('roomclosure.cancel', JText::_('VRCANCEL'));
	}
}
