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
 * VikRestaurants take-away menu entry management view.
 *
 * @since 1.5
 */
class VikRestaurantsViewmanagetkentry extends JViewVRE
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

		$id_menu = $input->get('id_takeaway_menu', 0, 'uint');
		
		$entry = null;

		if ($type == 'edit')
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
				->where($dbo->qn('id') . ' = ' . $ids[0]);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$entry = $dbo->loadObject();

				// get variations
				$entry->variations = array();

				$q = $dbo->getQuery(true)
					->select('*')
					->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option'))
					->where($dbo->qn('id_takeaway_menu_entry') . ' = ' . $entry->id)
					->order($dbo->qn('ordering') . ' ASC');

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$entry->variations = $dbo->loadObjectList();
				}

				if ($entry->img_extra)
				{
					// try to decode the images
					$extra = (array) json_decode($entry->img_extra);

					// merge main image with extra images
					$entry->img_path = array_merge(array($entry->img_path), $extra);
				}

				// get attributes
				$entry->attributes = array();

				$q = $dbo->getQuery(true)
					->select($dbo->qn('id_attribute'))
					->from($dbo->qn('#__vikrestaurants_takeaway_menus_attr_assoc'))
					->where($dbo->qn('id_menuentry') . ' = ' . $entry->id);

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					$entry->attributes = array_unique($dbo->loadColumn());
				}

				// get toppings groups
				$entry->groups = array();

				$q = $dbo->getQuery(true)
					->select('g.*')
					->select($dbo->qn('t.name', 'topping_name'))
					->select($dbo->qn('t.ordering', 'topping_ord'))
					->select(array(
						$dbo->qn('a.id_topping'),
						$dbo->qn('a.id', 'topping_group_assoc_id'),
						$dbo->qn('a.rate', 'topping_rate'),
					))
					->from($dbo->qn('#__vikrestaurants_takeaway_entry_group_assoc', 'g'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_group_topping_assoc', 'a') . ' ON ' . $dbo->qn('a.id_group') . ' = ' . $dbo->qn('g.id'))
					->leftjoin($dbo->qn('#__vikrestaurants_takeaway_topping', 't') . ' ON ' . $dbo->qn('a.id_topping') . ' = ' . $dbo->qn('t.id'))
					->where($dbo->qn('g.id_entry') . ' = ' . $entry->id)
					->order($dbo->qn('g.ordering') . ' ASC')
					->order($dbo->qn('a.ordering') . ' ASC');

				$dbo->setQuery($q);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					foreach ($dbo->loadObjectList() as $group)
					{
						if (!isset($entry->groups[$group->id]))
						{
							$group->toppings = array();
							$entry->groups[$group->id] = $group;
						}
						
						if (!empty($group->topping_group_assoc_id))
						{
							$topping = new stdClass;
							$topping->id_assoc = $group->topping_group_assoc_id;
							$topping->id       = $group->id_topping;
							$topping->name     = $group->topping_name;
							$topping->rate     = $group->topping_rate;
							$topping->ordering = $group->topping_ord;

							$entry->groups[$group->id]->toppings[] = $topping;
						}
					}

					// do not use array keys
					$entry->groups = array_values($entry->groups);
				}
			}
		}

		if (empty($entry))
		{
			$entry = (object) $this->getBlankItem($input->get('id_takeaway_menu', 0, 'uint'));
		}

		// use entry data stored in user state
		$this->injectUserStateData($entry, 'vre.tkentry.data');
		
		// get all attributes
		$attributes = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_attribute'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();
		
		if ($dbo->getNumRows())
		{
			$attributes = $dbo->loadObjectList();
		}
		
		// get all toppings
		$toppings = array();

		$q = $dbo->getQuery(true)
			->select('t.*')
			->select($dbo->qn('s.title', 'separator'))
			->from($dbo->qn('#__vikrestaurants_takeaway_topping', 't'))
			->leftjoin($dbo->qn('#__vikrestaurants_takeaway_topping_separator', 's') . ' ON ' . $dbo->qn('t.id_separator') . ' = ' . $dbo->qn('s.id'))
			->order($dbo->qn('s.ordering') . ' ASC')
			->order($dbo->qn('t.ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $topping)
			{
				$topping->id_separator = $topping->id_separator > 0 ? $topping->id_separator : 0;
				$topping->separator    = $topping->separator ? $topping->separator : JText::_('VRTKOTHERSSEPARATOR');

				if (!isset($toppings[$topping->id_separator]))
				{
					$sep = new stdClass;
					$sep->id       = $topping->id_separator;
					$sep->title    = $topping->separator;
					$sep->toppings = array();

					$toppings[$topping->id_separator] = $sep;
				}

				$toppings[$topping->id_separator]->toppings[] = $topping;
			}
		}
				
		$this->entry      = &$entry;
		$this->attributes = &$attributes;
		$this->toppings   = &$toppings;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Returns a blank item.
	 *
	 * @return 	array 	A blank item for new requests.
	 *
	 * @since 	1.8
	 */
	protected function getBlankItem($id_menu = null)
	{
		return array(
			'id'               => 0,
			'name'             => '',
			'alias'            => '',
			'price'            => 0,
			'img_path'         => '',
			'published'        => true,
			'ready'            => false,
			'id_takeaway_menu' => $id_menu,
			'description'      => '',
			'items_in_stock'   => 9999,
			'notify_below'     => 5,
			'variations'       => array(),
			'attributes'       => array(),
			'groups'           => array(),
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
			JToolbarHelper::title(JText::_('VRMAINTITLEEDITTKENTRY'), 'vikrestaurants');
		}
		else
		{
			JToolbarHelper::title(JText::_('VRMAINTITLENEWTKENTRY'), 'vikrestaurants');
		}
		
		$user = JFactory::getUser();
		
		if ($user->authorise('core.edit', 'com_vikrestaurants')
			|| $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('tkentry.save', JText::_('VRSAVE'));
			JToolbarHelper::save('tkentry.saveclose', JText::_('VRSAVEANDCLOSE'));
		}

		if ($user->authorise('core.edit', 'com_vikrestaurants')
			&& $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2new('tkentry.savenew', JText::_('VRSAVEANDNEW'));
		}

		if ($type == 'edit' && $user->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::save2copy('tkentry.savecopy');
		}
		
		JToolbarHelper::cancel('tkentry.cancel', JText::_('VRCANCEL'));
	}
}
