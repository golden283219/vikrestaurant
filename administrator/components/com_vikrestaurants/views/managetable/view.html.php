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
 * VikRestaurants table management view.
 *
 * @since 1.0
 */
class VikRestaurantsViewmanagetable extends JViewVRE
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

		// get rooms

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name')))
			->from($dbo->qn('#__vikrestaurants_room'))
			->order($dbo->qn('ordering') . ' ASC');
		
		$dbo->setQuery($q);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			$app->enqueueMessage(JText::_('VRROOMMISSINGERROR'), 'warning');
			$app->redirect('index.php?option=com_vikrestaurants&task=newroom');
			exit;
		}

		$rooms = $dbo->loadObjectList();
		
		$table = null;
		
		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_table'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$table = $dbo->loadObject();
			}
		}

		if (empty($table))
		{
			$table = (object) $this->getBlankItem($input->getUint('id_room', $rooms[0]->id));
		}

		// use table data stored in user state
		$this->injectUserStateData($table, 'vre.table.data');

		/**
		 * Retrieve the list of tables that can be merged with this one.
		 *
		 * @since 1.8
		 */
		if ($table->id)
		{
			$cluster = VREAvailabilitySearch::getTablesCluster($table->id);
		}
		else
		{
			$cluster = array();
		}

		$alltables = array();

		// get list of tables
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->select($dbo->qn('id_room'))
			->select($dbo->qn('name'))
			->from($dbo->qn('#__vikrestaurants_table'))
			->where($dbo->qn('id') . ' <> ' . $table->id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $t)
			{
				if (!isset($alltables[$t->id_room]))
				{
					$alltables[$t->id_room] = array();
				}

				$alltables[$t->id_room][] = JHtml::_('select.option', $t->id, $t->name);
			}
		}
		
		$this->rooms     = &$rooms;
		$this->table     = &$table;
		$this->cluster   = &$cluster;
		$this->allTables = &$alltables;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @param 	integer  $id_room  The room to pre-select.
	 *
	 * @return 	array 	 A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem($id_room = 0)
	{
		return array(
			'id'           => 0,
			'name'         => '', 
			'min_capacity' => 2, 
			'max_capacity' => 4, 
			'multi_res'    => 0, 
			'published'    => 1, 
			'id_room'      => (int) $id_room,
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITTABLE'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWTABLE'), 'vikrestaurants');
		}

		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('table.save', JText::_('VRSAVE'));
			JToolbarHelper::save('table.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('table.savenew', JText::_('VRSAVEANDNEW'));
		}
		
		JToolbarHelper::cancel('table.cancel', JText::_('VRCANCEL'));
	}
}
