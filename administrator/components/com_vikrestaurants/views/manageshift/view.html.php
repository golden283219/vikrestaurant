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
 * VikRestaurants shift management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanageshift extends JViewVRE
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
		
		$ids  = $input->getUint('cid', array());
		$type = $ids ? 'edit' : 'new';
		
		// set the toolbar
		$this->addToolBar($type);

		$shift = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_shifts'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$shift = $dbo->loadObject();

				if (strlen($shift->days))
				{
					$shift->days = preg_split("/\s*,\s*/", $shift->days);
				}
				else
				{
					$shift->days = range(0, 6);
				}
			}
		}

		if (empty($shift))
		{
			$shift = (object) $this->getBlankItem();
		}

		// use shift data stored in user state
		$this->injectUserStateData($shift, 'vre.shift.data');
		
		$this->shift = &$shift;

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
			'id'        => 0,
			'name'      => '',
			'from'      => 720,
			'to'        => 1380,
			'group'     => $group,
			'showlabel' => 1,
			'label'     => '',
			'days'      => range(0, 6),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITSHIFT'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWSHIFT'), 'vikrestaurants');
		}

		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('shift.save', JText::_('VRSAVE'));
			JToolbarHelper::save('shift.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('shift.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('shift.cancel', JText::_('VRCANCEL'));
	}
}
