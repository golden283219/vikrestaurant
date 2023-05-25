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
 * VikRestaurants special day management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagespecialday extends JViewVRE
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
		
		$ids   = $input->get('cid', array(), 'uint');
		$type  = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);
		
		$specialday = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('s.*')
				->from($dbo->qn('#__vikrestaurants_specialdays', 's'))
				->where($dbo->qn('s.id') . ' = ' . $ids[0]);
			
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$specialday = $dbo->loadObject();

				// JSON decode the list of accepted delivery areas
				if ($specialday->delivery_areas)
				{
					$specialday->delivery_areas = (array) json_decode($specialday->delivery_areas);
				}
				else
				{
					$specialday->delivery_areas = array();
				}

				$specialday->menus = array();

				$q = $dbo->getQuery(true)
					->select($dbo->qn('id_menu'))
					->from($dbo->qn('#__vikrestaurants_sd_menus'))
					->where($dbo->qn('id_spday') . ' = ' . $specialday->id);

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$specialday->menus = $dbo->loadColumn();
				}
			}
		}

		if (empty($specialday))
		{
			$specialday = (object) $this->getBlankItem();
		}

		// use special day data stored in user state
		$this->injectUserStateData($specialday, 'vre.specialday.data');

		if ($specialday->images)
		{
			$specialday->images = array_filter(explode(';;', $specialday->images));
		}

		$specialday->working_shifts = array_filter(preg_split("/,\s*/", $specialday->working_shifts));
		$specialday->days_filter    = array_filter(preg_split("/,\s*/", $specialday->days_filter), 'strlen');

		// get restaurant menus

		$rs_menus = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name', 'special_day')))
			->from($dbo->qn('#__vikrestaurants_menus'))
			->order(array(
				$dbo->qn('special_day') . ' DESC',
				$dbo->qn('ordering') . ' ASC',
			));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$rs_menus = $dbo->loadObjectList();
		}

		// get take-away menus

		$tk_menus = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'published')))
			->select($dbo->qn('title', 'name'))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus'))
			// display published menus first
			->order($dbo->qn('published') . ' DESC')
			->order($dbo->qn('ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$tk_menus = $dbo->loadObjectList();
		}	

		// get delivery areas

		$delivery_areas = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name', 'published')))
			->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
			// display published areas first
			->order($dbo->qn('published') . ' DESC')
			->order($dbo->qn('ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$delivery_areas = $dbo->loadObjectList();
		}
		
		$this->specialday      = &$specialday;
		$this->restaurantMenus = &$rs_menus;
		$this->takeawayMenus   = &$tk_menus;
		$this->deliveryAreas   = &$delivery_areas;

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
		$config = VREFactory::getConfig();

		/**
		 * The default group should be taken only in case 
		 * there is no selected group in request.
		 *
		 * @since 1.7.4
		 */
		if (is_null($group))
		{
			$group = RestaurantsHelper::getDefaultGroup(array(1, 2));
		}

		return array(
			'id'                => 0,
			'name'              => '',
			'start_ts'          => '',
			'end_ts'            => '',
			'working_shifts'    => '',
			'days_filter'       => '',
			'markoncal'         => 0,
			'ignoreclosingdays' => 1,
			'choosemenu'        => 0,
			'freechoose'        => 1,
			'peopleallowed'     => -1,
			'askdeposit'        => $config->getUint('askdeposit'),
			'depositcost'       => $config->getFloat('resdeposit'),
			'perpersoncost'     => $config->getUint('costperperson'),
			'images'            => array(),
			'priority'          => 1,
			'minorder'          => 0,
			'delivery_service'  => -1,
			'delivery_areas'    => array(),
			'group'             => $group,
			'menus'             => array(),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITSPECIALDAY'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWSPECIALDAY'), 'vikrestaurants');
		}

		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('specialday.save', JText::_('VRSAVE'));
			JToolbarHelper::save('specialday.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('specialday.savenew', JText::_('VRSAVEANDNEW'));
		}	
		
		JToolbarHelper::cancel('specialday.cancel', JText::_('VRCANCEL'));
	}
}
